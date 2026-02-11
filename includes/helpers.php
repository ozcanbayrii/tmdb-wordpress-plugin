<?php
// tmdb-ozcanwork/includes/helpers.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * --- DİL VE ÇEVİRİ FONKSİYONLARI ---
 */

// Geçerli dili döndürür: 'tr' veya 'en'
function tmdb_get_current_lang() {
    $setting = get_option( 'tmdb_language', 'default' );

    if ( $setting === 'tr' ) return 'tr';
    if ( $setting === 'en' ) return 'en';

    // Varsayılan ise WP Localine bak
    $locale = get_locale(); // tr_TR, en_US, fr_FR vb.
    if ( strpos( $locale, 'tr' ) === 0 ) {
        return 'tr';
    }
    
    // Türkçe değilse varsayılan olarak İngilizce kabul et
    return 'en';
}

// TMDB API için dil kodu döndürür
function tmdb_get_api_lang_code() {
    return ( tmdb_get_current_lang() === 'tr' ) ? 'tr-TR' : 'en-US';
}

// Basit çeviri fonksiyonu (PO/MO dosyaları olmadan)
function tmdb__( $text_tr, $text_en ) {
    return ( tmdb_get_current_lang() === 'tr' ) ? $text_tr : $text_en;
}


/**
 * --- VERİ ÇEKME YARDIMCILARI ---
 */

if ( ! function_exists( 'tmdb_get_field' ) ) {
    function tmdb_get_field( $field_key, $post_id = null ) {
        if ( ! $post_id ) $post_id = get_the_ID();
        
        // 1. Try ACF
        if ( function_exists( 'get_field' ) ) {
            $value = get_field( $field_key, $post_id );
            if ( $value !== null && $value !== '' && !empty($value) ) {
                return $value;
            }
        }

        // 2. Fallback to Meta Data (_tmdb_data)
        $data = get_post_meta( $post_id, '_tmdb_data', true );
        if ( ! $data || ! is_array( $data ) ) return null;

        switch ( $field_key ) {
            case 'tmdb_id': return isset($data['id']) ? $data['id'] : null;
            case 'tmdb_rating': return isset($data['vote_average']) ? $data['vote_average'] : null;
            case 'original_title': return isset($data['original_title']) ? $data['original_title'] : (isset($data['original_name']) ? $data['original_name'] : null);
            case 'overview': return isset($data['overview']) ? $data['overview'] : null;
            case 'budget': return isset($data['budget']) ? $data['budget'] : null;
            case 'revenue': return isset($data['revenue']) ? $data['revenue'] : null;
            case 'runtime': return isset($data['runtime']) ? $data['runtime'] : null;
            case 'release_date': return isset($data['release_date']) ? $data['release_date'] : (isset($data['first_air_date']) ? $data['first_air_date'] : null);
            case 'poster_url': // Special case for URL
                return isset($data['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $data['poster_path'] : null;
            
            case 'director':
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
                    $director = implode(', ', $creators);
               }
               return $director;

            case 'trailer':
                if ( isset( $data['videos']['results'] ) ) {
                    foreach ( $data['videos']['results'] as $video ) {
                        if ( isset($video['site'], $video['type']) && $video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser') ) {
                            return $video['key'];
                        }
                    }
                }
                return null;

            case 'cast':
                 if ( isset( $data['credits']['cast'] ) && is_array( $data['credits']['cast'] ) ) {
                    $cast = array();
                    foreach ( array_slice($data['credits']['cast'], 0, 10) as $actor ) {
                         $photo = isset($actor['profile_path']) && $actor['profile_path'] ? 'https://image.tmdb.org/t/p/w185' . $actor['profile_path'] : '';
                         $cast[] = array(
                             'cast_name' => $actor['name'],
                             'cast_character' => isset($actor['character']) ? $actor['character'] : '',
                             'cast_photo_url' => $photo
                         );
                    }
                    return $cast;
                 }
                 return array();
            
            default: return null;
        }
    }
}

// Wrapper Functions for Theme Developers

function tmdb_get_poster_url( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    
    // Check ACF Image Array
    if ( function_exists( 'get_field' ) ) {
        $img = get_field( 'poster', $post_id );
        if ( is_array( $img ) && isset( $img['url'] ) ) {
            return $img['url'];
        } elseif ( is_numeric( $img ) ) {
             return wp_get_attachment_url( $img );
        }
    }
    
    return tmdb_get_field( 'poster_url', $post_id );
}

function tmdb_get_cast( $post_id = null ) {
    return tmdb_get_field( 'cast', $post_id );
}

function tmdb_get_director( $post_id = null ) {
    return tmdb_get_field( 'director', $post_id );
}

function tmdb_get_trailer_id( $post_id = null ) {
    // Check Manual override first (Meta Box)
    $manual = get_post_meta( $post_id ? $post_id : get_the_ID(), '_tmdb_manual_trailer', true );
    if ( $manual ) return $manual;

    return tmdb_get_field( 'trailer', $post_id );
}

/**
 * Fetch Data directly for Shortcode usage (Frontend)
 * Caches results in Transient.
 * UPDATED: Uses dynamic language
 */
function tmdb_get_remote_data_for_shortcode( $id, $type ) {
    $api_key = get_option( 'tmdb_api_key' );
    if ( empty( $api_key ) ) return false;

    // Dil kodunu al
    $lang_code = tmdb_get_api_lang_code();

    // Cache key dile özgü olmalı
    $transient_key = "tmdb_sc_{$type}_{$id}_{$lang_code}";
    $cached = get_transient( $transient_key );
    if ( $cached !== false ) return $cached;

    $endpoint = ($type === 'tv') ? 'tv' : 'movie';
    $url = "https://api.themoviedb.org/3/{$endpoint}/{$id}?api_key={$api_key}&language={$lang_code}&append_to_response=videos,credits";
    
    $response = wp_remote_get( $url );
    if ( is_wp_error( $response ) ) return false;

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( empty( $data ) || isset( $data['success'] ) && $data['success'] === false ) return false;

    set_transient( $transient_key, $data, 24 * HOUR_IN_SECONDS );
    return $data;
}
