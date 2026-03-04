<?php
/**
 * File: admin/dashboard.php
 * Dashboard admin - versi dengan gambar dan pengantar informasi
 */

session_start();
include '../includes/header.php';
include '../includes/sidebar_publik.php';

// Koneksi database
require_once '../includes/db_connect.php';

// Ambil data pengumuman dari database
$sql_announcements = "SELECT * FROM announcements WHERE status = 'active' ORDER BY publish_date DESC LIMIT 3";
$result_announcements = $conn->query($sql_announcements);

// Ambil data berita dari database
$sql_news = "SELECT * FROM news WHERE status = 'active' ORDER BY news_date DESC LIMIT 3";
$result_news = $conn->query($sql_news);
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Bagian Gambar Utama -->
        <div class="card border-0 mb-4">
            <img src="../assets/gedung-fti.jpg" class="card-img-top img-hero" alt="Gedung Fakultas Teknologi">
        </div>

        <!-- Bagian Pengantar -->
        <div class="card border-0 shadow-sm mb-5">
            <div class="row g-0 align-items-center">
                <div class="col-md-4">
                    <img src="../assets/foto-dosen.png" class="img-fluid rounded-start img-intro" alt="Tim Akademik">
                </div>
                <div class="col-md-8">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3 text-primary">Pengantar</h4>
                        <p class="text-muted mb-3">
                            Program Studi adalah unit pelaksana akademik yang bertanggung jawab terhadap
                            penyelenggaraan dan pengelolaan pendidikan serta pengajaran. Untuk mengembangkan
                            kualitas program studi, maka ditetapkan visi dan misi program studi yang didasarkan
                            pada visi dan misi fakultas serta universitas.
                        </p>
                        <a href="#" class="btn btn-primary">
                            Selengkapnya <i class="bi bi-arrow-right-circle ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Join & Virtual Tour -->
        <div class="row text-center bg-primary bg-opacity-10 py-5 mb-5 rounded-3 banner-join" 
             style="background-image: url('../assets/kpattern-bg.png'); background-size: cover;">
            <div class="col-md-6 mb-3 mb-md-0">
                <h4 class="fw-bold text-primary">JOIN US!</h4>
                <p class="text-secondary mb-3">One Click Enrollment</p>
                <a href="https://admisi.uksw.edu/" class="btn btn-primary px-4">CLICK HERE</a>
            </div>
            <div class="col-md-6">
                <h4 class="fw-bold text-primary">Start <strong>360° Virtual Tour</strong></h4>
                <p class="text-secondary mb-3">Experience Your Surroundings</p>
                <a href="https://www.uksw.edu/360/Floormap/" class="btn btn-primary px-4">CLICK HERE</a>
            </div>
        </div>

        <!-- Bagian Pengumuman -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0 text-dark">Pengumuman</h4>
            <a href="announcement_all.php" class="text-primary text-decoration-none fw-semibold">
                Lihat Semua <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <div class="row mb-5">
            <?php if ($result_announcements->num_rows > 0): ?>
                <?php while ($announcement = $result_announcements->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <?php if (!empty($announcement['image'])): ?>
                                <img src="../assets/uploads/announcements/<?php echo htmlspecialchars($announcement['image']); ?>" 
                                     class="card-img-top img-announcement" alt="Gambar Pengumuman">
                            <?php else: ?>
                                <img src="../assets/images/announcement.jpg" 
                                     class="card-img-top img-announcement" alt="Gambar Pengumuman">
                            <?php endif; ?>
                            <div class="card-body">
                                <h6 class="fw-bold text-dark"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                <p class="text-muted small mb-2">
                                    <?php echo substr(htmlspecialchars($announcement['description']), 0, 150); ?>...
                                </p>
                            </div>
                            <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-event"></i> 
                                    <?php echo date('d M Y', strtotime($announcement['publish_date'])); ?>
                                </small>
                                <small class="text-muted">By <?php echo htmlspecialchars($announcement['author']); ?></small>
                            </div>
                            <div class="p-3 pt-0">
                                <a href="announcement_detail.php?id=<?php echo $announcement['id']; ?>" class="btn btn-outline-primary btn-sm">READ MORE</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> Belum ada pengumuman yang tersedia.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- BERITA TERBARU -->
        <section class="news-section py-5 bg-light rounded-3 mb-5">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Berita <span class="text-primary">Terbaru</span></h3>
                    <a href="news_all.php" class="text-primary text-decoration-none fw-semibold">
                        Lihat Semua <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                
                <div class="row g-4">
                    <?php if ($result_news->num_rows > 0): ?>
                        <?php while ($news = $result_news->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <?php if (!empty($news['image'])): ?>
                                        <img src="../assets/uploads/news/<?php echo htmlspecialchars($news['image']); ?>" 
                                             class="card-img-top img-news" alt="Berita">
                                    <?php else: ?>
                                        <img src="../assets/images/default-news.jpg" 
                                             class="card-img-top img-news" alt="Berita">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="date-box mb-3 d-flex align-items-center">
                                            <div class="calendar-icon text-center me-2">
                                                <div class="bg-danger text-white rounded p-2">
                                                    <h5 class="mb-0"><?php echo date('d', strtotime($news['news_date'])); ?></h5>
                                                    <small><?php echo date('M', strtotime($news['news_date'])); ?></small>
                                                </div>
                                            </div>
                                            <h5 class="card-title fw-bold mb-0 text-dark">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                            </h5>
                                        </div>
                                        <p class="card-text text-muted">
                                            <?php echo substr(strip_tags($news['content']), 0, 150); ?>...
                                        </p>
                                        <a href="news_detail.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-dark btn-sm">
                                            READ MORE <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="bi bi-info-circle"></i> Belum ada berita yang tersedia.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- QUICK LINKS -->
        <section class="quick-links py-5 rounded-3" style="background: url('../assets/bg-quicklinks.jpg') center/cover no-repeat; color: white;">
            <div class="container-fluid text-center">
                <h3 class="fw-bold mb-3">Quick Links</h3>
                <p class="mb-4 opacity-75">Dark for external portal and light for internal portal</p>
                <div class="links-grid d-flex flex-wrap justify-content-center gap-3">
                    <a href="https://uksw.edu/" class="btn btn-dark px-4 py-2 rounded-pill">UKSW</a>
                    <a href="https://uksw.edu/pages/siasat" class="btn btn-dark px-4 py-2 rounded-pill">SIASAT</a>
                    <a href="https://flearn.uksw.edu/" class="btn btn-dark px-4 py-2 rounded-pill">FLEARN</a>
                    <a href="#" class="btn btn-dark px-4 py-2 rounded-pill">DIGILAB</a>
                    <a href="https://swca.uksw.edu/" class="btn btn-dark px-4 py-2 rounded-pill">SWCA</a>
                    <a href="https://www.uksw.edu/pages/uksw-sms-info" class="btn btn-dark px-4 py-2 rounded-pill">SMS INFO</a>
                    <a href="https://www.uksw.edu/buletin-senin/#" class="btn btn-dark px-4 py-2 rounded-pill">BULETIN SENIN</a>
                    <a href="#" class="btn btn-dark px-4 py-2 rounded-pill">RHK</a>
                    <a href="https://ejournal.uksw.edu/" class="btn btn-dark px-4 py-2 rounded-pill">E-JOURNAL</a>
                    <a href="https://ris.uksw.edu/" class="btn btn-dark px-4 py-2 rounded-pill">RIS</a>
                    <a href="https://repository.uksw.edu/" class="btn btn-dark px-4 py-2 rounded-pill">REPOSITORY</a>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f8f9fa;
    }

    /* ======= HERO ======= */
    .hero-section {
      background: url('assets/bg-hero.png') center/cover no-repeat;
      color: #000;
      text-align: center;
      padding: 80px 0;
    }
    .hero-section h2 {
      font-weight: 700;
    }

    /* Gambar utama responsif */
    .img-hero {
        width: 100%;
        height: auto;
        max-height: 500px;
        object-fit: cover;
    }

    /* Gambar pengantar responsif */
    .img-intro {
        width: 100%;
        height: 100%;
        min-height: 250px;
        object-fit: cover;
    }

    /* Gambar pengumuman responsif */
    .img-announcement {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    /* Gambar berita responsif */
    .img-news {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    /* ======= PENGUMUMAN ======= */
    .announcement-card img {
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }

    .announcement-card {
      border-radius: 12px;
      background: #fff;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .announcement-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    /* ======= BERITA ======= */
    .news-section .calendar-icon {
      width: 60px;
      height: 60px;
      line-height: 1;
    }

    .news-section .calendar-icon h5 {
      font-weight: bold;
    }

    .news-section .card {
      border-radius: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .news-section .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* ======= QUICK LINKS ======= */
    .quick-links {
        background: url('../assets/bg-quicklinks.jpg') center/cover no-repeat !important;
        color: white;
        text-align: center;
        position: relative;
    }

    /* Tambahkan overlay untuk kontras teks yang lebih baik */
    .quick-links::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 0.5rem;
    }

    .quick-links > .container-fluid {
        position: relative;
        z-index: 1;
    }

    .quick-links a {
        color: #fff;
        background-color: rgba(0, 0, 0, 0.75);
        border: none;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .quick-links a:hover {
        background-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
    }

    /* ======= FOOTER ======= */
    footer {
      background-color: #212529;
      color: #f8f9fa;
    }

    /* ======= RESPONSIVE STYLES ======= */
    
    /* Untuk tablet */
    @media (max-width: 992px) {
        .img-hero {
            max-height: 400px;
        }
        
        .img-intro {
            min-height: 200px;
        }
        
        .banner-join .col-md-6 {
            margin-bottom: 1.5rem;
        }
        
        .news-section .date-box {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .news-section .calendar-icon {
            margin-bottom: 10px;
        }
    }
    
    /* Untuk ponsel */
    @media (max-width: 768px) {
        .img-hero {
            max-height: 300px;
        }
        
        .img-intro {
            min-height: 150px;
        }
        
        .img-announcement {
            height: 150px;
        }
        
        .img-news {
            height: 180px;
        }
        
        .card-body {
            padding: 1rem !important;
        }
        
        .banner-join {
            padding: 2rem 1rem !important;
        }
        
        .quick-links .btn {
            padding: 0.5rem 1rem !important;
            font-size: 0.9rem;
        }
        
        .news-section .date-box {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .news-section .calendar-icon {
            margin-bottom: 10px;
        }
    }
    
    /* Untuk ponsel kecil */
    @media (max-width: 576px) {
        .img-hero {
            max-height: 200px;
        }
        
        .img-intro {
            min-height: 120px;
        }
        
        .img-announcement {
            height: 120px;
        }
        
        .img-news {
            height: 150px;
        }
        
        .card-body {
            padding: 0.75rem !important;
        }
        
        .banner-join {
            padding: 1.5rem 0.75rem !important;
        }
        
        .quick-links .btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.8rem;
        }
        
        .news-section .calendar-icon {
            width: 50px;
            height: 50px;
        }
        
        .news-section .calendar-icon h5 {
            font-size: 1rem;
        }
        
        .news-section .calendar-icon small {
            font-size: 0.7rem;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>