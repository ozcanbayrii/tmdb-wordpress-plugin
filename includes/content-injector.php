<?php
// tmdb-ozcanwork/includes/content-injector.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Content_Injector {
    public function __construct() {
        add_filter( 'the_content', array( $this, 'inject_tmdb_content' ) );
    }

    public function inject_tmdb_content( $content ) {
        if ( ! is_singular( 'post' ) ) {
            return $content;
        }

        $position = get_option( 'tmdb_content_position', 'bottom' );
        if ( $position === 'none' ) {
            return $content;
        }

        // Check if this post is a TMDB post (check ID presence)
        $tmdb_id = get_post_meta( get_the_ID(), '_tmdb_id', true );
        if ( ! $tmdb_id && ! tmdb_get_field('tmdb_id') ) {
            return $content;
        }

        // Kart HTML'ini oluştur
        $card_html = $this->generate_html( get_the_ID(), $content );

        if ( $position === 'replace' ) {
            return $card_html;
        } elseif ( $position === 'top' ) {
            return $card_html . $content;
        } else {
            return $content . $card_html;
        }
    }

    private function generate_html( $post_id, $original_content = '' ) {
        // Options
        $show_poster = get_option( 'tmdb_show_poster', '1' );
        $show_meta = get_option( 'tmdb_show_meta', '1' ); 
        $show_financial = get_option( 'tmdb_show_financial', '1' );
        $show_cast = get_option( 'tmdb_show_cast', '1' );
        $show_trailer = get_option( 'tmdb_show_trailer', '1' );
        $show_overview = get_option( 'tmdb_show_overview', '1' );

        // Fetch Data using Helpers (ACF aware)
        $title = get_the_title( $post_id );
        $poster_url = tmdb_get_poster_url( $post_id );
        $overview = tmdb_get_field( 'overview', $post_id );
        
        // Use WP content if available and not empty, else ACF overview
        // trim() is important to check if content is truly empty (e.g. just spaces)
        $display_overview = ( !empty($original_content) && trim($original_content) !== '' ) ? $original_content : $overview;

        ob_start();
        ?>
        <div class="tmdb-info-box glass-effect">
            <div class="tmdb-main-content">
                
                <?php if ( $show_poster && $poster_url ) : ?>
                <div class="tmdb-poster-wrapper">
                    <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr($title); ?>" class="tmdb-poster-img">
                </div>
                <?php endif; ?>

                <div class="tmdb-details">
                    <h2 class="tmdb-title"><?php echo esc_html($title); ?></h2>
                    
                    <!-- 1. Row: Score & Director (Updated Structure) -->
                    <?php if ( $show_meta ) : ?>
                        <?php 
                        $director = tmdb_get_director( $post_id );
                        $vote = tmdb_get_field( 'tmdb_rating', $post_id );
                        $vote_fmt = $vote ? number_format( (float)$vote, 1 ) : 'N/A';
                        ?>
                        <div class="tmdb-meta-row">
                            <div class="tmdb-score-box">
                                ⭐ <?php echo $vote_fmt; ?>/10
                            </div>
                            <?php if($director): ?>
                                <div class="tmdb-director-box">
                                    <strong>YÖNETMEN/YARATICI:</strong>
                                    <span><?php echo esc_html($director); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- 2. Row: Description (Overview) -->
                    <?php if ( $show_overview && $display_overview ) : ?>
                        <div class="tmdb-overview-text">
                            <?php echo wpautop( $display_overview ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- 3. Row: Financials / Seasons Info -->
                    <?php if ( $show_financial ) : ?>
                        <?php 
                        $budget = tmdb_get_field( 'budget', $post_id );
                        $revenue = tmdb_get_field( 'revenue', $post_id );
                        
                        // Try to get TV fields
                        // Fallback to post meta if ACF helper not updated for these yet or use ACF directly if registered
                        $seasons = get_post_meta($post_id, '_tmdb_data', true);
                        $num_seasons = 0; $num_episodes = 0;
                        if(is_array($seasons)) {
                            $num_seasons = isset($seasons['number_of_seasons']) ? $seasons['number_of_seasons'] : 0;
                            $num_episodes = isset($seasons['number_of_episodes']) ? $seasons['number_of_episodes'] : 0;
                        }

                        if ( $budget > 0 || $revenue > 0 || $num_seasons > 0 ) :
                            $budget_txt = $budget ? '$' . number_format( $budget ) : 'Bilinmiyor';
                            $revenue_txt = $revenue ? '$' . number_format( $revenue ) : 'Bilinmiyor';
                        ?>
                        <div class="tmdb-financial">
                            <?php if($num_seasons > 0): ?>
                                <div class="fin-item"><strong>📺 Sezon:</strong> <?php echo esc_html($num_seasons); ?></div>
                                <div class="fin-item"><strong>🎞️ Bölüm:</strong> <?php echo esc_html($num_episodes); ?></div>
                            <?php else: ?>
                                <div class="fin-item"><strong>💰 Bütçe:</strong> <?php echo $budget_txt; ?></div>
                                <div class="fin-item"><strong>📈 Hasılat:</strong> <?php echo $revenue_txt; ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cast -->
            <?php 
            if ( $show_cast ) : 
                $cast = tmdb_get_cast( $post_id );
                if ( $cast && is_array( $cast ) && !empty( $cast ) ) :
            ?>
            <div class="tmdb-cast-section">
                <h3>Oyuncu Kadrosu</h3>
                <div class="tmdb-cast-grid">
                    <?php 
                    $limit = 6;
                    $count = 0;
                    foreach ( $cast as $actor ) {
                        if($count >= $limit) break;
                        $name = isset($actor['cast_name']) ? $actor['cast_name'] : '';
                        $photo = isset($actor['cast_photo_url']) ? $actor['cast_photo_url'] : TMDB_OZCANWORK_URL . 'assets/img/no-profile.png';
                        if(empty($photo)) $photo = TMDB_OZCANWORK_URL . 'assets/img/no-profile.png';
                        
                        echo '<div class="tmdb-actor">';
                        echo '<img src="' . esc_url($photo) . '" alt="' . esc_attr($name) . '" loading="lazy">';
                        echo '<span>' . esc_html($name) . '</span>';
                        echo '</div>';
                        $count++;
                    }
                    ?>
                </div>
            </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Trailer Logic -->
            <?php if ( $show_trailer ) : ?>
                <?php $trailer_key = tmdb_get_trailer_id( $post_id ); ?>
                <?php if ( $trailer_key ) : ?>
                <div class="tmdb-trailer">
                    <h3>Fragman</h3>
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/<?php echo esc_attr($trailer_key); ?>" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
