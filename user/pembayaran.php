<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../koneksi.php";

// Cek login
if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit();
}

$username = $_SESSION['maba'];
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username='$username'"));
$id_maba = $user['id_maba'];

// Ambil data pembayaran
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pembayaran_gedung WHERE id_maba='$id_maba' ORDER BY id_pembayaran DESC LIMIT 1"));

// Buat data baru jika belum ada
if (!$data) {
    $nominal = 10000000;
    $kode_unik = rand(100, 999);
    $total_bayar = $nominal + $kode_unik;
    $kode_pembayaran = "INV/" . date('Ymd') . "/" . str_pad($id_maba, 4, '0', STR_PAD_LEFT);
    $batas_waktu = date('Y-m-d H:i:s', strtotime('+5 days'));
    
    mysqli_query($koneksi, "INSERT INTO pembayaran_gedung 
        (id_maba, kode_pembayaran, nominal, kode_unik, total_bayar, batas_waktu, status) 
        VALUES ('$id_maba', '$kode_pembayaran', '$nominal', '$kode_unik', '$total_bayar', '$batas_waktu', 'menunggu')");
    
    $data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pembayaran_gedung WHERE id_maba='$id_maba' ORDER BY id_pembayaran DESC LIMIT 1"));
}

// Proses upload
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti'])) {
    $file = $_FILES['bukti'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (!in_array($ext, $allowed)) {
        $message = "Format file tidak didukung!";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $message = "File terlalu besar! Maksimal 2MB";
    } else {
        if (!file_exists("uploads/pembayaran")) {
            mkdir("uploads/pembayaran", 0777, true);
        }
        
        $new_name = "payment_" . $id_maba . "_" . time() . "." . $ext;
        $path = "uploads/pembayaran/" . $new_name;
        
        if (move_uploaded_file($file['tmp_name'], $path)) {
            $update = mysqli_query($koneksi, "UPDATE pembayaran_gedung SET 
                bukti_bayar='$new_name', 
                status='lunas',
                tanggal_upload=NOW()
                WHERE id_maba='$id_maba'");
            
            if ($update) {
                $message = "✅ Pembayaran berhasil!";
                $data['status'] = 'lunas';
            } else {
                $message = "Gagal update database";
                unlink($path);
            }
        } else {
            $message = "Gagal upload file!";
        }
    }
}

// WARNA UNTUK TEKNIK
$warna_primary = "#2E7D32";
$warna_primary_dark = "#1B5E20";
$warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";

// Hitung sisa waktu
$target_waktu = strtotime($data['batas_waktu']);
$sisa_detik = max(0, $target_waktu - time());
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Uang Gedung - Universitas Cendekia Nusantara</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: <?= $warna_gradient_bg ?>;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.1);
        }

        .header-icon i {
            font-size: 28px;
            color: <?= $warna_primary ?>;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 6px;
        }

        .header p {
            font-size: 13px;
            color: #6B7280;
        }

        /* Card Style */
        .card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(46, 125, 50, 0.08);
        }

        /* User Info Card */
        .user-card {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            justify-content: space-between;
            align-items: center;
        }

        .user-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-item i {
            width: 38px;
            height: 38px;
            background: <?= $warna_primary ?>;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .user-item .label {
            font-size: 11px;
            color: #9CA3AF;
            margin-bottom: 3px;
        }

        .user-item .value {
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
        }

        /* Payment Card */
        .payment-card {
            text-align: center;
            padding: 24px;
        }

        .payment-label {
            font-size: 12px;
            font-weight: 500;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .payment-amount {
            font-size: 48px;
            font-weight: 800;
            color: <?= $warna_primary ?>;
            letter-spacing: -1px;
            line-height: 1;
            margin-bottom: 8px;
        }

        .payment-note {
            font-size: 12px;
            color: #9CA3AF;
            margin-bottom: 20px;
        }

        /* Countdown Timer */
        .timer-section {
            background: <?= $warna_primary ?>08;
            border-radius: 16px;
            padding: 16px;
        }

        .timer-label {
            font-size: 12px;
            font-weight: 500;
            color: <?= $warna_primary ?>;
            text-align: center;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .countdown-wrapper {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .countdown-box {
            text-align: center;
            min-width: 65px;
        }

        .countdown-number {
            font-size: 28px;
            font-weight: 800;
            color: <?= $warna_primary ?>;
            background: white;
            padding: 8px 12px;
            border-radius: 12px;
            font-family: monospace;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .countdown-label {
            font-size: 10px;
            color: <?= $warna_primary ?>;
            margin-top: 6px;
            font-weight: 500;
        }

        /* Bank Grid */
        .bank-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .bank-card {
            background: #FAFCFE;
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(46, 125, 50, 0.1);
        }

        .bank-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.12);
            border-color: <?= $warna_primary ?>;
        }

        .bank-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(46, 125, 50, 0.1);
        }

        .bank-logo {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F3F4F6;
            border-radius: 14px;
        }

        .bank-logo img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .bank-title {
            flex: 1;
        }

        .bank-name {
            font-weight: 800;
            color: #1F2937;
            font-size: 18px;
            margin-bottom: 4px;
        }

        .bank-badge {
            font-size: 10px;
            color: <?= $warna_primary ?>;
            background: <?= $warna_primary ?>10;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-block;
        }

        .bank-details {
            margin-bottom: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 11px;
            color: #9CA3AF;
            font-weight: 500;
        }

        .detail-value {
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
            font-family: monospace;
        }

        .detail-value.swift {
            font-size: 12px;
            color: <?= $warna_primary ?>;
            letter-spacing: 0.5px;
        }

        .bank-account-number {
            font-size: 15px;
            font-weight: 700;
            color: <?= $warna_primary ?>;
            font-family: monospace;
            letter-spacing: 1px;
        }

        .for-info {
            background: <?= $warna_primary ?>05;
            border-radius: 10px;
            padding: 10px;
            margin: 12px 0;
            text-align: center;
        }

        .for-info span {
            font-size: 11px;
            color: <?= $warna_primary ?>;
            font-weight: 500;
        }

        .copy-btn {
            width: 100%;
            background: <?= $warna_primary ?>;
            border: none;
            padding: 10px;
            border-radius: 40px;
            cursor: pointer;
            color: white;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .copy-btn:hover {
            background: <?= $warna_primary_dark ?>;
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed rgba(46, 125, 50, 0.3);
            border-radius: 18px;
            padding: 28px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-area:hover {
            border-color: <?= $warna_primary ?>;
            background: <?= $warna_primary ?>03;
        }

        .upload-icon {
            font-size: 36px;
            color: <?= $warna_primary ?>;
            margin-bottom: 12px;
            opacity: 0.7;
        }

        .upload-text {
            font-weight: 600;
            color: #1F2937;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .upload-hint {
            font-size: 11px;
            color: #9CA3AF;
        }

        /* Selected File Info */
        .selected-file {
            background: <?= $warna_primary ?>08;
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 14px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .selected-file.show {
            display: flex;
        }

        .file-info-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }

        .file-info-left i {
            font-size: 24px;
            color: <?= $warna_primary ?>;
            flex-shrink: 0;
        }

        .file-info-left div {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-size: 13px;
            color: #1F2937;
            font-weight: 500;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-size {
            font-size: 11px;
            color: #9CA3AF;
        }

        .file-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .preview-btn {
            background: none;
            border: 1px solid <?= $warna_primary ?>;
            padding: 6px 14px;
            border-radius: 30px;
            color: <?= $warna_primary ?>;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .preview-btn:hover {
            background: <?= $warna_primary ?>;
            color: white;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #9CA3AF;
            cursor: pointer;
            font-size: 18px;
            padding: 6px;
            border-radius: 50%;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .remove-btn:hover {
            color: #EF4444;
            background: #FEE2E2;
        }

        /* Modal Preview */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            max-width: 90vw;
            max-height: 90vh;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            animation: zoomIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: white;
            border-bottom: 1px solid #E5E7EB;
        }

        .modal-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1F2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #9CA3AF;
            transition: all 0.2s;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .modal-close:hover {
            background: #F3F4F6;
            color: #EF4444;
        }

        .modal-body {
            padding: 20px;
            text-align: center;
            background: #F9FAFB;
            max-height: 70vh;
            overflow: auto;
        }

        .modal-image {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-pdf {
            padding: 40px;
            text-align: center;
        }

        .modal-pdf i {
            font-size: 80px;
            color: #EF4444;
        }

        .modal-pdf p {
            margin-top: 16px;
            color: #6B7280;
        }

        .btn-open-pdf {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 24px;
            background: <?= $warna_primary ?>;
            color: white;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: <?= $warna_primary ?>;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: <?= $warna_primary_dark ?>;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Status Card */
        .status-card {
            text-align: center;
            padding: 32px 24px;
        }

        .status-icon {
            width: 64px;
            height: 64px;
            background: <?= $warna_primary ?>10;
            border-radius: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .status-icon i {
            font-size: 32px;
            color: <?= $warna_primary ?>;
        }

        .status-title {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .status-message {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 20px;
        }

        .btn-outline {
            display: inline-block;
            padding: 10px 24px;
            background: white;
            color: <?= $warna_primary ?>;
            border: 1.5px solid <?= $warna_primary ?>;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: <?= $warna_primary ?>;
            color: white;
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 16px;
            color: #9CA3AF;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: <?= $warna_primary ?>;
        }

        /* Alert */
        .alert {
            background: white;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            border-left: 3px solid;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            border-left-color: <?= $warna_primary ?>;
            background: <?= $warna_primary ?>05;
        }

        .alert-error {
            border-left-color: #EF4444;
            background: #FEF2F2;
        }

        /* Info Box */
        .info-box {
            margin-top: 16px;
            padding: 12px;
            background: #FFF8E7;
            border-radius: 12px;
        }

        .info-box-item {
            text-align: center;
        }

        .info-box-item:first-child {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #FDE68A;
        }

        .info-text {
            font-size: 12px;
            color: #B45309;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .info-text-small {
            font-size: 11px;
            color: #B45309;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        @media (max-width: 680px) {
            body {
                padding: 20px 12px;
            }
            
            .container {
                max-width: 100%;
            }
            
            .user-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .payment-amount {
                font-size: 36px;
            }

            .countdown-wrapper {
                gap: 10px;
            }

            .countdown-box {
                min-width: 55px;
            }

            .countdown-number {
                font-size: 22px;
                padding: 6px 8px;
            }

            .bank-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .bank-header {
                flex-direction: column;
                text-align: center;
            }
            
            .bank-title {
                text-align: center;
            }

            .file-actions {
                flex-direction: column;
                gap: 6px;
            }

            .preview-btn {
                padding: 4px 10px;
                font-size: 11px;
            }

            .remove-btn {
                width: 28px;
                height: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1>Pembayaran Uang Gedung</h1>
            <p>Universitas Cendekia Nusantara</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-error' ?>">
                <i class="fas <?= strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($data['status'] == 'lunas'): ?>
            <div class="card status-card">
                <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                <div class="status-title">Pembayaran Lunas! 🎉</div>
                <div class="status-message">Terima kasih, pembayaran Anda telah dikonfirmasi.</div>
                <a href="form_daftar_ulang.php" class="btn-outline">Silakan lanjut ke DAFTAR ULANG <i class="fas fa-arrow-right"></i></a>
            </div>

        <?php elseif ($data['status'] == 'ditolak'): ?>
            <div class="card status-card">
                <div class="status-icon"><i class="fas fa-times-circle"></i></div>
                <div class="status-title">Pembayaran Ditolak</div>
                <div class="status-message"><?= $data['keterangan'] ?: 'Bukti pembayaran tidak valid. Silakan upload ulang.' ?></div>
                <a href="pembayaran.php" class="btn-outline">Upload Ulang <i class="fas fa-redo"></i></a>
            </div>

        <?php elseif ($data['status'] == 'expired'): ?>
            <div class="card status-card">
                <div class="status-icon"><i class="fas fa-clock"></i></div>
                <div class="status-title">Batas Waktu Habis</div>
                <div class="status-message">Masa tenggang 5 hari telah berakhir. Silakan buat kode pembayaran baru.</div>
                <a href="pembayaran.php" class="btn-outline">Buat Kode Baru <i class="fas fa-sync"></i></a>
            </div>

        <?php else: ?>
            <!-- User Info Card -->
            <div class="card">
                <div class="user-card">
                    <div class="user-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <div class="label">Nama Lengkap</div>
                            <div class="value"><?= htmlspecialchars($user['nama']) ?></div>
                        </div>
                    </div>
                    <div class="user-item">
                        <i class="fas fa-book-open"></i>
                        <div>
                            <div class="label">Program Studi</div>
                            <div class="value"><?= htmlspecialchars($user['jurusan']) ?></div>
                        </div>
                    </div>
                    <div class="user-item">
                        <i class="fas fa-barcode"></i>
                        <div>
                            <div class="label">Kode Pembayaran</div>
                            <div class="value"><?= $data['kode_pembayaran'] ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="card payment-card">
                <div class="payment-label">Total Pembayaran</div>
                <div class="payment-amount">Rp <?= number_format($data['total_bayar'], 0, ',', '.') ?></div>
                <div class="payment-note">Sudah termasuk kode unik Rp <?= number_format($data['kode_unik'], 0, ',', '.') ?></div>
                
                <div class="timer-section">
                    <div class="timer-label">
                        <i class="fas fa-hourglass-half"></i> Sisa Waktu Pembayaran
                    </div>
                    <div class="countdown-wrapper">
                        <div class="countdown-box">
                            <div class="countdown-number" id="countdown-days">00</div>
                            <div class="countdown-label">Hari</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-number" id="countdown-hours">00</div>
                            <div class="countdown-label">Jam</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-number" id="countdown-minutes">00</div>
                            <div class="countdown-label">Menit</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-number" id="countdown-seconds">00</div>
                            <div class="countdown-label">Detik</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Section -->
            <div class="card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-weight: 700; color: #1F2937; font-size: 16px;">🏦 Rekening Tujuan</span>
                </div>
                <div class="bank-grid">
                    <!-- Bank BCA -->
                    <div class="bank-card">
                        <div class="bank-header">
                            <div class="bank-logo">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/1200px-Bank_Central_Asia.svg.png" alt="BCA" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%232E7D32\'/%3E%3Ctext x=\'50\' y=\'50\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\' font-size=\'40\' font-weight=\'bold\'%3EBCA%3C/text%3E%3C/svg%3E'">
                            </div>
                            <div class="bank-title">
                                <div class="bank-name">Bank BCA</div>
                                <span class="bank-badge">Lokal & Internasional</span>
                            </div>
                        </div>
                        <div class="bank-details">
                            <div class="detail-row">
                                <span class="detail-label">Account Number</span>
                                <span class="detail-value bank-account-number">0810 1305 1607 26</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">SWIFT Code</span>
                                <span class="detail-value swift">CENAIDJA</span>
                            </div>
                        </div>
                        <div class="for-info">
                            <span><i class="fas fa-globe"></i> Transfer lokal & internasional (WNA)</span>
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('08101305160726')">
                            <i class="fas fa-copy"></i> Salin Nomor Rekening
                        </button>
                    </div>
                    
                    <!-- Bank Mandiri -->
                    <div class="bank-card">
                        <div class="bank-header">
                            <div class="bank-logo">
                                <i class="fas fa-landmark" style="font-size: 32px; color: <?= $warna_primary ?>;"></i>
                            </div>
                            <div class="bank-title">
                                <div class="bank-name">Bank Mandiri</div>
                                <span class="bank-badge">Lokal & Internasional</span>
                            </div>
                        </div>
                        <div class="bank-details">
                            <div class="detail-row">
                                <span class="detail-label">Account Number</span>
                                <span class="detail-value bank-account-number">1412 2405 1978 11</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">SWIFT Code</span>
                                <span class="detail-value swift">BMRIIDJA</span>
                            </div>
                        </div>
                        <div class="for-info">
                            <span><i class="fas fa-globe"></i> Transfer lokal & internasional (WNA)</span>
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('14122405197811')">
                            <i class="fas fa-copy"></i> Salin Nomor Rekening
                        </button>
                    </div>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <div class="info-box-item">
                        <span class="info-text">
                            <i class="fas fa-globe"></i> Untuk transfer internasional (WNA), gunakan SWIFT Code di atas
                        </span>
                    </div>
                    <div class="info-box-item">
                        <span class="info-text-small">
                            <i class="fas fa-receipt"></i> Total sudah termasuk pajak dan kode unik
                        </span>
                    </div>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="card">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-area" onclick="document.getElementById('bukti').click()">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Upload Bukti Pembayaran</div>
                        <div class="upload-hint">JPG, PNG, PDF (Max. 2MB)</div>
                    </div>
                    <input type="file" name="bukti" id="bukti" accept=".jpg,.jpeg,.png,.pdf" hidden required>
                    
                    <!-- Selected File Info - Tombol Preview dan X di ujung kanan -->
                    <div class="selected-file" id="selectedFile">
                        <div class="file-info-left">
                            <i class="fas fa-file-image" id="fileIcon"></i>
                            <div>
                                <span class="file-name" id="fileName"></span>
                                <span class="file-size" id="fileSize"></span>
                            </div>
                        </div>
                        <div class="file-actions">
                            <button type="button" class="preview-btn" id="previewBtn" onclick="openPreviewModal()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" class="remove-btn" onclick="clearFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn" disabled>
                        <i class="fas fa-check-circle"></i> KIRIM PEMBAYARAN
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <a href="dashboard_maba.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Modal Preview -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-image"></i> Preview Bukti Pembayaran</h3>
                <button class="modal-close" onclick="closePreviewModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be filled by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Countdown Timer
        var targetTime = <?= $target_waktu ?> * 1000;
        
        function updateCountdown() {
            var now = new Date().getTime();
            var distance = targetTime - now;
            
            if (distance <= 0) {
                document.getElementById('countdown-days').innerHTML = '00';
                document.getElementById('countdown-hours').innerHTML = '00';
                document.getElementById('countdown-minutes').innerHTML = '00';
                document.getElementById('countdown-seconds').innerHTML = '00';
                setTimeout(function() { location.reload(); }, 1000);
                return;
            }
            
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-days').innerHTML = String(days).padStart(2, '0');
            document.getElementById('countdown-hours').innerHTML = String(hours).padStart(2, '0');
            document.getElementById('countdown-minutes').innerHTML = String(minutes).padStart(2, '0');
            document.getElementById('countdown-seconds').innerHTML = String(seconds).padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Variables
        let currentFile = null;
        let currentFileType = null;
        let currentFileData = null;
        
        const fileInput = document.getElementById('bukti');
        const selectedFileDiv = document.getElementById('selectedFile');
        const fileNameSpan = document.getElementById('fileName');
        const fileSizeSpan = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');
        const submitBtn = document.getElementById('submitBtn');
        
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran
                if (file.size > 2 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 2MB');
                    clearFile();
                    return;
                }
                
                // Validasi ekstensi
                const ext = file.name.split('.').pop().toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'pdf'].includes(ext)) {
                    alert('Format file tidak didukung! Gunakan JPG, PNG, atau PDF');
                    clearFile();
                    return;
                }
                
                currentFile = file;
                currentFileType = ext;
                
                // Format ukuran file
                let sizeText = '';
                if (file.size < 1024) {
                    sizeText = file.size + ' B';
                } else if (file.size < 1024 * 1024) {
                    sizeText = (file.size / 1024).toFixed(1) + ' KB';
                } else {
                    sizeText = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
                }
                
                fileNameSpan.textContent = file.name;
                fileSizeSpan.textContent = sizeText;
                
                // Set icon berdasarkan tipe file
                if (ext === 'pdf') {
                    fileIcon.className = 'fas fa-file-pdf';
                    fileIcon.style.color = '#EF4444';
                } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    fileIcon.className = 'fas fa-file-image';
                    fileIcon.style.color = '<?= $warna_primary ?>';
                } else {
                    fileIcon.className = 'fas fa-file-alt';
                    fileIcon.style.color = '#9CA3AF';
                }
                
                // Untuk preview, baca file
                if (ext === 'pdf') {
                    currentFileData = URL.createObjectURL(file);
                } else if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        currentFileData = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
                
                selectedFileDiv.classList.add('show');
                submitBtn.disabled = false;
            }
        });
        
        function clearFile() {
            fileInput.value = '';
            selectedFileDiv.classList.remove('show');
            submitBtn.disabled = true;
            currentFile = null;
            currentFileData = null;
        }
        
        function openPreviewModal() {
            if (!currentFile) return;
            
            const modal = document.getElementById('previewModal');
            const modalBody = document.getElementById('modalBody');
            
            if (currentFileType === 'pdf') {
                modalBody.innerHTML = `
                    <div class="modal-pdf">
                        <i class="fas fa-file-pdf"></i>
                        <p><strong>${currentFile.name}</strong></p>
                        <p style="font-size: 12px;">File PDF siap diupload. Klik tombol kirim untuk melanjutkan.</p>
                        <a href="${currentFileData}" target="_blank" class="btn-open-pdf">
                            <i class="fas fa-external-link-alt"></i> Buka PDF
                        </a>
                    </div>
                `;
            } else if (currentFileType === 'jpg' || currentFileType === 'jpeg' || currentFileType === 'png') {
                modalBody.innerHTML = `
                    <img src="${currentFileData}" class="modal-image" alt="Preview Bukti Pembayaran">
                `;
            }
            
            modal.classList.add('show');
        }
        
        function closePreviewModal() {
            const modal = document.getElementById('previewModal');
            modal.classList.remove('show');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                closePreviewModal();
            }
        }
        
        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Nomor rekening telah disalin!');
            }).catch(() => {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast('Nomor rekening telah disalin!');
            });
        }
        
        function showToast(msg) {
            const toast = document.createElement('div');
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
            toast.style.cssText = `position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: <?= $warna_primary ?>; color: white; padding: 10px 24px; border-radius: 40px; font-size: 13px; z-index: 9999; animation: fadeInUp 0.3s ease;`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }
        
        const style = document.createElement('style');
        style.textContent = `@keyframes fadeInUp { from { opacity: 0; transform: translateX(-50%) translateY(10px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }`;
        document.head.appendChild(style);
    </script>
</body>
</html>