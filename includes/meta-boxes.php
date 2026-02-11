<?php
// tmdb-ozcanwork/includes/meta-boxes.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Meta_Boxes {
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_custom_meta_box' ) );
    }

    public function add_custom_meta_box() {
        add_meta_box(
            'tmdb_ozcanwork_meta',
            tmdb__('🎬 TMDB İçerik Yöneticisi', '🎬 TMDB Content Manager'),
            array( $this, 'render_meta_box' ),
            'post',
            'normal', // Side yerine Normal (Ana Sütun)
            'high'    // En üstte çıkması için High
        );
    }

    public function render_meta_box( $post ) {
        // Retrieve existing values
        $tmdb_id = get_post_meta( $post->ID, '_tmdb_id', true );
        $manual_trailer = get_post_meta( $post->ID, '_tmdb_manual_trailer', true );
        
        // Full Data backup
        $data = get_post_meta( $post->ID, '_tmdb_data', true );
        
        // Extract specific fields for the inputs
        $title = isset($data['title']) ? $data['title'] : (isset($data['name']) ? $data['name'] : '');
        $rating = isset($data['vote_average']) ? $data['vote_average'] : '';
        $date = isset($data['release_date']) ? $data['release_date'] : (isset($data['first_air_date']) ? $data['first_air_date'] : '');
        
        // Runtime vs Seasons
        $runtime = isset($data['runtime']) ? $data['runtime'] : '';
        $seasons = isset($data['number_of_seasons']) ? $data['number_of_seasons'] : '';
        $episodes = isset($data['number_of_episodes']) ? $data['number_of_episodes'] : '';
        
        // Type detection
        $type = isset($data['media_type']) ? $data['media_type'] : 'movie';
        
        wp_nonce_field( 'tmdb_ozcanwork_save_meta', 'tmdb_ozcanwork_nonce' );
        ?>
        <div class="tmdb-pro-panel">
            
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px;">
                <!-- Type Switcher -->
                <div class="tmdb-type-switch" style="margin-bottom:0;">
                    <label>
                        <input type="radio" name="tmdb_type_select" value="movie" <?php checked($type, 'movie'); ?>>
                        <span>🎥 <?php echo tmdb__('Film', 'Movie'); ?></span>
                    </label>
                    <label>
                        <input type="radio" name="tmdb_type_select" value="tv" <?php checked($type, 'tv'); ?>>
                        <span>📺 <?php echo tmdb__('Dizi', 'TV Show'); ?></span>
                    </label>
                </div>

                <!-- ID Input & Fetch -->
                <div class="tmdb-input-group main-fetch" style="flex:1; min-width:250px;">
                    <input type="number" id="tmdb_id_field" name="tmdb_id_field" value="<?php echo esc_attr( $tmdb_id ); ?>" placeholder="<?php echo tmdb__('TMDB ID Girin...', 'Enter TMDB ID...'); ?>" />
                    <button type="button" id="tmdb_editor_fetch_btn" class="button button-primary button-large">
                        <span class="dashicons dashicons-download" style="margin-top:5px;"></span> <?php echo tmdb__('Verileri Getir', 'Fetch Data'); ?>
                    </button>
                </div>
            </div>
            
            <div id="tmdb_loading_spinner" style="display:none; color:#666; font-size:11px; margin-top:5px;"><?php echo tmdb__('Veriler çekiliyor...', 'Fetching data...'); ?></div>

            <hr class="tmdb-divider">

            <!-- Data Fields (Grid Layout) -->
            <div class="tmdb-fields-grid">
                
                <div class="field-row full-width">
                    <label><?php echo tmdb__('Başlık', 'Title'); ?></label>
                    <input type="text" id="tmdb_title_input" value="<?php echo esc_attr($title); ?>" readonly class="readonly-input">
                </div>

                <div class="field-row">
                    <label><?php echo tmdb__('Puan', 'Rating'); ?></label>
                    <input type="text" id="tmdb_rating_input" value="<?php echo esc_attr($rating); ?>" readonly class="readonly-input">
                </div>

                <div class="field-row">
                    <label><?php echo tmdb__('Tarih', 'Date'); ?></label>
                    <input type="text" id="tmdb_date_input" value="<?php echo esc_attr($date); ?>" readonly class="readonly-input">
                </div>

                <!-- Movie Field -->
                <div class="field-row movie-only-field" style="<?php echo ($type === 'tv' ? 'display:none' : ''); ?>">
                    <label><?php echo tmdb__('Süre (dk)', 'Runtime (min)'); ?></label>
                    <input type="text" id="tmdb_runtime_input" value="<?php echo esc_attr($runtime); ?>" readonly class="readonly-input">
                </div>

                <!-- TV Fields -->
                <div class="field-row tv-only-field" style="<?php echo ($type === 'movie' ? 'display:none' : ''); ?>">
                    <label><?php echo tmdb__('Sezon Sayısı', 'Seasons'); ?></label>
                    <input type="text" id="tmdb_seasons_input" value="<?php echo esc_attr($seasons); ?>" readonly class="readonly-input">
                </div>
                <div class="field-row tv-only-field" style="<?php echo ($type === 'movie' ? 'display:none' : ''); ?>">
                    <label><?php echo tmdb__('Bölüm Sayısı', 'Episodes'); ?></label>
                    <input type="text" id="tmdb_episodes_input" value="<?php echo esc_attr($episodes); ?>" readonly class="readonly-input">
                </div>

            </div>

            <!-- Manual Trailer -->
            <div class="field-row full-width" style="margin-top:10px;">
                <label><?php echo tmdb__('Özel Fragman ID (Opsiyonel)', 'Custom Trailer ID (Optional)'); ?></label>
                <input type="text" id="tmdb_manual_trailer" name="tmdb_manual_trailer" value="<?php echo esc_attr( $manual_trailer ); ?>" placeholder="Youtube ID" />
            </div>
            
            <!-- Hidden Input for Full JSON (Sent to PHP on save) -->
            <textarea name="tmdb_full_data_json" id="tmdb_full_data_json" style="display:none;"></textarea>
            
            <p class="tmdb-status-msg"></p>

        </div>
        <?php
    }

    public function save_custom_meta_box( $post_id ) {
        if ( ! isset( $_POST['tmdb_ozcanwork_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['tmdb_ozcanwork_nonce'], 'tmdb_ozcanwork_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // 1. Save Basic Meta
        if ( isset( $_POST['tmdb_id_field'] ) ) {
            update_post_meta( $post_id, '_tmdb_id', sanitize_text_field( $_POST['tmdb_id_field'] ) );
        }
        if ( isset( $_POST['tmdb_manual_trailer'] ) ) {
            update_post_meta( $post_id, '_tmdb_manual_trailer', sanitize_text_field( $_POST['tmdb_manual_trailer'] ) );
        }

        // 2. Handle Full Data from JS populate
        if ( isset( $_POST['tmdb_full_data_json'] ) && !empty( $_POST['tmdb_full_data_json'] ) ) {
            $json_data = stripslashes($_POST['tmdb_full_data_json']);
            $data = json_decode($json_data, true);

            if ( $data && is_array($data) ) {
                update_post_meta( $post_id, '_tmdb_data', $data );
                update_post_meta( $post_id, '_tmdb_score', isset($data['vote_average']) ? $data['vote_average'] : 0 );

                // Image handling (if not exists)
                if ( ! has_post_thumbnail( $post_id ) && ! empty( $data['poster_path'] ) ) {
                    $image_url = 'https://image.tmdb.org/t/p/original' . $data['poster_path'];
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    require_once( ABSPATH . 'wp-admin/includes/media.php' );
                    $desc = "TMDB Poster for Post $post_id";
                    $media_id = media_sideload_image( $image_url, $post_id, $desc, 'id' );
                    if ( ! is_wp_error( $media_id ) ) set_post_thumbnail( $post_id, $media_id );
                }

                // ACF Mapping
                if ( class_exists( 'TMDB_OzcanWork_ACF_Manager' ) ) {
                    $poster_id = get_post_thumbnail_id( $post_id );
                    $acf_manager = new TMDB_OzcanWork_ACF_Manager();
                    $acf_manager->map_tmdb_to_acf( $post_id, $data, $poster_id );
                }
            }
        }
    }
}
