<?php
/**
 * File: fasilitas.php
 * Halaman Fasilitas Program Studi
 */

$page_title = "Fasilitas - Program Studi Doktor Ilmu Komputer UKSW";

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

    .content-card ul {
        list-style: none;
        padding-left: 0;
        margin: 20px 0;
    }

    .content-card ul li {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 32px;
        color: var(--muted-text);
        margin-bottom: 15px;
        padding-left: 35px;
        position: relative;
        text-align: justify;
    }

    .content-card ul li:before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--success-color);
        font-weight: bold;
        font-size: 20px;
        top: 2px;
    }

    /* Video Container */
    .video-container {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .video-container:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .video-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 20px;
        color: var(--dark-text);
        margin-bottom: 25px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .video-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 12px;
    }

    /* Slider Section */
    .slider-section {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .slider-section:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .slider-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 20px;
        color: var(--primary-color);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Slider Container */
    .slider-container {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        background: var(--light-bg);
    }

    .slider-wrapper {
        display: flex;
        transition: transform 0.5s ease-in-out;
    }

    .slide {
        min-width: 100%;
        position: relative;
        aspect-ratio: 16/9;
    }

    .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }

    .slide-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
        padding: 30px 20px 20px;
        color: white;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
    }

    /* Slider Navigation */
    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-color);
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .slider-nav:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-50%) scale(1.1);
    }

    .slider-prev {
        left: 15px;
    }

    .slider-next {
        right: 15px;
    }

    /* Slider Dots */
    .slider-dots {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #D1D5DB;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        padding: 0;
    }

    .slider-dot.active {
        background: var(--primary-color);
        width: 30px;
        border-radius: 6px;
    }

    .slider-dot:hover {
        background: var(--primary-color);
        opacity: 0.7;
    }

    /* Counter */
    .slider-counter {
        text-align: center;
        margin-top: 15px;
        color: var(--muted-text);
        font-size: 14px;
        font-weight: 500;
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

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-container {
            padding: 0 25px 40px 25px;
        }
        
        .content-card,
        .video-container,
        .slider-section {
            padding: 30px;
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
        
        .content-card,
        .video-container,
        .slider-section {
            padding: 20px;
        }
        
        .content-card p,
        .content-card ul li {
            font-size: 14px;
            line-height: 28px;
        }

        .slider-nav {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .slider-prev {
            left: 10px;
        }

        .slider-next {
            right: 10px;
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
        
        .content-card,
        .video-container,
        .slider-section {
            padding: 15px;
        }
        
        .content-card p,
        .content-card ul li {
            font-size: 13px;
            line-height: 24px;
        }

        .slider-title,
        .video-title {
            font-size: 16px;
        }

        .slider-nav {
            width: 35px;
            height: 35px;
            font-size: 18px;
        }

        .slide-caption {
            font-size: 14px;
            padding: 20px 15px 15px;
        }
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
                <h1>Fasilitas FTI</h1>
                <p class="hero-breadcrumb">Beranda<span class="separator">›</span>Halaman</p>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-container" role="main">
            <h1 class="page-title fade-in-up">Fasilitas Fakultas Teknologi Informasi</h1>

            <!-- Introduction -->
            <article class="content-card fade-in-up">
                <p>
                    Fakultas Teknologi Informasi mengembangkan sarana-prasarana yang memungkinkan interaksi akademik dapat berjalan. Pengembangan sarana prasarana ini dimungkinkan melalui penyediaan dukungan dana yang besar, dalam bentuk investasi maupun penyediaan fasilitas-fasilitas akademik lainnya. Adapun harapannya, dengan dukungan sarana-prasarana yang baik, maka akan tercipta suasana akademik yang kondusif.
                </p>
                <p><strong>Fasilitas yang tersedia di Fakultas Teknologi Informasi adalah:</strong></p>
                <ul>
                    <li>Penyediaan ruang kerja dosen yang menjamin terciptanya interaksi akademik antar sesama dosen dan antara dosen dengan mahasiswa.</li>
                    <li>Penyediaan LCD dan white board di setiap kelas untuk membantu mahasiswa menyerap materi perkuliahan dengan lebih mudah melalui media audio-visual.</li>
                    <li>Penyediaan hotspot area di lingkungan kampus UKSW sehingga mahasiswa bisa mengakses internet secara gratis setiap saat di area kampus.</li>
                    <li>Penyediaan Laboratorium Komputer UKSW untuk memfasilitasi mahasiswa yang tidak memiliki komputer, agar mereka bisa mengerjakan tugas dan skripsi secara gratis.</li>
                    <li>Khusus untuk intranet lokal intranet Fakultas menggunakan jaringan UTP CAT 6 hingga 1 Gbps, bandwidth yang disediakan intranet sebesar 1 Gbps untuk Fakultas. Selanjutnya Fakultas menggunakan bandwidth internet sebesar 150 Mbps. Secara keseluruhan universitas, bandwith internet yang disediakan sebesar 1,27 Gbps.</li>
                    <li>Penyediaan Laboratorium Internet FTI yang dapat memfasilitasi akses mahasiswa terhadap informasi yang mendukung perkuliahan.</li>
                    <li>Penyediaan laboratorium-laboratorium komputer yang mendukung proses belajar mengajar mahasiswa.</li>
                    <li>Membuat kelas-kelas kecil untuk mata kuliah, guna menjamin interaksi dosen-mahasiswa di dalam kelas berlangsung secara lebih intens.</li>
                    <li>Penyediaan Perpustakaan Notohamidjojo dengan koleksi buku, jurnal, serial, kliping maupun koleksi elektronik yang memadai dan mutakhir, serta dilengkapi pula oleh fasilitas baca yang nyaman.</li>
                    <li>Penyediaan perpustakaan Fakultas yang memiliki koleksi-koleksi buku, jurnal, yang dapat mendukung proses penguatan ilmu para staf dan mahasiswa di lingkungan Fakultas.</li>
                    <li>Pengadaan sarana olah raga (sepak bola, tenis, tenis meja, bola voli, basket) di lingkungan kampus.</li>
                    <li>Pengadaan taman teduh di berbagai titik di lingkungan kampus serta dilengkapi tempat duduk permanen dapat dimanfaatkan mahasiswa atau dosen untuk berdiskusi di luar ruangan.</li>
                    <li>Penyediaan auditorium Fakultas, yang dapat dipergunakan untuk kegiatan seminar, workshop, maupun kegiatan lainnya dilingkungan Fakultas.</li>
                </ul>
            </article>

            <!-- Video Drone -->
            <div class="video-container fade-in-up">
                <h2 class="video-title">
                    <i class="bi bi-camera-video"></i>
                    Video Drone Gedung FTI
                </h2>
                <div class="video-wrapper">
                    <iframe 
                        src="https://drive.google.com/file/d/1-VLceF-AMX8l1yx5KHeqHzndCYfse0XZ/preview" 
                        width="100%" 
                        height="360" 
                        frameborder="0" 
                        allow="autoplay; encrypted-media" 
                        allowfullscreen>
                        </iframe>
                </div>
            </div>

            <!-- Slider 1: Gedung FTI -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-building"></i>
                    Gedung FTI
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-gedung">
                        <div class="slide">
                            <img src="../assets/fasilitas/gedung_fti_1.JPG" alt="Gedung FTI 1" onerror="this.src='https://via.placeholder.com/800x450/0D6EFD/FFFFFF?text=Gedung+FTI+1'">
                            <div class="slide-caption">Gedung FTI - Tampak Depan</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/gedung_fti_2.jpg" alt="Gedung FTI 2" onerror="this.src='https://via.placeholder.com/800x450/0D6EFD/FFFFFF?text=Gedung+FTI+2'">
                            <div class="slide-caption">Gedung FTI - Tampak Samping</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/gedung_fti_3.jpg" alt="Gedung FTI 3" onerror="this.src='https://via.placeholder.com/800x450/0D6EFD/FFFFFF?text=Gedung+FTI+3'">
                            <div class="slide-caption">Gedung FTI - Area Luar</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('gedung', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('gedung', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-gedung"></div>
                <div class="slider-counter" id="counter-gedung"></div>
            </section>

            <!-- Slider 2: Open Space Lantai 2 -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-layout-text-window"></i>
                    Open Space Lantai 2
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-openspace2">
                        <div class="slide">
                            <img src="../assets/fasilitas/openspace_l2_1.jpg" alt="Open Space L2 1" onerror="this.src='https://via.placeholder.com/800x450/198754/FFFFFF?text=Open+Space+L2+1'">
                            <div class="slide-caption">Open Space Lantai 2 - Area 1</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/openspace_l2_2.jpg" alt="Open Space L2 2" onerror="this.src='https://via.placeholder.com/800x450/198754/FFFFFF?text=Open+Space+L2+2'">
                            <div class="slide-caption">Open Space Lantai 2 - Area 2</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('openspace2', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('openspace2', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-openspace2"></div>
                <div class="slider-counter" id="counter-openspace2"></div>
            </section>

            <!-- Slider 3: Open Space Lantai 5 -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    Open Space Lantai 5
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-openspace5">
                        <div class="slide">
                            <img src="../assets/fasilitas/openspace_l5_1.jpg" alt="Open Space L5 1" onerror="this.src='https://via.placeholder.com/800x450/0DCAF0/FFFFFF?text=Open+Space+L5+1'">
                            <div class="slide-caption">Open Space Lantai 5 - Area 1</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/openspace_l5_2.jpg" alt="Open Space L5 2" onerror="this.src='https://via.placeholder.com/800x450/0DCAF0/FFFFFF?text=Open+Space+L5+2'">
                            <div class="slide-caption">Open Space Lantai 5 - Area 2</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('openspace5', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('openspace5', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-openspace5"></div>
                <div class="slider-counter" id="counter-openspace5"></div>
            </section>

            <!-- Slider 4: Ruang Tematik -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-door-open"></i>
                    Ruang Tematik
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-tematik">
                        <div class="slide">
                            <img src="../assets/fasilitas/ruang_tematik_1.jpg" alt="Ruang Tematik 1" onerror="this.src='https://via.placeholder.com/800x450/FFC107/FFFFFF?text=Ruang+Tematik+1'">
                            <div class="slide-caption">Ruang Tematik 1</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/ruang_tematik_2.jpg" alt="Ruang Tematik 2" onerror="this.src='https://via.placeholder.com/800x450/FFC107/FFFFFF?text=Ruang+Tematik+2'">
                            <div class="slide-caption">Ruang Tematik 2</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/ruang_tematik_3.jpg" alt="Ruang Tematik 3" onerror="this.src='https://via.placeholder.com/800x450/FFC107/FFFFFF?text=Ruang+Tematik+3'">
                            <div class="slide-caption">Ruang Tematik 3</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/ruang_tematik_4.jpg" alt="Ruang Tematik 4" onerror="this.src='https://via.placeholder.com/800x450/FFC107/FFFFFF?text=Ruang+Tematik+4'">
                            <div class="slide-caption">Ruang Tematik 4</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/ruang_tematik_5.jpg" alt="Ruang Tematik 5" onerror="this.src='https://via.placeholder.com/800x450/FFC107/FFFFFF?text=Ruang+Tematik+5'">
                            <div class="slide-caption">Ruang Tematik 5</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/ruang_tematik_6.jpg" alt="Ruang Tematik 6" onerror="this.src='https://via.placeholder.com/800x450/FFC107/FFFFFF?text=Ruang+Tematik+6'">
                            <div class="slide-caption">Ruang Tematik 6</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('tematik', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('tematik', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-tematik"></div>
                <div class="slider-counter" id="counter-tematik"></div>
            </section>

            <!-- Slider 5: Auditorium -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-easel"></i>
                    Auditorium
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-auditorium">
                        <div class="slide">
                            <img src="../assets/fasilitas/auditorium_1.jpg" alt="Auditorium 1" onerror="this.src='https://via.placeholder.com/800x450/DC3545/FFFFFF?text=Auditorium+1'">
                            <div class="slide-caption">Auditorium - Tampak Depan</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/auditorium_2.jpg" alt="Auditorium 2" onerror="this.src='https://via.placeholder.com/800x450/DC3545/FFFFFF?text=Auditorium+2'">
                            <div class="slide-caption">Auditorium - Ruang Utama</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/auditorium_3.jpg" alt="Auditorium 3" onerror="this.src='https://via.placeholder.com/800x450/DC3545/FFFFFF?text=Auditorium+3'">
                            <div class="slide-caption">Auditorium - Panggung</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('auditorium', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('auditorium', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-auditorium"></div>
                <div class="slider-counter" id="counter-auditorium"></div>
            </section>

            <!-- Slider 6: Area Parkir -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-p-square"></i>
                    Area Parkir
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-parkir">
                        <div class="slide">
                            <img src="../assets/fasilitas/parkiran_1.jpg" alt="Parkiran 1" onerror="this.src='https://via.placeholder.com/800x450/6C757D/FFFFFF?text=Parkiran+1'">
                            <div class="slide-caption">Area Parkir Motor</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/parkiran_2.jpg" alt="Parkiran 2" onerror="this.src='https://via.placeholder.com/800x450/6C757D/FFFFFF?text=Parkiran+2'">
                            <div class="slide-caption">Area Parkir Mobil</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/parkiran_3.jpg" alt="Parkiran 3" onerror="this.src='https://via.placeholder.com/800x450/6C757D/FFFFFF?text=Parkiran+3'">
                            <div class="slide-caption">Area Parkir Umum</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/parkiran_4.jpg" alt="Parkiran 4" onerror="this.src='https://via.placeholder.com/800x450/6C757D/FFFFFF?text=Parkiran+4'">
                            <div class="slide-caption">Area Parkir Basement</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('parkir', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('parkir', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-parkir"></div>
                <div class="slider-counter" id="counter-parkir"></div>
            </section>

            <!-- Slider 7: Kantin -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-shop"></i>
                    Kantin
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-kantin">
                        <div class="slide">
                            <img src="../assets/fasilitas/kantin_1.jpg" alt="Kantin 1" onerror="this.src='https://via.placeholder.com/800x450/198754/FFFFFF?text=Kantin+1'">
                            <div class="slide-caption">Kantin - Area Makan</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/kantin_2.jpg" alt="Kantin 2" onerror="this.src='https://via.placeholder.com/800x450/198754/FFFFFF?text=Kantin+2'">
                            <div class="slide-caption">Kantin - Area Dapur</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('kantin', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('kantin', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-kantin"></div>
                <div class="slider-counter" id="counter-kantin"></div>
            </section>

            <!-- Slider 8: Ruang FTI 400 -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-door-closed"></i>
                    Ruang FTI 400
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-fti400">
                        <div class="slide">
                            <img src="../assets/fasilitas/fti400_1.jpg" alt="FTI 400 1" onerror="this.src='https://via.placeholder.com/800x450/0D6EFD/FFFFFF?text=FTI+400+1'">
                            <div class="slide-caption">Ruang FTI 400 - Interior</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/fti400_2.jpg" alt="FTI 400 2" onerror="this.src='https://via.placeholder.com/800x450/0D6EFD/FFFFFF?text=FTI+400+2'">
                            <div class="slide-caption">Ruang FTI 400 - Fasilitas</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('fti400', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('fti400', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-fti400"></div>
                <div class="slider-counter" id="counter-fti400"></div>
            </section>

            <!-- Slider 9: Studio FTI -->
            <section class="slider-section fade-in-up">
                <h2 class="slider-title">
                    <i class="bi bi-camera-reels"></i>
                    Studio FTI
                </h2>
                <div class="slider-container">
                    <div class="slider-wrapper" id="slider-studio">
                        <div class="slide">
                            <img src="../assets/fasilitas/studio_fti_1.jpg" alt="Studio FTI 1" onerror="this.src='https://via.placeholder.com/800x450/0DCAF0/FFFFFF?text=Studio+FTI+1'">
                            <div class="slide-caption">Studio FTI - Ruang Produksi</div>
                        </div>
                        <div class="slide">
                            <img src="../assets/fasilitas/studio_fti_2.jpg" alt="Studio FTI 2" onerror="this.src='https://via.placeholder.com/800x450/0DCAF0/FFFFFF?text=Studio+FTI+2'">
                            <div class="slide-caption">Studio FTI - Peralatan</div>
                        </div>
                    </div>
                    <button class="slider-nav slider-prev" onclick="moveSlide('studio', -1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="slider-nav slider-next" onclick="moveSlide('studio', 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="slider-dots" id="dots-studio"></div>
                <div class="slider-counter" id="counter-studio"></div>
            </section>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        // Slider state untuk setiap slider
        const sliders = {
            gedung: { current: 0, total: 3 },
            openspace2: { current: 0, total: 2 },
            openspace5: { current: 0, total: 2 },
            tematik: { current: 0, total: 6 },
            auditorium: { current: 0, total: 3 },
            parkir: { current: 0, total: 4 },
            kantin: { current: 0, total: 2 },
            fti400: { current: 0, total: 2 },
            studio: { current: 0, total: 2 }
        };

        // Initialize all sliders
        document.addEventListener('DOMContentLoaded', function() {
            Object.keys(sliders).forEach(slider => {
                initSlider(slider);
                updateSlider(slider);
            });

            // Add touch/swipe support
            Object.keys(sliders).forEach(slider => {
                addSwipeSupport(slider);
            });

            // Auto play (optional)
            // startAutoPlay();
        });

        function initSlider(name) {
            const dotsContainer = document.getElementById(`dots-${name}`);
            const total = sliders[name].total;

            // Create dots
            for (let i = 0; i < total; i++) {
                const dot = document.createElement('button');
                dot.className = 'slider-dot';
                dot.onclick = () => goToSlide(name, i);
                dotsContainer.appendChild(dot);
            }
        }

        function moveSlide(name, direction) {
            const slider = sliders[name];
            slider.current += direction;

            // Loop around
            if (slider.current >= slider.total) {
                slider.current = 0;
            } else if (slider.current < 0) {
                slider.current = slider.total - 1;
            }

            updateSlider(name);
        }

        function goToSlide(name, index) {
            sliders[name].current = index;
            updateSlider(name);
        }

        function updateSlider(name) {
            const slider = sliders[name];
            const wrapper = document.getElementById(`slider-${name}`);
            const dots = document.querySelectorAll(`#dots-${name} .slider-dot`);
            const counter = document.getElementById(`counter-${name}`);

            // Move slider
            wrapper.style.transform = `translateX(-${slider.current * 100}%)`;

            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === slider.current);
            });

            // Update counter
            counter.textContent = `${slider.current + 1} / ${slider.total}`;
        }

        // Swipe support for mobile
        function addSwipeSupport(name) {
            const container = document.querySelector(`#slider-${name}`).parentElement;
            let startX = 0;
            let endX = 0;

            container.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            });

            container.addEventListener('touchmove', (e) => {
                endX = e.touches[0].clientX;
            });

            container.addEventListener('touchend', () => {
                const diff = startX - endX;
                if (Math.abs(diff) > 50) { // Minimum swipe distance
                    if (diff > 0) {
                        moveSlide(name, 1); // Swipe left
                    } else {
                        moveSlide(name, -1); // Swipe right
                    }
                }
            });
        }

        // Optional: Auto play
        function startAutoPlay() {
            setInterval(() => {
                Object.keys(sliders).forEach(slider => {
                    moveSlide(slider, 1);
                });
            }, 5000); // Change slide every 5 seconds
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                Object.keys(sliders).forEach(slider => moveSlide(slider, -1));
            } else if (e.key === 'ArrowRight') {
                Object.keys(sliders).forEach(slider => moveSlide(slider, 1));
            }
        });

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            menuBtn.classList.toggle('active');
            
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
            const existingNotification = document.querySelector('.custom-notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = 'custom-notification';
            notification.innerHTML = `
                <div style="position: fixed; top: 20px; right: 20px; background: var(--success-color); color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1060; font-family: 'Poppins', sans-serif; font-size: 14px; animation: slideInRight 0.3s ease-out;">
                    <i class="bi bi-check-circle" style="margin-right: 8px;"></i>${message}
                </div>
            `;
            
            document.body.appendChild(notification);
            
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
    </script>
</body>
</html>