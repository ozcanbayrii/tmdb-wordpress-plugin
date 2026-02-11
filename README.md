# 🎬 TMDB OzcanWork - Premium WordPress Plugin

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue) ![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue) ![License](https://img.shields.io/badge/License-Proprietary-red)

**[TR]** WordPress sitenizi otomatik olarak profesyonel bir film ve dizi veritabanına dönüştüren gelişmiş bir entegrasyon eklentisi.  
**[EN]** An advanced integration plugin that automatically transforms your WordPress site into a professional movie and TV show database.

---

## 🇹🇷 Türkçe Dokümantasyon

**TMDB OzcanWork**, The Movie Database (TMDB) API'sini kullanarak film ve dizi verilerini (poster, oyuncular, fragman, puan, özet vb.) sitenize çeken, **ACF (Advanced Custom Fields)** ile tam uyumlu çalışan ve modern **Glassmorphism** arayüzü sunan bir WordPress eklentisidir.

### 🔥 Temel Özellikler

*   **Çoklu Dil Desteği:** Eklenti ayarlarından veya WordPress dilinize göre verileri (Film özeti, başlık vb.) otomatik olarak Türkçe veya İngilizce çeker.
*   **Otomatik İçerik Enjeksiyonu:** Yazıların altına veya üstüne şık, glassmorphism efektli bir bilgi kartı ekler.
*   **Toplu Veri Çekme (Bulk Fetch):** Yüzlerce TMDB ID'sini yapıştırın, arkanıza yaslanın. Eklenti hepsini tek tek çeker ve taslak yazı olarak oluşturur.
*   **ACF Entegrasyonu:** Çekilen verileri otomatik olarak özel alanlara (custom fields) işler.
*   **Görsel Yükleyici:** Film posterlerini sunucunuza indirir ve yazının "Öne Çıkan Görseli" olarak ayarlar.
*   **Shortcode Sistemi:** Özel "Tomb Raider" stili kart tasarımlarını istediğiniz yere ekleyin.
*   **Özel Şablon Desteği:** `tmdb-archive.php` şablonu ile filmleri özel bir arşiv sayfasında listeleyin.
*   **Admin Dark Mode:** Göz yormayan, şık bir yönetim paneli deneyimi.

### 🚀 Kurulum

1.  Bu klasörü `wp-content/plugins/` dizinine yükleyin.
2.  WordPress Paneli > **Eklentiler** > **Yeni Ekle** yolunu izleyin ve eklentiyi etkinleştirin.
3.  **TMDB OzcanWork > Genel Ayarlar** sayfasına gidin.
4.  [TheMovieDB.org](https://www.themoviedb.org/settings/api) adresinden aldığınız **API Key**'inizi girin.
5.  Lisans anahtarınızı girerek eklentiyi doğrulayın.

### 📖 Kullanım

#### 1. Tekil İçerik Ekleme
Yeni yazı ekleme ekranında (Gutenberg veya Klasik Editör), **"🎬 TMDB İçerik Yöneticisi"** kutusunu bulun. Film/Dizi seçin, ID'yi girin ve **"Verileri Getir"** butonuna basın. Başlık, içerik ve tüm meta veriler otomatik dolacaktır.

#### 2. Toplu Veri Çekme
`TMDB OzcanWork > Toplu Veri Çekme` menüsüne gidin. ID'leri alt alta yapıştırın ve işlemi başlatın.

#### 3. Shortcode (Kısa Kod)
Özel bir yerde kart göstermek için:
```
[tmdb_card id="550" type="movie"]
[tmdb_card id="66732" type="tv"]
```

#### 4. Arşiv Sayfası
Yeni bir sayfa oluşturun ve Sayfa Özellikleri > Şablon kısmından **"TMDB Arşivi"** seçeneğini seçin. Bu sayfa tüm eklenen filmleri listeleyecektir.

---

## 🇬🇧 English Documentation

**TMDB OzcanWork** is a powerful WordPress plugin that fetches movie and TV show data (posters, cast, trailers, ratings, overview, etc.) using The Movie Database (TMDB) API. It offers full **ACF (Advanced Custom Fields)** compatibility and a modern **Glassmorphism** UI.

### 🔥 Key Features

*   **Multi-Language Support:** Automatically fetches data (Movie overviews, titles, etc.) in English or Turkish based on your plugin settings or WordPress locale.
*   **Automatic Content Injection:** Adds a stylish info card with glassmorphism effect to the top or bottom of your posts automatically.
*   **Bulk Fetch:** Paste hundreds of TMDB IDs, sit back, and relax. The plugin fetches them all and creates draft posts instantly.
*   **ACF Integration:** Automatically maps fetched data to Advanced Custom Fields.
*   **Image Sideloading:** Downloads movie posters to your server and sets them as the "Featured Image".
*   **Shortcode System:** Insert special "Tomb Raider" style cards anywhere on your site.
*   **Custom Template Support:** Includes a `tmdb-archive.php` page template to list all movies in a grid layout.
*   **Admin Dark Mode:** A sleek, eye-friendly admin panel experience.

### 🚀 Installation

1.  Upload this folder to the `wp-content/plugins/` directory.
2.  Go to WordPress Dashboard > **Plugins** and activate the plugin.
3.  Navigate to **TMDB OzcanWork > General Settings**.
4.  Enter your **API Key** obtained from [TheMovieDB.org](https://www.themoviedb.org/settings/api).
5.  Enter your license key to activate the plugin.

### 📖 Usage

#### 1. Adding Single Content
In the post editor screen (Gutenberg or Classic), locate the **"🎬 TMDB Content Manager"** meta box. Select Movie/TV, enter the ID, and click **"Fetch Data"**. Title, content, and all meta data will be auto-filled.

#### 2. Bulk Fetch
Go to `TMDB OzcanWork > Bulk Fetch` menu. Paste IDs (one per line) and start the process.

#### 3. Shortcodes
To display a card in a custom location:
```
[tmdb_card id="550" type="movie"]
[tmdb_card id="66732" type="tv"]
```

#### 4. Archive Page
Create a new page and select **"TMDB Archive"** from Page Attributes > Template. This page will list all added movies.

---

### 🛠 Tech Stack
*   **Backend:** PHP 8.x
*   **Frontend:** Vanilla JS / jQuery, CSS3 (Glassmorphism Variables)
*   **API:** TMDB API v3
*   **CMS:** WordPress Core APIs (Settings, HTTP API, Transients)

---

**Developer:** OzcanWork  
**Website:** [ozcan.work](https://ozcan.work)
