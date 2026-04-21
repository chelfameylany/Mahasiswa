<?php
include "../koneksi.php";
include "auth_admin.php";

/* ====== UPDATE STATUS AJAX ====== */
if(isset($_POST['id']) && isset($_POST['status'])){
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $allowed_status = ['diterima', 'ditolak'];
    if(in_array($status, $allowed_status)){
        $update = mysqli_query($koneksi, "UPDATE daftar_ulang SET status_daftar_ulang='$status' WHERE id_daftar_ulang='$id'");
        
        if($update){
            echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal update: ' . mysqli_error($koneksi)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    }
    exit;
}

/* ====== EXPORT EXCEL ====== */
if(isset($_GET['export_excel'])){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=daftar_ulang_" . date("Y-m-d") . ".xls");
    
    $data_export = mysqli_query($koneksi, "
        SELECT 
            d.id_daftar_ulang,
            m.nama,
            m.email,
            m.jurusan,
            d.tanggal_daftar_ulang,
            d.status_daftar_ulang
        FROM daftar_ulang d
        JOIN calon_maba m ON d.id_maba = m.id_maba
        ORDER BY d.id_daftar_ulang DESC
    ");
    
    echo "<table border='1'>";
    echo "<tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Jurusan</th>
            <th>Tanggal Daftar Ulang</th>
            <th>Status</th>
          </tr>";
    
    $no = 1;
    while($row = mysqli_fetch_assoc($data_export)){
        $status_text = $row['status_daftar_ulang'] == 'menunggu' ? 'Menunggu' : ($row['status_daftar_ulang'] == 'diterima' ? 'Diterima' : 'Ditolak');
        echo "<tr>
                <td>{$no}</td>
                <td>{$row['nama']}</td>
                <td>{$row['email']}</td>
                <td>{$row['jurusan']}</td>
                <td>{$row['tanggal_daftar_ulang']}</td>
                <td>{$status_text}</td>
              </tr>";
        $no++;
    }
    echo "</table>";
    exit;
}

/* ====== LOAD DATA ====== */
$data = mysqli_query($koneksi, "
    SELECT 
        d.id_daftar_ulang,
        d.tanggal_daftar_ulang,
        d.status_daftar_ulang,
        d.bukti_pembayaran,
        m.nama,
        m.email,
        m.jurusan
    FROM daftar_ulang d
    JOIN calon_maba m ON d.id_maba = m.id_maba
    ORDER BY d.id_daftar_ulang DESC
");

// Hitung statistik
$total = mysqli_num_rows($data);
$terkonfirmasi = 0;
$belum_konfirmasi = 0;

mysqli_data_seek($data, 0);
while($row = mysqli_fetch_assoc($data)){
    if($row['status_daftar_ulang'] == 'diterima'){
        $terkonfirmasi++;
    } elseif($row['status_daftar_ulang'] == 'menunggu'){
        $belum_konfirmasi++;
    }
}
mysqli_data_seek($data, 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Verifikasi Daftar Ulang - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-main: linear-gradient(180deg, #0d3b66 0%, #0d6efd 45%, #ffffff 100%);
    --card-bg: #ffffff;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --accent: #0d6efd;
    --accent-hover: #0b5ed7;
    --success: #22c55e;
    --danger: #ef4444;
    --warning: #f59e0b;
    --border: #e2e8f0;
    --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    --table-header: linear-gradient(135deg, #0d3b66, #0d6efd);
}

body.dark {
    --bg-main: linear-gradient(180deg, #020617 0%, #0b1f3a 45%, #1e293b 100%);
    --card-bg: #1e293b;
    --text-main: #f1f5f9;
    --text-muted: #94a3b8;
    --border: #334155;
    --shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    --table-header: linear-gradient(135deg, #020617, #0b1f3a);
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg-main);
    color: var(--text-main);
    transition: all 0.3s ease;
    min-height: 100vh;
}

.main-content {
    margin-left: 250px;
    padding: 15px 20px;
}

/* CARD UTAMA */
.content-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 20px;
    box-shadow: var(--shadow);
    border: 1px solid rgba(255,255,255,0.1);
    animation: fadeInUp 0.5s ease;
}

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

/* STATS CARD */
.stats-wrapper {
    margin-bottom: 25px;
}

.stats-mini {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-mini-card {
    flex: 1;
    background: var(--card-bg);
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    animation: slideIn 0.5s ease backwards;
}

.stat-mini-card:nth-child(1) { animation-delay: 0.1s; }
.stat-mini-card:nth-child(2) { animation-delay: 0.2s; }
.stat-mini-card:nth-child(3) { animation-delay: 0.3s; }

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.stat-mini-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.stat-mini-card:hover::before {
    left: 100%;
}

.stat-mini-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-mini-card.total {
    border-left: 4px solid #3b82f6;
}
.stat-mini-card.konfirmasi {
    border-left: 4px solid #22c55e;
}
.stat-mini-card.belum {
    border-left: 4px solid #f59e0b;
}

.stat-mini-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: all 0.3s ease;
}

.stat-mini-card:hover .stat-mini-icon {
    transform: scale(1.1) rotate(5deg);
}

.stat-mini-icon.total { background: rgba(59,130,246,0.15); color: #3b82f6; }
.stat-mini-icon.konfirmasi { background: rgba(34,197,94,0.15); color: #22c55e; }
.stat-mini-icon.belum { background: rgba(245,158,11,0.15); color: #f59e0b; }

.stat-mini-info h4 {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin: 0 0 5px;
}

.stat-mini-info .number {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1;
}

/* HEADER */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border);
    flex-wrap: wrap;
    gap: 15px;
}

.header-title h2 {
    font-size: 22px;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(135deg, #0d6efd, #0d3b66);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

body.dark .header-title h2 {
    background: linear-gradient(135deg, #60a5fa, #a78bfa);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.header-title p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 5px 0 0;
}

/* TOOLBAR */
.toolbar {
    display: flex;
    gap: 12px;
    align-items: center;
}

.search-box {
    position: relative;
}

.search-box input {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 40px;
    padding: 10px 18px 10px 42px;
    font-size: 13px;
    width: 260px;
    color: var(--text-main);
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
    width: 280px;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 16px;
}

.btn-export {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border: none;
    padding: 10px 22px;
    border-radius: 40px;
    font-weight: 600;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-export:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(34,197,94,0.4);
    color: white;
}

.theme-toggle {
    width: 42px;
    height: 42px;
    background: rgba(13,110,253,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 20px;
    color: var(--accent);
}

.theme-toggle:hover {
    background: var(--accent);
    color: white;
    transform: rotate(15deg) scale(1.1);
}

/* TABLE */
.table-wrapper {
    overflow-x: auto;
    border-radius: 16px;
    animation: fadeIn 0.6s ease 0.4s backwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: var(--table-header);
    color: white;
    padding: 14px 12px;
    font-weight: 600;
    font-size: 13px;
    text-align: center;
}

.data-table td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 13px;
    transition: all 0.2s ease;
}

.data-table tr {
    transition: all 0.2s ease;
}

.data-table tr:hover {
    background: rgba(13,110,253,0.05);
    transform: scale(1.01);
}

body.dark .data-table tr:hover {
    background: rgba(96,165,250,0.1);
}

/* BADGE */
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 40px;
    font-size: 11px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.badge-menunggu {
    background: #fef3c7;
    color: #92400e;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.badge-diterima {
    background: #d1fae5;
    color: #065f46;
}

.badge-ditolak {
    background: #fee2e2;
    color: #991b1b;
}

/* BUTTON */
.btn-action {
    padding: 7px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-accept {
    background: #22c55e;
    color: white;
}

.btn-accept:hover {
    background: #16a34a;
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(34,197,94,0.3);
}

.btn-reject {
    background: #ef4444;
    color: white;
}

.btn-reject:hover {
    background: #dc2626;
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(239,68,68,0.3);
}

.btn-view {
    background: rgba(13,110,253,0.1);
    color: var(--accent);
    text-decoration: none;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-view:hover {
    background: var(--accent);
    color: white;
    transform: translateY(-2px);
}

/* MODAL BUKTI */
.modal-bukti {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    backdrop-filter: blur(8px);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal-bukti.show {
    display: flex;
    animation: fadeIn 0.3s ease;
}

.modal-bukti-content {
    background: var(--card-bg);
    border-radius: 24px;
    max-width: 90%;
    max-height: 90%;
    width: auto;
    position: relative;
    animation: modalZoom 0.3s ease;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    overflow: hidden;
}

@keyframes modalZoom {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-bukti-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: var(--table-header);
    color: white;
}

.modal-bukti-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.modal-bukti-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.modal-bukti-close:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(90deg);
}

.modal-bukti-body {
    padding: 20px;
    text-align: center;
}

.modal-bukti-body img {
    max-width: 100%;
    max-height: 70vh;
    border-radius: 12px;
    cursor: zoom-in;
}

.modal-bukti-body .no-image {
    padding: 50px;
    text-align: center;
    color: var(--text-muted);
}

.modal-bukti-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: center;
    gap: 10px;
}

.btn-download {
    background: var(--accent);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-download:hover {
    background: var(--accent-hover);
    transform: scale(1.02);
}

/* MODAL NOTIFIKASI */
.modal-custom {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-custom.show {
    display: flex;
    animation: fadeIn 0.3s ease;
}

.modal-content-custom {
    background: var(--card-bg);
    border-radius: 28px;
    padding: 35px;
    max-width: 380px;
    width: 90%;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    animation: modalPop 0.3s ease;
}

@keyframes modalPop {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-icon {
    font-size: 60px;
    margin-bottom: 15px;
}

.modal-title {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 10px;
}

.modal-message {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 25px;
}

.modal-btn {
    padding: 10px 30px;
    border-radius: 40px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    background: var(--accent);
    color: white;
    transition: all 0.2s ease;
}

.modal-btn:hover {
    background: var(--accent-hover);
    transform: scale(1.05);
}

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    animation: fadeIn 0.5s ease;
}

.empty-icon {
    font-size: 70px;
    color: var(--text-muted);
    margin-bottom: 15px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.empty-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

.empty-text {
    font-size: 13px;
    color: var(--text-muted);
}

@media (max-width: 1000px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .stats-mini {
        flex-direction: column;
    }
    
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .toolbar {
        justify-content: space-between;
    }
}

@media (max-width: 600px) {
    .toolbar {
        flex-direction: column;
        width: 100%;
    }
    
    .search-box {
        width: 100%;
    }
    
    .search-box input {
        width: 100%;
    }
    
    .search-box input:focus {
        width: 100%;
    }
    
    .btn-export {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    
    <!-- STATISTIK WRAPPER -->
    <div class="stats-wrapper">
        <div class="stats-mini">
            <div class="stat-mini-card total">
                <div class="stat-mini-icon total">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-mini-info">
                    <h4>Total Pendaftar</h4>
                    <div class="number"><?= $total ?></div>
                </div>
            </div>
            <div class="stat-mini-card konfirmasi">
                <div class="stat-mini-icon konfirmasi">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-mini-info">
                    <h4>Terkonfirmasi</h4>
                    <div class="number"><?= $terkonfirmasi ?></div>
                </div>
            </div>
            <div class="stat-mini-card belum">
                <div class="stat-mini-icon belum">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-mini-info">
                    <h4>Belum Dikonfirmasi</h4>
                    <div class="number"><?= $belum_konfirmasi ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-card">
        
        <!-- HEADER -->
        <div class="card-header">
            <div class="header-title">
                <h2><i class="bi bi-check2-square me-2"></i> Verifikasi Daftar Ulang</h2>
                <p><i class="bi bi-info-circle me-1"></i> Klik ACC untuk menerima / Tolak untuk menolak daftar ulang mahasiswa</p>
            </div>
            <div class="toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama, email, jurusan..." onkeyup="searchTable()">
                </div>
                <a href="?export_excel=1" class="btn-export">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                </a>
                <div class="theme-toggle" onclick="toggleTheme()">
                    <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-wrapper">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Jurusan</th>
                        <th>Tanggal</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php 
                $no = 1;
                if(mysqli_num_rows($data) > 0):
                    while($row = mysqli_fetch_assoc($data)): 
                ?>
                <tr id="row_<?= $row['id_daftar_ulang'] ?>">
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="searchable"><?= htmlspecialchars($row['nama']) ?></td>
                    <td class="searchable"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="searchable"><?= htmlspecialchars($row['jurusan']) ?></td>
                    <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['tanggal_daftar_ulang'])) ?></td>
                    <td class="text-center">
                        <?php if($row['bukti_pembayaran']): ?>
                            <button class="btn-view" onclick="showBukti('<?= $row['bukti_pembayaran'] ?>', '<?= htmlspecialchars($row['nama']) ?>')">
                                <i class="bi bi-eye"></i> Lihat Bukti
                            </button>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge-status badge-<?= $row['status_daftar_ulang'] ?>" id="status_<?= $row['id_daftar_ulang'] ?>">
                            <i class="bi <?= $row['status_daftar_ulang'] == 'menunggu' ? 'bi-hourglass-split' : ($row['status_daftar_ulang'] == 'diterima' ? 'bi-check-circle-fill' : 'bi-x-circle-fill') ?>"></i>
                            <?= $row['status_daftar_ulang'] == 'menunggu' ? 'Menunggu' : ($row['status_daftar_ulang'] == 'diterima' ? 'Diterima' : 'Ditolak') ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if($row['status_daftar_ulang'] == 'menunggu'): ?>
                            <button class="btn-action btn-accept" onclick="updateStatus(<?= $row['id_daftar_ulang'] ?>, 'diterima')">
                                <i class="bi bi-check-lg"></i> ACC
                            </button>
                            <button class="btn-action btn-reject" onclick="updateStatus(<?= $row['id_daftar_ulang'] ?>, 'ditolak')">
                                <i class="bi bi-x-lg"></i> Tolak
                            </button>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr class="no-data-row">
                    <td colspan="8">
                        <div class="empty-state" style="padding: 40px;">
                            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                            <div class="empty-title">Belum Ada Pengajuan</div>
                            <div class="empty-text">Belum ada mahasiswa yang melakukan daftar ulang</div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<!-- MODAL BUKTI PEMBAYARAN -->
<div id="modalBukti" class="modal-bukti">
    <div class="modal-bukti-content">
        <div class="modal-bukti-header">
            <h3><i class="bi bi-receipt me-2"></i> Bukti Pembayaran - <span id="buktiNama"></span></h3>
            <button class="modal-bukti-close" onclick="closeModalBukti()">&times;</button>
        </div>
        <div class="modal-bukti-body" id="buktiBody">
            <img id="buktiImage" src="" alt="Bukti Pembayaran">
        </div>
        <div class="modal-bukti-footer">
            <button class="btn-download" onclick="downloadBukti()">
                <i class="bi bi-download"></i> Download
            </button>
        </div>
    </div>
</div>

<!-- MODAL NOTIFIKASI -->
<div id="notifModal" class="modal-custom">
    <div class="modal-content-custom">
        <div class="modal-icon" id="modalIcon">
            <i class="bi bi-check-circle-fill" style="color: #22c55e;"></i>
        </div>
        <div class="modal-title" id="modalTitle">Berhasil!</div>
        <div class="modal-message" id="modalMessage">Status berhasil diupdate</div>
        <button class="modal-btn" onclick="closeModal()">Tutup</button>
    </div>
</div>

<script>
let currentBuktiUrl = '';

function toggleTheme(){
    const body = document.body;
    const icon = document.getElementById('themeIcon');
    body.classList.toggle('dark');
    icon.className = body.classList.contains('dark')
        ? "bi bi-sun-fill"
        : "bi bi-moon-stars-fill";
}

function showModal(isSuccess, title, message) {
    const modal = document.getElementById('notifModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    
    if(isSuccess) {
        modalIcon.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #22c55e; font-size: 50px;"></i>';
        modalTitle.style.color = '#22c55e';
    } else {
        modalIcon.innerHTML = '<i class="bi bi-x-circle-fill" style="color: #ef4444; font-size: 50px;"></i>';
        modalTitle.style.color = '#ef4444';
    }
    
    modalTitle.innerHTML = title;
    modalMessage.innerHTML = message;
    modal.classList.add('show');
}

function closeModal() {
    document.getElementById('notifModal').classList.remove('show');
}

function showBukti(filename, nama) {
    currentBuktiUrl = '../uploads/bukti_hasil_tes/' + filename;
    document.getElementById('buktiNama').innerText = nama;
    const imgElement = document.getElementById('buktiImage');
    imgElement.src = currentBuktiUrl;
    imgElement.onerror = function() {
        document.getElementById('buktiBody').innerHTML = '<div class="no-image"><i class="bi bi-file-image" style="font-size: 50px;"></i><br>Gambar tidak dapat dimuat</div>';
    };
    imgElement.onload = function() {
        document.getElementById('buktiBody').innerHTML = '';
        document.getElementById('buktiBody').appendChild(imgElement);
    };
    document.getElementById('modalBukti').classList.add('show');
}

function closeModalBukti() {
    document.getElementById('modalBukti').classList.remove('show');
    document.getElementById('buktiBody').innerHTML = '<img id="buktiImage" src="" alt="Bukti Pembayaran">';
}

function downloadBukti() {
    if(currentBuktiUrl) {
        const link = document.createElement('a');
        link.href = currentBuktiUrl;
        link.download = currentBuktiUrl.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Tutup modal bukti dengan tombol ESC
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') {
        closeModalBukti();
        closeModal();
    }
});

// Tutup modal bukti klik di luar area
document.getElementById('modalBukti').addEventListener('click', function(e) {
    if(e.target === this) {
        closeModalBukti();
    }
});

function updateStatus(id, status) {
    const statusText = status == 'diterima' ? 'diterima' : 'ditolak';
    if(!confirm(`Yakin ingin ${statusText} daftar ulang ini?`)) return;
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const badge = document.getElementById(`status_${id}`);
            if(badge) {
                badge.className = `badge-status badge-${status}`;
                if(status == 'diterima') {
                    badge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Diterima';
                } else {
                    badge.innerHTML = '<i class="bi bi-x-circle-fill"></i> Ditolak';
                }
            }
            
            const row = document.getElementById(`row_${id}`);
            if(row) {
                const actionCell = row.cells[7];
                if(actionCell) {
                    actionCell.innerHTML = '<span class="text-muted">-</span>';
                }
            }
            
            showModal(true, 'Berhasil!', `Status berhasil diubah menjadi ${status == 'diterima' ? 'DITERIMA' : 'DITOLAK'}`);
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showModal(false, 'Gagal!', data.message || 'Terjadi kesalahan saat update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModal(false, 'Error!', 'Terjadi kesalahan koneksi');
    });
}

function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('dataTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const searchableCells = row.getElementsByClassName('searchable');
        let found = false;
        
        for (let j = 0; j < searchableCells.length; j++) {
            const cellText = searchableCells[j].textContent.toLowerCase();
            if (cellText.indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        if (found) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

let searchTimeout;
const searchInput = document.getElementById('searchInput');
if(searchInput) {
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchTable();
        }, 300);
    });
}
</script>

</body>
</html>