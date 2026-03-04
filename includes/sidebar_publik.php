<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar UKSW</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Logo */
        .sidebar-logo {
            padding: 20px 28px;
            text-align: center;
        }

        .sidebar-logo .logo-img {
            display: block;
            width: 100%;
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

        /* Submenu */
        .submenu {
            display: none;
            list-style: none;
            padding-left: 20px;
            margin: 0;
            background: rgba(0, 0, 0, 0.03);
        }

        .submenu li a {
            padding: 10px 20px;
            font-size: 12px;
        }

        .has-submenu.active .submenu {
            display: block;
        }

        .has-submenu > a::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            float: right;
            transition: transform 0.3s;
        }

        .has-submenu.active > a::after {
            transform: rotate(180deg);
        }

        /* Login Button */
        .sidebar-login {
            text-align: center;
            padding: 15px 0;
        }

        .sidebar-login a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 13.66px;
            line-height: 20px;
            letter-spacing: 0.03em;
            color: #000000;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-login a:hover {
            font-weight: 600;
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
</head>
<body>
    <!-- Sidebar Admin -->
    <nav class="sidebar">
        <div class="sidebar-content">
            <!-- Logo UKSW -->
            <div class="sidebar-logo">
                <img src="assets/logo_uksw.png" alt="UKSW Logo" class="logo-img" onerror="tryAlternatePath(this)">
            </div>
            
            <!-- Menu Navigation -->
            <ul class="sidebar-menu">
                <li>
                    <a href="beranda.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'beranda.php') ? 'active' : ''; ?>">
                        HOME
                    </a>
                </li>
                <li class="has-submenu">
                    <a href="#" class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['visi_misi.php', 'manajemen.php'])) ? 'active' : ''; ?>">
                        TENTANG KAMI
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="visi_misi.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'visi_misi.php') ? 'active' : ''; ?>">
                                Visi Misi
                            </a>
                        </li>
                        <li>
                            <a href="manajemen.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manajemen.php') ? 'active' : ''; ?>">
                                Manajemen
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#" class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['sejarah.php', 'kurikulum.php', 'staf_pegawai.php'])) ? 'active' : ''; ?>">
                        DOKTOR ILMU KOMPUTER
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="sejarah.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'sejarah.php') ? 'active' : ''; ?>">
                                Sejarah DIK
                            </a>
                        </li>
                        <li>
                            <a href="kurikulum.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'kurikulum.php') ? 'active' : ''; ?>">
                                Kurikulum
                            </a>
                        </li>
                        <li>
                            <a href="staf_pengajar.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'staf_pengajar.php') ? 'active' : ''; ?>">
                                Staf Pengajar
                            </a>
                        </li>
                        <li>
                            <a href="bagan_alir.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'bagan_alir.php') ? 'active' : ''; ?>">
                                Bagan Alir Mahasiswa S3 Ilmu Komputer
                            </a>
                        </li>
                        <li>
                            <a href="jadwal_pendaftaran.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'jadwal_pendaftaran.php') ? 'active' : ''; ?>">
                                Jadwal/Syarat Pendaftaran
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="registrasi.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'registrasi.php') ? 'active' : ''; ?>">
                        REGISTRASI UJIAN DIK
                    </a>
                </li>
                <li>
                    <a href="fasilitas.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'fasilitas.php') ? 'active' : ''; ?>">
                        FASILITAS
                    </a>
                </li>
            </ul>
            
            
            
            <!-- Bottom Section -->
            <div class="sidebar-bottom">
                
                
                <div class="sidebar-divider-thin"></div>
                
                <!-- Login Button -->
                <div class="sidebar-login">
                    <a href="../login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>LOGIN</span>
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

    <script>
        // Function to try alternate paths if image fails to load
        function tryAlternatePath(img) {
            const paths = [
                '../assets/LOGO UKSW CM.png',
                'assets/LOGO UKSW CM.png',
                '../assets/logo-uksw.png',
                'assets/logo-uksw.png',
                '/assets/LOGO UKSW CM.png',
                '/assets/logo-uksw.png'
            ];
            
            // Get current failed path
            const currentPath = img.src.split('/').pop();
            
            // Try next path
            for (let i = 0; i < paths.length; i++) {
                if (!img.dataset.tried || !img.dataset.tried.includes(paths[i])) {
                    img.dataset.tried = (img.dataset.tried || '') + paths[i] + ';';
                    img.src = paths[i];
                    return;
                }
            }
            
            // If all paths failed, hide the image
            img.style.display = 'none';
            console.error('Logo UKSW tidak ditemukan di semua path yang dicoba');
        }

        // Toggle submenu
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.has-submenu > a');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    
                    // Toggle active class
                    parent.classList.toggle('active');
                    
                    // Close other open submenus
                    document.querySelectorAll('.has-submenu').forEach(otherItem => {
                        if (otherItem !== parent) {
                            otherItem.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>