<?php
// tmdb-ozcanwork/templates/tmdb-archive.php

get_header(); 
?>

<div class="tmdb-archive-container">
    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>

    <div class="tmdb-movies-grid">
        <?php
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 12,
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_tmdb_id',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) :
            while ( $query->have_posts() ) : $query->the_post();
                $tmdb_id = get_post_meta( get_the_ID(), '_tmdb_id', true );
                $score = get_post_meta( get_the_ID(), '_tmdb_score', true );
                $poster_id = get_post_thumbnail_id();
                $poster_url = wp_get_attachment_image_url( $poster_id, 'medium' );
                ?>
                <article class="tmdb-card-item">
                    <a href="<?php the_permalink(); ?>" class="tmdb-card-link">
                        <div class="tmdb-card-poster">
                            <?php if($poster_url): ?>
                                <img src="<?php echo esc_url($poster_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="no-poster">No Image</div>
                            <?php endif; ?>
                            <div class="tmdb-overlay">
                                <span class="play-icon">▶</span>
                            </div>
                        </div>
                        <div class="tmdb-card-content">
                            <h3 class="tmdb-card-title"><?php the_title(); ?></h3>
                            <div class="tmdb-card-meta">
                                <span class="tmdb-rating">⭐ <?php echo number_format((float)$score, 1); ?></span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php
            endwhile;
            
            // Pagination
            echo '<div class="tmdb-pagination">';
            echo paginate_links( array(
                'total' => $query->max_num_pages,
                'prev_text' => '«',
                'next_text' => '»'
            ) );
            echo '</div>';

            wp_reset_postdata();
        else :
            echo '<p>Henüz film eklenmemiş.</p>';
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>
