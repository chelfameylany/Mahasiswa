<?php
include "auth_admin.php";
include "../koneksi.php";

// ===== CEK JADWAL UJIAN =====
$tanggal_ujian = "2025-04-21 08:00:00";
$waktu_sekarang = date('Y-m-d H:i:s');

$timestamp_ujian = strtotime($tanggal_ujian);
$timestamp_sekarang = strtotime($waktu_sekarang);
$selisih_detik = $timestamp_ujian - $timestamp_sekarang;
$ujian_terkunci = ($selisih_detik > 0);

$jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : 'umum';
$search  = isset($_GET['search']) ? mysqli_real_escape_string($koneksi,$_GET['search']) : "";
$show_soal = isset($_GET['show_soal']) ? $_GET['show_soal'] : 'hide';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$table = ($jurusan == "teknik") ? "soal_tes_teknik" : "soal_tes";

$total_query = mysqli_query($koneksi,"SELECT COUNT(*) as total FROM $table WHERE pertanyaan LIKE '%$search%'");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_page = ceil($total_data / $limit);

$data = mysqli_query($koneksi,"SELECT * FROM $table WHERE pertanyaan LIKE '%$search%' ORDER BY id_soal DESC LIMIT $start,$limit");

$cek_kolom_kategori = mysqli_query($koneksi, "SHOW COLUMNS FROM soal_tes LIKE 'kategori'");
$kolom_kategori_ada = (mysqli_num_rows($cek_kolom_kategori) > 0);

$cek_kolom_kategori_teknik = mysqli_query($koneksi, "SHOW COLUMNS FROM soal_tes_teknik LIKE 'kategori'");
$kolom_kategori_teknik_ada = (mysqli_num_rows($cek_kolom_kategori_teknik) > 0);

$stat_umum = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM soal_tes");
$sumum = mysqli_fetch_assoc($stat_umum);

$stat_teknik = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM soal_tes_teknik");
$steknik = mysqli_fetch_assoc($stat_teknik);

// PROSES HAPUS SINGLE SOAL (DIUBAH JADI REDIRECT KE HALAMAN DENGAN PARAMETER, TIDAK LANGSUNG ALERT)
// Hapus langsung di sini tanpa echo script, biar sweetalert di halaman tujuan yang handle

// PROSES HAPUS SEMUA SOAL (SAMA, REDIRECT SAJA)

// PERHATIAN: Proses hapus sebenarnya tetap jalan via GET, 
// tapi kita akan menampilkan SweetAlert di halaman ini setelah redirect?
// Lebih baik: proses hapus tetap di sini, lalu redirect ke halaman dengan parameter success.
// Tapi karena ini halaman yang sama, kita bisa set session flash message.

// Saya akan ubah: setelah hapus, redirect ke halaman yang sama dengan parameter status
if(isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $jurusan_hapus = isset($_GET['jurusan_hapus']) ? $_GET['jurusan_hapus'] : 'umum';
    $table_hapus = ($jurusan_hapus == 'teknik') ? 'soal_tes_teknik' : 'soal_tes';
    
    $q_gambar = mysqli_query($koneksi, "SELECT gambar FROM $table_hapus WHERE id_soal = '$id_hapus'");
    $d_gambar = mysqli_fetch_assoc($q_gambar);
    if($d_gambar && !empty($d_gambar['gambar'])) {
        $path = "uploads/" . $d_gambar['gambar'];
        if(file_exists($path)) unlink($path);
    }
    
    mysqli_query($koneksi, "DELETE FROM $table_hapus WHERE id_soal = '$id_hapus'");
    // Redirect dengan parameter success
    header("Location: soal_tes.php?jurusan=$jurusan_hapus&show_soal=show&delete_success=1");
    exit();
}

// PROSES HAPUS SEMUA SOAL
if(isset($_GET['hapus_semua'])) {
    $jurusan_hapus = isset($_GET['jurusan_semua']) ? $_GET['jurusan_semua'] : 'umum';
    $table_hapus = ($jurusan_hapus == 'teknik') ? 'soal_tes_teknik' : 'soal_tes';
    
    $q_gambar = mysqli_query($koneksi, "SELECT gambar FROM $table_hapus WHERE gambar IS NOT NULL AND gambar != ''");
    while($d_gambar = mysqli_fetch_assoc($q_gambar)) {
        $path = "uploads/" . $d_gambar['gambar'];
        if(file_exists($path)) unlink($path);
    }
    
    mysqli_query($koneksi, "DELETE FROM $table_hapus");
    mysqli_query($koneksi, "ALTER TABLE $table_hapus AUTO_INCREMENT = 1");
    
    header("Location: soal_tes.php?jurusan=$jurusan_hapus&show_soal=show&delete_all_success=1");
    exit();
}

