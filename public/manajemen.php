<?php
/**
 * File: management.php
 * Halaman Manajemen - Struktur Kepemimpinan Fakultas Teknologi Informasi UKSW
 */

$page_title = "Manajemen Fakultas - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_publik.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
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

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 29.06%);
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    /* Content Wrapper */
    .content-wrapper {
        margin-left: 0;
        background: transparent;
        min-height: 100vh;
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

    .hero-subtitle {
        font-family: 'Poppins', sans-serif;
        font-style: normal;
        font-weight: 500;
        font-size: 15px;
        line-height: 22px;
        letter-spacing: 0.01em;
        color: #FFFFFF;
        margin: 0;
    }

    /* Main Container */
    .main-container {
        position: relative;
        padding: 0 37px 60px 37px;
        max-width: 1440px;
        margin: 0 auto;
        background: transparent;
    }

    /* Content Card */
    .content-card {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .content-card:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .content-card-header {
        background: #FFFFFF;
        padding: 24px 30px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .content-card-header h3 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 20px;
        line-height: 30px;
        color: var(--dark-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .content-card-body {
        padding: 30px;
    }

    /* Leader Items */
    .leader-item {
        background: var(--light-bg);
        border-radius: 10px;
        padding: 20px 25px;
        margin-bottom: 15px;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .leader-item:hover {
        background: #e3f2fd;
        border-left-color: var(--primary-color);
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
    }

    .leader-item .row {
        align-items: center;
    }

    .leader-item strong {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 15px;
        color: var(--dark-text);
    }

    .leader-item span {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 16px;
        color: var(--dark-text);
    }

    /* Section Headers */
    .section-header {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: var(--muted-text);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-header i {
        color: var(--primary-color);
    }

    /* Department Section */
    .department-section {
        margin-bottom: 30px;
    }

    .department-section:last-child {
        margin-bottom: 0;
    }

    /* Info Cards */
    .info-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .info-card {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.15);
    }

    .info-card i {
        font-size: 48px;
        margin-bottom: 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .info-card h5 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: var(--dark-text);
        margin-bottom: 15px;
    }

    .info-card p {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 14px;
        color: var(--muted-text);
        margin: 0;
        line-height: 1.6;
    }

    /* Highlight Items */
    .highlight-item {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 202, 240, 0.05) 100%);
        border-left: 4px solid var(--primary-color);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-container {
            padding: 0 25px 40px 25px;
        }
        
        .content-card-body {
            padding: 25px;
        }
        
        .leader-item {
            padding: 18px 20px;
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
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
        
        .hero-subtitle {
            font-size: 14px;
        }
        
        .main-container {
            padding: 0 20px 30px 20px;
        }
        
        .content-card-header {
            padding: 20px;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .content-card-header h3 {
            font-size: 18px;
        }
        
        .content-card-body {
            padding: 20px;
        }
        
        .leader-item {
            padding: 15px;
        }
        
        .leader-item .row .col-md-3,
        .leader-item .row .col-md-5 {
            margin-bottom: 10px;
        }
        
        .info-cards-grid {
            grid-template-columns: 1fr;
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
        
        .hero-subtitle {
            font-size: 12px;
        }
        
        .main-container {
            padding: 0 15px 20px 15px;
        }
        
        .content-card-body {
            padding: 15px;
        }
        
        .leader-item {
            padding: 12px;
        }
        
        .leader-item strong {
            font-size: 14px;
        }
        
        .leader-item span {
            font-size: 14px;
        }
        
        .section-header {
            font-size: 16px;
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
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Hero Section -->
        <section class="hero-section" role="banner">
            <div class="hero-content">
                <h1>Manajemen Fakultas 👨‍💼</h1>
                <p class="hero-subtitle">Struktur Kepemimpinan Fakultas Teknologi Informasi UKSW</p>
            </div>
        </section>
        
        <!-- Main Content -->
        <main class="main-container" role="main">
            <!-- Struktur Kepemimpinan -->
            <article class="content-card fade-in-up">
                <div class="content-card-header">
                    <h3><i class="bi bi-people-fill"></i>Jajaran Pemimpin Fakultas Teknologi Informasi UKSW</h3>
                </div>
                <div class="content-card-body">
                    <!-- Dekan -->
                    <div class="leader-item highlight-item">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>Dekan:</strong>
                            </div>
                            <div class="col-md-9">
                                <span>Prof. Ir. Danny Manongga, M.Sc., Ph.D.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Wakil Dekan -->
                    <div class="leader-item highlight-item">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>Wakil Dekan:</strong>
                            </div>
                            <div class="col-md-9">
                                <span>Hendry, S.Kom., M.Kom., Ph.D.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kepala Departemen -->
                    <div class="department-section">
                        <h4 class="section-header"><i class="bi bi-building"></i>Kepala Departemen</h4>
                        
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Teknik Informatika:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Dr. Wiwin Sulistyo, S.T., M.Kom.</span>
                                </div>
                            </div>
                        </div>

                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Sistem Informasi:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Prof. Dr. Kristoko Dwi Hartomo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kepala Program Studi -->
                    <div class="department-section">
                        <h4 class="section-header"><i class="bi bi-book"></i>Kepala Program Studi</h4>

                        <!-- S1 Teknik Informatika -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>S1 Teknik Informatika:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Budhi Kristanto, S.Kom., M.Cs., Ph.D.</span>
                                </div>
                            </div>
                        </div>

                        <!-- D3 Teknik Informatika -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>D3 Teknik Informatika:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Teguh Indra Bayu, Ph.D.</span>
                                </div>
                            </div>
                        </div>

                        <!-- S1 Sistem Informasi -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>S1 Sistem Informasi:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Hanna Priliysca Chernovita, S.SI., M.Cs.</span>
                                </div>
                            </div>
                        </div>

                        <!-- S1 Perpustakaan dan Sains Informatik -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>S1 Perpustakaan dan Sains Informasi:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Albertoes Pramoekti Narendra, M.IP.</span>
                                </div>
                            </div>
                        </div>

                        <!-- D3 Sistem Informasi -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>D3 Sistem Informasi Akuntansi:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Dr. Evi Maria, S.E., M.Acc., AK., CA.</span>
                                </div>
                            </div>
                        </div>

                        <!-- S1 Desain Komunikasi Visual -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>S1 Desain Komunikasi Visual:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Birmani Setia Utami, S.Sn., M.Sn.</span>
                                </div>
                            </div>
                        </div>

                        <!-- S1 Pendidikan Teknik Informatika dan Komputer -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>S1 Pendidikan Teknik Informatika dan Komputer:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Krismiyati, S.Pd., M.A., Ph.D.</span>
                                </div>
                            </div>
                        </div>

                        <!-- S1 Hubungan Masyarakat -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>S1 Hubungan Masyarakat:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Hendr, S.Kom., M.Kom., Ph.D.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kepala Program Doktor Ilmu Komputer -->
                    <div class="department-section">
                        <h4 class="section-header"><i class="bi bi-mortarboard"></i>Kepala Program Doktor</h4>
                        
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Ilmu Komputer:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Dr. Irwan Sembiring, S.T., M.Kom.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Koordinator Unit -->
            <article class="content-card fade-in-up">
                <div class="content-card-header">
                    <h3><i class="bi bi-diagram-3"></i>Koordinator Unit Fakultas Teknologi Informasi UKSW</h3>
                </div>
                <div class="content-card-body">
                    <div class="department-section">
                        <h4 class="section-header"><i class="bi bi-gear"></i>Koordinator Unit</h4>

                        <!-- Bidang Kemahasiswaan -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Bidang Kemahasiswaan:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Evangs Mailoa, S.Kom., M.Cs.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bidang Pengabdian Masyarakat & CTC -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Bidang Pengabdian Masyarakat & CTC:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Suharyadi, S.Kom., M.Cs.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Promosi -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Promosi:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Pratyaksa Ocsa Nugraha Saian, S.Kom., M.T.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tugas Akhir/Skripsi -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Tugas Akhir/Skripsi:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Deasy Carolina, S.E., M.M</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kerja Praktek -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Kerja Praktek:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Hanita Yulia, M.Pd.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Lembaga Penjaminan Mutu Fakultas -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Lembaga Penjaminan Mutu Fakultas:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Theophilus Erman Wellem, Ph.D.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sarpras & Infrastruktur Fakultas -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Sarpras & Infrastruktur Fakultas:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Erwien Christanto, S.Kom., M.Cs.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Jurnal AITI -->
                        <div class="leader-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong>Jurnal AITI:</strong>
                                </div>
                                <div class="col-md-7">
                                    <span>Dr. Indrastanti R. Widiasari, M.T</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Informasi Tambahan -->
            <div class="info-cards-grid">
                <div class="info-card fade-in-up">
                    <i class="bi bi-clock-history"></i>
                    <h5>Jam Kerja</h5>
                    <p>
                        Senin - Jumat: 08.00 - 16.00 WIB<br>
                        Sabtu: Libur
                    </p>
                </div>
                
                <div class="info-card fade-in-up">
                    <i class="bi bi-telephone"></i>
                    <h5>Kontak</h5>
                    <p>
                        Email: fti@uksw.edu<br>
                        Telepon: 089646027727
                    </p>
                </div>
                
                <div class="info-card fade-in-up">
                    <i class="bi bi-geo-alt"></i>
                    <h5>Lokasi</h5>
                    <p>
                        Fakultas Teknologi Informasi<br>
                        Universitas Kristen Satya Wacana
                    </p>
                </div>
            </div>
        </main>
    </div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <script>
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

        // Add hover effects for leader items
        document.querySelectorAll('.leader-item').forEach(item => {
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

<?php include '../includes/footer.php'; ?>