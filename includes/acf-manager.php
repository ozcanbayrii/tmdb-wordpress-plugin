<?php
// tmdb-ozcanwork/includes/acf-manager.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_ACF_Manager {
    public function __construct() {
        // Register fields on init
        add_action( 'acf/init', array( $this, 'register_acf_fields' ) );
    }

    /**
     * Otomatik olarak ACF Field Group oluşturur
     */
    public function register_acf_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_tmdb_film_data',
            'title' => 'TMDB Film Data',
            'fields' => array(
                array(
                    'key' => 'field_tmdb_id',
                    'label' => 'TMDB ID',
                    'name' => 'tmdb_id',
                    'type' => 'number',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tmdb_rating',
                    'label' => 'IMDb/TMDB Puanı',
                    'name' => 'tmdb_rating',
                    'type' => 'number',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tmdb_original_title',
                    'label' => 'Orijinal İsim',
                    'name' => 'original_title',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_tmdb_release_date',
                    'label' => 'Yayın Tarihi',
                    'name' => 'release_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_tmdb_runtime',
                    'label' => 'Süre (Dakika)',
                    'name' => 'runtime',
                    'type' => 'number',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_tmdb_seasons',
                    'label' => 'Sezon Sayısı',
                    'name' => 'number_of_seasons',
                    'type' => 'number',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_tmdb_episodes',
                    'label' => 'Bölüm Sayısı',
                    'name' => 'number_of_episodes',
                    'type' => 'number',
                    'wrapper' => array('width' => '33'),
                ),
                array(
                    'key' => 'field_tmdb_genres',
                    'label' => 'Türler',
                    'name' => 'genres',
                    'type' => 'checkbox',
                    'choices' => array(), // Dinamik doldurulacak veya text olarak basılacak
                    'allow_custom' => 1,
                    'save_custom' => 1,
                ),
                array(
                    'key' => 'field_tmdb_director',
                    'label' => 'Yönetmen / Yaratıcı',
                    'name' => 'director',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_tmdb_trailer',
                    'label' => 'Fragman (Youtube ID)',
                    'name' => 'trailer',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_tmdb_budget',
                    'label' => 'Bütçe',
                    'name' => 'budget',
                    'type' => 'number',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tmdb_revenue',
                    'label' => 'Hasılat',
                    'name' => 'revenue',
                    'type' => 'number',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_tmdb_poster',
                    'label' => 'Poster',
                    'name' => 'poster',
                    'type' => 'image',
                    'return_format' => 'array',
                ),
                array(
                    'key' => 'field_tmdb_backdrop',
                    'label' => 'Arka Plan (Backdrop)',
                    'name' => 'backdrop',
                    'type' => 'image',
                    'return_format' => 'array',
                ),
                array(
                    'key' => 'field_tmdb_cast',
                    'label' => 'Oyuncular',
                    'name' => 'cast',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_tmdb_cast_name',
                            'label' => 'Oyuncu Adı',
                            'name' => 'cast_name',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_tmdb_cast_character',
                            'label' => 'Karakter',
                            'name' => 'cast_character',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_tmdb_cast_photo',
                            'label' => 'Fotoğraf URL',
                            'name' => 'cast_photo_url',
                            'type' => 'url',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ) );
    }

    /**
     * TMDB verisini ACF alanlarına map eder
     */
    public function map_tmdb_to_acf( $post_id, $data, $poster_att_id = null ) {
        if ( ! function_exists( 'update_field' ) ) {
            return;
        }

        // Basic Fields
        update_field( 'tmdb_id', $data['id'], $post_id );
        update_field( 'original_title', isset($data['original_title']) ? $data['original_title'] : (isset($data['original_name']) ? $data['original_name'] : ''), $post_id );
        update_field( 'tmdb_rating', isset($data['vote_average']) ? $data['vote_average'] : '', $post_id );
        update_field( 'release_date', isset($data['release_date']) ? $data['release_date'] : (isset($data['first_air_date']) ? $data['first_air_date'] : ''), $post_id );
        update_field( 'runtime', isset($data['runtime']) ? $data['runtime'] : (isset($data['episode_run_time'][0]) ? $data['episode_run_time'][0] : ''), $post_id );
        update_field( 'budget', isset($data['budget']) ? $data['budget'] : 0, $post_id );
        update_field( 'revenue', isset($data['revenue']) ? $data['revenue'] : 0, $post_id );
        
        // TV Specific
        update_field( 'number_of_seasons', isset($data['number_of_seasons']) ? $data['number_of_seasons'] : '', $post_id );
        update_field( 'number_of_episodes', isset($data['number_of_episodes']) ? $data['number_of_episodes'] : '', $post_id );

        // Director / Creator
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
        update_field( 'director', $director, $post_id );

        // Trailer
        $trailer_key = '';
        if ( isset( $data['videos']['results'] ) ) {
            foreach ( $data['videos']['results'] as $video ) {
                if ( isset($video['site'], $video['type']) && $video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser') ) {
                    $trailer_key = $video['key'];
                    break;
                }
            }
        }
        update_field( 'trailer', $trailer_key, $post_id );

        // Genres (Save as checkbox array)
        if ( isset( $data['genres'] ) && is_array( $data['genres'] ) ) {
            $genre_names = array_map(function($g) { return $g['name']; }, $data['genres']);
            update_field( 'genres', $genre_names, $post_id );
        }

        // Poster Image (Attach ID)
        if ( $poster_att_id ) {
            update_field( 'poster', $poster_att_id, $post_id );
        }

        // Cast Repeater
        if ( isset( $data['credits']['cast'] ) && is_array( $data['credits']['cast'] ) ) {
            $cast_repeater = array();
            $limit = 10; // Max 10 actors
            $count = 0;
            foreach ( $data['credits']['cast'] as $actor ) {
                if ($count >= $limit) break;
                
                $photo_url = isset($actor['profile_path']) && $actor['profile_path'] ? 'https://image.tmdb.org/t/p/w185' . $actor['profile_path'] : '';
                
                $cast_repeater[] = array(
                    'cast_name' => isset($actor['name']) ? $actor['name'] : '',
                    'cast_character' => isset($actor['character']) ? $actor['character'] : '',
                    'cast_photo_url' => $photo_url
                );
                $count++;
            }
            update_field( 'cast', $cast_repeater, $post_id );
        }
    }
}
