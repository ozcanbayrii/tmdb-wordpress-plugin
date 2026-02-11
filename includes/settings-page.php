<?php
// tmdb-ozcanwork/includes/settings-page.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMDB_OzcanWork_Settings_Page {
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_settings() {
        // General
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_api_key' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_primary_color' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_dark_mode' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_content_position' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_language' ); // New Language Setting

        // License
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_license_key' );

        // Visibility Toggles
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_show_poster' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_show_cast' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_show_trailer' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_show_financial' );
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_show_meta' ); 
        register_setting( 'tmdb_ozcanwork_options', 'tmdb_show_overview' ); 
    }

    // --- ORTAK WRAPPER BAŞLANGICI ---
    private static function get_wrapper_class() {
        $dark_mode = get_option( 'tmdb_dark_mode' );
        return 'wrap tmdb-admin-wrapper ' . ( $dark_mode ? 'dark-mode' : '' );
    }

    // =========================================================
    // SAYFA 1: GENEL AYARLAR
    // =========================================================
    public static function render_general_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        // Gatekeeper: Lisans yoksa uyarı göster
        $license_status = get_option( 'tmdb_license_status' );
        if ( $license_status !== 'valid' ) {
            self::render_locked_screen();
            return;
        }

        $api_key = get_option( 'tmdb_api_key' );
        $primary_color = get_option( 'tmdb_primary_color', '#D4AF37' );
        $dark_mode = get_option( 'tmdb_dark_mode' );
        $content_position = get_option( 'tmdb_content_position', 'bottom' );
        $language = get_option( 'tmdb_language', 'default' );
        ?>
        <div class="<?php echo esc_attr( self::get_wrapper_class() ); ?>">
            <h1>⚙️ <?php echo tmdb__('Genel Ayarlar', 'General Settings'); ?></h1>
            <div class="tmdb-glass-container">
                <form method="post" action="options.php">
                    <?php settings_fields( 'tmdb_ozcanwork_options' ); ?>
                    <?php do_settings_sections( 'tmdb_ozcanwork_options' ); ?>
                    
                    <!-- Lisans key'i kaybetmemek için hidden -->
                    <input type="hidden" name="tmdb_license_key" value="<?php echo esc_attr(get_option('tmdb_license_key')); ?>">

                    <div class="tmdb-card settings-card">
                        <h2>🔧 <?php echo tmdb__('Yapılandırma', 'Configuration'); ?></h2>
                        
                        <div class="form-group">
                            <label for="tmdb_api_key">TMDB API Key (v3 Auth)</label>
                            <input type="text" id="tmdb_api_key" name="tmdb_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" placeholder="TMDB v3 api key..." />
                            <p class="description"><?php echo tmdb__('Verilerin çekilebilmesi için zorunludur.', 'Required to fetch data from TMDB.'); ?></p>
                        </div>

                        <div class="form-group">
                            <label for="tmdb_language"><?php echo tmdb__('Eklenti & Veri Dili', 'Plugin & Data Language'); ?></label>
                            <select id="tmdb_language" name="tmdb_language">
                                <option value="default" <?php selected( 'default', $language ); ?>><?php echo tmdb__('Varsayılan (WP Diline Göre)', 'Default (Based on WP)'); ?></option>
                                <option value="tr" <?php selected( 'tr', $language ); ?>>Türkçe (Turkish)</option>
                                <option value="en" <?php selected( 'en', $language ); ?>>English (İngilizce)</option>
                            </select>
                            <p class="description"><?php echo tmdb__('Admin paneli ve çekilen film verilerinin (Özet, Başlık) dilini belirler.', 'Determines the language of Admin panel and fetched movie data.'); ?></p>
                        </div>

                        <div class="form-group">
                            <label for="tmdb_primary_color"><?php echo tmdb__('Ana Renk', 'Primary Color'); ?></label>
                            <input type="color" id="tmdb_primary_color" name="tmdb_primary_color" value="<?php echo esc_attr( $primary_color ); ?>" />
                            <p class="description"><?php echo tmdb__('Puan kutuları ve başlık çizgileri bu rengi alır.', 'Score boxes and title lines will use this color.'); ?></p>
                        </div>

                        <div class="form-group toggle-group">
                            <label for="tmdb_dark_mode">Admin Dark Mode</label>
                            <input type="checkbox" id="tmdb_dark_mode" name="tmdb_dark_mode" value="1" <?php checked( 1, $dark_mode ); ?> />
                            <span class="description"><?php echo tmdb__('Bu panelin koyu modda çalışmasını sağlar.', 'Enables dark mode for this admin panel.'); ?></span>
                        </div>

                        <div class="form-group">
                            <label for="tmdb_content_position"><?php echo tmdb__('İçerik Konumu', 'Content Position'); ?></label>
                            <select id="tmdb_content_position" name="tmdb_content_position">
                                <option value="top" <?php selected( 'top', $content_position ); ?>><?php echo tmdb__('Kartı Başa Ekle', 'Insert Before Content'); ?></option>
                                <option value="bottom" <?php selected( 'bottom', $content_position ); ?>><?php echo tmdb__('Kartı Sona Ekle (Önerilen)', 'Insert After Content (Recommended)'); ?></option>
                                <option value="replace" <?php selected( 'replace', $content_position ); ?>><?php echo tmdb__('Sadece Kart Göster', 'Replace Content'); ?></option>
                                <option value="none" <?php selected( 'none', $content_position ); ?>><?php echo tmdb__('Kapalı (Manuel Shortcode)', 'Disabled (Manual Shortcode)'); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php submit_button( tmdb__('Ayarları Kaydet', 'Save Settings'), 'primary large' ); ?>
                </form>
            </div>
        </div>
        <style>:root { --tmdb-primary: <?php echo esc_attr($primary_color); ?>; }</style>
        <?php
    }

    // =========================================================
    // SAYFA 2: GÖRÜNÜM AYARLARI
    // =========================================================
    public static function render_display_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        
        $show_poster = get_option( 'tmdb_show_poster', '1' );
        $show_cast = get_option( 'tmdb_show_cast', '1' );
        $show_trailer = get_option( 'tmdb_show_trailer', '1' );
        $show_financial = get_option( 'tmdb_show_financial', '1' );
        $show_meta = get_option( 'tmdb_show_meta', '1' );
        $show_overview = get_option( 'tmdb_show_overview', '1' );
        ?>
        <div class="<?php echo esc_attr( self::get_wrapper_class() ); ?>">
            <h1>🎨 <?php echo tmdb__('Görünüm Ayarları', 'Display Settings'); ?></h1>
            <div class="tmdb-glass-container">
                <form method="post" action="options.php">
                    <?php settings_fields( 'tmdb_ozcanwork_options' ); ?>
                    
                    <input type="hidden" name="tmdb_api_key" value="<?php echo esc_attr(get_option('tmdb_api_key')); ?>">
                    <input type="hidden" name="tmdb_license_key" value="<?php echo esc_attr(get_option('tmdb_license_key')); ?>">
                    <input type="hidden" name="tmdb_language" value="<?php echo esc_attr(get_option('tmdb_language')); ?>">

                    <div class="tmdb-card settings-card">
                        <h2><?php echo tmdb__('Kart Özelleştirme', 'Card Customization'); ?></h2>
                        <p><?php echo tmdb__('Otomatik oluşturulan film kartında hangi öğelerin gözükeceğini seçin.', 'Select which elements to show on the auto-generated movie card.'); ?></p>
                        
                        <div class="toggle-grid">
                            <div class="form-group toggle-group">
                                <input type="checkbox" id="tmdb_show_poster" name="tmdb_show_poster" value="1" <?php checked( 1, $show_poster ); ?> />
                                <label for="tmdb_show_poster"><?php echo tmdb__('Poster Resmi', 'Poster Image'); ?></label>
                            </div>
                            <div class="form-group toggle-group">
                                <input type="checkbox" id="tmdb_show_meta" name="tmdb_show_meta" value="1" <?php checked( 1, $show_meta ); ?> />
                                <label for="tmdb_show_meta"><?php echo tmdb__('Puan, Yönetmen, Yıl', 'Score, Director, Year'); ?></label>
                            </div>
                            <div class="form-group toggle-group">
                                <input type="checkbox" id="tmdb_show_overview" name="tmdb_show_overview" value="1" <?php checked( 1, $show_overview ); ?> />
                                <label for="tmdb_show_overview"><?php echo tmdb__('Özet Metni', 'Overview Text'); ?></label>
                            </div>
                            <div class="form-group toggle-group">
                                <input type="checkbox" id="tmdb_show_financial" name="tmdb_show_financial" value="1" <?php checked( 1, $show_financial ); ?> />
                                <label for="tmdb_show_financial"><?php echo tmdb__('Bütçe / Hasılat', 'Budget / Revenue'); ?></label>
                            </div>
                            <div class="form-group toggle-group">
                                <input type="checkbox" id="tmdb_show_cast" name="tmdb_show_cast" value="1" <?php checked( 1, $show_cast ); ?> />
                                <label for="tmdb_show_cast"><?php echo tmdb__('Oyuncu Kadrosu', 'Cast (Grid)'); ?></label>
                            </div>
                            <div class="form-group toggle-group">
                                <input type="checkbox" id="tmdb_show_trailer" name="tmdb_show_trailer" value="1" <?php checked( 1, $show_trailer ); ?> />
                                <label for="tmdb_show_trailer"><?php echo tmdb__('Youtube Fragman', 'Youtube Trailer'); ?></label>
                            </div>
                        </div>
                    </div>
                    <?php submit_button( tmdb__('Görünümü Kaydet', 'Save Display'), 'primary large' ); ?>
                </form>
            </div>
        </div>
        <?php
    }

    // =========================================================
    // SAYFA 3: TOPLU VERİ ÇEKME
    // =========================================================
    public static function render_fetch_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="<?php echo esc_attr( self::get_wrapper_class() ); ?>">
            <h1>🚀 <?php echo tmdb__('Toplu Veri Çekme', 'Bulk Fetch'); ?></h1>
            <div class="tmdb-glass-container">
                <div class="tmdb-card fetch-card">
                    <h2><?php echo tmdb__('İçerik Oluşturucu', 'Content Generator'); ?></h2>
                    <p><?php echo tmdb__('Buradan yüzlerce filmi tek seferde sitenize ekleyebilirsiniz.', 'Add hundreds of movies/shows to your site at once.'); ?></p>
                    
                    <div class="form-group">
                        <label><strong><?php echo tmdb__('İçerik Türü:', 'Content Type:'); ?></strong></label><br>
                        <label style="margin-right: 15px;">
                            <input type="radio" name="fetch_type" value="movie" checked> 🎥 <?php echo tmdb__('Film', 'Movie'); ?>
                        </label>
                        <label>
                            <input type="radio" name="fetch_type" value="tv"> 📺 <?php echo tmdb__('Dizi', 'TV Show'); ?>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="tmdb_ids"><strong>TMDB ID List</strong> (<?php echo tmdb__('Her satıra bir ID', 'One ID per line'); ?>):</label>
                        <textarea id="tmdb_ids" rows="5" class="large-text code" placeholder="550, 155, 680&#10;120&#10;597"></textarea>
                        <p class="description"><?php echo tmdb__('Bu işlem sitenizde yeni taslak yazılar oluşturur.', 'This will create new draft posts on your site.'); ?></p>
                    </div>
                    
                    <div class="fetch-controls">
                        <button type="button" id="tmdb_fetch_btn" class="button button-primary button-hero">
                            <span class="dashicons dashicons-download"></span> <?php echo tmdb__('Verileri Çek ve Oluştur', 'Fetch & Create'); ?>
                        </button>
                    </div>

                    <div class="fetch-progress-wrapper" style="display:none; margin-top:15px;">
                        <progress id="fetch_progress" value="0" max="100" style="width:100%; height:20px;"></progress>
                        <p id="fetch_status_text" style="font-weight:bold; text-align:center;">Loading...</p>
                    </div>

                    <div id="tmdb_fetch_result" class="fetch-log"></div>
                </div>
            </div>
        </div>
        <?php
    }

    // =========================================================
    // SAYFA 4: REHBER (GÜNCELLENMİŞ DETAYLI VERSİYON)
    // =========================================================
    public static function render_docs_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $primary = get_option( 'tmdb_primary_color', '#D4AF37' );
        ?>
        <div class="<?php echo esc_attr( self::get_wrapper_class() ); ?>">
            <h1>📚 <?php echo tmdb__('Kullanım Rehberi & Dokümantasyon', 'User Guide & Documentation'); ?></h1>
            <div class="tmdb-glass-container">
                <div class="tmdb-card docs-card">
                    
                    <!-- Intro -->
                    <div class="doc-block">
                        <p style="font-size:1.1em; color:#555;">
                            <?php echo tmdb__(
                                'TMDB OzcanWork eklentisine hoş geldiniz. Bu rehber, eklentinin tüm özelliklerini en verimli şekilde kullanmanız için hazırlanmıştır.', 
                                'Welcome to TMDB OzcanWork plugin. This guide is designed to help you use all features efficiently.'
                            ); ?>
                        </p>
                    </div>

                    <!-- 1. Kurulum -->
                    <div class="doc-block">
                        <h3>🚀 1. <?php echo tmdb__('Kurulum ve İlk Ayarlar', 'Installation & Setup'); ?></h3>
                        <p><?php echo tmdb__(
                            'Eklentiyi kullanmaya başlamadan önce yapmanız gereken 2 temel ayar vardır:', 
                            'There are 2 basic settings you need to configure before using the plugin:'
                        ); ?></p>
                        <ol class="tmdb-doc-list">
                            <li>
                                <strong>TMDB API Key:</strong> 
                                <?php echo tmdb__(
                                    'Verilerin çekilebilmesi için TheMovieDB (TMDB) üzerinden ücretsiz bir API anahtarı almanız gerekir. <a href="https://www.themoviedb.org/settings/api" target="_blank">Buraya tıklayarak</a> hesabınızla giriş yapın ve API anahtarınızı oluşturun. Aldığınız "v3 auth" anahtarını <em>Genel Ayarlar</em> sekmesindeki ilgili alana yapıştırın.',
                                    'You need a free API key from TheMovieDB (TMDB) to fetch data. <a href="https://www.themoviedb.org/settings/api" target="_blank">Click here</a> to login and generate your API key. Paste the "v3 auth" key into the relevant field in the <em>General Settings</em> tab.'
                                ); ?>
                            </li>
                            <li>
                                <strong>Lisans Anahtarı:</strong>
                                <?php echo tmdb__(
                                    'Eklentinin tüm özelliklerini açmak için lisans anahtarınızı <em>Lisans Yönetimi</em> sekmesinden aktif etmelisiniz.',
                                    'To unlock all features, you must activate your license key from the <em>License Management</em> tab.'
                                ); ?>
                            </li>
                        </ol>
                    </div>

                    <!-- 2. Tekil İçerik Ekleme -->
                    <div class="doc-block">
                        <h3>✍️ 2. <?php echo tmdb__('Tekil İçerik Ekleme (Yazı Editörü)', 'Adding Single Content (Post Editor)'); ?></h3>
                        <p><?php echo tmdb__(
                            'WordPress yazı editöründe (Yeni Yazı Ekle), içerik alanının hemen üzerinde veya altında <strong>"🎬 TMDB İçerik Yöneticisi"</strong> kutusunu göreceksiniz.',
                            'In the WordPress post editor (Add New Post), you will see the <strong>"🎬 TMDB Content Manager"</strong> box above or below the content area.'
                        ); ?></p>
                        <ul class="tmdb-doc-list">
                            <li><strong>Film/Dizi Seçimi:</strong> <?php echo tmdb__('Eklemek istediğiniz içerik türünü (Film veya Dizi) seçin.', 'Select the content type (Movie or TV Show) you want to add.'); ?></li>
                            <li><strong>TMDB ID Girin:</strong> <?php echo tmdb__('TMDB sitesindeki URL\'den ID numarasını kopyalayın (Örn: <em>themoviedb.org/movie/<strong>550</strong>-fight-club</em> için ID 550\'dir).', 'Copy the ID number from the URL on TMDB website (e.g. for <em>themoviedb.org/movie/<strong>550</strong>-fight-club</em> the ID is 550).'); ?></li>
                            <li><strong>Verileri Getir Butonu:</strong> <?php echo tmdb__('ID\'yi yazıp bu butona bastığınızda; Başlık, Özet, Puan, Vizyon Tarihi, Oyuncular ve Poster otomatik olarak çekilir. Yazı başlığı ve içeriği otomatik doldurulur.', 'When you type the ID and click this button; Title, Overview, Rating, Release Date, Cast, and Poster are fetched automatically. Post title and content are auto-filled.'); ?></li>
                            <li><strong>Özel Fragman:</strong> <?php echo tmdb__('Otomatik çekilen fragman yerine başka bir video kullanmak isterseniz "Özel Fragman ID" alanına Youtube video ID\'sini (URL değil) yazabilirsiniz.', 'If you want to use a different video instead of the auto-fetched trailer, you can enter the Youtube video ID (not URL) into the "Custom Trailer ID" field.'); ?></li>
                        </ul>
                    </div>

                    <!-- 3. Toplu Veri Çekme -->
                    <div class="doc-block">
                        <h3>⚡ 3. <?php echo tmdb__('Toplu Veri Çekme (Bulk Fetch)', 'Bulk Fetch'); ?></h3>
                        <p><?php echo tmdb__(
                            'Sitenize hızlıca yüzlerce film eklemek için bu menüyü kullanın. <strong>TMDB > Toplu Veri Çekme</strong> sayfasına gidin.',
                            'Use this menu to quickly add hundreds of movies to your site. Go to <strong>TMDB > Bulk Fetch</strong> page.'
                        ); ?></p>
                        <ul class="tmdb-doc-list">
                            <li><?php echo tmdb__('İçerik türünü seçin (Film veya Dizi).', 'Select content type (Movie or TV).'); ?></li>
                            <li><?php echo tmdb__('Metin kutusuna her satıra bir tane gelecek şekilde TMDB ID\'lerini yapıştırın.', 'Paste TMDB IDs into the text area, one per line.'); ?></li>
                            <li><?php echo tmdb__('"Verileri Çek ve Oluştur" butonuna basın.', 'Click "Fetch & Create" button.'); ?></li>
                            <li><?php echo tmdb__('Eklenti sırayla tüm ID\'leri işleyecek ve sitenizde "Taslak" (Draft) durumunda yeni yazılar oluşturacaktır.', 'The plugin will process all IDs sequentially and create new posts in "Draft" status on your site.'); ?></li>
                        </ul>
                    </div>

                    <!-- 4. Görünüm Ayarları -->
                    <div class="doc-block">
                        <h3>🎨 4. <?php echo tmdb__('Görünüm ve Otomatik Gösterim', 'Appearance & Auto Display'); ?></h3>
                        <p><?php echo tmdb__(
                            'Çekilen veriler (Poster, Puan, Oyuncular vb.) yazılarınızın içinde şık bir kart tasarımında gösterilir. Bu kartın davranışını <strong>Genel Ayarlar</strong> ve <strong>Görünüm Ayarları</strong> sekmelerinden yönetebilirsiniz.',
                            'Fetched data (Poster, Score, Cast etc.) is displayed in a stylish card design within your posts. You can manage the behavior of this card from <strong>General Settings</strong> and <strong>Display Settings</strong> tabs.'
                        ); ?></p>
                        <ul class="tmdb-doc-list">
                            <li><strong>İçerik Konumu:</strong> <?php echo tmdb__('Kartın yazının başında mı yoksa sonunda mı görüneceğini seçebilirsiniz.', 'You can choose whether the card appears at the beginning or end of the post.'); ?></li>
                            <li><strong>Öğeleri Gizle/Göster:</strong> <?php echo tmdb__('Görünüm Ayarları sekmesinden oyuncu kadrosu, hasılat bilgisi veya fragman gibi alanları tek tıkla kapatıp açabilirsiniz.', 'You can toggle visibility of fields like cast, revenue info, or trailer from the Display Settings tab.'); ?></li>
                        </ul>
                    </div>

                    <!-- 5. Shortcodes -->
                    <div class="doc-block">
                        <h3>🧩 5. Shortcode (Kısa Kod) <?php echo tmdb__('Kullanımı', 'Usage'); ?></h3>
                        <p><?php echo tmdb__(
                            'Eğer otomatik ekleme (Auto Inject) özelliğini kapattıysanız veya sayfanın özel bir yerinde kart göstermek istiyorsanız aşağıdaki kısa kodu kullanabilirsiniz:',
                            'If you disabled Auto Inject or want to display a card in a specific location, you can use the shortcode below:'
                        ); ?></p>
                        <div style="background:#eee; padding:15px; border-radius:5px; margin:10px 0;">
                            <code>[tmdb_card id="550" type="movie"]</code>
                        </div>
                        <ul class="tmdb-doc-list">
                            <li><strong>id:</strong> <?php echo tmdb__('Filmin veya dizinin TMDB ID numarası.', 'TMDB ID number of the movie or show.'); ?></li>
                            <li><strong>type:</strong> <?php echo tmdb__('Film için "movie", dizi için "tv" yazılmalıdır.', 'Use "movie" for movies, "tv" for TV shows.'); ?></li>
                        </ul>
                        <p><em><?php echo tmdb__('Bu kısa kod, özel "Tomb Raider" stili geniş ve arka planlı bir kart tasarımı kullanır.', 'This shortcode uses a special "Tomb Raider" style wide card design with backdrop.'); ?></em></p>
                    </div>

                </div>
            </div>
        </div>
        <style>
            .doc-block { margin-bottom: 40px; border-bottom: 1px solid #eee; padding-bottom: 30px; }
            .doc-block:last-child { border-bottom: none; }
            .doc-block h3 { color: #2c3e50; border-left: 5px solid <?php echo esc_attr($primary); ?>; padding-left: 15px; font-size: 1.4em; margin-bottom: 15px; }
            .tmdb-doc-list { list-style: disc; padding-left: 20px; line-height: 1.8; color: #444; }
            .tmdb-doc-list li { margin-bottom: 8px; }
            .tmdb-doc-list strong { color: #333; }
            code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-family: monospace; color: #c7254e; }
        </style>
        <?php
    }

    // =========================================================
    // SAYFA 5: LİSANS YÖNETİMİ
    // =========================================================
    public static function render_license_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $license_key = get_option( 'tmdb_license_key' );
        $license_status = get_option( 'tmdb_license_status' );
        $is_licensed = ( $license_status === 'valid' );

        $status_html = '';
        if ( $is_licensed ) {
            $status_html = '<span class="tmdb-badge" style="background:green;">✅ ' . tmdb__('Aktif', 'Active') . '</span>';
        } elseif ( !empty($license_key) ) {
            $status_html = '<span class="tmdb-badge" style="background:red;">❌ ' . tmdb__('Geçersiz', 'Invalid') . '</span>';
        } else {
            $status_html = '<span class="tmdb-badge" style="background:#f39c12;">⚠️ ' . tmdb__('Bekliyor', 'Pending') . '</span>';
        }
        ?>
        <div class="<?php echo esc_attr( self::get_wrapper_class() ); ?>">
            <h1>🔑 <?php echo tmdb__('Lisans Yönetimi', 'License Management'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'tmdb_ozcanwork_options' ); ?>
                <input type="hidden" name="tmdb_api_key" value="<?php echo esc_attr(get_option('tmdb_api_key')); ?>"> 
                <input type="hidden" name="tmdb_language" value="<?php echo esc_attr(get_option('tmdb_language')); ?>">
                
                <div class="tmdb-card settings-card" style="max-width: 600px; border-top: 4px solid #f39c12;">
                    <h2><?php echo tmdb__('Aktivasyon', 'Activation'); ?></h2>
                    <p><?php echo tmdb__('Eklenti özelliklerini açmak için lisansınızı girin.', 'Enter your license key to unlock plugin features.'); ?></p>
                    
                    <!-- LİSANS ALMA KUTUSU -->
                    <?php if ( ! $is_licensed ) : ?>
                    <div style="background: #fdf2ce; border-left: 4px solid #f39c12; padding: 15px; margin-bottom: 20px;">
                        <p style="margin: 0 0 10px 0;">
                            <strong><?php echo tmdb__('Lisans anahtarınız yok mu?', 'Don\'t have a license key?'); ?></strong><br>
                            <?php echo tmdb__('Sitemizden saniyeler içinde ücretsiz lisans oluşturabilirsiniz.', 'You can generate a free license in seconds from our site.'); ?>
                        </p>
                        <a href="https://www.key.ozcan.work/tmdb/" target="_blank" class="button button-secondary">
                            🔑 <?php echo tmdb__('Lisans Anahtarı Al', 'Get License Key'); ?> &rarr;
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group" style="background:#f9f9f9; padding:20px; border:1px solid #ddd; border-radius:5px;">
                        <label for="tmdb_license_key" style="font-size:1.1em; margin-bottom:10px; display:block;"><strong>License Key</strong> <?php echo $status_html; ?></label>
                        <input type="text" id="tmdb_license_key" name="tmdb_license_key" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text" style="width:100%; padding:12px; font-size:1.1em;" placeholder="TMDB-XXXX-XXXX-XXXX" />
                    </div>
                    
                    <div style="margin-top:20px; padding:15px; background:#fff; border-radius:5px; border:1px solid #eee;">
                        <strong>System Info:</strong>
                        <ul style="margin-top:5px; margin-bottom:0; color:#777; font-size:0.9em;">
                            <li>Domain: <code><?php echo $_SERVER['SERVER_NAME']; ?></code></li>
                            <li>Status: <strong><?php echo $is_licensed ? 'ACTIVE' : 'INACTIVE'; ?></strong></li>
                        </ul>
                    </div>

                    <?php submit_button( tmdb__('Doğrula ve Kaydet', 'Verify & Save'), 'primary large', 'submit', true, array('style' => 'margin-top:20px; width:100%;') ); ?>
                </div>
            </form>
        </div>
        <?php
    }

    private static function render_locked_screen() {
        ?>
        <div class="wrap">
            <h1>🚫 Access Denied</h1>
            <div class="notice notice-error inline" style="padding: 20px; border-left-width: 5px;">
                <h2><?php echo tmdb__('Eklenti Kilitli', 'Plugin Locked'); ?></h2>
                <p style="font-size: 1.1em;"><?php echo tmdb__('Ayarları görmek için lütfen lisansınızı aktif ediniz.', 'Please activate your license to view settings.'); ?></p>
                <p><a href="admin.php?page=tmdb-ozcanwork-license" class="button button-primary button-large">🔑 <?php echo tmdb__('Lisans Sayfasına Git', 'Go to License Page'); ?></a></p>
            </div>
        </div>
        <?php
    }
}
