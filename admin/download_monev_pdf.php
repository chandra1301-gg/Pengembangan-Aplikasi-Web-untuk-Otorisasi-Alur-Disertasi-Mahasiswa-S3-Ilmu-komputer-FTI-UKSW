<?php
/**
 * File: admin/download_monev_pdf.php
 * Generate PDF untuk laporan monitoring evaluasi menggunakan browser print
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Get ID
$id_monev = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_monev <= 0) {
    die("ID Monev tidak valid");
}

// Query data
$query = "SELECT mv.*, m.nama_lengkap, m.nim, m.email, m.program_studi, m.angkatan
          FROM monev mv 
          JOIN mahasiswa m ON mv.id_mahasiswa = m.id_mahasiswa 
          WHERE mv.id_monev = $id_monev";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("Data tidak ditemukan");
}

$monev = mysqli_fetch_assoc($result);
$deskripsi_data = json_decode($monev['deskripsi'], true);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Monev - <?= htmlspecialchars($monev['nim']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .page {
            padding: 20mm;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1E40AF;
        }
        
        .header h1 {
            margin: 0;
            color: #1E40AF;
            font-size: 20pt;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .header h2 {
            margin: 8px 0 0 0;
            color: #666;
            font-size: 12pt;
            font-weight: 500;
        }
        
        .header h3 {
            margin: 5px 0 0 0;
            color: #888;
            font-size: 11pt;
            font-weight: 400;
        }
        
        .info-section {
            background: #F3F4F6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 180px;
            padding: 8px 15px 8px 0;
            font-weight: 600;
            color: #374151;
            font-size: 10pt;
        }
        
        .info-value {
            display: table-cell;
            padding: 8px 0;
            color: #1F2937;
            font-size: 10pt;
        }
        
        .period-badge {
            display: inline-block;
            background: #DBEAFE;
            color: #1E40AF;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 9pt;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #1E40AF;
            color: white;
            padding: 12px 18px;
            font-size: 11pt;
            font-weight: 600;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }
        
        .section-content {
            padding: 18px;
            border: 2px solid #E5E7EB;
            border-top: none;
            border-radius: 0 0 6px 6px;
            background: #FAFAFA;
            text-align: justify;
            font-size: 10pt;
            line-height: 1.8;
            min-height: 80px;
        }
        
        .section-content em {
            color: #9CA3AF;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        /* Print Button */
        .print-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border: 2px solid #1E40AF;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-print {
            background: #1E40AF;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-print:hover {
            background: #1E3A8A;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.3);
        }
        
        .btn-close {
            background: #6B7280;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            margin-left: 10px;
            transition: all 0.2s ease;
        }
        
        .btn-close:hover {
            background: #4B5563;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                max-width: 100%;
                box-shadow: none;
            }
            
            .page {
                padding: 0;
            }
            
            .print-button-container {
                display: none !important;
            }
            
            .section {
                page-break-inside: avoid;
            }
            
            .header {
                page-break-after: avoid;
            }
        }
        
        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
</head>
<body>
    <div class="print-button-container">
        <button onclick="window.print()" class="btn-print">
            📄 Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" class="btn-close">
            ✕ Tutup
        </button>
    </div>

    <div class="container">
        <div class="page">
            <div class="header">
                <h1>LAPORAN MONITORING EVALUASI</h1>
                <h2>Program Studi Doktor Ilmu Komputer</h2>
                <h3>Universitas Kristen Satya Wacana</h3>
            </div>

            <div class="info-section">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Nama Mahasiswa</div>
                        <div class="info-value">: <?= htmlspecialchars($monev['nama_lengkap']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">NIM</div>
                        <div class="info-value">: <?= htmlspecialchars($monev['nim']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value">: <?= htmlspecialchars($monev['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Program Studi</div>
                        <div class="info-value">: <?= htmlspecialchars($monev['program_studi']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Angkatan</div>
                        <div class="info-value">: <?= htmlspecialchars($monev['angkatan']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Periode Laporan</div>
                        <div class="info-value">: <span class="period-badge"><?= htmlspecialchars($monev['periode']); ?></span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tanggal Upload</div>
                        <div class="info-value">: <?= date('d F Y, H:i', strtotime($monev['tanggal_upload'])); ?> WIB</div>
                    </div>
                </div>
            </div>

            <?php
            // Tambahkan jawaban
            if ($deskripsi_data && is_array($deskripsi_data)) {
                $pertanyaan = [
                    'jawaban1' => 'I. Jabarkan kegiatan yang sudah dapat direalisasikan (4 bulan yang lalu)',
                    'jawaban2' => 'II. Kegiatan yang seharusnya selesai, tetapi tidak/belum dapat direalisasikan. Sebutkan hambatannya dan rencana penyelesaiannya',
                    'jawaban3' => 'III. Kegiatan penelitian yang direncanakan dalam 4 bulan yang akan datang',
                    'jawaban4' => 'IV. Garis besar kegiatan penelitian selanjutnya',
                    'jawaban5' => 'V. Saran/masukan/komentar untuk perbaikan (dari reviewer)'
                ];

                foreach ($pertanyaan as $key => $label) {
                    $jawaban = isset($deskripsi_data[$key]) && !empty($deskripsi_data[$key]) 
                        ? nl2br(htmlspecialchars($deskripsi_data[$key])) 
                        : '<em>Tidak ada jawaban</em>';
                    
                    echo '
            <div class="section">
                <div class="section-title">' . $label . '</div>
                <div class="section-content">' . $jawaban . '</div>
            </div>';
                }
            } else {
                // Fallback untuk format lama
                echo '
            <div class="section">
                <div class="section-title">Laporan Monitoring Evaluasi</div>
                <div class="section-content">' . (!empty($monev['deskripsi']) ? nl2br(htmlspecialchars($monev['deskripsi'])) : '<em>Tidak ada laporan</em>') . '</div>
            </div>';
            }
            ?>

            <div class="footer">
                <p><strong>Laporan Monitoring Evaluasi Mahasiswa Program Doktor Ilmu Komputer UKSW</strong></p>
                <p>Dokumen ini dibuat secara otomatis pada <?= date('d F Y, H:i'); ?> WIB</p>
                <p>© <?= date('Y'); ?> Universitas Kristen Satya Wacana. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>