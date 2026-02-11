<?php
// tmdb-ozcanwork/includes/template-loader.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Template_Loader {
    public function __construct() {
        add_filter( 'theme_page_templates', array( $this, 'register_template' ) );
        add_filter( 'template_include', array( $this, 'load_template' ) );
    }

    public function register_template( $templates ) {
        $templates['tmdb-archive.php'] = 'TMDB Arşivi';
        return $templates;
    }

    public function load_template( $template ) {
        if ( is_page() ) {
            $meta = get_post_meta( get_the_ID(), '_wp_page_template', true );
            if ( $meta === 'tmdb-archive.php' ) {
                $file = TMDB_OZCANWORK_PATH . 'templates/tmdb-archive.php';
                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }
        return $template;
    }
}
