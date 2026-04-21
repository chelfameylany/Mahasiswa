<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id_maba'])) {
    header("Location: login.php");
    exit;
}

$id_maba = $_SESSION['id_maba'];

// ===== AMBIL DATA USER TERLEBIH DAHULU (SEBELUM DETEKSI WARNA) =====
$query_biodata = mysqli_query($koneksi, "SELECT nama, jurusan FROM calon_maba WHERE id_maba='$id_maba'");
$biodata = mysqli_fetch_assoc($query_biodata);
$nama_user = $biodata['nama'] ?? 'User';
$jurusan_user = $biodata['jurusan'] ?? 'Belum memilih';

// ===== DETEKSI WARNA BERDASARKAN JURUSAN =====
$jurusan_asli = $jurusan_user; // Gunakan dari hasil query
$jurusan_lower = strtolower(trim($jurusan_asli));

if(strpos($jurusan_lower, 'teknik') !== false) {
    // WARNA HIJAU UNTUK TEKNIK
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #2E7D32, #1B5E20)";
    $warna_soft = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan = "fa-gear";
} else {
    // WARNA BIRU UNTUK UMUM
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #DBEAFE 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #3B82F6, #2563EB)";
    $warna_soft = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan = "fa-users";
}

// ===== CEK JADWAL UJIAN =====
$tanggal_ujian = "2025-04-21 08:00:00";
$waktu_sekarang = date('Y-m-d H:i:s');

// Konversi ke timestamp
$timestamp_ujian = strtotime($tanggal_ujian);
$timestamp_sekarang = strtotime($waktu_sekarang);

// Hitung selisih
$selisih_detik = $timestamp_ujian - $timestamp_sekarang;
$ujian_dimulai = ($selisih_detik <= 0);

// Hitung komponen countdown
if ($selisih_detik > 0) {
    $hari = floor($selisih_detik / (60 * 60 * 24));
    $jam = floor(($selisih_detik % (60 * 60 * 24)) / (60 * 60));
    $menit = floor(($selisih_detik % (60 * 60)) / 60);
    $detik = $selisih_detik % 60;
} else {
    $hari = $jam = $menit = $detik = 0;
}

// FORMAT ID
$id_maba_formatted = "PMB" . str_pad($id_maba, 5, '0', STR_PAD_LEFT);

