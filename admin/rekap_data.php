<?php
/**
 * File: admin/rekap_data.php
 * Halaman untuk merekap data keseluruhan dan download CSV
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// ==========================
// PROSES DOWNLOAD CSV
// ==========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['download_csv'])) {
    // Set header untuk file CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rekap_data_disertasi_' . date('Y-m-d') . '.csv');
    
    // Output langsung ke browser
    $output = fopen('php://output', 'w');
    
    // Header CSV (tanpa nilai dan revisi)
    $header = array(
        'No',
        'Nama Mahasiswa',
        'NIM',
        'Email',
        'Program Studi',
        'Judul Disertasi',
        'Jenis Ujian',
        'Tanggal Pengajuan',
        'Promotor',
        'Co-Promotor 1',
        'Co-Promotor 2',
        'Status Verifikasi',
        'Status Kelulusan'
    );
    
    fputcsv($output, $header);
    
    // Query data dengan JOIN untuk mendapatkan semua informasi
    $query = "SELECT 
                r.*,
                m.nama_lengkap,
                m.nim,
                m.email,
                m.program_studi,
                d_promotor.nama_lengkap as nama_promotor,
                d_copromotor.nama_lengkap as nama_copromotor,
                d_copromotor2.nama_lengkap as nama_copromotor2
              FROM registrasi r 
              JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
              LEFT JOIN dosen d_promotor ON r.promotor = d_promotor.id_dosen
              LEFT JOIN dosen d_copromotor ON r.co_promotor = d_copromotor.id_dosen
              LEFT JOIN dosen d_copromotor2 ON r.co_promotor2 = d_copromotor2.id_dosen
              ORDER BY r.tanggal_pengajuan DESC";
    
    $result_query = mysqli_query($conn, $query);
    $no = 1;
    
    while ($data = mysqli_fetch_assoc($result_query)) {
        // Data untuk CSV (tanpa nilai dan revisi)
        $csv_data = array(
            $no++,
            $data['nama_lengkap'],
            $data['nim'],
            $data['email'],
            $data['program_studi'],
            $data['judul_disertasi'],
            ucfirst($data['jenis_ujian']),
            date('d/m/Y', strtotime($data['tanggal_pengajuan'])),
            $data['nama_promotor'] ?? '-',
            $data['nama_copromotor'] ?? '-',
            $data['nama_copromotor2'] ?? '-',
            $data['status'],
            $data['status_kelulusan'] ?? 'belum_ujian'
        );
        
        fputcsv($output, $csv_data);
    }
    
    fclose($output);
    exit();
}

// ==========================
// QUERY DATA UNTUK TAMPILAN
// ==========================
$query = "SELECT 
            r.*,
            m.nama_lengkap,
            m.nim,
            m.email,
            m.program_studi,
            d_promotor.nama_lengkap as nama_promotor,
            d_copromotor.nama_lengkap as nama_copromotor,
            d_copromotor2.nama_lengkap as nama_copromotor2
          FROM registrasi r 
          JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
          LEFT JOIN dosen d_promotor ON r.promotor = d_promotor.id_dosen
          LEFT JOIN dosen d_copromotor ON r.co_promotor = d_copromotor.id_dosen
          LEFT JOIN dosen d_copromotor2 ON r.co_promotor2 = d_copromotor2.id_dosen
          ORDER BY r.tanggal_pengajuan DESC";

$result = mysqli_query($conn, $query);
$total_data = mysqli_num_rows($result);

$page_title = "Rekap Data - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<link href="../assets/css/admin-styles.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Rekap Data Keseluruhan</h1>
            <p class="hero-breadcrumb">Dashboard<span class="separator">›</span>Rekap Data</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <div class="header-section">
            <h2 class="page-title">📊 Rekap Data Disertasi</h2>
            <div class="header-actions">
                <form method="POST" class="inline-form">
                    <button type="submit" name="download_csv" class="btn-download-csv">
                        <span class="btn-icon">📥</span>
                        Download CSV
                    </button>
                </form>
                <div class="total-counter">
                    <span class="counter-icon">📋</span>
                    Total Data: <?= $total_data; ?>
                </div>
            </div>
        </div>
        
        <hr class="title-divider">
        
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Cari Data:</label>
                    <input type="text" id="searchInput" class="filter-select" placeholder="Cari berdasarkan nama, NIM, judul...">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status Verifikasi:</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="Menunggu">Menunggu</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Jenis Ujian:</label>
                    <select id="jenisFilter" class="filter-select">
                        <option value="">Semua Jenis</option>
                        <option value="proposal">Proposal</option>
                        <option value="kualifikasi">Kualifikasi</option>
                        <option value="kelayakan">Kelayakan</option>
                        <option value="tertutup">Tertutup</option>
                    </select>
                </div>
                <button type="button" id="resetFilter" class="btn-reset">Reset Filter</button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">Data Disertasi Mahasiswa</h5>
                <div class="table-info">
                    <span id="filteredCount"><?= $total_data; ?></span> data ditampilkan
                </div>
            </div>
            
            <?php if ($total_data > 0): ?>
                <div class="table-wrapper">
                    <table class="table-custom" id="dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Judul Disertasi</th>
                                <th>Ujian</th>
                                <th>Pengajuan</th>
                                <th>Tim Pembimbing</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1; 
                            mysqli_data_seek($result, 0);
                            while ($data_row = mysqli_fetch_assoc($result)): 
                                
                                // Status badge class
                                $status_class = match($data_row['status']) {
                                    'Menunggu' => 'badge-menunggu',
                                    'Diterima' => 'badge-diterima',
                                    'Ditolak' => 'badge-ditolak',
                                    default => 'badge-menunggu'
                                };
                                
                                // Kelulusan badge
                                $kelulusan_badge = match($data_row['status_kelulusan'] ?? 'belum_ujian') {
                                    'lulus' => '<span class="status-badge badge-diterima" style="font-size: 9px;">Lulus</span>',
                                    'tidak_lulus' => '<span class="status-badge badge-ditolak" style="font-size: 9px;">Tidak Lulus</span>',
                                    default => '<span class="status-badge badge-menunggu" style="font-size: 9px;">Belum</span>'
                                };
                            ?>
                            <tr data-status="<?= $data_row['status']; ?>" data-jenis="<?= $data_row['jenis_ujian']; ?>"
                                data-search="<?= strtolower($data_row['nama_lengkap'] . ' ' . $data_row['nim'] . ' ' . $data_row['judul_disertasi']); ?>">
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="student-info-compact">
                                        <span class="student-name"><?= $data_row['nama_lengkap']; ?></span>
                                        <span class="student-nim"><?= $data_row['nim']; ?></span>
                                        <small class="student-email"><?= $data_row['email']; ?></small>
                                        <small class="student-prodi"><?= $data_row['program_studi']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="dissertation-title-compact" title="<?= htmlspecialchars($data_row['judul_disertasi']); ?>">
                                        <?= mb_strimwidth($data_row['judul_disertasi'], 0, 60, '...'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="exam-type-badge"><?= ucfirst($data_row['jenis_ujian']); ?></span>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <?= date('d/m/Y', strtotime($data_row['tanggal_pengajuan'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="supervisor-info">
                                        <div class="supervisor-item">
                                            <small><strong>Promotor:</strong> <?= $data_row['nama_promotor'] ?? '-' ?></small>
                                        </div>
                                        <div class="supervisor-item">
                                            <small><strong>Co-Promotor 1:</strong> <?= $data_row['nama_copromotor'] ?? '-' ?></small>
                                        </div>
                                        <div class="supervisor-item">
                                            <small><strong>Co-Promotor 2:</strong> <?= $data_row['nama_copromotor2'] ?? '-' ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="status-info">
                                        <div class="status-item">
                                            <span class="status-badge <?= $status_class; ?>" style="font-size: 9px;">
                                                <?= $data_row['status']; ?>
                                            </span>
                                        </div>
                                        <div class="status-item">
                                            <?= $kelulusan_badge; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p class="empty-state-title">Tidak ada data pendaftaran</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Custom Styles for Rekap Page */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.header-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-download-csv {
    background: #059669;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-download-csv:hover {
    background: #047857;
    transform: translateY(-2px);
}

