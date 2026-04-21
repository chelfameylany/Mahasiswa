<?php
// dashboard_maba.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../koneksi.php";

// Cek jika belum login
if (!isset($_SESSION['maba'])) {
    header("Location: ../auth/login_maba.php");
    exit();
}

$username = $_SESSION['maba'];
$query = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username = '$username'");
$maba = mysqli_fetch_assoc($query);

$id_maba = $maba['id_maba'];

// ===== BUAT NOMOR PENDAFTARAN =====
$no_pendaftaran = 'PMB' . str_pad($id_maba, 5, '0', STR_PAD_LEFT);

// ===== AMBIL FOTO PROFIL =====
$foto_profil = !empty($maba['foto']) ? $maba['foto'] : 'default-avatar.png';

// ===== CEK KELENGKAPAN DOKUMEN =====
$dokumen_wajib = ['foto','ijazah_pdf','kartu_keluarga','akte_kelahiran','kartu_pelajar_ktp'];
$dokumen_lengkap = true;
$dokumen_kosong = [];
foreach ($dokumen_wajib as $dok) {
    if (empty($maba[$dok])) {
        $dokumen_lengkap = false;
        $dokumen_kosong[] = $dok;
    }
}

// ===== CEK APAKAH SUDAH TES =====
$cekTes = mysqli_query($koneksi, "SELECT * FROM hasil_tes WHERE id_maba='$id_maba'");
$sudahTes = mysqli_num_rows($cekTes) > 0;

// ===== CEK STATUS KELULUSAN =====
$status_lulus = 'pending';
$nilai_tes = 0;

if ($sudahTes) {
    $queryHasil = mysqli_query($koneksi, "SELECT * FROM hasil_tes WHERE id_maba='$id_maba' ORDER BY tanggal_tes DESC LIMIT 1");
    $dataHasil = mysqli_fetch_assoc($queryHasil);
    
    if ($dataHasil) {
        $status_lulus = $dataHasil['status_lulus'] ?? 'pending';
        $nilai_tes = $dataHasil['nilai'] ?? 0;
    }
}

