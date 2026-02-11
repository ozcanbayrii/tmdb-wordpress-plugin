<?php
// tmdb-ozcanwork/includes/admin-menu.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Admin_Menu {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    public function register_menu() {
        // Lisans durumunu kontrol et
        $license_status = get_option( 'tmdb_license_status' );
        $is_licensed = ( $license_status === 'valid' );

        // 1. Ana Menü
        add_menu_page(
            __( 'TMDB OzcanWork', 'tmdb-ozcanwork' ),
            __( 'TMDB OzcanWork', 'tmdb-ozcanwork' ),
            'manage_options',
            'tmdb-ozcanwork',
            array( 'TMDB_OzcanWork_Settings_Page', 'render_general_page' ), 
            'dashicons-video-alt2',
            25
        );

        // 2. Alt Menü: Genel Ayarlar
        add_submenu_page(
            'tmdb-ozcanwork',
            tmdb__('Genel Ayarlar', 'General Settings'),
            tmdb__('Genel', 'General'),
            'manage_options',
            'tmdb-ozcanwork',
            array( 'TMDB_OzcanWork_Settings_Page', 'render_general_page' )
        );

        // --- LİSANSLI İSE GÖRÜNECEK MENÜLER ---
        if ( $is_licensed ) {
            // Görünüm
            add_submenu_page(
                'tmdb-ozcanwork',
                tmdb__('Görünüm Ayarları', 'Display Settings'),
                tmdb__('Görünüm', 'Display'),
                'manage_options',
                'tmdb-ozcanwork-display',
                array( 'TMDB_OzcanWork_Settings_Page', 'render_display_page' )
            );

            // Toplu Veri Çekme
            add_submenu_page(
                'tmdb-ozcanwork',
                tmdb__('Toplu Veri Çekme', 'Bulk Fetch'),
                tmdb__('Toplu İşlem', 'Bulk Actions'),
                'manage_options',
                'tmdb-ozcanwork-fetch',
                array( 'TMDB_OzcanWork_Settings_Page', 'render_fetch_page' )
            );

            // Rehber
            add_submenu_page(
                'tmdb-ozcanwork',
                tmdb__('Kullanım Rehberi', 'Documentation'),
                tmdb__('Rehber', 'Docs'),
                'manage_options',
                'tmdb-ozcanwork-docs',
                array( 'TMDB_OzcanWork_Settings_Page', 'render_docs_page' )
            );
        }

        // 3. Alt Menü: Lisans
        add_submenu_page(
            'tmdb-ozcanwork',
            tmdb__('Lisans Yönetimi', 'License Management'),
            tmdb__('Lisans', 'License'),
            'manage_options',
            'tmdb-ozcanwork-license', 
            array( 'TMDB_OzcanWork_Settings_Page', 'render_license_page' )
        );
    }
}
