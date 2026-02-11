<?php
// tmdb-ozcanwork/includes/post-generator.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Post_Generator {
    
    public function create_post( $data ) {
        $post_data = array(
            'post_title'    => sanitize_text_field( $data['title'] ),
            'post_content'  => wp_kses_post( $data['overview'] ),
            'post_status'   => 'draft',
            'post_type'     => 'post',
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        // Save Raw Meta (Backups)
        update_post_meta( $post_id, '_tmdb_id', $data['id'] );
        update_post_meta( $post_id, '_tmdb_data', $data ); 
        update_post_meta( $post_id, '_tmdb_score', $data['vote_average'] );

        // Handle Image
        $poster_att_id = null;
        if ( ! empty( $data['poster_path'] ) ) {
            $image_url = 'https://image.tmdb.org/t/p/original' . $data['poster_path'];
            $poster_att_id = $this->upload_and_attach_image( $image_url, $post_id );
        }

        // Trigger ACF Mapping
        if ( class_exists( 'TMDB_OzcanWork_ACF_Manager' ) ) {
            $acf_manager = new TMDB_OzcanWork_ACF_Manager();
            $acf_manager->map_tmdb_to_acf( $post_id, $data, $poster_att_id );
        }

        return $post_id;
    }

    private function upload_and_attach_image( $image_url, $post_id ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $desc = "TMDB Poster for Post $post_id";
        $media_id = media_sideload_image( $image_url, $post_id, $desc, 'id' );

        if ( ! is_wp_error( $media_id ) ) {
            set_post_thumbnail( $post_id, $media_id );
            return $media_id;
        }

        return null;
    }
}