// Cek parameter success untuk menampilkan SweetAlert
$show_delete_success = isset($_GET['delete_success']);
$show_delete_all_success = isset($_GET['delete_all_success']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bank Soal - Admin Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root{
    --bg-main: linear-gradient(180deg,#0d3b66 0%, #0d6efd 45%, #ffffff 100%);
    --nav-bg: linear-gradient(135deg, #0d3b66, #0d6efd);
    --card-bg: rgba(255, 255, 255, 0.95);
    --text-main:#0f172a;
    --text-muted:#475569;
    --accent:#0d6efd;
    --shadow-main:0 15px 35px -12px rgba(13,59,102,0.3);
}
body.dark{
    --bg-main: linear-gradient(180deg,#020617 0%, #020b1f 55%, #020617 100%);
    --nav-bg: linear-gradient(135deg, #020617, #0b1f3a);
    --card-bg: rgba(2,6,23,0.9);
    --text-main:#e5e7eb;
    --text-muted:#9ca3af;
    --accent:#60a5fa;
    --shadow-main:0 15px 35px rgba(0,0,0,.5);
}
body{
    margin:0;
    min-height:100vh;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
    color: var(--text-main);
    background: var(--bg-main);
    transition:.35s ease;
}

/* ===== TRANSISI FADE ===== */
.page-transition {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    z-index: 99999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease-in-out, visibility 0.4s ease-in-out;
    pointer-events: none;
}
.page-transition.active {
    opacity: 1;
    visibility: visible;
}
body.dark .page-transition {
    background: linear-gradient(135deg, #020617, #0b1f3a);
}

/* ===== TOPBAR ===== */
.topbar {
    position: fixed;
    top: 18px;
    left: 240px;
    right: 24px;
    height: 74px;
    background: var(--nav-bg);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    box-shadow: 0 22px 55px rgba(13,59,102,.45);
}
.nav-menu {
    display: flex;
    gap: 42px;
    align-items: center;
    justify-content: center;
}
.nav-link {
    color: white;
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: 16px;
    transition: .25s ease;
    opacity: .95;
    cursor: pointer;
}
.nav-link:hover {
    opacity: 1;
    background: rgba(255,255,255,.18);
    transform: translateY(-2px) scale(1.06);
}
.nav-link.active {
    background: white;
    color: #0d3b66;
    opacity: 1;
}
.nav-link.active i {
    color: #0d3b66;
}
.theme-toggle {
    position: absolute;
    right: 22px;
    cursor: pointer;
    font-size: 20px;
    color: white;
    opacity: .9;
    transition: .25s;
}
.theme-toggle:hover {
    transform: rotate(15deg) scale(1.25);
    opacity: 1;
}

/* ===== CONTENT ===== */
.content {
    margin-left: 250px;
    padding: 105px 25px 30px;
}
.box {
    background: var(--card-bg);
    border-radius: 24px;
    padding: 20px;
    box-shadow: var(--shadow-main);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(13,110,253,0.1);
}
.card-bank {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 20px;
    border: 1px solid rgba(13,110,253,0.1);
}
.card-bank-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}
.card-bank-header h2 {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.stats {
    display: flex;
    gap: 15px;
    margin: 15px 0;
}
.stat {
    flex: 1;
    background: rgba(13,110,253,0.05);
    padding: 12px;
    border-radius: 16px;
    text-align: center;
}
.stat .label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
}
.stat .value {
    font-size: 24px;
    font-weight: 800;
    color: var(--accent);
}
.btn-group {
    display: flex;
    gap: 12px;
    margin-top: 15px;
    flex-wrap: wrap;
}
.btn-bank {
    flex: 1;
    padding: 10px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    text-align: center;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-primary { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; }
.btn-primary-teknik { background: linear-gradient(135deg, #198754, #157347); color: white; }
.btn-outline { border: 2px solid #0d6efd; color: #0d6efd; background: transparent; }
.btn-outline-teknik { border: 2px solid #198754; color: #198754; background: transparent; }
.btn-bank:hover { transform: translateY(-2px); }
.btn-danger-custom { background: linear-gradient(135deg, #dc3545, #bb2d3b); color: white; }

.card-soal {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 20px;
    border: 1px solid rgba(13,110,253,0.08);
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 12px;
}
.card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(13,110,253,0.08);
    padding: 6px 18px;
    border-radius: 35px;
}
.card-title h3 {
    font-size: 14px;
    font-weight: 700;
    margin: 0;
}
.search-box {
    display: flex;
    background: var(--card-bg);
    border: 1px solid rgba(13,110,253,0.2);
    border-radius: 40px;
    overflow: hidden;
}
.search-box input {
    border: none;
    padding: 8px 15px;
    width: 220px;
    outline: none;
    background: transparent;
    font-size: 12px;
    color: var(--text-main);
}
.search-box button {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    border: none;
    padding: 8px 20px;
    color: white;
    cursor: pointer;
}
.filter {
    display: flex;
    gap: 8px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.filter a {
    padding: 5px 14px;
    border-radius: 25px;
    font-size: 11px;
    background: rgba(13,110,253,0.08);
    color: var(--text-muted);
    text-decoration: none;
}
.filter a.active-umum { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; }
.filter a.active-teknik { background: linear-gradient(135deg, #198754, #157347); color: white; }

.table-wrap { overflow-x: auto; border-radius: 16px; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th {
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    color: white;
    font-weight: 700;
    padding: 12px 10px;
    text-align: center;
}
td {
    padding: 12px 10px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    vertical-align: middle;
}
body.dark td { border-bottom-color: rgba(255,255,255,0.06); }
tbody tr:hover { background: rgba(13,110,253,0.04); }
.question-text { font-weight: 700; margin-bottom: 8px; font-size: 12px; }
.options { display: flex; flex-wrap: wrap; gap: 6px; }
.option {
    background: rgba(13,110,253,0.06);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 10px;
    color: var(--text-muted);
}
.jawaban {
    background: linear-gradient(135deg, #e7f1ff, #d0e4ff);
    color: #0d6efd;
    padding: 5px 14px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 11px;
    display: inline-block;
}
body.dark .jawaban { background: #1e3a5f; color: #60a5fa; }
.action-btns { display: flex; gap: 8px; justify-content: center; }
.action-btn {
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}
.btn-edit { background: linear-gradient(135deg, #0d6efd, #0b5ed7); }
.btn-delete { background: linear-gradient(135deg, #dc3545, #bb2d3b); }
.action-btn:hover { transform: translateY(-2px); }
.soal-img { max-width: 120px; margin-top: 8px; border-radius: 10px; cursor: pointer; }

.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 20px;
}
.page {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: var(--card-bg);
    color: var(--text-muted);
    text-decoration: none;
    border: 1px solid rgba(13,110,253,0.15);
    font-size: 12px;
    font-weight: 600;
}
.page.active-umum { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; }
.page.active-teknik { background: linear-gradient(135deg, #198754, #157347); color: white; }

.bottom-close {
    display: flex;
    justify-content: flex-end;
    margin-top: 18px;
    padding-top: 15px;
    border-top: 1px solid rgba(0,0,0,0.06);
}
.btn-tutup {
    background: rgba(13,110,253,0.08);
    padding: 8px 20px;
    border-radius: 35px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
}

.empty-state-mini {
    text-align: center;
    padding: 40px 20px;
    background: rgba(13,110,253,0.04);
    border-radius: 20px;
}
.empty-state-mini .empty-icon {
    width: 70px;
    height: 70px;
    background: rgba(13,110,253,0.08);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}
.empty-state-mini .empty-icon i { font-size: 35px; color: var(--accent); }
.empty-state-mini h4 { font-size: 16px; font-weight: 700; margin-bottom: 5px; }
.empty-state-mini p { font-size: 12px; margin-bottom: 20px; }
.empty-state-mini .btn-add-mini {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 24px;
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    color: white;
    border-radius: 40px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

@media (max-width: 1000px) {
    .topbar { left: 20px; right: 20px; }
    .content { margin-left: 0; padding: 95px 15px 25px; }
}
@media (max-width: 768px) {
    .nav-menu { gap: 15px; }
    .nav-link { padding: 8px 16px; font-size: 13px; }
    .stats, .btn-group { flex-direction: column; }
    .card-header { flex-direction: column; }
    .search-box { width: 100%; }
    .search-box input { width: 100%; }
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<!-- TRANSISI FADE -->
<div class="page-transition" id="pageTransition"></div>

<!-- ===== TOPBAR ===== -->
<div class="topbar">
    <div class="nav-menu">
        <a href="javascript:void(0)" onclick="navigateTo('umum')" class="nav-link <?=($jurusan=='umum')?'active':''?>">
            <i class="bi bi-people-fill"></i> Soal Umum
        </a>
        <a href="javascript:void(0)" onclick="navigateTo('teknik')" class="nav-link <?=($jurusan=='teknik')?'active':''?>">
            <i class="bi bi-gear-fill"></i> Soal Teknik
        </a>
    </div>
    <div class="theme-toggle" onclick="toggleMode()" title="Mode Gelap / Terang">
        <i class="bi bi-moon-stars-fill" id="modeIcon"></i>
    </div>
</div>

<!-- ===== CONTENT ===== -->
<div class="content">
    <div class="box">
        <?php if($jurusan == 'umum'): ?>
            <!-- BANK SOAL UMUM -->
            <div class="card-bank">
                <div class="card-bank-header">
                    <h2><i class="bi bi-question-circle-fill"></i> 📚 BANK SOAL UMUM</h2>
                </div>
                <div class="stats">
                    <div class="stat">
                        <div class="label">Total Soal</div>
                        <div class="value"><?= $sumum['total'] ?></div>
                    </div>
                </div>
                <div class="btn-group">
                    <a href="?jurusan=umum&show_soal=show" class="btn-bank btn-primary"><i class="bi bi-eye-fill"></i> Lihat Semua</a>
                    <a href="tambah_soal_umum.php" class="btn-bank btn-outline"><i class="bi bi-plus-circle"></i> Tambah Soal</a>
                    <?php if($sumum['total'] > 0): ?>
                    <button type="button" class="btn-bank btn-danger-custom" onclick="hapusSemua('umum')">
                        <i class="bi bi-trash-fill"></i> Hapus Semua
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($show_soal == 'show'): ?>
            <div class="card-soal">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-list-ul"></i>
                        <h3>Daftar Soal Umum</h3>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Cari soal..." value="<?= htmlspecialchars($search) ?>" id="cariInput">
                        <button onclick="cariSoal()"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <?php if($kolom_kategori_ada): ?>
                <div class="filter">
                    <a href="?jurusan=umum&show_soal=show&kategori=semua" class="<?= (!isset($_GET['kategori']) || $_GET['kategori']=='semua') ? 'active-umum' : '' ?>">Semua</a>
                    <a href="?jurusan=umum&show_soal=show&kategori=umum" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='umum') ? 'active-umum' : '' ?>">Umum</a>
                    <a href="?jurusan=umum&show_soal=show&kategori=bahasa" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='bahasa') ? 'active-umum' : '' ?>">Bahasa</a>
                    <a href="?jurusan=umum&show_soal=show&kategori=logika" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='logika') ? 'active-umum' : '' ?>">Logika</a>
                    <a href="?jurusan=umum&show_soal=show&kategori=ips" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='ips') ? 'active-umum' : '' ?>">IPS</a>
                </div>
                <?php endif; ?>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th width="50">No</th><th>Pertanyaan & Opsi</th><th width="100">Jawaban</th><th width="150">Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $kategori_filter = isset($_GET['kategori']) && $_GET['kategori'] != 'semua' ? $_GET['kategori'] : '';
                            if($kategori_filter && $kolom_kategori_ada) {
                                $data_soal = mysqli_query($koneksi,"SELECT * FROM soal_tes WHERE pertanyaan LIKE '%$search%' AND kategori='$kategori_filter' ORDER BY id_soal DESC LIMIT $start,$limit");
                                $total_soal = mysqli_query($koneksi,"SELECT COUNT(*) as total FROM soal_tes WHERE pertanyaan LIKE '%$search%' AND kategori='$kategori_filter'");
                            } else {
                                $data_soal = mysqli_query($koneksi,"SELECT * FROM soal_tes WHERE pertanyaan LIKE '%$search%' ORDER BY id_soal DESC LIMIT $start,$limit");
                                $total_soal = mysqli_query($koneksi,"SELECT COUNT(*) as total FROM soal_tes WHERE pertanyaan LIKE '%$search%'");
                            }
                            
                            if(mysqli_num_rows($data_soal)>0): 
                                $no = $start + 1;
                                while($row = mysqli_fetch_assoc($data_soal)): 
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <div class="question-text"><?= htmlspecialchars($row['pertanyaan']) ?></div>
                                    <div class="options">
                                        <span class="option"><strong>A.</strong> <?= htmlspecialchars($row['opsi_a']) ?></span>
                                        <span class="option"><strong>B.</strong> <?= htmlspecialchars($row['opsi_b']) ?></span>
                                        <span class="option"><strong>C.</strong> <?= htmlspecialchars($row['opsi_c']) ?></span>
                                        <span class="option"><strong>D.</strong> <?= htmlspecialchars($row['opsi_d']) ?></span>
                                        <?php if(!empty($row['opsi_e'])): ?><span class="option"><strong>E.</strong> <?= htmlspecialchars($row['opsi_e']) ?></span><?php endif; ?>
                                    </div>
                                    <?php if (!empty($row['gambar'])): ?>
                                        <br><img src="uploads/<?= $row['gambar'] ?>" class="soal-img" onclick="window.open('uploads/<?= $row['gambar'] ?>')">
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="jawaban"><?= strtoupper($row['jawaban_benar']) ?></span></td>
                                <td class="text-center">
                                    <div class="action-btns">
                                        <a href="edit_soal.php?id=<?= $row['id_soal'] ?>&jurusan=umum" class="action-btn btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                                        <button class="action-btn btn-delete" onclick="hapusSoal(<?= $row['id_soal'] ?>, 'umum')"><i class="bi bi-trash"></i> Hapus</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4"><div class="empty-state-mini"><div class="empty-icon"><i class="bi bi-inbox"></i></div><h4>Belum Ada Soal</h4><p>Silakan tambah soal untuk jurusan umum</p><a href="tambah_soal_umum.php" class="btn-add-mini"><i class="bi bi-plus-circle"></i> Tambah Soal</a></div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                $total_data = mysqli_fetch_assoc($total_soal)['total'];
                $total_page = ceil($total_data / $limit);
                if($total_page > 1): 
                ?>
                <div class="pagination">
                    <?php if($page > 1): ?><a href="?jurusan=umum&show_soal=show&kategori=<?= $kategori_filter ?>&search=<?= $search ?>&page=<?= $page-1 ?>" class="page"><i class="bi bi-chevron-left"></i></a><?php endif; ?>
                    <?php for($i=1; $i<=$total_page; $i++): ?><a href="?jurusan=umum&show_soal=show&kategori=<?= $kategori_filter ?>&search=<?= $search ?>&page=<?= $i ?>" class="page <?= ($i==$page) ? 'active-umum' : '' ?>"><?= $i ?></a><?php endfor; ?>
                    <?php if($page < $total_page): ?><a href="?jurusan=umum&show_soal=show&kategori=<?= $kategori_filter ?>&search=<?= $search ?>&page=<?= $page+1 ?>" class="page"><i class="bi bi-chevron-right"></i></a><?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="bottom-close">
                    <a href="?jurusan=umum&show_soal=hide" class="btn-tutup"><i class="bi bi-x-circle"></i> Tutup Daftar Soal</a>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- BANK SOAL TEKNIK -->
            <div class="card-bank">
                <div class="card-bank-header">
                    <h2><i class="bi bi-gear-fill"></i> ⚙️ BANK SOAL TEKNIK</h2>
                </div>
                <div class="stats">
                    <div class="stat">
                        <div class="label">Total Soal</div>
                        <div class="value"><?= $steknik['total'] ?></div>
                    </div>
                </div>
                <div class="btn-group">
                    <a href="?jurusan=teknik&show_soal=show" class="btn-bank btn-primary-teknik"><i class="bi bi-eye-fill"></i> Lihat Semua</a>
                    <a href="tambah_soal_teknik.php" class="btn-bank btn-outline-teknik"><i class="bi bi-plus-circle"></i> Tambah Soal</a>
                    <?php if($steknik['total'] > 0): ?>
                    <button type="button" class="btn-bank btn-danger-custom" onclick="hapusSemua('teknik')">
                        <i class="bi bi-trash-fill"></i> Hapus Semua
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($show_soal == 'show'): ?>
            <div class="card-soal">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-list-ul"></i>
                        <h3>Daftar Soal Teknik</h3>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Cari soal..." value="<?= htmlspecialchars($search) ?>" id="cariInputTeknik">
                        <button onclick="cariSoalTeknik()"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <?php if($kolom_kategori_teknik_ada): ?>
                <div class="filter">
                    <a href="?jurusan=teknik&show_soal=show&kategori=semua" class="<?= (!isset($_GET['kategori']) || $_GET['kategori']=='semua') ? 'active-teknik' : '' ?>">Semua</a>
                    <a href="?jurusan=teknik&show_soal=show&kategori=dasar" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='dasar') ? 'active-teknik' : '' ?>">Dasar</a>
                    <a href="?jurusan=teknik&show_soal=show&kategori=lanjut" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='lanjut') ? 'active-teknik' : '' ?>">Lanjut</a>
                    <a href="?jurusan=teknik&show_soal=show&kategori=studi_kasus" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='studi_kasus') ? 'active-teknik' : '' ?>">Studi Kasus</a>
                    <a href="?jurusan=teknik&show_soal=show&kategori=praktikum" class="<?= (isset($_GET['kategori']) && $_GET['kategori']=='praktikum') ? 'active-teknik' : '' ?>">Praktikum</a>
                </div>
                <?php endif; ?>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th width="50">No</th><th>Pertanyaan & Opsi</th><th width="100">Jawaban</th><th width="150">Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $kategori_filter = isset($_GET['kategori']) && $_GET['kategori'] != 'semua' ? $_GET['kategori'] : '';
                            if($kategori_filter && $kolom_kategori_teknik_ada) {
                                $data_soal = mysqli_query($koneksi,"SELECT * FROM soal_tes_teknik WHERE pertanyaan LIKE '%$search%' AND kategori='$kategori_filter' ORDER BY id_soal DESC LIMIT $start,$limit");
                                $total_soal = mysqli_query($koneksi,"SELECT COUNT(*) as total FROM soal_tes_teknik WHERE pertanyaan LIKE '%$search%' AND kategori='$kategori_filter'");
                            } else {
                                $data_soal = mysqli_query($koneksi,"SELECT * FROM soal_tes_teknik WHERE pertanyaan LIKE '%$search%' ORDER BY id_soal DESC LIMIT $start,$limit");
                                $total_soal = mysqli_query($koneksi,"SELECT COUNT(*) as total FROM soal_tes_teknik WHERE pertanyaan LIKE '%$search%'");
                            }
                            
                            if(mysqli_num_rows($data_soal)>0): 
                                $no = $start + 1;
                                while($row = mysqli_fetch_assoc($data_soal)): 
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <div class="question-text"><?= htmlspecialchars($row['pertanyaan']) ?></div>
                                    <div class="options">
                                        <span class="option"><strong>A.</strong> <?= htmlspecialchars($row['opsi_a']) ?></span>
                                        <span class="option"><strong>B.</strong> <?= htmlspecialchars($row['opsi_b']) ?></span>
                                        <span class="option"><strong>C.</strong> <?= htmlspecialchars($row['opsi_c']) ?></span>
                                        <span class="option"><strong>D.</strong> <?= htmlspecialchars($row['opsi_d']) ?></span>
                                        <?php if(!empty($row['opsi_e'])): ?><span class="option"><strong>E.</strong> <?= htmlspecialchars($row['opsi_e']) ?></span><?php endif; ?>
                                    </div>
                                    <?php if (!empty($row['gambar'])): ?>
                                        <br><img src="uploads/<?= $row['gambar'] ?>" class="soal-img" onclick="window.open('uploads/<?= $row['gambar'] ?>')">
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="jawaban"><?= strtoupper($row['jawaban_benar']) ?></span></td>
                                <td class="text-center">
                                    <div class="action-btns">
                                        <a href="edit_soal.php?id=<?= $row['id_soal'] ?>&jurusan=teknik" class="action-btn btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                                        <button class="action-btn btn-delete" onclick="hapusSoal(<?= $row['id_soal'] ?>, 'teknik')"><i class="bi bi-trash"></i> Hapus</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4"><div class="empty-state-mini"><div class="empty-icon"><i class="bi bi-inbox"></i></div><h4>Belum Ada Soal</h4><p>Silakan tambah soal untuk jurusan teknik</p><a href="tambah_soal_teknik.php" class="btn-add-mini"><i class="bi bi-plus-circle"></i> Tambah Soal</a></div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                $total_data = mysqli_fetch_assoc($total_soal)['total'];
                $total_page = ceil($total_data / $limit);
                if($total_page > 1): 
                ?>
                <div class="pagination">
                    <?php if($page > 1): ?><a href="?jurusan=teknik&show_soal=show&kategori=<?= $kategori_filter ?>&search=<?= $search ?>&page=<?= $page-1 ?>" class="page"><i class="bi bi-chevron-left"></i></a><?php endif; ?>
                    <?php for($i=1; $i<=$total_page; $i++): ?><a href="?jurusan=teknik&show_soal=show&kategori=<?= $kategori_filter ?>&search=<?= $search ?>&page=<?= $i ?>" class="page <?= ($i==$page) ? 'active-teknik' : '' ?>"><?= $i ?></a><?php endfor; ?>
                    <?php if($page < $total_page): ?><a href="?jurusan=teknik&show_soal=show&kategori=<?= $kategori_filter ?>&search=<?= $search ?>&page=<?= $page+1 ?>" class="page"><i class="bi bi-chevron-right"></i></a><?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="bottom-close">
                    <a href="?jurusan=teknik&show_soal=hide" class="btn-tutup"><i class="bi bi-x-circle"></i> Tutup Daftar Soal</a>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// ===== SWEETALERT2 DENGAN DESAIN GRADASI BIRU =====
const swalCustomClass = {
    popup: 'swal-popup-custom',
    title: 'swal-title-custom',
    htmlContainer: 'swal-html-custom',
    confirmButton: 'swal-confirm-custom',
    cancelButton: 'swal-cancel-custom',
    icon: 'swal-icon-custom'
};

// Tambahkan CSS untuk SweetAlert kustom
const swalStyle = document.createElement('style');
swalStyle.textContent = `
    .swal-popup-custom {
        border-radius: 32px !important;
        background: linear-gradient(135deg, #0d3b66 0%, #0d6efd 100%) !important;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4) !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    body.dark .swal-popup-custom {
        background: linear-gradient(135deg, #020617 0%, #0b1f3a 100%) !important;
    }
    .swal-title-custom {
        color: white !important;
        font-size: 24px !important;
        font-weight: 800 !important;
        padding: 25px 25px 10px 25px !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
    }
    .swal-html-custom {
        color: rgba(255,255,255,0.95) !important;
        font-size: 15px !important;
        padding: 10px 25px 20px 25px !important;
    }
    .swal-icon-custom {
        margin: 25px auto 10px !important;
        border: none !important;
    }
    .swal-icon-custom .swal2-icon-content {
        color: white !important;
        font-size: 60px !important;
    }
    .swal-confirm-custom {
        background: white !important;
        color: #0d6efd !important;
        border-radius: 50px !important;
        padding: 12px 30px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        margin: 0 10px 20px 10px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        transition: all 0.3s ease !important;
    }
    .swal-confirm-custom:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.25) !important;
    }
    .swal-cancel-custom {
        background: rgba(255,255,255,0.2) !important;
        color: white !important;
        border-radius: 50px !important;
        padding: 12px 30px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        margin: 0 10px 20px 10px !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255,255,255,0.3) !important;
        transition: all 0.3s ease !important;
    }
    .swal-cancel-custom:hover {
        background: rgba(255,255,255,0.3) !important;
        transform: translateY(-2px) !important;
    }
    .swal2-actions {
        gap: 15px !important;
        margin-bottom: 10px !important;
    }
    .swal2-loader {
        border-color: white !important;
        border-top-color: transparent !important;
    }
`;
document.head.appendChild(swalStyle);

// ===== TAMPILKAN SWEETALERT JIKA ADA PARAMETER SUCCESS =====
<?php if($show_delete_success): ?>
Swal.fire({
    title: '<i class="bi bi-check-circle-fill" style="margin-right: 10px;"></i> Berhasil!',
    html: '<span style="font-size: 16px;">Soal berhasil dihapus!</span>',
    icon: 'success',
    iconHtml: '<i class="bi bi-check-circle-fill" style="font-size: 55px;"></i>',
    confirmButtonText: '<i class="bi bi-check-lg me-2"></i> OK',
    customClass: swalCustomClass,
    buttonsStyling: false,
    backdrop: `rgba(0,0,0,0.6) backdrop-filter: blur(8px)`
});
<?php endif; ?>

<?php if($show_delete_all_success): ?>
Swal.fire({
    title: '<i class="bi bi-check-circle-fill" style="margin-right: 10px;"></i> Berhasil!',
    html: '<span style="font-size: 16px;">Semua soal berhasil dihapus!</span>',
    icon: 'success',
    iconHtml: '<i class="bi bi-check-circle-fill" style="font-size: 55px;"></i>',
    confirmButtonText: '<i class="bi bi-check-lg me-2"></i> OK',
    customClass: swalCustomClass,
    buttonsStyling: false,
    backdrop: `rgba(0,0,0,0.6) backdrop-filter: blur(8px)`
});
<?php endif; ?>

// ===== FUNGSI NAVIGASI DENGAN FADE =====
function navigateTo(jurusan) {
    const transition = document.getElementById('pageTransition');
    transition.classList.add('active');
    
    setTimeout(() => {
        window.location.href = '?jurusan=' + jurusan + '&show_soal=show';
    }, 400);
}

function toggleMode() {
    document.body.classList.toggle('dark');
    const icon = document.getElementById('modeIcon');
    if(document.body.classList.contains('dark')) {
        icon.className = 'bi bi-sun-fill';
    } else {
        icon.className = 'bi bi-moon-stars-fill';
    }
    localStorage.setItem('darkMode', document.body.classList.contains('dark'));
}

if(localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark');
    document.getElementById('modeIcon').className = 'bi bi-sun-fill';
}

function cariSoal() {
    let val = document.getElementById('cariInput').value;
    window.location.href = '?jurusan=umum&show_soal=show&search=' + encodeURIComponent(val);
}

function cariSoalTeknik() {
    let val = document.getElementById('cariInputTeknik').value;
    window.location.href = '?jurusan=teknik&show_soal=show&search=' + encodeURIComponent(val);
}

// ===== FUNGSI HAPUS SOAL DENGAN SWEETALERT GRADASI =====
function hapusSoal(id, jurusan) {
    Swal.fire({
        title: '<i class="bi bi-trash-fill" style="margin-right: 10px;"></i> Hapus Soal?',
        html: `<span style="font-size: 14px;">Soal yang dihapus <strong>tidak dapat dikembalikan</strong>!</span><br><span style="font-size: 12px; opacity: 0.8;">ID Soal: ${id} | Jurusan: ${jurusan.toUpperCase()}</span>`,
        icon: 'warning',
        iconHtml: '<i class="bi bi-exclamation-triangle-fill" style="font-size: 55px;"></i>',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash-fill me-2"></i> Ya, Hapus!',
        cancelButtonText: '<i class="bi bi-x-lg me-2"></i> Batal',
        customClass: swalCustomClass,
        buttonsStyling: false,
        backdrop: `
            rgba(0,0,0,0.6)
            backdrop-filter: blur(8px)
        `
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '<i class="bi bi-hourglass-split"></i> Menghapus...',
                html: 'Sedang menghapus soal, harap tunggu.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: swalCustomClass,
                showConfirmButton: false
            });
            window.location.href = '?hapus=' + id + '&jurusan_hapus=' + jurusan + '&show_soal=show';
        }
    });
}

// ===== FUNGSI HAPUS SEMUA SOAL DENGAN SWEETALERT GRADASI =====
function hapusSemua(jurusan) {
    Swal.fire({
        title: '<i class="bi bi-trash3-fill" style="margin-right: 10px;"></i> Hapus Semua Soal?',
        html: `
            <div style="margin: 10px 0;">
                <span style="display: inline-block; background: rgba(255,255,255,0.15); padding: 8px 20px; border-radius: 50px; font-weight: 700; margin-bottom: 15px;">
                    Jurusan: ${jurusan.toUpperCase()}
                </span>
            </div>
            <span style="color: #ffcc00; font-size: 14px;">⚠️ Peringatan!</span><br>
            <span style="font-size: 13px;">Semua soal jurusan <strong>${jurusan.toUpperCase()}</strong> akan <strong style="color: #ff6b6b;">hilang permanen</strong>!</span>
        `,
        icon: 'warning',
        iconHtml: '<i class="bi bi-exclamation-octagon-fill" style="font-size: 55px;"></i>',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash-fill me-2"></i> Ya, Hapus Semua!',
        cancelButtonText: '<i class="bi bi-x-lg me-2"></i> Batal',
        customClass: swalCustomClass,
        buttonsStyling: false,
        backdrop: `
            rgba(0,0,0,0.6)
            backdrop-filter: blur(8px)
        `
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '<i class="bi bi-shield-exclamation"></i> Konfirmasi Akhir',
                html: `<span style="font-size: 14px;">Yakin ingin menghapus <strong>SEMUA SOAL</strong> jurusan <strong>${jurusan.toUpperCase()}</strong>?</span><br><span style="font-size: 12px; opacity: 0.8; margin-top: 10px; display: block;">Tindakan ini tidak dapat dibatalkan!</span>`,
                icon: 'question',
                iconHtml: '<i class="bi bi-question-octagon-fill" style="font-size: 55px;"></i>',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-lg me-2"></i> Ya, Saya Yakin!',
                cancelButtonText: '<i class="bi bi-x-lg me-2"></i> Tidak',
                customClass: swalCustomClass,
                buttonsStyling: false,
                backdrop: `
                    rgba(0,0,0,0.6)
                    backdrop-filter: blur(8px)
                `
            }).then((result2) => {
                if (result2.isConfirmed) {
                    Swal.fire({
                        title: '<i class="bi bi-arrow-repeat spin-icon"></i> Menghapus Data...',
                        html: '<span style="font-size: 13px;">Mohon tunggu, sedang menghapus semua soal...</span>',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        customClass: swalCustomClass,
                        showConfirmButton: false
                    });
                    window.location.href = '?hapus_semua=1&jurusan_semua=' + jurusan + '&show_soal=show';
                }
            });
        }
    });
}

// Tambahkan animasi spin untuk icon loading
const spinStyle = document.createElement('style');
spinStyle.textContent = `
    @keyframes spinIcon {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin-icon {
        display: inline-block;
        animation: spinIcon 1s linear infinite;
    }
`;
document.head.appendChild(spinStyle);

document.getElementById('cariInput')?.addEventListener('keypress', function(e) {
    if(e.key === 'Enter') cariSoal();
});
document.getElementById('cariInputTeknik')?.addEventListener('keypress', function(e) {
    if(e.key === 'Enter') cariSoalTeknik();
});
</script>

</body>
</html>