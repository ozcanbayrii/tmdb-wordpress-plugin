<?php
/*
Plugin Name: TMDB OzcanWork
Plugin URI: https://www.ozcan.work/
Description: Otomatik içerik enjeksiyonu, şablon desteği, ACF entegrasyonu ve glassmorphism UI ile Premium TMDB entegrasyonu.
Version: 1.4.0
Author: OzcanWork
Text Domain: tmdb-ozcanwork
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Constants
define( 'TMDB_OZCANWORK_PATH', plugin_dir_path( __FILE__ ) );
define( 'TMDB_OZCANWORK_URL', plugin_dir_url( __FILE__ ) );
define( 'TMDB_OZCANWORK_VERSION', '1.4.0' );

// Include Files with checks
$includes = array(
    'includes/helpers.php', // Helper functions for theme devs
    'includes/admin-menu.php',
    'includes/settings-page.php',
    'includes/fetch-movie.php',
    'includes/post-generator.php',
    'includes/content-injector.php',
    'includes/template-loader.php',
    'includes/meta-boxes.php',
    'includes/acf-manager.php', // ACF Integration
    'includes/shortcodes.php',   // Shortcode System
    'includes/license-manager.php' // License System (New)
);

foreach ( $includes as $file ) {
    if ( file_exists( TMDB_OZCANWORK_PATH . $file ) ) {
        require_once TMDB_OZCANWORK_PATH . $file;
    }
}

// Initialize Classes
class TMDB_OzcanWork {
    public function __construct() {
        // Initialize modules only if classes exist
        if ( class_exists( 'TMDB_OzcanWork_Admin_Menu' ) ) new TMDB_OzcanWork_Admin_Menu();
        if ( class_exists( 'TMDB_OzcanWork_Settings_Page' ) ) new TMDB_OzcanWork_Settings_Page();
        if ( class_exists( 'TMDB_OzcanWork_Fetch_Movie' ) ) new TMDB_OzcanWork_Fetch_Movie();
        if ( class_exists( 'TMDB_OzcanWork_Content_Injector' ) ) new TMDB_OzcanWork_Content_Injector();
        if ( class_exists( 'TMDB_OzcanWork_Template_Loader' ) ) new TMDB_OzcanWork_Template_Loader();
        if ( class_exists( 'TMDB_OzcanWork_Meta_Boxes' ) ) new TMDB_OzcanWork_Meta_Boxes();
        if ( class_exists( 'TMDB_OzcanWork_ACF_Manager' ) ) new TMDB_OzcanWork_ACF_Manager();
        if ( class_exists( 'TMDB_OzcanWork_Shortcodes' ) ) new TMDB_OzcanWork_Shortcodes();
        if ( class_exists( 'TMDB_OzcanWork_License_Manager' ) ) new TMDB_OzcanWork_License_Manager();

        // Enqueue Assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        
        // ACF Warning
        add_action( 'admin_notices', array( $this, 'check_acf_dependency' ) );
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'tmdb-ozcanwork-style', TMDB_OZCANWORK_URL . 'assets/css/style.css', array(), TMDB_OZCANWORK_VERSION );
    }

    public function enqueue_admin_assets( $hook ) {
        global $post_type;
        if ( strpos( $hook, 'tmdb-ozcanwork' ) !== false || 'post' === $post_type ) {
            wp_enqueue_style( 'tmdb-ozcanwork-style', TMDB_OZCANWORK_URL . 'assets/css/style.css', array(), TMDB_OZCANWORK_VERSION );
            wp_enqueue_script( 'tmdb-ozcanwork-admin', TMDB_OZCANWORK_URL . 'assets/js/admin.js', array( 'jquery' ), TMDB_OZCANWORK_VERSION, true );
            
            wp_localize_script( 'tmdb-ozcanwork-admin', 'tmdb_vars', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'tmdb_fetch_nonce' )
            ));
        }
    }

    public function check_acf_dependency() {
        if ( ! class_exists( 'ACF' ) ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e( 'TMDB OzcanWork: Tam özellikli kullanım için <strong>Advanced Custom Fields (ACF)</strong> eklentisinin yüklenmesi önerilir (Zorunlu Değildir).', 'tmdb-ozcanwork' ); ?></p>
            </div>
            <?php
        }
    }
}

// Run Plugin
add_action( 'plugins_loaded', 'tmdb_ozcanwork_init' );
function tmdb_ozcanwork_init() {
    new TMDB_OzcanWork();
}
