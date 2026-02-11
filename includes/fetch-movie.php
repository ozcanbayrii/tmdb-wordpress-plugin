<?php
// tmdb-ozcanwork/includes/fetch-movie.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Fetch_Movie {
    public function __construct() {
        add_action( 'wp_ajax_tmdb_fetch_and_create', array( $this, 'ajax_fetch_and_create' ) );
        add_action( 'wp_ajax_tmdb_get_movie_data', array( $this, 'ajax_get_movie_data' ) );
    }

    /**
     * Editor Meta Box Instant Fetch
     */
    public function ajax_get_movie_data() {
        check_ajax_referer( 'tmdb_fetch_nonce', 'nonce' );

        $movie_id = isset( $_POST['movie_id'] ) ? intval( $_POST['movie_id'] ) : 0;
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'movie';
        
        $api_key = get_option( 'tmdb_api_key' );
        if ( empty( $api_key ) ) {
            wp_send_json_error( 'API Key missing.' );
        }

        $data = $this->get_tmdb_data( $movie_id, $type, $api_key );
        
        if ( is_wp_error( $data ) ) {
            wp_send_json_error( $data->get_error_message() );
        }

        $data = $this->normalize_data( $data, $type );

        wp_send_json_success( $data );
    }

    // ... Batch Fetch (Existing) ...
    public function ajax_fetch_and_create() {
        check_ajax_referer( 'tmdb_fetch_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized.' );
        }

        $movie_id = isset( $_POST['movie_id'] ) ? intval( $_POST['movie_id'] ) : 0;
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'movie';

        if ( ! $movie_id ) {
            wp_send_json_error( 'Invalid ID.' );
        }

        // Duplicate Check
        $existing = new WP_Query( array(
            'meta_key'   => '_tmdb_id',
            'meta_value' => $movie_id,
            'post_type'  => 'post',
            'post_status'=> 'any'
        ));

        if ( $existing->have_posts() ) {
            wp_send_json_error( "ID: $movie_id exists." );
        }

        $api_key = get_option( 'tmdb_api_key' );
        $data = $this->get_tmdb_data( $movie_id, $type, $api_key );

        if ( is_wp_error( $data ) ) {
            wp_send_json_error( $data->get_error_message() );
        }

        $data = $this->normalize_data( $data, $type );
        $post_generator = new TMDB_OzcanWork_Post_Generator();
        $post_id = $post_generator->create_post( $data );

        if ( $post_id ) {
            wp_send_json_success( array( 
                'message' => "($type) $movie_id created!",
                'post_id' => $post_id,
                'edit_link' => get_edit_post_link( $post_id, '' )
            ));
        } else {
            wp_send_json_error( 'Failed to create post.' );
        }
    }

    private function get_tmdb_data( $id, $type, $api_key ) {
        // Dinamik Dil Kodu (tr-TR veya en-US)
        $lang_code = tmdb_get_api_lang_code();
        
        $transient_key = "tmdb_{$type}_{$id}_{$lang_code}";
        $cached = get_transient( $transient_key );

        if ( $cached !== false ) {
            return $cached;
        }

        $endpoint = ($type === 'tv') ? 'tv' : 'movie';
        $url = "https://api.themoviedb.org/3/{$endpoint}/{$id}?api_key={$api_key}&language={$lang_code}&append_to_response=credits,videos";
        
        $response = wp_remote_get( $url );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return new WP_Error( 'api_error', "TMDB API ($type) Error: " . $code );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( empty( $data ) ) {
            return new WP_Error( 'json_error', 'Invalid JSON response.' );
        }

        set_transient( $transient_key, $data, 12 * HOUR_IN_SECONDS );
        return $data;
    }

    private function normalize_data( $data, $type ) {
        $data['media_type'] = $type;
        
        if ( $type === 'tv' ) {
            $data['title'] = isset($data['name']) ? $data['name'] : '';
            $data['release_date'] = isset($data['first_air_date']) ? $data['first_air_date'] : '';
            $data['budget'] = 0; 
            $data['revenue'] = 0;
            // Ensure seasons/episodes exist
            $data['number_of_seasons'] = isset($data['number_of_seasons']) ? $data['number_of_seasons'] : 0;
            $data['number_of_episodes'] = isset($data['number_of_episodes']) ? $data['number_of_episodes'] : 0;
        }
        return $data;
    }
}