// Cek apakah sudah pernah tes
$query_cek = mysqli_query($koneksi, "SELECT * FROM hasil_tes WHERE id_maba='$id_maba'");
if (mysqli_num_rows($query_cek) > 0) {
    header("Location: tes_online.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Tes - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: <?= $warna_gradient_bg ?>;
            min-height: 100vh;
        }
        
        /* NAVBAR */
        .navbar {
            background: <?= $warna_gradient_card ?>;
            padding: 16px 40px;
            box-shadow: 0 6px 25px rgba(0,0,0,.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .logo {
            color: white;
            font-size: 22px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-img {
            height: 45px;
            width: auto;
            object-fit: contain;
        }
        
        .logo span {
            color: white;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }
        
        .user-id {
            background: rgba(255,255,255,0.15);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-id i {
            color: #ffd166;
        }
        
        /* MAIN CONTAINER */
        .main-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 25px;
        }
        
        /* LOCKED CARD - JIKA UJIAN BELUM DIMULAI */
        .locked-card {
            background: <?= $warna_gradient_card ?>;
            color: white;
            border-radius: 30px;
            padding: 50px 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px <?= $warna_primary ?>80;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .locked-card::before {
            content: "🔒";
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }
        
        .locked-icon {
            width: 90px;
            height: 90px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 42px;
            border: 3px solid rgba(255,255,255,0.3);
        }
        
        .locked-card h2 {
            font-weight: 800;
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .locked-card p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        /* COUNTDOWN TIMER */
        .countdown-container {
            margin: 30px 0;
        }
        
        .countdown-title {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 16px;
            color: rgba(255,255,255,0.9);
        }
        
        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .countdown-item {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px 10px;
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .countdown-number {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }
        
        .countdown-label {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-refresh {
            background: white;
            color: <?= $warna_primary ?>;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            text-decoration: none;
        }
        
        .btn-refresh:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        /* HEADER CARD (UNTUK YANG SUDAH BISA UJIAN) */
        .header-card {
            background: <?= $warna_gradient_card ?>;
            color: white;
            border-radius: 30px;
            padding: 35px 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px <?= $warna_primary ?>80;
            position: relative;
            overflow: hidden;
        }
        
        .header-card::before {
            content: "📝";
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 100px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }
        
        .header-card h1 {
            font-weight: 800;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .header-card .welcome-text {
            font-size: 18px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-card .welcome-text i {
            color: #ffd166;
        }
        
        .user-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .user-badge i {
            margin-right: 8px;
            color: #ffd166;
        }
        
        /* PANDUAN CARD */
        .panduan-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: <?= $warna_soft ?>;
            border-radius: 24px;
            padding: 25px;
            border: 1px solid <?= $warna_primary ?>40;
            transition: all 0.3s ease;
        }
        
        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px <?= $warna_primary ?>20;
        }
        
        .info-box-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .info-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        
        .icon-prepare { background: <?= $warna_primary ?>20; color: <?= $warna_primary ?>; }
        .icon-forbidden { background: #ffe6e6; color: #dc3545; }
        .icon-rules { background: <?= $warna_primary ?>20; color: <?= $warna_primary ?>; }
        .icon-after { background: #fff3e6; color: #fd7e14; }
        
        .info-box-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .info-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 15px;
            color: #475569;
            line-height: 1.5;
        }
        
        .info-list li i {
            font-size: 18px;
            margin-top: 2px;
        }
        
        .info-list li i.bi-check-circle-fill { color: #28a745; }
        .info-list li i.bi-x-circle-fill { color: #dc3545; }
        .info-list li i.bi-info-circle-fill { color: <?= $warna_primary ?>; }
        
        /* TIMER INFO */
        .timer-info {
            background: linear-gradient(135deg, #fff3cd, #ffecb3);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid #ffd966;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .timer-icon {
            width: 60px;
            height: 60px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            flex-shrink: 0;
        }
        
        .timer-text {
            flex: 1;
        }
        
        .timer-text h4 {
            font-weight: 700;
            color: #856404;
            margin-bottom: 5px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .timer-text p {
            color: #856404;
            margin: 0;
            font-size: 15px;
        }
        
        .timer-highlight {
            font-weight: 800;
            font-size: 28px;
            color: #dc3545;
            background: white;
            padding: 8px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        
        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: <?= $warna_gradient_card ?>;
            color: white;
            box-shadow: 0 10px 20px <?= $warna_primary ?>80;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px <?= $warna_primary ?>80;
        }
        
        .btn-secondary {
            background: white;
            color: <?= $warna_primary ?>;
            border: 2px solid <?= $warna_primary ?>;
        }
        
        .btn-secondary:hover {
            background: <?= $warna_soft ?>;
            transform: translateY(-3px);
        }
        
        /* PERNYATAAN */
        .pernyataan-box {
            background: <?= $warna_soft ?>;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0 20px;
            border: 2px solid <?= $warna_primary ?>80;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: <?= $warna_primary ?>;
        }
        
        .checkbox-wrapper span {
            font-size: 16px;
            color: #1e293b;
            font-weight: 500;
        }
        
        .checkbox-wrapper span strong {
            color: <?= $warna_primary ?>;
        }
        
        /* ===== POP UP WARNING ===== */
        .warning-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }
        
        .warning-modal {
            background: linear-gradient(135deg, #ffffff, <?= $warna_soft ?>);
            width: 380px;
            max-width: 90%;
            border-radius: 28px;
            padding: 30px 25px;
            box-shadow: 0 30px 60px <?= $warna_primary ?>80;
            text-align: center;
            animation: warningIn 0.4s ease;
            border: 2px solid <?= $warna_primary ?>40;
        }
        
        @keyframes warningIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .warning-icon {
            width: 70px;
            height: 70px;
            background: <?= $warna_gradient_card ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: white;
            box-shadow: 0 15px 30px <?= $warna_primary ?>80;
        }
        
        .warning-modal h3 {
            font-weight: 800;
            font-size: 22px;
            background: <?= $warna_gradient_card ?>;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .warning-modal p {
            color: #475569;
            font-size: 15px;
            margin-bottom: 25px;
            line-height: 1.6;
            padding: 0 10px;
        }
        
        .warning-modal .btn-ok {
            background: <?= $warna_gradient_card ?>;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px <?= $warna_primary ?>80;
            min-width: 140px;
        }
        
        .warning-modal .btn-ok:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px <?= $warna_primary ?>80;
        }
        
        .warning-modal .btn-ok i {
            margin-right: 8px;
        }
        
        /* MODAL KONFIRMASI MULAI TES */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .confirm-modal {
            background: white;
            border-radius: 30px;
            width: 90%;
            max-width: 450px;
            padding: 35px;
            text-align: center;
            animation: modalIn 0.4s ease;
        }
        
        @keyframes modalIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .confirm-icon {
            width: 70px;
            height: 70px;
            background: <?= $warna_gradient_card ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: white;
        }
        
        .confirm-modal h3 {
            font-weight: 800;
            color: <?= $warna_primary ?>;
            margin-bottom: 15px;
        }
        
        .confirm-modal p {
            color: #475569;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .confirm-buttons {
            display: flex;
            gap: 12px;
        }
        
        .confirm-btn {
            flex: 1;
            padding: 12px;
            border-radius: 50px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .confirm-btn.confirm {
            background: <?= $warna_gradient_card ?>;
            color: white;
        }
        
        .confirm-btn.confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px <?= $warna_primary ?>80;
        }
        
        .confirm-btn.cancel {
            background: #e2e8f0;
            color: #475569;
        }
        
        .confirm-btn.cancel:hover {
            background: #cbd5e1;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                padding: 15px 20px;
                flex-direction: column;
                gap: 10px;
            }
            
            .header-card, .locked-card {
                padding: 25px;
            }
            
            .header-card h1, .locked-card h2 {
                font-size: 22px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .timer-info {
                flex-direction: column;
                text-align: center;
            }
            
            .countdown-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
            }
            
            .countdown-number {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<!-- POP UP WARNING -->
<div class="warning-modal-overlay" id="warningModal">
    <div class="warning-modal">
        <div class="warning-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h3>Perhatian!</h3>
        <p>Anda harus menyetujui pernyataan terlebih dahulu sebelum memulai tes.</p>
        <button class="btn-ok" onclick="closeWarningModal()">
            <i class="bi bi-check-lg"></i> Mengerti
        </button>
    </div>
</div>

<!-- MODAL KONFIRMASI MULAI TES -->
<div class="confirm-modal-overlay" id="confirmModal">
    <div class="confirm-modal">
        <div class="confirm-icon">
            <i class="bi bi-question-circle-fill"></i>
        </div>
        <h3>Mulai Tes Sekarang?</h3>
        <p>Pastikan Anda sudah membaca dan memahami seluruh panduan ujian. Tes akan berlangsung selama <strong>2 jam</strong> dan tidak dapat dihentikan setelah dimulai.</p>
        <div class="confirm-buttons">
            <button class="confirm-btn cancel" onclick="closeConfirmModal()">Batal</button>
            <button class="confirm-btn confirm" onclick="startTest()">Ya, Mulai Tes</button>
        </div>
    </div>
</div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">
        <img src="../assets/logokampus1.png" alt="Logo UCN" class="logo-img" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2745%27 height=%2745%27%3E%3Crect width=%2745%27 height=%2745%27 fill=%27%233B82F6%27/%3E%3Ctext x=%2722.5%27 y=%2728%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2720%27%3E🎓%3C/text%3E%3C/svg%3E'">
        <span>UNIVERSITAS CENDEKIA NUSANTARA</span>
    </div>
    <div class="user-info">
        <div class="user-id">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($id_maba_formatted) ?> - <?= htmlspecialchars($nama_user) ?>
        </div>
    </div>
</div>

<!-- MAIN CONTAINER -->
<div class="main-container">
    
    <?php if (!$ujian_dimulai): ?>
    <!-- TAMPILAN JIKA UJIAN BELUM DIMULAI -->
    <div class="locked-card">
        <div class="locked-icon">
            <i class="bi bi-lock-fill"></i>
        </div>
        <h2>UJIAN TERKUNCI 🔒</h2>
        <p>Ujian akan dibuka secara serentak pada:</p>
        <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 50px; display: inline-block; margin-bottom: 20px;">
            <i class="bi bi-calendar-check-fill"></i> 21 April 2025 • 08:00 WIB
        </div>
        
        <div class="countdown-container">
            <div class="countdown-title">⏳ Waktu menuju ujian dibuka</div>
            <div class="countdown-grid" id="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="days"><?= $hari ?></div>
                    <div class="countdown-label">Hari</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="hours"><?= $jam ?></div>
                    <div class="countdown-label">Jam</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes"><?= $menit ?></div>
                    <div class="countdown-label">Menit</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds"><?= $detik ?></div>
                    <div class="countdown-label">Detik</div>
                </div>
            </div>
        </div>
        
        <a href="panduan_tes.php" class="btn-refresh">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </a>
        
        <div style="margin-top: 20px; opacity: 0.8;">
            <i class="bi bi-info-circle"></i> Halaman ini akan otomatis menampilkan panduan ketika waktu ujian tiba.
        </div>
    </div>
    
    <?php else: ?>
    <!-- TAMPILAN JIKA UJIAN SUDAH DIMULAI -->
    
    <!-- HEADER CARD -->
    <div class="header-card">
        <h1>📋 PANDUAN UJIAN SELEKSI MASUK</h1>
        <div class="welcome-text">
            <i class="bi bi-person-fill"></i> <?= htmlspecialchars($nama_user) ?>
        </div>
        <div class="welcome-text">
            <i class="bi bi-book-fill"></i> Program Studi: <strong><?= htmlspecialchars($jurusan_user) ?></strong>
        </div>
        <div class="user-badge">
            <i class="bi bi-clock-fill"></i> Durasi: 2 Jam | 50 Soal Pilihan Ganda
        </div>
    </div>

    <!-- PANDUAN CARD -->
    <div class="panduan-card">
        <!-- GRID 2 KOLOM -->
        <div class="grid-2">
            <!-- KIRI: PERSIAPAN -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-icon icon-prepare">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="info-box-title">✅ Persiapan Sebelum Ujian</div>
                </div>
                <ul class="info-list">
                    <li><i class="bi bi-check-circle-fill"></i> <span>Pastikan koneksi internet stabil selama ujian</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Siapkan alat tulis jika diperlukan untuk coretan</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Gunakan perangkat yang nyaman (laptop/PC lebih disarankan)</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Pastikan kondisi tubuh sehat dan fokus</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Baca doa sebelum memulai ujian</span></li>
                </ul>
            </div>

            <!-- KANAN: LARANGAN -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-icon icon-forbidden">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="info-box-title">🚫 Larangan Saat Ujian</div>
                </div>
                <ul class="info-list">
                    <li><i class="bi bi-x-circle-fill"></i> <span>Dilarang membuka tab atau jendela baru</span></li>
                    <li><i class="bi bi-x-circle-fill"></i> <span>Dilarang mencontek atau bekerja sama dengan peserta lain</span></li>
                    <li><i class="bi bi-x-circle-fill"></i> <span>Tidak diperkenankan keluar dari halaman ujian</span></li>
                    <li><i class="bi bi-x-circle-fill"></i> <span>Dilarang menggunakan HP atau perangkat lain</span></li>
                    <li><i class="bi bi-x-circle-fill"></i> <span>Dilarang me-refresh halaman selama ujian</span></li>
                </ul>
            </div>

            <!-- KIRI BAWAH: KETENTUAN -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-icon icon-rules">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div class="info-box-title">📝 Ketentuan Saat Ujian</div>
                </div>
                <ul class="info-list">
                    <li><i class="bi bi-info-circle-fill"></i> <span>Baca setiap soal dengan teliti sebelum menjawab</span></li>
                    <li><i class="bi bi-info-circle-fill"></i> <span>Gunakan fitur "Tandai Ragu" untuk soal yang belum yakin</span></li>
                    <li><i class="bi bi-info-circle-fill"></i> <span>Periksa kembali jawaban sebelum submit</span></li>
                    <li><i class="bi bi-info-circle-fill"></i> <span>Jawaban akan tersimpan otomatis</span></li>
                    <li><i class="bi bi-info-circle-fill"></i> <span>Ikuti arahan dan waktu yang ditentukan</span></li>
                </ul>
            </div>

            <!-- KANAN BAWAH: SETELAH UJIAN -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-icon icon-after">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="info-box-title">📌 Setelah Ujian</div>
                </div>
                <ul class="info-list">
                    <li><i class="bi bi-check-circle-fill"></i> <span>Pastikan semua soal sudah terjawab</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Klik tombol "Submit" untuk mengumpulkan jawaban</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Hasil tes dapat dilihat melalui menu "Hasil Tes" dan akan diumumkan 24 jam setelah pelaksanaan ujian, yaitu pada pukul 15.00 WIB.</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Tunggu informasi kelulusan dari admin</span></li>
                    <li><i class="bi bi-check-circle-fill"></i> <span>Jangan logout sebelum submit</span></li>
                </ul>
            </div>
        </div>

        <!-- TIMER INFO -->
        <div class="timer-info">
            <div class="timer-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="timer-text">
                <h4><i class="bi bi-exclamation-triangle-fill"></i> Perhatian! Waktu Terbatas</h4>
                <p>Anda memiliki waktu <strong>2 jam</strong> untuk mengerjakan 50 soal. Timer akan mulai setelah Anda klik "Mulai Tes". Pastikan koneksi internet stabil selama ujian berlangsung.</p>
            </div>
            <div class="timer-highlight">
                02:00:00
            </div>
        </div>

        <!-- PERNYATAAN -->
        <div class="pernyataan-box">
            <label class="checkbox-wrapper">
                <input type="checkbox" id="setujuCheckbox">
                <span>Saya telah membaca dan memahami seluruh <strong>panduan, larangan, dan ketentuan</strong> ujian. Saya siap mengikuti tes dengan jujur dan bertanggung jawab.</span>
            </label>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="action-buttons">
            <a href="dashboard_maba.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
            <button class="btn btn-primary" id="mulaiTesBtn" onclick="showConfirmModal()">
                <i class="bi bi-play-circle-fill"></i> Mulai Tes
            </button>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<script>
    <?php if (!$ujian_dimulai): ?>
    // COUNTDOWN REAL-TIME (UPDATE SETIAP DETIK)
    let countdownDate = <?= $timestamp_ujian ?> * 1000; // Konversi ke milidetik
    
    function updateCountdown() {
        let now = new Date().getTime();
        let distance = countdownDate - now;
        
        if (distance < 0) {
            // Waktu habis, reload halaman
            location.reload();
            return;
        }
        
        let days = Math.floor(distance / (1000 * 60 * 60 * 24));
        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('days').innerHTML = days;
        document.getElementById('hours').innerHTML = hours;
        document.getElementById('minutes').innerHTML = minutes;
        document.getElementById('seconds').innerHTML = seconds;
    }
    
    // Update setiap detik
    setInterval(updateCountdown, 1000);
    <?php endif; ?>

    // Fungsi untuk menampilkan warning modal
    function showWarningModal() {
        document.getElementById('warningModal').style.display = 'flex';
    }

    function closeWarningModal() {
        document.getElementById('warningModal').style.display = 'none';
    }

    // Fungsi untuk menampilkan confirm modal
    function showConfirmModal() {
        const checkbox = document.getElementById('setujuCheckbox');
        if (!checkbox.checked) {
            showWarningModal();
            return;
        }
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function startTest() {
        // Redirect ke halaman tes
        window.location.href = 'tes_online.php';
    }

    // Tutup modal kalo klik di luar
    window.onclick = function(event) {
        const warningModal = document.getElementById('warningModal');
        const confirmModal = document.getElementById('confirmModal');
        
        if (event.target === warningModal) {
            closeWarningModal();
        }
        if (event.target === confirmModal) {
            closeConfirmModal();
        }
    }
</script>

</body>
</html>