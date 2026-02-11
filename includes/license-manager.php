<?php
// tmdb-ozcanwork/includes/license-manager.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_License_Manager {
    // Sunucu ile aynı secret key
    private $api_secret = 'OZCAN-SEC-TOKEN-2024-X9'; 
    
    // Doğrulama Sunucusu (Sabit)
    private $api_endpoint = 'https://key.ozcan.work/tmdb/'; 

    public function __construct() {
        add_action( 'admin_init', array( $this, 'verify_license_on_save' ) );
    }

    /**
     * Settings kaydedildiğinde lisansı doğrula
     */
    public function verify_license_on_save() {
        // Options.php'den save işlemi dönüyor mu kontrol et
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
            $license_key = get_option( 'tmdb_license_key' );
            $domain = $_SERVER['SERVER_NAME'];

            if ( empty( $license_key ) ) {
                update_option( 'tmdb_license_status', 'invalid' );
                return;
            }

            // Master Key Bypass (Plugin Side Check)
            if ( $license_key === 'OZCAN-MASTER-DEV-2024' ) {
                update_option( 'tmdb_license_status', 'valid' );
                add_settings_error( 'tmdb_ozcanwork_options', 'license_valid', 'Geliştirici Lisansı Aktif!', 'success' );
                return;
            }

            // --- SECURITY SIGNATURE GENERATION ---
            // Domain ve Key'i birleştirip Secret ile hashliyoruz.
            $signature = hash_hmac('sha256', $domain . $license_key, $this->api_secret);

            // Remote Verification URL
            $api_url = add_query_arg( array(
                'action' => 'verify',
                'domain' => $domain,
                'key'    => $license_key,
                'sig'    => $signature // İmzayı gönder
            ), $this->api_endpoint );

            $response = wp_remote_get( $api_url, array( 'timeout' => 15 ) );

            if ( is_wp_error( $response ) ) {
                add_settings_error( 'tmdb_ozcanwork_options', 'license_error', 'Lisans sunucusuna bağlanılamadı. Lütfen internet bağlantınızı kontrol edin.', 'error' );
                return;
            }

            $body = wp_remote_retrieve_body( $response );
            $result = json_decode( $body, true );

            if ( isset( $result['success'] ) && $result['success'] === true ) {
                update_option( 'tmdb_license_status', 'valid' );
                add_settings_error( 'tmdb_ozcanwork_options', 'license_valid', 'Lisans Başarıyla Doğrulandı ve Etkinleştirildi!', 'success' );
            } else {
                update_option( 'tmdb_license_status', 'invalid' );
                $msg = isset( $result['message'] ) ? $result['message'] : 'Lisans geçersiz.';
                add_settings_error( 'tmdb_ozcanwork_options', 'license_invalid', 'Lisans Hatası: ' . $msg, 'error' );
            }
        }
    }
}
