<?php
/**
 * File: kurikulum.php
 * Halaman Kurikulum Program Studi Doktor Ilmu Komputer UKSW
 */

$page_title = "Kurikulum - Program Studi Doktor Ilmu Komputer UKSW";

include '../includes/sidebar_publik.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-color: #0D6EFD;
        --success-color: #198754;
        --warning-color: #FFC107;
        --danger-color: #DC3545;
        --info-color: #0DCAF0;
        --light-bg: #F8F9FA;
        --dark-text: #212529;
        --muted-text: #6C757D;
        --border-color: #E4E4E4;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 29.06%);
        min-height: 100vh;
        color: var(--dark-text);
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1030;
        background: var(--primary-color);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 12px 18px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .mobile-menu-btn:hover {
        background: #0b5ed7;
        transform: scale(1.05);
    }

    .mobile-menu-btn.active {
        background: #0b5ed7;
    }

    /* Overlay untuk mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1010;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.show {
        display: block;
        opacity: 1;
    }

    /* Content dengan Sidebar */
    .content-wrapper {
        margin-left: 0;
        background: transparent;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    @media (min-width: 769px) {
        .content-wrapper {
            margin-left: 269px;
        }
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        width: 100%;
        height: 355px;
        margin-bottom: 31px;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 355px;
        left: 0;
        top: 0;
        background: url('../assets/foto_header.png') center/cover;
        z-index: 0;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 355px;
        left: 0;
        top: 0;
        background: linear-gradient(90deg, rgba(109, 150, 101, 0.6) 0%, rgba(124, 105, 65, 0.6) 31.25%, rgba(0, 0, 0, 0.8) 98.08%);
        z-index: 1;
    }

    .hero-content {
        position: absolute;
        left: 59px;
        top: 147px;
        z-index: 10;
    }

    .hero-content h1 {
        font-family: 'Poppins', sans-serif;
        font-style: normal;
        font-weight: 700;
        font-size: 25px;
        line-height: 38px;
        letter-spacing: 0.01em;
        color: #FFFFFF;
        margin: 0 0 8px 0;
    }

    .hero-breadcrumb {
        font-family: 'Poppins', sans-serif;
        font-style: normal;
        font-weight: 500;
        font-size: 15px;
        line-height: 22px;
        letter-spacing: 0.01em;
        color: #FFFFFF;
        margin: 0;
    }

    .hero-breadcrumb .separator {
        margin: 0 8px;
    }

    /* Main Container */
    .main-container {
        position: relative;
        padding: 0 37px 60px 37px;
        max-width: 1440px;
        margin: 0 auto;
        background: transparent;
    }

    /* Page Title */
    .page-title {
        text-align: center;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 32px;
        color: var(--dark-text);
        margin-bottom: 40px;
        position: relative;
        padding-bottom: 15px;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--info-color));
        border-radius: 2px;
    }

    /* Content Card */
    .content-card {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .content-card:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .content-card p {
        font-family: 'Poppins', sans-serif;
        font-size: 16px;
        line-height: 32px;
        color: var(--muted-text);
        text-align: justify;
        margin-bottom: 24px;
    }

    .content-card p:last-child {
        margin-bottom: 0;
    }

    .content-card strong {
        color: var(--dark-text);
        font-weight: 600;
    }

    /* Model PBM Section */
    .model-section {
        margin: 30px 0;
    }

    .model-section h3 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 24px;
        color: var(--dark-text);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .model-section h3 i {
        color: var(--primary-color);
    }

    .model-item {
        background: var(--light-bg);
        border-radius: 10px;
        padding: 20px 25px;
        margin-bottom: 15px;
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .model-item:hover {
        background: #e3f2fd;
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
    }

    .model-item h4 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: var(--dark-text);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .model-item h4 i {
        color: var(--success-color);
        font-size: 16px;
    }

    .model-item p {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 28px;
        color: var(--muted-text);
        margin: 0;
        text-align: left;
    }

    /* Highlight Box */
    .highlight {
        background: linear-gradient(120deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%);
        padding: 25px 30px;
        border-radius: 10px;
        border-left: 4px solid var(--primary-color);
        margin: 30px 0;
    }

    .highlight p {
        font-family: 'Poppins', sans-serif;
        font-size: 16px;
        line-height: 32px;
        color: var(--dark-text);
        font-weight: 500;
        margin: 0;
        text-align: justify;
    }

    /* Methods Grid */
    .methods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .method-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .method-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.15);
        border-color: var(--primary-color);
    }

    .method-card i {
        font-size: 40px;
        margin-bottom: 15px;
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .method-card h4 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: var(--dark-text);
        margin-bottom: 10px;
    }

    .method-card p {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 24px;
        color: var(--muted-text);
        margin: 0;
    }

    /* Language Switcher */
    .language-switcher {
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: flex;
        gap: 10px;
        z-index: 1000;
        background: #FFFFFF;
        padding: 10px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
    }

    .lang-btn {
        padding: 10px 20px;
        border: 2px solid var(--primary-color);
        background-color: white;
        color: var(--primary-color);
        font-weight: 600;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
    }

    .lang-btn.active,
    .lang-btn:hover {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
        transform: translateY(-1px);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-container {
            padding: 0 25px 40px 25px;
        }
        
        .content-card {
            padding: 30px;
        }
        
        .methods-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
        }
        
        .mobile-menu-btn {
            display: block;
        }
        
        .hero-section {
            height: 250px;
        }
        
        .hero-section::before,
        .hero-section::after {
            height: 250px;
        }
        
        .hero-content {
            left: 20px;
            top: 100px;
        }
        
        .hero-content h1 {
            font-size: 20px;
            line-height: 30px;
        }
        
        .hero-breadcrumb {
            font-size: 14px;
        }
        
        .main-container {
            padding: 0 20px 30px 20px;
        }
        
        .page-title {
            font-size: 24px;
            margin-bottom: 30px;
        }
        
        .content-card {
            padding: 20px;
        }
        
        .content-card p {
            font-size: 14px;
            line-height: 28px;
        }
        
        .model-item {
            padding: 15px 20px;
        }
        
        .model-item h4 {
            font-size: 16px;
        }
        
        .methods-grid {
            grid-template-columns: 1fr;
        }
        
        .language-switcher {
            bottom: 20px;
            right: 20px;
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .hero-section {
            height: 200px;
        }
        
        .hero-section::before,
        .hero-section::after {
            height: 200px;
        }
        
        .hero-content {
            left: 15px;
            top: 80px;
        }
        
        .hero-content h1 {
            font-size: 18px;
            line-height: 24px;
        }
        
        .hero-breadcrumb {
            font-size: 12px;
        }
        
        .main-container {
            padding: 0 15px 20px 15px;
        }
        
        .page-title {
            font-size: 20px;
        }
        
        .content-card {
            padding: 15px;
        }
        
        .content-card p {
            font-size: 13px;
            line-height: 24px;
        }
        
        .model-item {
            padding: 12px 15px;
        }
        
        .highlight {
            padding: 20px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 80px;
        right: 30px;
        background: var(--primary-color);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
        opacity: 0;
        visibility: hidden;
        z-index: 999;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background: #0b5ed7;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <div class="content-wrapper">
        <!-- Hero Section -->
        <section class="hero-section" role="banner">
            <div class="hero-content">
                <h1>Kurikulum DIK</h1>
                <p class="hero-breadcrumb">Beranda<span class="separator">›</span>Halaman</p>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-container" role="main">
            <h1 class="page-title fade-in-up">Kurikulum Program Doktor Ilmu Komputer</h1>

            <article class="content-card fade-in-up">
                <p>
                    Sebagai penyelenggara akademik aras pendidikan Doktoral (S3), maka Program Studi selalu merespon dan melakukan penyesuaian secara tepat terhadap perubahan yang terjadi di masyarakat. Untuk itu Program Studi selalu pro aktif dengan melakukan kegiatan-kegiatan inovatif untuk mengatasi masalah yang tampak, membangun relasi komunikasi yang efisien dengan pihak atau individu yang mumpuni, baik dari dalam lingkungan lembaga UKSW maupun dengan lembaga diluar UKSW baik yang bertaraf nasional maupun internasional.
                </p>

                <div class="highlight">
                    <p>
                        Program Studi selalu berusaha menjadi lembaga pendidikan tinggi yang kompetitif dengan menyediakan seperangkat program pendidikan, pengajaran, penelitian dan pengabdian masyarakat yang dibutuhkan baik secara nasional dan internasional. Salah satu perangkat penting didalam program pendidikan dan pengajaran tersebut adalah Kurikulum.
                    </p>
                </div>

                <p>
                    Dasar penyusunan kurikulum unggul baik secara nasional maupun internasional adalah berbasis pada intellectual capital, serta menggabungkan antara kearifan lokal dan kebutuhan dari stakeholder. Sesuai dengan Visi dan Misi Program Studi maka kurikulum yang saat ini dibangun adalah Kurikulum Kerangka Kualifikasi Nasional Indonesia (KKNI), mampu diterapkan ke mahasiswa serta menghasilkan profil lulusan yang unggul.
                </p>
            </article>

            <!-- Model Pelaksanaan Proses Belajar Mengajar -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-mortarboard"></i>Model Pelaksanaan Proses Belajar Mengajar (PBM)</h3>
                    
                    <p style="margin-bottom: 25px;">
                        Karakteristik pelaksanaan pembelajaran memperhatikan sifat interactiviti, holistik, integratif, saintifik, kontekstual, tematik, kolaboratif, dan berpusat pada mahasiswa.
                    </p>

                    <div class="model-item">
                        <h4><i class="bi bi-arrow-left-right"></i>Interaktif</h4>
                        <p>Menyatakan bahwa capaian pembelajaran lulusan diraih dengan menggunakan proses interaksi dua arah antara mahasiswa dan dosen.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-globe"></i>Holistik</h4>
                        <p>Mencerminkan bahwa proses pembelajaran mendorong terbentuknya pola pikir yang komprehensif dan luas dengan menginternalisasi keunggulan dan kearifan lokal maupun nasional.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-puzzle"></i>Integratif</h4>
                        <p>Menunjukkan bahwa capaian pembelajaran lulusan diraih melalui proses pembelajaran yang terintegrasi untuk memenuhi capaian pembelajaran lulusan secara keseluruhan dalam satu kesatuan program melalui pendekatan antardisiplin dan multidisiplin.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-flask"></i>Saintifik</h4>
                        <p>Menyatakan bahwa capaian pembelajaran lulusan diraih melalui proses pembelajaran yang mengutamakan pendekatan ilmiah sehingga tercipta lingkungan akademik yang berdasarkan sistem nilai, norma, dan kaidah ilmu pengetahuan serta menjunjung tinggi nilai-nilai agama dan kebangsaan.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-gear"></i>Kontekstual</h4>
                        <p>Menjelaskan bahwa capaian pembelajaran lulusan diraih melalui proses pembelajaran yang disesuaikan dengan tuntutan kemampuan menyelesaikan masalah dalam ranah keahliannya.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-lightbulb"></i>Tematik</h4>
                        <p>Berarti capaian pembelajaran lulusan diraih melalui proses pembelajaran yang disesuaikan dengan karakteristik keilmuan program studi dan dikaitkan dengan permasalahan nyata melalui pendekatan transdisiplin. Efektif menyatakan bahwa capaian pembelajaran lulusan diraih secara berhasil guna dengan mementingkan internalisasi materi secara baik dan benar dalam kurun waktu yang optimum.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-people"></i>Kolaboratif</h4>
                        <p>Adalah proses pembelajaran bersama yang melibatkan interaksi antar individu pembelajar untuk menghasilkan kapitalisasi sikap, pengetahuan, dan keterampilan dalam upaya meraih capaian pembelajaran.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-person-circle"></i>Berpusat pada Mahasiswa</h4>
                        <p>Menunjukkan bahwa capaian pembelajaran lulusan diraih melalui proses pembelajaran yang mengutamakan pengembangan kreativitas, kapasitas, kepribadian, dan kebutuhan mahasiswa, serta mengembangkan kemandirian dalam mencari dan menemukan pengetahuan.</p>
                    </div>
                </div>
                <!-- Metode Pembelajaran Detail -->
                <div class="model-section">
                    <h3><i class="bi bi-gear"></i>Pelaksanaan Belajar yang diselenggarakan Program Studi adalah menggabungkan metode:</h3>
                    
                    <div class="model-item">
                        <h4><i class="bi bi-book"></i>1. Kuliah Kelas Tutorial</h4>
                        <p>Yang diisi dengan perpaduan pemaparan dosen dan diskusi dengan peserta belajar atau mahasiswa yang aktif menggali informasi. Pada kuliah kelas tutorial, mahasiswa dituntut untuk aktif mempersiapkan dan menggali informasi sumber belajar baik di perpustakaan, internet (e-book dan atau e-journal), studi kasus lapangan dan laboratorium. Kelas tutorial dilakukan pada tahun pertama.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-tasks"></i>2. Tugas Kuliah Terstruktur</h4>
                        <p>Pemecahan kasus lapangan secara mandiri dan atau kelompok oleh mahasiswa untuk mengembangkan kemampuan analitis dan problem solving.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-people"></i>3. Pembelajaran Kolaboratif</h4>
                        <p>Penyelesaian tugas dan pemecahan masalah melalui kerja sama dalam kelompok untuk meningkatkan kemampuan kolaborasi dan komunikasi.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-laptop"></i>4. Praktikum Laboratorium & Lapangan</h4>
                        <p>Praktikum laboratorium dan atau lapangan secara bersama dan atau mandiri untuk mengaplikasikan teori ke dalam praktik nyata.</p>
                    </div>

                    <div class="model-item">
                        <h4><i class="bi bi-clipboard-check"></i>5. Evaluasi Berkelanjutan</h4>
                        <p>Sistem evaluasi oleh dosen terhadap mahasiswa selalu dilakukan setiap saat pada proses pembelajaran untuk setiap bahan kajian selesai dibahas.</p>
                    </div>
                </div>
            </article>

            <!-- Kurikulum -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-journal-bookmark"></i>Kurikulum</h3>
                    
                    <div class="highlight">
                        <p>
                            Besaran beban studi mahasiswa untuk strata S3 Program Studi Doktor Ilmu Komputer UKSW adalah <strong>42 SKS</strong> (Satuan Kredit Semester). Masa studi normal Program Studi Doktor Ilmu Komputer UKSW adalah <strong>4 tahun</strong> dan paling lama <strong>7 tahun</strong> dengan <strong>IPK minimal 3.25</strong>.
                        </p>
                    </div>

                    <!-- Struktur Kurikulum -->
                    <div class="methods-grid">
                        <div class="method-card">
                            <i class="bi bi-1-circle"></i>
                            <h4>Semester 1</h4>
                            <p><strong>Fundamental</strong><br>12 SKS</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-2-circle"></i>
                            <h4>Semester 2</h4>
                            <p><strong>Pendukung Penelitian</strong><br>12 SKS</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-file-earmark-text"></i>
                            <h4>Proposal</h4>
                            <p><strong>Penelitian</strong><br>3 SKS</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-clipboard-check"></i>
                            <h4>Ujian Kualifikasi</h4>
                            <p><strong>Komprehensif</strong><br>3 SKS</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-journal"></i>
                            <h4>Publikasi I</h4>
                            <p><strong>Konferensi Internasional</strong><br>3 SKS</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-journals"></i>
                            <h4>Publikasi II</h4>
                            <p><strong>Jurnal Internasional</strong><br>3 SKS</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-mortarboard"></i>
                            <h4>Ujian Tertutup</h4>
                            <p><strong>Disertasi</strong><br>6 SKS</p>
                        </div>
                    </div>

                    <!-- Persyaratan Publikasi -->
                    <div class="model-item" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(13, 110, 253, 0.1) 100%); border-left-color: var(--success-color);">
                        <h4><i class="bi bi-megaphone"></i>Persyaratan Publikasi</h4>
                        <p>
                            Mahasiswa wajib melakukan <strong>1 publikasi dalam konferensi internasional yang bereputasi</strong> dan <strong>2 publikasi dalam jurnal internasional bereputasi minimal Scopus Q2</strong> atau sederajat sebagai syarat kelulusan. Semua publikasi yang dipakai sebagai syarat kelulusan harus mencantumkan afiliasi <strong>Universitas Kristen Satya Wacana</strong>.
                        </p>
                    </div>
                </div>
            </article>

            <!-- Profil Lulusan -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-person-badge"></i>Profil Lulusan</h3>
                    
                    <p style="margin-bottom: 25px;">
                        Melalui serangkaian program pendidikan akademik dan non akademik, maka Lulusan Program Studi Doktor Ilmu Komputer Universitas Kristen Satya Wacana dirumuskan sebagai berikut:
                    </p>

                    <div class="highlight">
                        <p>
                            <strong>Mampu memecahkan permasalahan sains dan atau teknologi di bidang komputer melalui pendekatan inter, multi atau transdisipliner</strong> bidang Software Engineering, Data Analytics and Intelligence Systems, dan Network Technology and Data Security.
                        </p>
                    </div>

                    <p>
                        Dengan demikian lulusan dapat berperan dalam dunia industri di level menengah ke atas, pemerintahan, perguruan tinggi, lembaga penelitian dan entrepreneur.
                    </p>

                    <!-- Bidang Keahlian -->
                    <div class="methods-grid">
                        <div class="method-card">
                            <i class="bi bi-code-slash"></i>
                            <h4>Software Engineering</h4>
                            <p>Pengembangan perangkat lunak dan sistem informasi yang kompleks</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-graph-up"></i>
                            <h4>Data Analytics & Intelligence Systems</h4>
                            <p>Analisis data besar dan pengembangan sistem kecerdasan buatan</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-shield-lock"></i>
                            <h4>Network Technology & Data Security</h4>
                            <p>Teknologi jaringan dan keamanan data digital</p>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Capaian Pembelajaran -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-bullseye"></i>Capaian Pembelajaran</h3>
                    
                    <p style="margin-bottom: 25px;">
                        Rumusan capaian program studi doktor ilmu komputer FTI-UKSW yang sesuai dengan level 9 KKNI dan SN-Dikti adalah sebagai berikut:
                    </p>

                    <div class="model-item">
                        <h4><i class="bi bi-compass"></i>A. Lingkup Kompetensi KKNI dalam Aspek Kemampuan di Bidang Kerja</h4>
                        
                        <div class="mt-3">
                            <h5 style="color: var(--primary-color); margin-bottom: 15px;">Kode LO-1</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu mengidentifikasi masalah di bidang komputer yang bersifat baru dan original, merumuskan alternatif pemecahan melalui pendekatan penelitian yang inovatif dan teruji.
                            </p>
                        </div><div class="mt-4">
                            <h5 style="color: var(--primary-color); margin-bottom: 15px;">Kode LO-2</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu berpikir kritis untuk menyelesaikan permasalahan komputer yang berbasis teori dasar di bidang Software Engineering, Data Analytics dan Sistem Cerdas, serta Network Technology & Data Security.
                            </p>
                        </div>
                    </div>

                    <!-- Aspek Pengetahuan yang Dikuasai -->
                    <div class="model-item mt-4">
                        <h4><i class="bi bi-journal-text"></i>B. Lingkup Kompetensi KKNI dalam Aspek Pengetahuan yang Dikuasai</h4>
                        
                        <div class="mt-3">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-3</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu merancang dan melaksanakan eksperimen di bidang software engineering, sekaligus menganalisis dan menginterpretasi data.
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-4</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu merancang sistem, komponen dan/atau proses yang sesuai dengan kebutuhan di bidang-bidang yang realistis, misalnya ekonomi, lingkungan, sosial, politik, etik, kesehatan, industrialisasi, dan kelestarian lingkungan.
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-5</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu membentuk dan bekerjasama dalam kelompok yang multidisiplin.
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-6</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu merumuskan dan melakukan analisis statistik terhadap masalah-masalah yang harus menggunakan sistem cerdas.
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-7</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu menggunakan analisis prediktif (predictive analytics).
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-8</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu merancang dan melaksanakan analisis layanan dan media sosial (Service & Social Media Analytics).
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-9</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu melaksanakan analisis resiko dalam jaringan komputer.
                            </p>
                        </div>

                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-10</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu menganalisis dan mengimplementasikan mekanisme keamanan, kebijakan keamanan, komponen-komponen keamanan (seperti domain proteksi dan firewall), proteksi dan keamanan port untuk mengamankan jaringan komputer.
                            </p>
                        </div>
                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-11</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu menganalisis dan mengimplementasikan standar keamanan yang relevan dan efektif, memiliki pertimbangan etis, dan memiliki pengetahuan yang luas tentang isu-isu keamanan untuk merancang jaringan yang aman.
                            </p>
                        </div>
                        <div class="mt-4">
                            <h5 style="color: var(--success-color); margin-bottom: 15px;">Kode LO-12</h5>
                            <p style="margin-bottom: 0;">
                                Setelah menyelesaikan program, mahasiswa mampu menyelesaikan masalah- masalah jaringan komputer dan keamanannya dengan mempertimbangkan nilai bisnis, isu teknis dan keamanan.
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Kompetensi -->
                    <div class="methods-grid mt-4">
                        <div class="method-card">
                            <i class="bi bi-gear"></i>
                            <h4>Software Engineering</h4>
                            <p>LO-2, LO-3, LO-4: Kompetensi dalam rekayasa perangkat lunak dan sistem</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-graph-up"></i>
                            <h4>Data & Analytics</h4>
                            <p>LO-6, LO-7, LO-8: Kemampuan analisis data dan sistem cerdas</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-shield-lock"></i>
                            <h4>Network & Security</h4>
                            <p>LO-9, LO-10, LO-11, LO-12: Keahlian jaringan komputer dan keamanan siber</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-people"></i>
                            <h4>Kolaborasi</h4>
                            <p>LO-5: Kemampuan kerja sama multidisiplin</p>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Informasi Tambahan -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-info-circle"></i>Informasi Tambahan</h3>
                    
                    <div class="methods-grid">
                        <div class="method-card" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 202, 240, 0.05) 100%);">
                            <i class="bi bi-clock"></i>
                            <h4>Masa Studi</h4>
                            <p>Normal: 4 tahun<br>Maksimal: 7 tahun</p>
                        </div>

                        <div class="method-card" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(13, 110, 253, 0.05) 100%);">
                            <i class="bi bi-graph-up"></i>
                            <h4>IPK Minimum</h4>
                            <p>IPK Kelulusan: 3.25</p>
                        </div>

                        <div class="method-card" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, rgba(13, 110, 253, 0.05) 100%);">
                            <i class="bi bi-journal"></i>
                            <h4>Publikasi Wajib</h4>
                            <p>1 Konferensi + 2 Jurnal Internasional</p>
                        </div>

                        <div class="method-card" style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(13, 110, 253, 0.05) 100%);">
                            <i class="bi bi-award"></i>
                            <h4>Standar Kualitas</h4>
                            <p>Scopus Q2 Minimal<br>Dengan Afiliasi UKSW</p>
                        </div>
                    </div>
                </div>
            </article>
            <!-- Daftar Mata Kuliah -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-list-check"></i>Daftar Mata Kuliah</h3>
                    
                    <p style="margin-bottom: 25px;">
                        Daftar nama mata kuliah/Blok Prodi Doktor Ilmu Komputer FTI-UKSW:
                    </p>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead style="background: linear-gradient(135deg, var(--primary-color), var(--info-color)); color: white;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">NO</th>
                                    <th style="width: 100px; text-align: center;">KODE</th>
                                    <th style="width: 80px; text-align: center;">SKS</th>
                                    <th>MATA KULIAH/BLOK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">1</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI710</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Falsafah Sains</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">2</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI711</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Metode Riset</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI712</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Software Engineering</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">4</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI713</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Data Analytic and Intelligence System</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">5</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI714</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Critical Thinking</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">6</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI715</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Network Engineering and Security</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">7</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI716</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Kapita Selekta</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">8</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI717</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Academic Writing</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">9</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI718</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Ujian Kualifikasi</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">10</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI719</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Ujian Proposal</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">11</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI720</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Publikasi 1</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">12</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI721</td>
                                    <td style="text-align: center; font-weight: 600;">3</td>
                                    <td>Publikasi 2</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">13</td>
                                    <td style="text-align: center; font-family: 'Courier New', monospace; font-weight: 600;">DI722</td>
                                    <td style="text-align: center; font-weight: 600;">6</td>
                                    <td>Ujian Tertutup</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background: var(--light-bg);">
                                    <td colspan="2" style="text-align: right; font-weight: 600;">Total SKS:</td>
                                    <td style="text-align: center; font-weight: 600;">42</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Keterangan Mata Kuliah -->
                    <div class="methods-grid mt-4">
                        <div class="method-card">
                            <i class="bi bi-1-circle"></i>
                            <h4>Semester 1-2</h4>
                            <p>Mata Kuliah Fundamental & Pendukung Penelitian (DI710-DI717)</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-2-circle"></i>
                            <h4>Semester 3-4</h4>
                            <p>Ujian Kualifikasi & Proposal (DI718-DI719)</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-3-circle"></i>
                            <h4>Semester 5-6</h4>
                            <p>Publikasi & Penelitian (DI720-DI721)</p>
                        </div>

                        <div class="method-card">
                            <i class="bi bi-4-circle"></i>
                            <h4>Semester 7-8</h4>
                            <p>Ujian Tertutup & Wisuda (DI722)</p>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Ringkasan Kurikulum -->
            <article class="content-card fade-in-up">
                <div class="model-section">
                    <h3><i class="bi bi-diagram-3"></i>Struktur Kurikulum</h3>
                    
                    <div class="methods-grid">
                        <div class="method-card" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%);">
                            <i class="bi bi-book"></i>
                            <h4>Mata Kuliah Inti</h4>
                            <p><strong>24 SKS</strong><br>7 mata kuliah fundamental</p>
                        </div>

                        <div class="method-card" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%);">
                            <i class="bi bi-clipboard-check"></i>
                            <h4>Ujian</h4>
                            <p><strong>12 SKS</strong><br>Kualifikasi, Proposal & Tertutup</p>
                        </div>

                        <div class="method-card" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%);">
                            <i class="bi bi-journal"></i>
                            <h4>Publikasi</h4>
                            <p><strong>6 SKS</strong><br>2 publikasi wajib</p>
                        </div>

                        <div class="method-card" style="background: linear-gradient(135deg, rgba(108, 117, 125, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%);">
                            <i class="bi bi-calculator"></i>
                            <h4>Total</h4>
                            <p><strong>42 SKS</strong><br>Seluruh komponen</p>
                        </div>
                    </div>

                    <!-- Timeline Studi -->
                    <div class="model-item mt-4" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 202, 240, 0.05) 100%);">
                        <h4><i class="bi bi-calendar-check"></i>Timeline Studi</h4>
                        <p>
                            <strong>Tahun 1-2:</strong> Penyelesaian mata kuliah inti dan persiapan penelitian<br>
                            <strong>Tahun 3:</strong> Ujian kualifikasi dan proposal disertasi<br>
                            <strong>Tahun 4:</strong> Penelitian, publikasi, dan ujian tertutup
                        </p>
                    </div>
                </div>
            </article>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            menuBtn.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open
            if (sidebar.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Language switcher functionality
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show language change notification
                const lang = this.textContent === 'IND' ? 'Bahasa Indonesia' : 'English';
                showNotification(`Bahasa diubah ke ${lang}`);
            });
        });

        // Back to top functionality
        const backToTopButton = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Notification function
        function showNotification(message) {
            // Remove existing notification
            const existingNotification = document.querySelector('.custom-notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Create new notification
            const notification = document.createElement('div');
            notification.className = 'custom-notification';
            notification.innerHTML = `
                <div style="position: fixed; top: 20px; right: 20px; background: var(--success-color); color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1060; font-family: 'Poppins', sans-serif; font-size: 14px; animation: slideInRight 0.3s ease-out;">
                    <i class="bi bi-check-circle" style="margin-right: 8px;"></i>${message}
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Add slideInRight animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);

        // Add intersection observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all fade-in-up elements
        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Add hover effects for model items
        document.querySelectorAll('.model-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>