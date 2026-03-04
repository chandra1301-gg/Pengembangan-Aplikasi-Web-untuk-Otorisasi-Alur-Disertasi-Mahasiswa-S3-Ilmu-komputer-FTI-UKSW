<?php
/**
 * File: staf_pengajar.php
 * Halaman Staf Pengajar Program Studi
 */

$page_title = "Staf Pengajar - Program Studi Doktor Ilmu Komputer UKSW";

// Data Dosen
$dosen_list = [
    [
        'nama' => 'Prof. Danny Manongga, M.Sc., Ph.D',
        'email' => 'danny.manongga@uksw.edu',
        'keahlian' => 'Kecerdasan Buatan, Sistem Cerdas',
        'foto' => 'danny_manongga.png',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2017-2020', 'topik' => 'Network and Social Media Analysis'],
            ['tahun' => '2021-2023', 'topik' => 'Criminology in Social network'],
            ['tahun' => '2024-2025', 'topik' => 'Internet of Things, Tracking and monitoring Behavior or persons, things or data']
        ]
    ],
    [
        'nama' => 'Prof. Dr. Ir. Eko Sediyono, M.Kom',
        'email' => 'eko@uksw.edu',
        'keahlian' => 'Teknik Kompilasi dan Komputasi',
        'foto' => 'eko_sediyono.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2006-2015', 'topik' => 'Pengembangan algoritma parallel yang efisien untuk BigData Sorting (external Sorting)'],
            ['tahun' => '2013-2015', 'topik' => 'Pengembangan bahan ajar untuk anak berkebutuhan khusus'],
            ['tahun' => '2016-2020', 'topik' => 'Pengembangan Algoritma web semantik dan Ontology'],
            ['tahun' => '2015-2018', 'topik' => 'Pengembangan Aplikasi Pertanahan dan pemanfaatan lahan'],
            ['tahun' => '2019-2025', 'topik' => 'Analisis Sistem Informasi Kependudukan dan Catatan Sipil']
        ]
    ],
    [
        'nama' => 'Prof. Dr. Sutarto Wijono',
        'email' => 'Sutarto@uksw.edu',
        'keahlian' => 'Riset Kualitatif, Psikologi Industri',
        'foto' => 'sutarto_wijono.jpeg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ],
    [
        'nama' => 'Prof. Hindriyanto D. Purnomo',
        'email' => 'hindriyanto.purnomo@uksw.edu',
        'keahlian' => 'Soft Computing, Metaheuristic, dan Machine Learning',
        'foto' => 'hindriyanto_purnomo.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ],
    [
        'nama' => 'Prof. Adi Setiawan',
        'email' => 'adi.setiawan@uksw.edu',
        'keahlian' => 'Data Analysis in GIS, Economics, Business',
        'foto' => 'adi_setiawan.jpeg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2017-2019', 'topik' => 'Clustering data Inflasi, Distribusi Komoditas Penyumbang Inflasi, Early warning sistem'],
            ['tahun' => '2020-2022', 'topik' => 'High dimensional data analysis, Big data analysis, Big data application to Spatial Data'],
            ['tahun' => '2023-2025', 'topik' => 'Big data application to Economical Data, Financial Data, Biological Data']
        ]
    ],
    [
        'nama' => 'Prof. Dr. Kristoko Dwi Hartomo, M.Kom',
        'email' => 'kristoko@staff.uksw.edu',
        'keahlian' => 'Software Designer, Database Administrator, Data Forecaster',
        'foto' => 'kristoko_hartomo.jpeg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2009-2010', 'topik' => 'Model spasial pola tanam berbasis kearifan lokal'],
            ['tahun' => '2011-2012', 'topik' => 'Optimalisasi Spatial Desision Colaborative Support Framework'],
            ['tahun' => '2013-2014', 'topik' => 'The Agroecological zone using Local Wisdom, Pemodelan Spasial Penanggulangan Kemiskinan Daerah'],
            ['tahun' => '2015-2018', 'topik' => 'Model Spasial Penentuan Bencana, Deteksi Dini Tanah Longsor'],
            ['tahun' => '2019-2021', 'topik' => 'Aplikasi cerdas Deteksi Dini bencana berbasis kearifan lokal'],
            ['tahun' => '2022-2027', 'topik' => 'Komersialisasi Piranti deteksi dini bencana']
        ]
    ],
    [
        'nama' => 'Dr. Irwan Sembiring, M.Kom',
        'email' => 'irwan@uksw.edu',
        'keahlian' => 'Network Security, Digital Forensic',
        'foto' => 'irwan_sembiring.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2016-2027', 'topik' => 'Network Security Technology'],
            ['tahun' => '2018-2020', 'topik' => 'Digital Forensic'],
            ['tahun' => '2021-2023', 'topik' => 'Internet of thing security, Data and system security'],
            ['tahun' => '2024-2025', 'topik' => 'Cloud Computing Security']
        ]
    ],
    [
        'nama' => 'Dr. Sri Yulianto J. Prasetyo, M.Kom',
        'email' => 'sri.yulianto@staff.uksw.edu',
        'keahlian' => 'Analisis Spasial dan SIG',
        'foto' => 'sri_yulianto.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2018-2021', 'topik' => 'Ekstraksi, Analisis indeks vegetasi NDVI untuk penentuan wilayah risiko bencana hidrometeorologi'],
            ['tahun' => '2021-2023', 'topik' => 'Kearifan Lokal Pranatamangsa yang diintegrasikan dengan indeks vegetasi'],
            ['tahun' => '2023-2026', 'topik' => 'Perbandingan model prediksi anomali iklim untuk pertanian dan perikanan laut']
        ]
    ],
    [
        'nama' => 'Dr. Iwan Setyawan',
        'email' => 'iwan.setyawan@uksw.edu',
        'keahlian' => 'Image Processing, Video Processing',
        'foto' => 'iwan_setyawan.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => [
            ['tahun' => '2018-2020', 'topik' => 'Visual hashing for digital image and video based on local texture descriptors'],
            ['tahun' => '2020-2022', 'topik' => 'Content-dependent image and video digital watermarking'],
            ['tahun' => '2022-2024', 'topik' => 'New visual hashing methods for image and video'],
            ['tahun' => '2024-2025', 'topik' => 'New content-dependent image and video watermarking algorithms'],
            ['tahun' => '2018-2025', 'topik' => 'Multimodal smart security system']
        ]
    ],
    [
        'nama' => 'Hendry, Ph.D',
        'email' => 'hendry@uksw.edu',
        'keahlian' => 'Software Engineering',
        'foto' => 'hendry.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ],
    [
        'nama' => 'Dr. Indrastanti Widiasari, M.T',
        'email' => 'indrastanti@uksw.edu',
        'keahlian' => 'Network Sensor, Smart Network dan Jaringan Nirkabel',
        'foto' => 'indrastanti.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ],
    [
        'nama' => 'Dr. Wiwin Sulistyo, M.Kom',
        'email' => 'wiwin.sulistyo@uksw.edu',
        'keahlian' => 'Computer Networking, Soft Computing, Data Analyst, Spatial Econometrics',
        'foto' => 'wiwin_sulistyo.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ],
    [
        'nama' => 'Yessica Nataliani, Ph.D',
        'email' => 'yessica.nataliani@uksw.edu',
        'keahlian' => 'Fuzzy Logic, Mathematical Modelling',
        'foto' => 'yessica_nataliani.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ],
    [
        'nama' => 'Budhi Kristianto, Ph.D',
        'email' => 'budhik@uksw.edu',
        'keahlian' => 'Information Technology, Biomedical Informatic',
        'foto' => 'budhi_kristianto.jpg',
        'scholar' => '#',
        'scopus' => '#',
        'roadmap' => []
    ]
];

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

    /* Dosen Grid */
    .dosen-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    /* Dosen Card */
    .dosen-card {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 0;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .dosen-card:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .dosen-photo {
        width: 100%;
        height: 320px;
        overflow: hidden;
        position: relative;
        background: #F5E6D3;
    }

    .dosen-photo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        background: #F5E6D3;
    }

    .dosen-photo-placeholder {
        width: 100%;
        height: 100%;
        background: #F5E6D3;
        position: relative;
        overflow: hidden;
    }

    .dosen-photo-placeholder::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            linear-gradient(135deg, rgba(139, 115, 85, 0.15) 25%, transparent 25%),
            linear-gradient(225deg, rgba(139, 115, 85, 0.15) 25%, transparent 25%),
            linear-gradient(45deg, rgba(139, 115, 85, 0.15) 25%, transparent 25%),
            linear-gradient(315deg, rgba(139, 115, 85, 0.15) 25%, transparent 25%);
        background-position: 0 0, 50px 0, 50px -50px, 0 50px;
        background-size: 100px 100px;
        z-index: 1;
    }

    .dosen-photo-placeholder .initial-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 64px;
        font-weight: 700;
        color: rgba(139, 115, 85, 0.3);
        z-index: 2;
        font-family: 'Poppins', sans-serif;
    }

    .dosen-info {
        padding: 28px 24px;
        text-align: center;
    }

    .dosen-nama {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 17px;
        line-height: 25px;
        color: #000000;
        margin-bottom: 8px;
    }

    .dosen-email {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        line-height: 18px;
        color: var(--muted-text);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .dosen-email i {
        font-size: 11px;
    }

    .dosen-keahlian {
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        line-height: 20px;
        color: #4B5563;
        margin-bottom: 20px;
        font-style: italic;
        min-height: 40px;
    }

    .dosen-links {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .dosen-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(13, 110, 253, 0.1);
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        text-decoration: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .dosen-link:hover {
        background: var(--primary-color);
        color: #FFFFFF;
        transform: translateY(-2px);
    }

    .dosen-link i {
        font-size: 13px;
    }

    .view-roadmap-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px 16px;
        background: transparent;
        border: 1.5px solid var(--success-color);
        color: var(--success-color);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .view-roadmap-btn:hover {
        background: var(--success-color);
        color: #FFFFFF;
        transform: translateY(-2px);
    }

    .view-roadmap-btn i {
        font-size: 14px;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        animation: fadeIn 0.3s ease;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-content {
        background: #FFFFFF;
        border-radius: 10px;
        max-width: 700px;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        background: var(--primary-color);
        color: #FFFFFF;
        padding: 22px 28px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 19px;
        font-weight: 600;
        margin: 0;
        line-height: 1.4;
    }

    .modal-close {
        background: transparent;
        border: none;
        color: #FFFFFF;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border-radius: 4px;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 28px;
    }

    .roadmap-item {
        background: #F8F9FA;
        border-left: 4px solid var(--primary-color);
        padding: 18px 20px;
        margin-bottom: 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .roadmap-item:hover {
        background: #E9ECEF;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transform: translateX(4px);
    }

    .roadmap-item:last-child {
        margin-bottom: 0;
    }

    .roadmap-tahun {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 15px;
        line-height: 23px;
        color: var(--primary-color);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .roadmap-tahun i {
        font-size: 14px;
    }

    .roadmap-topik {
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        line-height: 22px;
        color: #4B5563;
    }

    .no-roadmap {
        text-align: center;
        padding: 50px 20px;
        color: #9CA3AF;
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        font-style: italic;
    }

    .no-roadmap i {
        font-size: 48px;
        margin-bottom: 16px;
        display: block;
        opacity: 0.5;
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
        bottom: 30px;
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

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-container {
            padding: 0 25px 40px 25px;
        }
        
        .dosen-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
        
        .dosen-grid {
            grid-template-columns: 1fr;
        }

        .dosen-photo {
            height: 280px;
        }

        .dosen-info {
            padding: 24px 20px;
        }

        .dosen-links {
            flex-direction: row;
            gap: 8px;
        }

        .dosen-link {
            flex: 1;
            justify-content: center;
        }

        .modal-content {
            max-width: 95%;
        }

        .modal-header,
        .modal-body {
            padding: 20px;
        }

        .modal-header h2 {
            font-size: 17px;
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

        .dosen-photo {
            height: 250px;
        }
        
        .dosen-info {
            padding: 20px 15px;
        }

        .dosen-nama {
            font-size: 16px;
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
                <h1>Staf Pengajar DIK</h1>
                <p class="hero-breadcrumb">Beranda<span class="separator">›</span>Halaman</p>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-container" role="main">
            <h1 class="page-title fade-in-up">Staf Pengajar Program Studi Doktor Ilmu Komputer</h1>

            <div class="dosen-grid">
                <?php foreach ($dosen_list as $index => $dosen): 
                    $initial = strtoupper(substr($dosen['nama'], 0, 1));
                ?>
                <article class="dosen-card fade-in-up">
                    <div class="dosen-photo">
                        <?php 
                            // Path foto di folder assets/dosen/
                            $foto_path = "../assets/dosen/" . $dosen['foto'];

                            // Jika file foto ada, tampilkan gambar
                            if (file_exists($foto_path) && !empty($dosen['foto'])): 
                        ?>
                            <img src="<?= $foto_path ?>" alt="<?= htmlspecialchars($dosen['nama']) ?>">
                        <?php else: ?>
                            <!-- Placeholder jika foto tidak ditemukan -->
                            <div class="dosen-photo-placeholder">
                                <span class="initial-text"><?= $initial ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="dosen-info">
                        <h2 class="dosen-nama"><?= htmlspecialchars($dosen['nama']) ?></h2>
                        <div class="dosen-email">
                            <i class="bi bi-envelope"></i>
                            <?= htmlspecialchars($dosen['email']) ?>
                        </div>
                        <p class="dosen-keahlian"><?= htmlspecialchars($dosen['keahlian']) ?></p>
                        
                        <div class="dosen-links">
                            <a href="<?= $dosen['scholar'] ?>" class="dosen-link" target="_blank" rel="noopener">
                                <i class="bi bi-mortarboard"></i> Scholar
                            </a>
                            <a href="<?= $dosen['scopus'] ?>" class="dosen-link" target="_blank" rel="noopener">
                                <i class="bi bi-book"></i> Scopus
                            </a>
                        </div>

                        <?php if (!empty($dosen['roadmap'])): ?>
                        <button class="view-roadmap-btn" onclick="showRoadmap(<?= $index ?>)" aria-label="Lihat roadmap penelitian <?= htmlspecialchars($dosen['nama']) ?>">
                            <i class="bi bi-signpost"></i> Lihat Roadmap Penelitian
                        </button>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Modal Roadmap -->
    <div id="roadmapModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Roadmap Penelitian</h2>
                <button class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Roadmap content will be inserted here -->
            </div>
        </div>
    </div>

    <script>
        const dosenData = <?= json_encode($dosen_list) ?>;

        function showRoadmap(index) {
            const dosen = dosenData[index];
            const modal = document.getElementById('roadmapModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');

            modalTitle.textContent = `Roadmap Penelitian - ${dosen.nama}`;
            
            if (dosen.roadmap && dosen.roadmap.length > 0) {
                let html = '';
                dosen.roadmap.forEach(item => {
                    html += `
                        <div class="roadmap-item">
                            <div class="roadmap-tahun">
                                <i class="bi bi-calendar-event"></i> ${item.tahun}
                            </div>
                            <div class="roadmap-topik">${item.topik}</div>
                        </div>
                    `;
                });
                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = '<div class="no-roadmap"><i class="bi bi-info-circle"></i>Roadmap penelitian belum tersedia</div>';
            }

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('roadmapModal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

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

        // Close modal when clicking outside
        document.getElementById('roadmapModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
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
        