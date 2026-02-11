<?php
// tmdb-ozcanwork/includes/shortcodes.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Shortcodes {
    public function __construct() {
        add_shortcode( 'tmdb_card', array( $this, 'render_tmdb_card' ) );
    }

    public function render_tmdb_card( $atts ) {
        $atts = shortcode_atts( array(
            'id' => '',
            'type' => 'movie', // movie or tv
        ), $atts, 'tmdb_card' );

        if ( empty( $atts['id'] ) ) {
            return '';
        }

        // Fetch Data (Cached)
        $data = tmdb_get_remote_data_for_shortcode( $atts['id'], $atts['type'] );

        if ( ! $data ) {
            return '<div style="padding:10px; border:1px solid red; color:red;">TMDB: Veri bulunamadı (ID: ' . esc_html($atts['id']) . ')</div>';
        }

        // --- Data Preparation ---
        $title = isset($data['title']) ? $data['title'] : (isset($data['name']) ? $data['name'] : '');
        $original_title = isset($data['original_title']) ? $data['original_title'] : (isset($data['original_name']) ? $data['original_name'] : '');
        
        // Year
        $date = isset($data['release_date']) ? $data['release_date'] : (isset($data['first_air_date']) ? $data['first_air_date'] : '');
        $year = substr($date, 0, 4);

        // Runtime / Seasons
        $runtime_display = '';
        if ( $atts['type'] === 'movie' && isset($data['runtime']) && $data['runtime'] > 0 ) {
            $runtime_display = $data['runtime'] . ' dakika';
        } elseif ( $atts['type'] === 'tv' ) {
            if ( isset($data['number_of_seasons']) ) {
                $runtime_display = $data['number_of_seasons'] . ' Sezon';
            }
        }

        // Genres
        $genres_arr = array();
        if ( isset($data['genres']) ) {
            foreach( array_slice($data['genres'], 0, 3) as $g ) $genres_arr[] = $g['name'];
        }
        $genres = implode(', ', $genres_arr);

        // Director (Crew parsing)
        $director = '';
        if ( isset( $data['credits']['crew'] ) ) {
            foreach ( $data['credits']['crew'] as $crew ) {
                if ( isset($crew['job']) && $crew['job'] === 'Director' ) {
                    $director = $crew['name'];
                    break;
                }
            }
        }
        if ( empty($director) && isset($data['created_by']) ) {
             $creators = array_map(function($c) { return $c['name']; }, $data['created_by']);
             $director = implode(', ', $creators); // TV Show Creators
        }

        // Images
        $poster_url = isset($data['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $data['poster_path'] : '';
        $backdrop_url = isset($data['backdrop_path']) ? 'https://image.tmdb.org/t/p/w1280' . $data['backdrop_path'] : '';

        // Overview
        $overview = isset($data['overview']) ? wp_trim_words( $data['overview'], 35, '...' ) : '';

        // --- HTML Output (Tomb Raider Style) ---
        ob_start();
        ?>
        <div class="tmdb-sc-tr-card">
            <?php if($backdrop_url): ?>
                <div class="tmdb-sc-tr-bg" style="background-image: url('<?php echo esc_url($backdrop_url); ?>');"></div>
            <?php endif; ?>

            <div class="tmdb-sc-tr-content">
                <div class="tmdb-sc-tr-header">
                    <?php if($poster_url): ?>
                        <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr($title); ?>" class="tmdb-sc-tr-poster">
                    <?php endif; ?>
                    
                    <div class="tmdb-sc-tr-info">
                        <h2 class="tmdb-sc-tr-title"><?php echo esc_html($title); ?></h2>
                        <div class="tmdb-sc-tr-meta">
                            <?php echo esc_html($year); ?><?php if($director): ?>, <?php echo esc_html($director); ?><?php endif; ?>
                        </div>
                        <div class="tmdb-sc-tr-tags">
                            <?php if($runtime_display): ?>
                                <span class="tmdb-sc-pill"><?php echo esc_html($runtime_display); ?></span>
                            <?php endif; ?>
                            <span><?php echo esc_html($genres); ?></span>
                        </div>
                    </div>
                </div>

                <div class="tmdb-sc-tr-desc">
                    <?php echo esc_html($overview); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