.btn-icon {
    font-size: 16px;
}

.total-counter {
    background: #EFF6FF;
    padding: 10px 15px;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #1E40AF;
    display: flex;
    align-items: center;
    gap: 8px;
}

.counter-icon {
    font-size: 16px;
}

.student-info-compact {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.student-name {
    font-weight: 600;
    font-size: 13px;
    color: #111827;
}

.student-nim {
    font-size: 11px;
    color: #6B7280;
}

.student-email {
    font-size: 10px;
    color: #9CA3AF;
    word-break: break-all;
}

.student-prodi {
    font-size: 10px;
    color: #059669;
    font-weight: 500;
}

.dissertation-title-compact {
    max-width: 200px;
    font-size: 12px;
    line-height: 1.4;
    color: #374151;
}

.supervisor-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.supervisor-item small {
    display: block;
    font-size: 10px;
    line-height: 1.3;
    color: #6B7280;
}

.supervisor-item strong {
    color: #374151;
}

.status-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.status-item {
    display: flex;
    justify-content: center;
}

.date-info {
    font-size: 11px;
    color: #374151;
    text-align: center;
}

/* Table Responsive */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Mobile Optimizations */
@media (max-width: 1200px) {
    .dissertation-title-compact {
        max-width: 150px;
    }
    
    .supervisor-info {
        max-width: 180px;
    }
}

@media (max-width: 992px) {
    .header-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .filter-row {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .table-card {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table-custom {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .table-custom thead {
        display: none;
    }
    
    .table-custom tbody tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 15px;
        background: white;
    }
    
    .table-custom tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border: none;
        border-bottom: 1px solid #F3F4F6;
    }
    
    .table-custom tbody td:last-child {
        border-bottom: none;
    }
    
    .table-custom tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 11px;
        color: #374151;
        text-align: left;
        flex: 1;
    }
    
    .table-custom tbody td > * {
        flex: 2;
        text-align: right;
    }
    
    /* Hide some columns on mobile */
    .student-email,
    .student-prodi,
    .supervisor-item:nth-child(n+2) {
        display: none;
    }
    
    .dissertation-title-compact {
        max-width: 100%;
    }
    
    .supervisor-info {
        max-width: 100%;
    }
}

@media (max-width: 576px) {
    .supervisor-info {
        max-width: 120px;
    }
    
    .dissertation-title-compact {
        max-width: 100px;
    }
}

@media (max-width: 480px) {
    .btn-download-csv {
        width: 100%;
        justify-content: center;
    }
    
    .total-counter {
        width: 100%;
        justify-content: center;
    }
    
    .filter-section {
        padding: 15px;
    }
    
    .supervisor-info {
        max-width: 100px;
    }
    
    .dissertation-title-compact {
        max-width: 80px;
        font-size: 11px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const jenisFilter = document.getElementById('jenisFilter');
    const resetFilter = document.getElementById('resetFilter');
    const dataTable = document.getElementById('dataTable');
    const filteredCount = document.getElementById('filteredCount');
    const rows = dataTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const jenisValue = jenisFilter.value;
        
        let visibleCount = 0;
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const searchData = row.getAttribute('data-search') || '';
            const rowStatus = row.getAttribute('data-status');
            const rowJenis = row.getAttribute('data-jenis');
            
            // Apply all filters
            const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
            const matchesStatus = statusValue === '' || rowStatus === statusValue;
            const matchesJenis = jenisValue === '' || rowJenis === jenisValue;
            
            if (matchesSearch && matchesStatus && matchesJenis) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
        
        // Update filtered count
        filteredCount.textContent = visibleCount;
        
        // Add data-label attributes for mobile responsive
        if (window.innerWidth <= 768) {
            const headers = ['No', 'Mahasiswa', 'Judul Disertasi', 'Ujian', 'Pengajuan', 'Tim Pembimbing', 'Status'];
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                for (let j = 0; j < cells.length && j < headers.length; j++) {
                    cells[j].setAttribute('data-label', headers[j]);
                }
            }
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    jenisFilter.addEventListener('change', filterTable);
    
    resetFilter.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        jenisFilter.value = '';
        filterTable();
    });
    
    // Initial filter
    filterTable();
    
    // Handle window resize for mobile responsive
    window.addEventListener('resize', function() {
        filterTable();
    });
    
    // Download CSV button confirmation
    const downloadBtn = document.querySelector('button[name="download_csv"]');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mendownload data dalam format CSV?')) {
                // Show loading
                downloadBtn.innerHTML = '<span class="btn-icon">⏳</span>Menyiapkan CSV...';
                downloadBtn.disabled = true;
                
                // Re-enable after 3 seconds if still on page
                setTimeout(() => {
                    downloadBtn.innerHTML = '<span class="btn-icon">📥</span>Download CSV';
                    downloadBtn.disabled = false;
                }, 3000);
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>