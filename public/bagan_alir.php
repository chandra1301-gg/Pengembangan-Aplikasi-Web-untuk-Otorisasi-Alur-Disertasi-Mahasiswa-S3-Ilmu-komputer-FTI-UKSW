<?php
/**
 * File: bagan_alir.php
 * Halaman Bagan Alir Program Studi
 */

$page_title = "Bagan Alir - Program Studi Doktor Ilmu Komputer UKSW";

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

    .content-card h3 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 20px;
        color: var(--primary-color);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #E5E7EB;
        display: flex;
        align-items: center;
        gap: 10px;
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

    /* Flowchart Container */
    .flowchart-container {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .flowchart-container:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .flowchart-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: var(--dark-text);
        margin-bottom: 25px;
        text-align: center;
    }

    .flowchart-container img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        display: block;
        margin: 0 auto;
    }

    /* Ujian Section */
    .ujian-section {
        background: linear-gradient(120deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 202, 240, 0.05) 100%);
        border-left: 4px solid var(--primary-color);
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }

    .ujian-section:hover {
        background: linear-gradient(120deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%);
        transform: translateX(5px);
        box-shadow: 0px 4px 15px rgba(13, 110, 253, 0.15);
    }

    .ujian-section h4 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: var(--primary-color);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ujian-section h4 i {
        font-size: 20px;
    }

    .ujian-section p {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 28px;
        color: var(--muted-text);
        margin-bottom: 12px;
        text-align: justify;
    }

    .ujian-section ul {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .ujian-section li {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 28px;
        color: var(--muted-text);
        margin-bottom: 10px;
        padding-left: 30px;
        position: relative;
    }

    .ujian-section li:before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--primary-color);
        font-weight: bold;
        font-size: 18px;
    }

    /* Prosedur Section */
    .prosedur-section {
        background: linear-gradient(120deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
        border-left: 4px solid var(--success-color);
        padding: 30px;
        border-radius: 10px;
        margin-top: 30px;
    }

    .prosedur-section h3 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 20px;
        color: var(--success-color);
        margin-bottom: 20px;
        text-align: center;
    }

    .prosedur-section p {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 28px;
        color: var(--muted-text);
        text-align: center;
        margin-bottom: 20px;
    }

    .prosedur-section ol {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 32px;
        color: var(--muted-text);
        padding-left: 20px;
        text-align: justify;
    }

    .prosedur-section ol li {
        margin-bottom: 15px;
    }

    .prosedur-section ol li strong {
        color: var(--success-color);
        font-weight: 600;
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
        .flowchart-container {
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
        .flowchart-container {
            padding: 20px;
        }
        
        .content-card p,
        .ujian-section p,
        .ujian-section li,
        .prosedur-section p,
        .prosedur-section ol {
            font-size: 14px;
            line-height: 28px;
        }
        
        .language-switcher {
            bottom: 20px;
            right: 20px;
            flex-direction: column;
        }

        .flowchart-container img {
            border-radius: 6px;
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
        .flowchart-container {
            padding: 15px;
        }
        
        .content-card p,
        .ujian-section p,
        .ujian-section li,
        .prosedur-section p,
        .prosedur-section ol {
            font-size: 13px;
            line-height: 24px;
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
                <h1>Bagan Alir DIK</h1>
                <p class="hero-breadcrumb">Beranda<span class="separator">›</span>Halaman</p>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-container" role="main">
            <h1 class="page-title fade-in-up">Bagan Alir Program Doktor Ilmu Komputer</h1>

            <!-- Intro -->
            <article class="content-card fade-in-up">
                <p>
                    Proses pengambilan Mata Kuliah, kualifikasi, proposal, publikasi maupun ujian tertutup 
                    dapat dilihat pada diagram berikut:
                </p>
            </article>

            <!-- Flowchart Image -->
            <div class="flowchart-container fade-in-up">
                <p class="flowchart-title">Diagram Alur Program Doktor Ilmu Komputer</p>
                <img src="../assets/bagan_alir.png" alt="Bagan Alir Program Doktor Ilmu Komputer" 
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22800%22 height=%221200%22%3E%3Crect width=%22800%22 height=%221200%22 fill=%22%23f0f0f0%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2220%22 fill=%22%23666%22%3EDiagram Bagan Alir%3C/text%3E%3C/svg%3E'">
            </div>

            <!-- Penjelasan Ujian -->
            <article class="content-card fade-in-up">
                <h3><i class="bi bi-clipboard-list"></i> Penjelasan Tahapan Ujian</h3>

                <!-- Ujian Proposal -->
                <div class="ujian-section">
                    <h4><i class="bi bi-file-earmark-text"></i> 3.1. Ujian Proposal</h4>
                    <p>Ujian Proposal dilakukan setelah mahasiswa:</p>
                    <ul>
                        <li>Lulus semua mata kuliah.</li>
                        <li>Melakukan publikasi dalam konferensi internasional dengan persetujuan promotor dan copromotor. 
                            Bukti minimal untuk publikasi adalah Letter Acceptance.</li>
                    </ul>
                </div>

                <!-- Ujian Kualifikasi -->
                <div class="ujian-section">
                    <h4><i class="bi bi-check-circle"></i> 3.2. Ujian Kualifikasi</h4>
                    <p>Ujian Kualifikasi dilakukan setelah mahasiswa:</p>
                    <ul>
                        <li>Lulus ujian proposal.</li>
                        <li>Siap untuk melakukan publikasi dalam jurnal internasional bereputasi.</li>
                    </ul>
                </div>

                <!-- Ujian Kelayakan -->
                <div class="ujian-section">
                    <h4><i class="bi bi-clipboard-check"></i> 3.3. Ujian Kelayakan</h4>
                    <p>Ujian Kelayakan dilakukan setelah mahasiswa:</p>
                    <ul>
                        <li>Lulus ujian Kualifikasi.</li>
                        <li>Sudah menyelesaikan penelitian disertasinya.</li>
                        <li>Melakukan publikasi minimal 2 buah dalam jurnal Internasional bereputasi setara Q2. 
                            Bukti minimal untuk publikasi adalah Letter Acceptance.</li>
                        <li>Memenuhi persyaratan administratif yang ditentukan oleh program studi.</li>
                    </ul>
                    <p>
                        <strong>Catatan:</strong> Ujian kelayakan bertujuan untuk melihat kesiapan mahasiswa dalam ujian akhir. 
                        Penguji dalam ujian kelayakan adalah penguji internal.
                    </p>
                </div>

                <!-- Ujian Tertutup -->
                <div class="ujian-section">
                    <h4><i class="bi bi-mortarboard"></i> 3.4. Ujian Tertutup</h4>
                    <p>Ujian Tertutup dilakukan setelah mahasiswa:</p>
                    <ul>
                        <li>Mahasiswa lulus ujian Kelayakan.</li>
                        <li>Menyelesaikan penelitian disertasinya.</li>
                        <li>Melakukan publikasi minimal 2 buah dalam jurnal Internasional bereputasi setara Q2. 
                            Bukti minimal untuk publikasi adalah Letter Acceptance.</li>
                        <li>Memenuhi persyaratan administratif yang ditentukan oleh program studi.</li>
                    </ul>
                    <p>
                        <strong>Catatan:</strong> Penguji ujian tertutup berasal dari penguji internal dan eksternal.
                    </p>
                </div>

                <!-- Prosedur Ujian Akhir -->
                <div class="prosedur-section">
                    <h3>3.5. PROSEDUR UJIAN AKHIR PROGRAM DOKTOR ILMU KOMPUTER</h3>
                    <p><strong>MENETAPKAN:</strong></p>
                    <ol>
                        <li>
                            <strong>Syarat ujian proposal disertasi:</strong> Sudah menyelesaikan dan lulus semua matakuliah 
                            dibuktikan dengan transkrip nilai serta sudah melakukan publikasi dalam konferensi internasional 
                            yang bereputasi, dibuktikan dengan adanya Letter of Acceptance (LoA).
                        </li>
                        <li>
                            <strong>Syarat ujian kualifikasi:</strong> Sudah lulus ujian proposal dan sudah memiliki draft 
                            artikel untuk disubmit ke Jurnal Internasional bereputasi.
                        </li>
                        <li>
                            <strong>Syarat ujian kelayakan:</strong> Mahasiswa lulus ujian kualifikasi, sudah menyelesaikan 
                            penelitian disertasi dan sudah melakukan publikasi dalam 2 jurnal internasional bereputasi 
                            index Scopus, setara Q2.
                        </li>
                        <li>
                            <strong>Syarat ujian tertutup:</strong> Mahasiswa sudah lulus ujian kelayakan, memiliki sertifikat 
                            TOEFL yang masih berlaku dengan score minimal 500, memiliki sertifikat hasil TPA dengan score 
                            minimal 500 serta memenuhi persyaratan administrasi yang ditentukan.
                        </li>
                    </ol>
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
    </script>
</body>
</html>