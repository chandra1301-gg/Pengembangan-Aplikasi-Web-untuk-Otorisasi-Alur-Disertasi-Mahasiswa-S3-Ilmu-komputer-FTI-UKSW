<?php
/**
 * File: visi_misi.php
 * Halaman Visi Misi Program Studi
 */

$page_title = "Visi Misi - Program Studi Doktor Ilmu Komputer UKSW";

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

    /* Tabs Navigation */
    .tabs-nav {
        display: flex;
        gap: 0;
        margin-bottom: 40px;
        background: var(--primary-color);
        border-radius: 12px 12px 0 0;
        overflow: hidden;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
    }

    .tab-button {
        flex: 1;
        padding: 20px 30px;
        background: #F3F4F6;
        border: none;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 16px;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }

    .tab-button:hover {
        background: #E5E7EB;
        color: #374151;
    }

    .tab-button.active {
        background: var(--primary-color);
        color: #FFFFFF;
        border-bottom-color: var(--primary-color);
    }

    /* Tab Content */
    .tab-content {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    /* Content Card */
    .content-card {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .content-card:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .content-card h2 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 28px;
        color: var(--dark-text);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-color);
    }

    .content-card ol {
        padding-left: 20px;
        margin: 0;
    }

    .content-card li {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 28px;
        color: #4B5563;
        margin-bottom: 20px;
        text-align: justify;
    }

    .content-card li:last-child {
        margin-bottom: 0;
    }

    .content-card p {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 28px;
        color: #4B5563;
        text-align: justify;
        margin: 0;
    }

    /* Single Column Layout */
    .single-column {
        grid-template-columns: 1fr;
        max-width: 1100px;
        margin: 0 auto;
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
        
        .content-card {
            padding: 30px;
        }

        .content-grid {
            grid-template-columns: 1fr;
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

        .tabs-nav {
            flex-direction: column;
            border-radius: 8px 8px 0 0;
        }

        .tab-button {
            padding: 15px 20px;
            font-size: 14px;
        }

        .content-grid {
            grid-template-columns: 1fr;
        }

        .content-card {
            padding: 20px;
        }

        .content-card h2 {
            font-size: 22px;
            margin-bottom: 20px;
        }

        .content-card li,
        .content-card p {
            font-size: 13px;
            line-height: 24px;
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

        .content-card h2 {
            font-size: 20px;
        }

        .tab-button {
            padding: 12px 15px;
            font-size: 13px;
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
                <h1>Visi & Misi</h1>
                <p class="hero-breadcrumb">Beranda<span class="separator">›</span>Tentang Kami<span class="separator">›</span>Visi Misi</p>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-container" role="main">
            <!-- Tabs Navigation -->
            <div class="tabs-nav" role="tablist">
                <button class="tab-button active" onclick="openTab(event, 'tab1')" role="tab" aria-selected="true" aria-controls="tab1">
                    Visi Misi UKSW
                </button>
                <button class="tab-button" onclick="openTab(event, 'tab2')" role="tab" aria-selected="false" aria-controls="tab2">
                    Visi Misi FTI
                </button>
                <button class="tab-button" onclick="openTab(event, 'tab3')" role="tab" aria-selected="false" aria-controls="tab3">
                    Visi Misi DIK
                </button>
            </div>

            <!-- Tab 1: Visi Misi UKSW -->
            <div id="tab1" class="tab-content active" role="tabpanel">
                <h2 class="page-title fade-in-up">Visi Misi UKSW</h2>
                
                <div class="content-grid single-column">
                    <!-- Visi UKSW -->
                    <article class="content-card fade-in-up">
                        <h3>Visi UKSW</h3>
                        <ol>
                            <li>Menjadi Universitas Scientiarum, untuk pembentukan persekutuan pengetahuan tingkat tinggi, yang terikat kepada pengajaran kebenaran (alethea) berdasarkan pada realisme Alkitabiah.</li>
                            <li>Menjadi Universitas Magistroum et Scholarium untuk pembentukan minoritas yang berdaya cipta (creative minority) bagi pembangunan dan pembaharuan masyarakat dan negara Indonesia.</li>
                            <li>Menjadi pembina kepemimpinan untuk berbagai jabatan dalam masyarakat (termasuk gereja) yang sedang membangun.</li>
                            <li>Menjadi radar dalam situasi perubahan kebudayaan, politik, moral dan rohaniah, yang mensinyalir, mencatat, dan mengikuti perubahan-perubahan itu guna menjadikannya objek atau sasaran pembahasan dan penelitian.</li>
                            <li>Menjadi pelayan dan lembaga pendidikan pelayanan (diakonia), sepanjang masa mencakup kritik yang konstruktif serta informatif kepada gereja dan masyarakat terhadap keadaan masyarakat dimana masih terdapat kemiskinan, ketidakadilan, ketidakbenaran, dan ketidakdamaian.</li>
                        </ol>
                    </article>

                    <!-- Misi UKSW -->
                    <article class="content-card fade-in-up">
                        <h3>Misi UKSW</h3>
                        <ol>
                            <li>Melaksanakan Tri Darma Perguruan Tinggi, yaitu: Pendidikan dan pengajaran tinggi, Penelitian, Pengabdian kepada masyarakat.</li>
                            <li>Melaksanakan Perguruan Tinggi Kristen Indonesia, yang berarti bahwa hidup dan kegiatan-kegiatannya pada satu pihak mempunyai motivasi dan merupakan bentuk perwujudan Iman Kristen yang Oikumenis dan pada pihak lain menjawab secara tepat dan bertanggung jawab situasi sosiokultural dan kebutuhan bangsa serta negara Republik Indonesia.</li>
                            <li>Mendorong dan mengembangkan sikap serta pemikiran yang kritis-prinsipal dan kreatif-realistis, berdasarkan kepekaan hati nurani yang luhur dan dibimbing oleh Firman Allah.</li>
                            <li>Mewujudkan pusat pemikiran dan pengalaman untuk pembinaan kehidupan yang adil, bebas, tertib serta sejahtera.</li>
                            <li>Mencari dan mengusahakan terdapatnya hubungan yang bermakna antara iman Kristen dengan berbagai bidang ilmu dan kegiatan atau pelayanan.</li>
                            <li>Mengusahakan terbentuknya dan membina angkatan-angkatan pemimpin masyarakat yang selain diperlengkapi dengan bekal ilmu pengetahuan dan kepakaran di bidang tertentu, juga memiliki kesadaran pengabdian yang tinggi kepada masyarakat.</li>
                        </ol>
                    </article>
                </div>
            </div>

            <!-- Tab 2: Visi Misi FTI UKSW -->
                         <!-- Tab 2: Visi Misi Fakultas Teknologi Informasi -->
            <div id="tab2" class="tab-content" role="tabpanel">
                <h2 class="page-title fade-in-up">Visi Misi Fakultas Teknologi Informasi</h2>
                
                <div class="content-grid single-column">
                    <!-- Visi FTI -->
                    <article class="content-card fade-in-up">
                        <h3>Visi Fakultas Teknologi Informasi</h3>
                        <p>
                            Pada tahun 2030 menjadi Fakultas di bidang Teknologi Informasi yang berkualitas di kawasan ASEAN dan menjunjung tinggi nilai kebenaran dan iman Kristiani serta memiliki kepekaan terhadap perubahan berlandaskan nilai kritis, kreatif dan inovatif.
                        </p>
                    </article>

                    <!-- Misi FTI -->
                    <article class="content-card fade-in-up">
                        <h3>Misi Fakultas Teknologi Informasi</h3>
                        <ol>
                            <li>Melaksanakan proses pembelajaran yang berbasis keunggulan dalam bidang teknologi informasi yang menjunjung tinggi nilai kebenaran dan iman Kristiani.</li>
                            <li>Melaksanakan penelitian yang berbasis keunggulan dan selaras dengan perkembangan teknologi informasi yang berciri kritis, kreatif dan inovatif.</li>
                            <li>Menerapkan bidang ilmu teknologi informasi untuk kesejahteraan masyarakat sebagai wujud pelayanan.</li>
                            <li>Mengembangkan kepemimpinan yang mencerminkan sikap kritis, kreatif dan inovatif serta memiliki kepekaan terhadap perubahan.</li>
                            <li>Menciptakan dan mengembangkan sinergi antara pengajaran, penelitian dan pengabdian masyarakat dalam semangat pelayanan dengan berbagai pihak, baik dalam lingkup Nasional maupun ASEAN.</li>
                        </ol>
                    </article>
                </div>
            </div>


            <!-- Tab 3: Visi Misi Program Studi Doktor Ilmu Komputer -->
            <div id="tab3" class="tab-content" role="tabpanel">
                <h2 class="page-title fade-in-up">Visi Misi Program Studi Doktor Ilmu Komputer</h2>
                
                <div class="content-grid single-column">
                    <!-- Visi Prodi -->
                    <article class="content-card fade-in-up">
                        <h3>Visi Program Studi Doktor Ilmu Komputer</h3>
                        <p>
                            Pada tahun 2027 menjadi institusi pendidikan yang menjadi pusat rujukan ilmu komputer dan terapannya di Indonesia: terutama dalam bidang Software Engineering, Data Analytics and Intelligence Systems serta Network Technology and Data Security: menghasilkan lulusan yang memiliki kepakaran ilmu maupun penerapannya dalam ketiga bidang tersebut serta menjunjung tinggi nilai-nilai kebenaran kristiani.
                        </p>
                    </article>

                    <!-- Misi Prodi -->
                    <article class="content-card fade-in-up">
                        <h3>Misi Program Studi Doktor Ilmu Komputer</h3>
                        <ol>
                            <li>Melaksanakan proses pembelajaran yang berbasis penelitian dalam bidang Ilmu Komputer yang menjunjung tinggi nilai kebenaran dan iman Kristiani.</li>
                            <li>Melaksanakan penelitian yang berbasis keunggulan program studi dan selaras dengan perkembangan ilmu komputer yang berciri kritis, kreatif dan inovatif.</li>
                            <li>Melaksanakan Pengabdian Masyarakat yang berbasis bidang ilmu komputer yang berciri pada semangat pelayanan.</li>
                            <li>Mengembangkan kepemimpinan yang mencerminkan sikap kritis, kreatif dan inovatif serta memiliki kepekaan terhadap perubahan.</li>
                            <li>Menciptakan dan mengembangkan sinergi antara pengajaran, penelitian dan pengabdian kepada masyarakat dengan berbagai pihak, baik di dalam maupun luar negeri.</li>
                        </ol>
                    </article>
                </div>
            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        function openTab(evt, tabName) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }

            // Remove active class from all buttons
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
                tabButtons[i].setAttribute('aria-selected', 'false');
            }

            // Show the current tab and add active class to button
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
            evt.currentTarget.setAttribute('aria-selected', 'true');

            // Smooth scroll to top of content
            window.scrollTo({
                top: document.querySelector('.tabs-nav').offsetTop - 20,
                behavior: 'smooth'
            });
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