// ===== CEK PEMBAYARAN =====
$qBayar = mysqli_query($koneksi, "
    SELECT * FROM pembayaran_gedung 
    WHERE id_maba='$id_maba' 
    ORDER BY id_pembayaran DESC 
    LIMIT 1
");
$pembayaran = mysqli_fetch_assoc($qBayar);

// ===== NORMALISASI STATUS PEMBAYARAN =====
$status_pembayaran = 'belum_upload';
if($pembayaran) {
    $status_pembayaran = strtolower(trim($pembayaran['status']));
}
if($status_pembayaran == 'menunggur') {
    $status_pembayaran = 'menunggu';
}

// ===== CEK APAKAH LULUS =====
$is_lulus = ($status_lulus == 'lulus');

// ===== CEK APAKAH PEMBAYARAN SUDAH LUNAS =====
$is_lunas = ($status_pembayaran == 'lunas');

// ===== CEK APAKAH SUDAH DAFTAR ULANG =====
$sudah_daftar_ulang = false;
$status_daftar_ulang = '';
$daftar_ulang_data = null;

$cekDaftarUlang = mysqli_query($koneksi, "
    SELECT * FROM daftar_ulang 
    WHERE id_maba='$id_maba' 
    ORDER BY id_daftar_ulang DESC 
    LIMIT 1
");

if(mysqli_num_rows($cekDaftarUlang) > 0) {
    $daftar_ulang_data = mysqli_fetch_assoc($cekDaftarUlang);
    $sudah_daftar_ulang = true;
    $status_daftar_ulang = $daftar_ulang_data['status_daftar_ulang'] ?? 'menunggu';
}

// ===== CEK APAKAH SUDAH BISA DAFTAR ULANG =====
$memenuhi_syarat = ($sudahTes && $is_lulus && $is_lunas);
$bisa_daftar_ulang = ($memenuhi_syarat && !$sudah_daftar_ulang) || ($memenuhi_syarat && $sudah_daftar_ulang && $status_daftar_ulang == 'ditolak');

// ===== STATUS UNTUK CARD DAFTAR ULANG =====
if($sudah_daftar_ulang) {
    if($status_daftar_ulang == 'diterima') {
        $status_du_text = "DAFTAR ULANG DITERIMA";
        $status_du_class = "status-diterima";
        $status_du_icon = "check-circle";
        $status_du_desc = "Selamat! Daftar ulang Anda telah diterima";
    } elseif($status_daftar_ulang == 'ditolak') {
        $status_du_text = "DAFTAR ULANG DITOLAK";
        $status_du_class = "status-ditolak";
        $status_du_icon = "x-circle";
        $status_du_desc = "Silakan daftar ulang ulang dengan data yang benar";
    } else {
        $status_du_text = "MENUNGGU VERIFIKASI";
        $status_du_class = "status-menunggu";
        $status_du_icon = "hourglass-split";
        $status_du_desc = "Dokumen daftar ulang sedang diverifikasi";
    }
} else {
    if (!$sudahTes) {
        $status_du_text = "BELUM TES";
        $status_du_class = "status-pending";
        $status_du_icon = "clock-history";
        $status_du_desc = "Selesaikan tes terlebih dahulu";
    } elseif ($status_lulus != 'lulus') {
        $status_du_text = "TIDAK LULUS";
        $status_du_class = "status-ditolak";
        $status_du_icon = "x-circle";
        $status_du_desc = "Tidak dapat daftar ulang";
    } elseif ($status_pembayaran == 'belum_upload' || !$pembayaran) {
        $status_du_text = "BELUM BAYAR";
        $status_du_class = "status-warning";
        $status_du_icon = "cash-coin";
        $status_du_desc = "Selesaikan pembayaran terlebih dahulu";
    } elseif ($status_pembayaran == 'menunggu') {
        $status_du_text = "MENUNGGU VERIFIKASI BAYAR";
        $status_du_class = "status-menunggu";
        $status_du_icon = "hourglass-split";
        $status_du_desc = "Menunggu konfirmasi pembayaran";
    } elseif ($status_pembayaran == 'ditolak') {
        $status_du_text = "PEMBAYARAN DITOLAK";
        $status_du_class = "status-ditolak";
        $status_du_icon = "x-circle";
        $status_du_desc = "Upload ulang bukti pembayaran";
    } elseif ($status_pembayaran == 'lunas') {
        $status_du_text = "SIAP DAFTAR ULANG";
        $status_du_class = "status-diterima";
        $status_du_icon = "check-circle";
        $status_du_desc = "Silakan klik tombol Daftar Ulang";
    } else {
        $status_du_text = "BELUM DAFTAR ULANG";
        $status_du_class = "status-belum-upload";
        $status_du_icon = "journal-check";
        $status_du_desc = "Silakan daftar ulang sekarang";
    }
}

// ===== CEK APAKAH BARU SELESAI LENGKAPI DATA =====
$showPopupDokumenLengkap = isset($_GET['dokumen_lengkap']) && $_GET['dokumen_lengkap'] == 1;

$last_login = $maba['last_login'] ?? 'Belum pernah login';

// ========== SWITCH WARNA ==========
$jurusan = $maba['jurusan'] ?? 'Belum memilih jurusan';
$jurusan_lower = strtolower(trim($jurusan));

$prodi_teknik_list = [
    'teknik nuklir', 'teknik informatika', 'teknik sipil', 
    'teknik elektro', 'teknik mesin', 'teknik industri',
    'teknik kimia', 'teknik lingkungan', 'teknik geologi',
    'teknik pertambangan', 'teknik komputer', 'teknik elektronika',
    'teknik telekomunikasi', 'teknik pertanian', 'teknik perkapalan'
];

if(strpos($jurusan_lower, 'teknik') !== false || in_array($jurusan_lower, $prodi_teknik_list)) {
    $prodi_color = "#2E7D32";
    $prodi_color_dark = "#1B5E20";
    $prodi_color_light = "#E8F5E9";
    $prodi_badge = "teknik";
    $prodi_badge_text = "TEKNIK";
    $prodi_badge_icon = "cpu";
    $prodi_icon_color = "text-success";
    $navbar_gradient = "linear-gradient(135deg, #1B5E20, #2E7D32)";
} else {
    $prodi_color = "#3B82F6";
    $prodi_color_dark = "#1E40AF";
    $prodi_color_light = "#DBEAFE";
    $prodi_badge = "umum";
    $prodi_badge_text = "UMUM";
    $prodi_badge_icon = "globe2";
    $prodi_icon_color = "text-primary";
    $navbar_gradient = "linear-gradient(135deg, #1E40AF, #3B82F6)";
}

// Tentukan path folder foto (SUDAH BENER - karena uploads ada di folder user)
$foto_path = 'uploads/foto/';
$default_foto = 'default-avatar.png';
$foto_display = (!empty($foto_profil) && file_exists($foto_path . $foto_profil)) ? $foto_path . $foto_profil : $default_foto;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f8f9fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .navbar { background: <?= $navbar_gradient ?> !important; padding: 1rem 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .navbar-brand { font-weight: 800; color: white !important; font-size: 1.3rem; display: flex; align-items: center; gap: 12px; }
        .navbar-brand img { height: 45px; width: auto; border-radius: 8px; }
        .navbar-brand span { font-weight: 800; }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .user-info { color: white; display: flex; align-items: center; gap: 12px; }
        
        .user-avatar { 
            width: 55px; 
            height: 55px; 
            border-radius: 50%; 
            background: rgba(255,255,255,0.2);
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .user-avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 0 15px rgba(255,255,255,0.5);
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .user-avatar:hover img {
            transform: scale(1.1);
        }
        
        .nama-clickable {
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1rem;
        }
        .nama-clickable:hover {
            text-decoration: underline;
            opacity: 0.85;
            transform: translateY(-1px);
        }
        
        .no-pendaftaran-text {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 2px;
        }
        
        .btn-logout { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 0.5rem 1.5rem; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        .btn-logout:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); color: white; }
        .main-content { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .welcome-card { background: linear-gradient(135deg, <?= $prodi_color ?>, <?= $prodi_color_dark ?>); color: white; border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .dashboard-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .card { border-radius: 16px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s ease; height: 100%; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .card-body { padding: 1.5rem; display: flex; flex-direction: column; align-items: flex-start; }
        .card-icon { font-size: 2.5rem; margin-bottom: 1rem; display: inline-block; }
        .card-title { font-size: 0.9rem; color: #6c757d; margin-bottom: 0.5rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .card-text { font-size: 1.1rem; font-weight: 600; margin-bottom: 0; }
        .card-status { display: inline-block; padding: 0.35rem 1rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; margin-top: 0.25rem; width: auto; max-width: 100%; }
        .status-pending, .status-menunggu, .status-belum-upload { background: #fef3c7; color: #92400e; }
        .status-ditolak { background: #fee2e2; color: #991b1b; }
        .status-diterima, .status-lunas { background: #d1fae5; color: #065f46; }
        .status-warning { background: #fef3c7; color: #92400e; }
        .status-expired { background: #fee2e2; color: #991b1b; }
        .action-buttons { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
        .btn-action, .btn-action-disabled, .btn-outline { flex: 0 0 auto; width: calc((100% / 6) - (1rem * 5 / 6)); min-width: 130px; padding: 0.75rem 0.5rem; border-radius: 12px; font-weight: 600; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; box-sizing: border-box; text-align: center; font-size: 0.9rem; }
        .btn-action { background: linear-gradient(135deg, <?= $prodi_color ?>, <?= $prodi_color_dark ?>); color: white; border: none; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); color: white; }
        .btn-outline { background: transparent; border: 2px solid <?= $prodi_color ?>; color: <?= $prodi_color ?>; }
        .btn-outline:hover { background: <?= $prodi_color ?>; color: white; }
        .btn-action-disabled { background: #e2e8f0; color: #94a3b8; border: 2px solid #cbd5e1; cursor: not-allowed; pointer-events: none; opacity: 0.7; }
        .badge-tombol { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600; margin-left: 5px; background: rgba(255,255,255,0.3); color: white; }
        .badge-tombol-outline { background: <?= $prodi_color ?>; color: white; padding: 0.2rem 0.5rem; border-radius: 50px; font-size: 0.7rem; margin-left: 5px; }
        .popup-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(13, 59, 102, 0.9); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .popup-content { background: white; border-radius: 25px; width: 90%; max-width: 450px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.3); animation: popIn 0.3s ease; }
        @keyframes popIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        .popup-header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 25px; text-align: center; }
        .popup-header h4 { font-weight: 800; margin: 0; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .popup-header i { font-size: 2rem; }
        .popup-body { padding: 30px; text-align: center; }
        .popup-body p { font-size: 1.1rem; color: #334155; margin-bottom: 10px; }
        .popup-body .highlight { font-weight: 700; color: #059669; font-size: 1.2rem; }
        .popup-footer { padding: 0 30px 30px; display: flex; gap: 15px; justify-content: center; }
        .popup-btn { padding: 12px 30px; border-radius: 50px; border: none; font-weight: 600; cursor: pointer; transition: all 0.3s ease; flex: 1; }
        .popup-btn-primary { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .popup-btn-secondary { background: #e2e8f0; color: #475569; }
        .info-section { background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.08); margin-bottom: 2rem; scroll-margin-top: 80px; transition: all 0.3s ease; }
        .info-title { font-weight: 700; font-size: 1.3rem; margin-bottom: 2rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0; display: flex; align-items: center; gap: 10px; color: <?= $prodi_color ?>; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem 3rem; }
        .info-col { display: flex; flex-direction: column; gap: 1.2rem; }
        .info-group { border-bottom: 1px dashed #e2e8f0; padding-bottom: 0.8rem; }
        .info-label { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; color: <?= $prodi_color ?>; }
        .info-value { color: #1e293b; font-size: 1rem; font-weight: 500; line-height: 1.4; }
        .status-badge-info { display: inline-block; padding: 0.3rem 1.2rem; border-radius: 30px; font-weight: 600; font-size: 0.85rem; margin-right: 0.5rem; }
        .status-badge-info.pending { background: #fef3c7; color: #92400e; }
        .status-badge-info.success { background: #d1fae5; color: #065f46; }
        .dokumen-badge { display: inline-block; padding: 0.3rem 1.2rem; background: #d1fae5; color: #065f46; border-radius: 30px; font-weight: 600; font-size: 0.85rem; }
        .prodi-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600; margin-left: 8px; vertical-align: middle; }
        .prodi-badge.bg-teknik { background: #2E7D32; color: white; }
        .prodi-badge.bg-umum { background: #3B82F6; color: white; }
        
        .highlight-section {
            box-shadow: 0 0 0 3px <?= $prodi_color ?>, 0 5px 15px rgba(0,0,0,0.08) !important;
            transition: all 0.3s ease;
        }
        
        .modal-photo {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .modal-photo-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(255,255,255,0.3);
            animation: zoomIn 0.3s ease;
        }
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
            z-index: 10001;
        }
        .close-modal:hover {
            color: #bbb;
        }
        
        @media (max-width: 1100px) { .dashboard-cards { grid-template-columns: repeat(3, 1fr); } .btn-action, .btn-action-disabled, .btn-outline { width: calc((100% / 3) - (1rem * 2 / 3)); } }
        @media (max-width: 992px) { .dashboard-cards { grid-template-columns: repeat(2, 1fr); } .btn-action, .btn-action-disabled, .btn-outline { width: calc((100% / 2) - (1rem * 1 / 2)); } .info-grid { grid-template-columns: 1fr; gap: 1.5rem; } }
        @media (max-width: 768px) { .btn-action, .btn-action-disabled, .btn-outline { width: 100%; } .navbar-brand span { display: none; } }
        @media (max-width: 576px) { .dashboard-cards { grid-template-columns: 1fr; } }
        
        html { scroll-behavior: smooth; }
    </style>
</head>
<body>
    <!-- MODAL FOTO -->
    <div id="modalFoto" class="modal-photo" onclick="tutupModalFoto()">
        <span class="close-modal">&times;</span>
        <img class="modal-photo-content" id="fotoModalImg">
    </div>

    <!-- POPUP DOKUMEN LENGKAP -->
    <div class="popup-overlay" id="popupDokumenLengkap">
        <div class="popup-content">
            <div class="popup-header">
                <h4><i class="bi bi-check-circle-fill"></i> DOKUMEN LENGKAP!</h4>
            </div>
            <div class="popup-body">
                <p>✨ Selamat! Semua dokumen Anda sudah lengkap.</p>
                <p class="highlight">Silakan lanjutkan ke Tes Online</p>
            </div>
            <div class="popup-footer">
                <button class="popup-btn popup-btn-primary" onclick="tutupPopup()"><i class="bi bi-check-lg me-2"></i>Mengerti</button>
                <a href="panduan_tes.php" class="popup-btn popup-btn-secondary"><i class="bi bi-pencil-square me-2"></i>Ke Tes</a>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard_maba.php">
                <img src="logokampus1.png" alt="Logo" onerror="this.src='https://via.placeholder.com/45x45/ffffff/0d3b66?text=UCN'">
                <span>Universitas Cendekia Nusantara</span>
            </a>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar" id="fotoAvatar" onclick="lihatFotoBesar('<?= $foto_display ?>')">
                        <img src="<?= $foto_display ?>" alt="Foto Profil" onerror="this.src='default-avatar.png'">
                    </div>
                    <div>
                        <div class="nama-clickable" id="namaLink" title="Klik untuk lihat data diri">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($maba['nama']) ?>
                        </div>
                        <div class="no-pendaftaran-text">
                            <i class="bi bi-qr-code"></i> No. Pendaftaran: <?= htmlspecialchars($no_pendaftaran) ?>
                        </div>
                    </div>
                </div>
                <a href="../auth/logout_maba.php" class="btn-logout"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-card">
            <h1 class="mb-3">Selamat Datang, <?= htmlspecialchars($maba['nama']) ?>! 😊</h1>
            <p class="mb-0">Anda login sebagai calon mahasiswa Universitas Cendekia Nusantara</p>
        </div>
        
        <!-- 5 STATUS CARDS -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-body">
                    <div class="card-icon text-primary"><i class="bi bi-file-earmark-text"></i></div>
                    <h5 class="card-title">STATUS PENDAFTARAN</h5>
                    <?php if (!$sudahTes): ?>
                        <span class="card-status status-pending"><i class="bi bi-clock-history me-1"></i> PENDING</span>
                        <small class="text-muted mt-2">Menunggu tes</small>
                    <?php elseif ($status_lulus == 'lulus'): ?>
                        <span class="card-status status-diterima"><i class="bi bi-check-circle me-1"></i> DITERIMA</span>
                        <small class="text-muted mt-2">Nilai: <?= $nilai_tes ?></small>
                    <?php elseif ($status_lulus == 'tidak_lulus'): ?>
                        <span class="card-status status-ditolak"><i class="bi bi-x-circle me-1"></i> DITOLAK</span>
                        <small class="text-muted mt-2">Nilai: <?= $nilai_tes ?></small>
                    <?php else: ?>
                        <span class="card-status status-pending"><i class="bi bi-clock-history me-1"></i> MENUNGGU VERIFIKASI</span>
                        <small class="text-muted mt-2">Nilai: <?= $nilai_tes ?></small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="card-icon <?= $prodi_icon_color ?>"><i class="bi bi-book"></i></div>
                    <h5 class="card-title">PROGRAM STUDI</h5>
                    <p class="card-text" style="color: <?= $prodi_color ?>; font-weight: 700;"><?= htmlspecialchars($jurusan) ?></p>
                    <span class="prodi-badge bg-<?= $prodi_badge ?>"><i class="bi bi-<?= $prodi_badge_icon ?> me-1"></i><?= $prodi_badge_text ?></span>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="card-icon text-warning"><i class="bi bi-clock-history"></i></div>
                    <h5 class="card-title">TERAKHIR LOGIN</h5>
                    <p class="card-text"><?= $last_login != 'Belum pernah login' ? date('d/m/Y', strtotime($last_login)) : '-' ?></p>
                    <small class="text-muted"><?= $last_login != 'Belum pernah login' ? date('H:i', strtotime($last_login)) : '' ?></small>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="card-icon text-info"><i class="bi bi-cash-coin"></i></div>
                    <h5 class="card-title">PEMBAYARAN</h5>
                    <?php 
                    if($status_pembayaran == 'belum_upload' || !$pembayaran): 
                        echo '<span class="card-status status-belum-upload">BELUM UPLOAD</span>';
                    elseif($status_pembayaran == 'menunggu'): 
                        echo '<span class="card-status status-menunggu">MENUNGGU VERIFIKASI</span>';
                    elseif($status_pembayaran == 'ditolak'): 
                        echo '<span class="card-status status-ditolak">DITOLAK</span>';
                    elseif($status_pembayaran == 'expired'): 
                        echo '<span class="card-status status-expired">EXPIRED</span>';
                    elseif($status_pembayaran == 'lunas'): 
                        echo '<span class="card-status status-lunas">LUNAS</span>';
                    endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="card-icon text-secondary"><i class="bi bi-journal-check"></i></div>
                    <h5 class="card-title">DAFTAR ULANG</h5>
                    <span class="card-status <?= $status_du_class ?>"><i class="bi bi-<?= $status_du_icon ?> me-1"></i> <?= $status_du_text ?></span>
                    <small class="text-muted mt-2"><?= $status_du_desc ?></small>
                </div>
            </div>
        </div>
        
        <!-- ACTION BUTTONS -->
        <div class="action-buttons">
            <?php if (!$sudahTes): ?>
                <a href="<?= $dokumen_lengkap ? 'panduan_tes.php' : 'lengkapi_data_maba.php' ?>" class="btn-action">
                    <i class="bi bi-pencil-square"></i> Tes Online
                    <?php if($dokumen_lengkap): ?><span class="badge-tombol">!</span><?php endif; ?>
                </a>
            <?php else: ?>
                <span class="btn-action-disabled"><i class="bi bi-check-circle"></i> Tes Selesai</span>
            <?php endif; ?>
            
            <a href="lengkapi_data_maba.php" class="btn-outline">
                <i class="bi bi-person-lines-fill"></i> Lengkapi Data
                <?php if($dokumen_lengkap): ?>
                    <span class="badge-tombol-outline">✓</span>
                <?php else: ?>
                    <span class="badge-tombol-outline"><?= count($dokumen_kosong) ?></span>
                <?php endif; ?>
            </a>
            
            <a href="ganti_password.php" class="btn-outline"><i class="bi bi-key"></i> Ganti Password</a>
            <a href="hasil_tes_maba.php" class="btn-outline"><i class="bi bi-graph-up"></i> Hasil Tes</a>
            
            <?php if($is_lulus && $sudahTes): ?>
                <a href="panduan_pembayarantest.php" class="btn-outline">
                    <i class="bi bi-cash-coin"></i> Pembayaran
                </a>
            <?php else: ?>
                <span class="btn-action-disabled"><i class="bi bi-cash-coin"></i> Pembayaran</span>
            <?php endif; ?>
            
            <?php if($bisa_daftar_ulang): ?>
                <a href="form_daftar_ulang.php" class="btn-outline" style="background: <?= $prodi_color ?>; color: white; border: none;">
                    <i class="bi bi-journal-check"></i> Daftar Ulang
                    <?php if($sudah_daftar_ulang && $status_daftar_ulang == 'ditolak'): ?>
                        <span class="badge-tombol" style="background: #dc2626;">Ulang</span>
                    <?php endif; ?>
                </a>
            <?php elseif($sudah_daftar_ulang && $status_daftar_ulang == 'diterima'): ?>
                <a href="ktm_maba.php" class="btn-outline" style="background: <?= $prodi_color ?>; color: white; border: none;">
                    <i class="bi bi-id-card"></i> Lihat KTM
                </a>
            <?php elseif($sudah_daftar_ulang && $status_daftar_ulang == 'menunggu'): ?>
                <span class="btn-action-disabled">
                    <i class="bi bi-hourglass-split"></i> Menunggu Verifikasi DU
                </span>
            <?php else: ?>
                <span class="btn-action-disabled">
                    <i class="bi bi-journal-check"></i> Daftar Ulang
                </span>
            <?php endif; ?>
        </div>
        
        <!-- INFORMATION SECTION -->
        <div class="info-section" id="infoSection">
            <div class="info-title"><i class="bi bi-info-circle-fill"></i> Informasi Mahasiswa</div>
            <div class="info-grid">
                <div class="info-col">
                    <div class="info-group"><div class="info-label">Nama Lengkap</div><div class="info-value"><?= htmlspecialchars($maba['nama'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">Kewarganegaraan</div><div class="info-value"><?= htmlspecialchars($maba['kewarganegaraan'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">NIK</div><div class="info-value"><?= htmlspecialchars($maba['nik'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">Jenis Kelamin</div><div class="info-value"><?= isset($maba['jenis_kelamin']) ? ($maba['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') : '-' ?></div></div>
                    <div class="info-group"><div class="info-label">Tempat Lahir</div><div class="info-value"><?= htmlspecialchars($maba['tempat_lahir'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">Tanggal Lahir</div><div class="info-value"><?= htmlspecialchars($maba['tanggal_lahir'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">Agama</div><div class="info-value"><?= htmlspecialchars($maba['agama'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">Alamat Lengkap</div><div class="info-value"><?= htmlspecialchars($maba['alamat'] ?? '-') ?></div></div>
                </div>
                <div class="info-col">
                    <div class="info-group"><div class="info-label">Asal Sekolah</div><div class="info-value"><?= htmlspecialchars($maba['asal_sekolah'] ?? '-') ?></div></div>
                    <div class="info-group">
                        <div class="info-label">Program Studi</div>
                        <div class="info-value">
                            <span style="color: <?= $prodi_color ?>; font-weight: 600;"><?= htmlspecialchars($jurusan) ?></span>
                            <span class="prodi-badge bg-<?= $prodi_badge ?> ms-2"><i class="bi bi-<?= $prodi_badge_icon ?> me-1"></i><?= $prodi_badge_text ?></span>
                        </div>
                    </div>
                    <div class="info-group"><div class="info-label">Email</div><div class="info-value"><?= htmlspecialchars($maba['email'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">Username</div><div class="info-value"><?= htmlspecialchars($maba['username'] ?? '-') ?></div></div>
                    <div class="info-group"><div class="info-label">No. Pendaftaran</div><div class="info-value"><strong><?= htmlspecialchars($no_pendaftaran) ?></strong></div></div>
                    <div class="info-group">
                        <div class="info-label">Nilai Tes</div>
                        <div class="info-value">
                            <?php if($sudahTes): ?>
                                <strong><?= $nilai_tes ?></strong> 
                                <small>(<?= $status_lulus == 'lulus' ? 'Lulus' : ($status_lulus == 'tidak_lulus' ? 'Tidak Lulus' : 'Verifikasi') ?>)</small>
                            <?php else: ?>
                                <span class="status-badge-info pending">Belum Tes</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status Dokumen</div>
                        <div class="info-value">
                            <?php if($dokumen_lengkap): ?>
                                <span class="dokumen-badge">LENGKAP</span>
                            <?php else: ?>
                                <span class="status-badge-info pending">BELUM LENGKAP (<?= count($dokumen_kosong) ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status Daftar Ulang</div>
                        <div class="info-value">
                            <?php if($sudah_daftar_ulang): ?>
                                <?php if($status_daftar_ulang == 'diterima'): ?>
                                    <span class="dokumen-badge" style="background: #d1fae5; color: #065f46;"><i class="bi bi-check-circle"></i> DITERIMA</span>
                                <?php elseif($status_daftar_ulang == 'ditolak'): ?>
                                    <span class="status-badge-info pending" style="background: #fee2e2; color: #991b1b;"><i class="bi bi-x-circle"></i> DITOLAK</span>
                                <?php else: ?>
                                    <span class="status-badge-info pending"><i class="bi bi-hourglass-split"></i> MENUNGGU</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="status-badge-info pending">BELUM DAFTAR ULANG</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-group"><div class="info-label">Tanggal Daftar</div><div class="info-value"><?= isset($maba['tanggal_daftar']) ? date('d F Y', strtotime($maba['tanggal_daftar'])) : '-' ?></div></div>
                </div>
            </div>
        </div>
        
        <!-- Status Alert -->
        <?php if ($status_lulus == 'tidak_lulus'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i> <strong>Maaf, Anda TIDAK LULUS</strong> - Silakan coba lagi di periode pendaftaran berikutnya.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($status_lulus == 'lulus' && $status_pembayaran == 'belum_upload'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> <strong>Selamat! Anda LULUS</strong> - Silakan lanjutkan ke pembayaran.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($status_lulus == 'lulus' && $status_pembayaran == 'lunas' && !$sudah_daftar_ulang): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-journal-check me-2"></i> <strong>Pembayaran LUNAS!</strong> - Silakan lanjutkan ke daftar ulang.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($sudah_daftar_ulang && $status_daftar_ulang == 'diterima'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-id-card me-2"></i> <strong>Daftar Ulang DITERIMA!</strong> - Silakan lihat Kartu Tanda Mahasiswa (KTM) Anda.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function lihatFotoBesar(src) {
            const modal = document.getElementById('modalFoto');
            const modalImg = document.getElementById('fotoModalImg');
            modal.style.display = 'flex';
            modalImg.src = src;
        }
        
        function tutupModalFoto() {
            document.getElementById('modalFoto').style.display = 'none';
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalFoto();
            }
        });
        
        <?php if ($showPopupDokumenLengkap): ?>
        document.getElementById('popupDokumenLengkap').style.display = 'flex';
        <?php endif; ?>
        
        function tutupPopup() {
            document.getElementById('popupDokumenLengkap').style.display = 'none';
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        document.getElementById('namaLink').addEventListener('click', function(e) {
            e.preventDefault();
            const infoSection = document.getElementById('infoSection');
            if (infoSection) {
                infoSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
                infoSection.classList.add('highlight-section');
                setTimeout(() => {
                    infoSection.classList.remove('highlight-section');
                }, 1500);
            }
        });
        
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if(!alert.innerHTML.includes('DITERIMA') && !alert.innerHTML.includes('LUNAS') && !alert.innerHTML.includes('Selamat')) {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>