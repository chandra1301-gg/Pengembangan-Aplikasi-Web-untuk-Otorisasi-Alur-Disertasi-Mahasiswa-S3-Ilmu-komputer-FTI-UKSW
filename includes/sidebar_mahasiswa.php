<!-- Sidebar Mahasiswa -->
<nav class="sidebar">
    <div class="sidebar-content">
        <!-- Logo UKSW -->
        <div class="sidebar-logo">
            <!-- Gunakan sistem fallback seperti admin -->
            <img src="../assets/logo_uksw.png" alt="UKSW Logo" class="logo-img" onerror="tryAlternatePath(this)">
        </div>
        
        <!-- Menu Navigation -->
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    BERANDA
                </a>
            </li>
            <li>
                <a href="registrasi.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'registrasi.php' || strpos($_SERVER['PHP_SELF'], 'form_') !== false) ? 'active' : ''; ?>">
                    REGISTRASI
                </a>
            </li>
            <li>
                <a href="unduh_dokumen.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'unduh_dokumen.php') ? 'active' : ''; ?>">
                    UNDUH DOKUMEN
                </a>
            </li>
            <li>
                <a href="revisi_disertasi.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'revisi_disertasi.php') ? 'active' : ''; ?>">
                    REVISI DISERTASI
                </a>
            </li>
            
            <!-- Tambahkan di sidebar mahasiswa -->
            <li class="nav-item">
                <a class="nav-link" href="messages.php">
                    <i class="fas fa-envelope"></i> Pesan
                    <?php 
                    // Cek apakah session sudah aktif sebelum menggunakan MessageModel
                    if (isset($_SESSION['user_id'])) {
                        require_once '../includes/message_model.php';
                        $unread_count = MessageModel::getUnreadCount($_SESSION['user_id']);
                        if ($unread_count > 0): ?>
                            <span class="badge bg-danger float-end"><?php echo $unread_count; ?></span>
                        <?php endif;
                    }
                    ?>
                </a>
            </li>
        </ul>
        
        <!-- Bottom Section -->
        <div class="sidebar-bottom">
            <div class="sidebar-divider"></div>
            
            <div class="sidebar-user">
                <a href="profil.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profil.php') ? 'active' : ''; ?>">
                    PROFIL
                </a>
            </div>
            
            <div class="sidebar-logout">
                <a href="../logout.php" onclick="return confirm('Yakin ingin keluar?')">
                    KELUAR
                </a>
            </div>
            
            <div class="sidebar-divider-thin"></div>
            
            <div class="sidebar-language">
                <span class="lang-active">IND</span>
                <span class="lang-inactive">ENG</span>
            </div>
            
            <div class="sidebar-copyright">
                © 2025 UKSW. All rights reserved
            </div>
        </div>
    </div>
</nav>

<style>
/* Import Poppins Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 269px;
    height: 100vh;
    background: #E4E4E4;
    z-index: 1020;
    overflow-y: auto;
    font-family: 'Poppins', sans-serif;
}

.sidebar-content {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Logo (disamakan dengan admin) */
.sidebar-logo {
    padding: 20px 28px;
    text-align: center;
}

.sidebar-logo .logo-img {
    display: block;
    width: 150%;
    max-width: 230px;
    height: auto;
    margin: 0 auto;
}

/* Menu */
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 10px 0 0 0;
}

.sidebar-menu li {
    margin-bottom: 0;
}

.sidebar-menu a {
    display: block;
    padding: 12px 35px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13.66px;
    line-height: 20px;
    letter-spacing: 0.03em;
    color: #000000;
    text-decoration: none;
    transition: all 0.3s;
    position: relative;
}

.sidebar-menu a:hover {
    background: rgba(0, 0, 0, 0.05);
}

.sidebar-menu a.active {
    font-weight: 700;
    border-bottom: 0.91px solid #000000;
    padding-bottom: 12px;
    margin-bottom: 8px;
}

/* Bottom Section */
.sidebar-bottom {
    margin-top: auto;
    padding: 20px 0;
}

.sidebar-divider {
    width: 183px;
    height: 1px;
    background: #000000;
    margin: 0 auto 20px;
    opacity: 0.5;
}

.sidebar-divider-thin {
    width: 183px;
    height: 0.5px;
    background: #000000;
    margin: 20px auto;
    opacity: 0.5;
}

.sidebar-user a,
.sidebar-logout a {
    display: block;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 13.66px;
    line-height: 20px;
    letter-spacing: 0.03em;
    color: #000000;
    text-decoration: none;
    padding: 10px 0;
    transition: all 0.3s;
}

.sidebar-user a:hover,
.sidebar-logout a:hover {
    background: rgba(0, 0, 0, 0.05);
}

.sidebar-user a.active {
    font-weight: 600;
}

/* Language */
.sidebar-language {
    text-align: center;
    padding: 10px 0;
}

.sidebar-language span {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13.66px;
    line-height: 20px;
    letter-spacing: 0.03em;
    margin: 0 8px;
    cursor: pointer;
}

.sidebar-language .lang-active {
    color: #000000;
}

.sidebar-language .lang-inactive {
    color: #747474;
}

/* Copyright */
.sidebar-copyright {
    text-align: center;
    font-family: 'Poppins', sans-serif;
    font-weight: 300;
    font-size: 10px;
    line-height: 15px;
    letter-spacing: 0.03em;
    color: #747474;
    padding: 20px 10px;
}

/* Scrollbar */
.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: #d4d4d4;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        left: -269px;
        transition: left 0.3s;
    }
    
    .sidebar.show {
        left: 0;
    }
}
</style>

<script>
// Sama seperti sidebar admin — fallback jika logo gagal dimuat
function tryAlternatePath(img) {
    const paths = [
        '../assets/LOGO UKSW CM.png',
        'assets/LOGO UKSW CM.png',
        '../assets/logo-uksw.png',
        'assets/logo-uksw.png',
        '/assets/LOGO UKSW CM.png',
        '/assets/logo-uksw.png'
    ];
    
    for (let i = 0; i < paths.length; i++) {
        if (!img.dataset.tried || !img.dataset.tried.includes(paths[i])) {
            img.dataset.tried = (img.dataset.tried || '') + paths[i] + ';';
            img.src = paths[i];
            return;
        }
    }
    img.style.display = 'none';
    console.error('Logo UKSW tidak ditemukan di semua path yang dicoba');
}
</script>
