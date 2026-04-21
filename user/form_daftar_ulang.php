<?php
session_start();
include "../koneksi.php";

// CEK LOGIN
if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit();
}

// Ambil data user
$username = $_SESSION['maba'];
$query_user = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username='$username'");
$data = mysqli_fetch_assoc($query_user);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit();
}

$id_maba = $data['id_maba'];
$jurusan_asli = $data['jurusan'] ?? 'umum';

// ===== DETEKSI WARNA SEPERTI DI PEMBAYARAN =====
$jurusan_lower = strtolower(trim($jurusan_asli));
if(strpos($jurusan_lower, 'teknik') !== false) {
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #2E7D32, #1B5E20)";
    $warna_soft = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan = "fa-gear";
} else {
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #DBEAFE 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #3B82F6, #2563EB)";
    $warna_soft = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan = "fa-users";
}

// CEK NILAI TES
$query_nilai = mysqli_query($koneksi, "SELECT nilai, status_lulus FROM hasil_tes WHERE id_maba='$id_maba' ORDER BY tanggal_tes DESC LIMIT 1");
$nilai_data = mysqli_fetch_assoc($query_nilai);
$nilai = $nilai_data['nilai'] ?? 0;
$status_lulus = $nilai_data['status_lulus'] ?? 'tidak_lulus';
$is_lulus = ($status_lulus == 'lulus');

// CEK PEMBAYARAN
$query_pembayaran = mysqli_query($koneksi, "SELECT * FROM pembayaran_gedung WHERE id_maba='$id_maba' ORDER BY id_pembayaran DESC LIMIT 1");
$pembayaran = mysqli_fetch_assoc($query_pembayaran);
$pembayaran_lunas = ($pembayaran && $pembayaran['status'] == 'lunas');

// CEK DAFTAR ULANG
$query_daftar = mysqli_query($koneksi, "SELECT * FROM daftar_ulang WHERE id_maba='$id_maba' ORDER BY id_daftar_ulang DESC LIMIT 1");
$data_daftar = mysqli_fetch_assoc($query_daftar);

$sudah_daftar = !empty($data_daftar);
$status_daftar = $data_daftar['status_daftar_ulang'] ?? '';
$keterangan = $data_daftar['keterangan'] ?? '';
$tanggal_daftar = $data_daftar['tanggal_daftar_ulang'] ?? '';

// PROSES UPLOAD
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_daftar'])) {
    if (!$pembayaran_lunas) {
        $message = 'Pembayaran uang gedung harus lunas terlebih dahulu.';
        $message_type = 'error';
    } else {
        $target_dir = "uploads/bukti_hasil_tes/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES['bukti_hasil_tes']['name']);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
        
        if (in_array($file_type, $allowed_types)) {
            if ($_FILES['bukti_hasil_tes']['size'] <= 5000000) {
                if (move_uploaded_file($_FILES['bukti_hasil_tes']['tmp_name'], $target_file)) {
                    if ($sudah_daftar) {
                        $update = mysqli_query($koneksi, "UPDATE daftar_ulang SET 
                            bukti_pembayaran='$file_name',
                            tanggal_daftar_ulang=NOW(),
                            status_daftar_ulang='menunggu',
                            keterangan=NULL
                            WHERE id_maba='$id_maba'");
                    } else {
                        $update = mysqli_query($koneksi, "INSERT INTO daftar_ulang 
                            (id_maba, tanggal_daftar_ulang, bukti_pembayaran, status_daftar_ulang) 
                            VALUES ('$id_maba', NOW(), '$file_name', 'menunggu')");
                    }
                    
                    if ($update) {
                        $message = 'Berhasil! Bukti terkirim. Silakan tunggu verifikasi.';
                        $message_type = 'success';
                        $sudah_daftar = true;
                        $status_daftar = 'menunggu';
                        $tanggal_daftar = date('Y-m-d H:i:s');
                    } else {
                        $message = 'Gagal menyimpan data.';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Gagal upload file.';
                    $message_type = 'error';
                }
            } else {
                $message = 'File terlalu besar! Maksimal 5MB.';
                $message_type = 'error';
            }
        } else {
            $message = 'Format file tidak didukung. Gunakan JPG, PNG, PDF.';
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ulang - Universitas Cendekia Nusantara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: <?= $warna_gradient_bg ?>;
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 680px;
            margin: 0 auto;
        }
        
        /* HEADER CARD - SEPERTI PEMBAYARAN */
        .header-card {
            background: <?= $warna_gradient_card ?>;
            border-radius: 28px;
            padding: 24px 28px;
            margin-bottom: 28px;
            text-align: center;
            color: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .header-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        
        .header-icon i {
            font-size: 28px;
            color: white;
        }
        
        .header-card h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        
        .header-card p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        /* STATUS CARD - KECIL HORIZONTAL */
        .status-card {
            background: <?= $warna_soft ?>;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-left: 4px solid <?= $warna_primary ?>;
        }
        
        .status-icon {
            width: 44px;
            height: 44px;
            background: <?= $warna_primary ?>;
            border-radius: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .status-icon i {
            font-size: 20px;
            color: white;
        }
        
        .status-content {
            flex: 1;
        }
        
        .status-title {
            font-size: 16px;
            font-weight: 700;
            color: <?= $warna_primary ?>;
            margin-bottom: 2px;
        }
        
        .status-message {
            font-size: 12px;
            color: #4B5563;
        }
        
        .status-date {
            font-size: 11px;
            color: #9CA3AF;
            margin-top: 4px;
        }
        
        /* STATUS CARD UNTUK DITOLAK */
        .status-card.rejected {
            background: #FEF2F2;
            border-left-color: #EF4444;
        }
        .status-card.rejected .status-icon {
            background: #EF4444;
        }
        .status-card.rejected .status-title {
            color: #DC2626;
        }
        
        /* DATA CARD - Single Card */
        .data-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #F0F0F0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        
        .data-title {
            font-size: 13px;
            font-weight: 600;
            color: <?= $warna_primary ?>;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #F0F0F0;
        }
        
        .data-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .data-row {
            display: flex;
            align-items: baseline;
            gap: 16px;
        }
        
        .data-label {
            width: 110px;
            font-size: 12px;
            color: #6B7280;
            font-weight: 500;
        }
        
        .data-value {
            flex: 1;
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
        }
        
        /* UPLOAD CARD */
        .upload-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid #F0F0F0;
        }
        
        .upload-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }
        
        .upload-area {
            border: 2px dashed #E5E7EB;
            border-radius: 16px;
            padding: 28px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #FAFAFA;
        }
        
        .upload-area:hover {
            border-color: <?= $warna_primary ?>;
            background: <?= $warna_soft ?>;
        }
        
        .upload-icon {
            font-size: 36px;
            color: <?= $warna_primary ?>;
            margin-bottom: 10px;
        }
        
        .upload-text {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }
        
        .upload-hint {
            font-size: 11px;
            color: #9CA3AF;
        }
        
        /* File Preview */
        .file-preview {
            background: <?= $warna_soft ?>;
            border-radius: 12px;
            padding: 10px 14px;
            margin-top: 14px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        
        .file-preview.show {
            display: flex;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }
        
        .file-info i {
            font-size: 24px;
            color: <?= $warna_primary ?>;
        }
        
        .file-details {
            flex: 1;
            min-width: 0;
        }
        
        .file-name {
            font-size: 12px;
            font-weight: 500;
            color: #1F2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        
        .file-size {
            font-size: 10px;
            color: #6B7280;
        }
        
        .remove-file {
            width: 28px;
            height: 28px;
            background: white;
            border: none;
            border-radius: 28px;
            color: #9CA3AF;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-file:hover {
            background: #FEE2E2;
            color: #EF4444;
        }
        
        /* BUTTON */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: <?= $warna_primary ?>;
            border: none;
            border-radius: 40px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            margin-top: 16px;
        }
        
        .btn-submit:hover {
            background: <?= $warna_primary_dark ?>;
            transform: translateY(-1px);
        }
        
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: <?= $warna_primary ?>;
            color: white;
            padding: 12px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: <?= $warna_primary_dark ?>;
        }
        
        .btn-success {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #10B981;
            color: white;
            padding: 12px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            width: 100%;
        }
        
        /* Back Link */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #9CA3AF;
            text-decoration: none;
            font-size: 12px;
            transition: 0.2s;
        }
        
        .back-link:hover {
            color: <?= $warna_primary ?>;
        }
        
        /* Alert */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .alert-success {
            background: #F0FDF4;
            color: #166534;
            border-left: 3px solid #22C55E;
        }
        
        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border-left: 3px solid #EF4444;
        }
        
        /* Responsive */
        @media (max-width: 550px) {
            body {
                padding: 24px 16px;
            }
            
            .header-card {
                padding: 20px;
            }
            
            .header-icon {
                width: 50px;
                height: 50px;
            }
            
            .header-icon i {
                font-size: 24px;
            }
            
            .header-card h1 {
                font-size: 20px;
            }
            
            .data-row {
                flex-direction: column;
                gap: 4px;
            }
            
            .data-label {
                width: 100%;
            }
            
            .data-card {
                padding: 20px;
            }
            
            .upload-card {
                padding: 20px;
            }
            
            .status-card {
                padding: 14px 16px;
            }
            
            .status-icon {
                width: 38px;
                height: 38px;
            }
            
            .status-icon i {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER CARD - SEPERTI PEMBAYARAN -->
        <div class="header-card">
            <div class="header-icon">
                <i class="fas fa-file-signature"></i>
            </div>
            <h1>Daftar Ulang</h1>
            <p>Universitas Cendekia Nusantara · TA 2025/2026</p>
        </div>
        
        <?php if($message): ?>
        <div class="alert alert-<?= $message_type ?>">
            <i class="fas <?= $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= $message ?>
        </div>
        <?php endif; ?>
        
        <!-- BELUM LUNAS -->
        <?php if(!$pembayaran_lunas): ?>
        <div class="status-card">
            <div class="status-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="status-content">
                <div class="status-title">Belum Lunas</div>
                <div class="status-message">Pembayaran uang gedung harus lunas terlebih dahulu.</div>
            </div>
        </div>
        
        <div class="data-card">
            <div class="data-title">Data Mahasiswa</div>
            <div class="data-grid">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Program Studi</span>
                    <span class="data-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID Pendaftaran</span>
                    <span class="data-value">PMB<?= str_pad($id_maba, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nilai Tes</span>
                    <span class="data-value"><?= number_format($nilai, 0) ?> / 100</span>
                </div>
            </div>
        </div>
        
        <a href="pembayaran.php" class="btn-primary">
            <i class="fas fa-wallet"></i> Bayar Sekarang
        </a>
        
        <!-- MENUNGGU VERIFIKASI -->
        <?php elseif($sudah_daftar && $status_daftar == 'menunggu'): ?>
        <div class="status-card">
            <div class="status-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="status-content">
                <div class="status-title">Menunggu Verifikasi</div>
                <div class="status-message">Bukti hasil tes sudah diterima. Admin akan memverifikasi maksimal 1x24 jam.</div>
                <?php if($tanggal_daftar): ?>
                <div class="status-date">Didaftarkan: <?= date('d M Y, H:i', strtotime($tanggal_daftar)) ?> WIB</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="data-card">
            <div class="data-title">Data Mahasiswa</div>
            <div class="data-grid">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Program Studi</span>
                    <span class="data-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID Pendaftaran</span>
                    <span class="data-value">PMB<?= str_pad($id_maba, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nilai Tes</span>
                    <span class="data-value"><?= number_format($nilai, 0) ?> / 100</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status Pembayaran</span>
                    <span class="data-value" style="color: <?= $warna_primary ?>;">✅ LUNAS</span>
                </div>
            </div>
        </div>
        
        <!-- DITERIMA -->
        <?php elseif($sudah_daftar && $status_daftar == 'diterima'): ?>
        <div class="status-card">
            <div class="status-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="status-content">
                <div class="status-title">LULUS</div>
                <div class="status-message">Selamat! Anda dinyatakan lulus seleksi dan resmi menjadi mahasiswa.</div>
                <?php if($tanggal_daftar): ?>
                <div class="status-date">Terverifikasi: <?= date('d M Y, H:i', strtotime($tanggal_daftar)) ?> WIB</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="data-card">
            <div class="data-title">Data Mahasiswa</div>
            <div class="data-grid">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Program Studi</span>
                    <span class="data-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID Pendaftaran</span>
                    <span class="data-value">PMB<?= str_pad($id_maba, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nilai Tes</span>
                    <span class="data-value"><?= number_format($nilai, 0) ?> / 100</span>
                </div>
            </div>
        </div>
        
        <a href="daftarulang_maba.php" target="_blank" class="btn-success">
            <i class="fas fa-print"></i> Cetak Bukti Daftar Ulang
        </a>
        
        <!-- DITOLAK -->
        <?php elseif($sudah_daftar && $status_daftar == 'ditolak'): ?>
        <div class="status-card rejected">
            <div class="status-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="status-content">
                <div class="status-title">Ditolak</div>
                <div class="status-message"><?= htmlspecialchars($keterangan ?? 'Bukti hasil tes tidak valid. Silakan upload ulang.') ?></div>
            </div>
        </div>
        
        <div class="data-card">
            <div class="data-title">Data Mahasiswa</div>
            <div class="data-grid">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Program Studi</span>
                    <span class="data-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID Pendaftaran</span>
                    <span class="data-value">PMB<?= str_pad($id_maba, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nilai Tes</span>
                    <span class="data-value"><?= number_format($nilai, 0) ?> / 100</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status Pembayaran</span>
                    <span class="data-value" style="color: <?= $warna_primary ?>;">✅ LUNAS</span>
                </div>
            </div>
        </div>
        
        <button onclick="window.location.reload()" class="btn-primary" style="border: none; cursor: pointer;">
            <i class="fas fa-redo-alt"></i> Upload Ulang
        </button>
        
        <!-- FORM UPLOAD -->
        <?php elseif($is_lulus && $pembayaran_lunas): ?>
        <div class="status-card">
            <div class="status-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="status-content">
                <div class="status-title">LULUS</div>
                <div class="status-message">Selamat! Anda dinyatakan lulus seleksi. Upload bukti hasil tes untuk daftar ulang.</div>
            </div>
        </div>
        <div class="data-card">
            <div class="data-title">Data Mahasiswa</div>
            <div class="data-grid">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Program Studi</span>
                    <span class="data-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID Pendaftaran</span>
                    <span class="data-value">PMB<?= str_pad($id_maba, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nilai Tes</span>
                    <span class="data-value"><?= number_format($nilai, 0) ?> / 100</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status Pembayaran</span>
                    <span class="data-value" style="color: <?= $warna_primary ?>;">✅ LUNAS</span>
                </div>
            </div>
        </div>
        
        <div class="upload-card">
            <div class="upload-title">Upload Bukti Hasil Tes</div>
            <form method="POST" enctype="multipart/form-data">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">Klik atau drag file ke sini</div>
                    <div class="upload-hint">Format: JPG, PNG, PDF (Max 5MB)</div>
                    <input type="file" name="bukti_hasil_tes" id="fileInput" accept=".jpg,.jpeg,.png,.pdf" hidden required>
                </div>
                
                <div class="file-preview" id="filePreview">
                    <div class="file-info">
                        <i class="fas fa-file-image" id="fileIcon"></i>
                        <div class="file-details">
                            <span class="file-name" id="fileNamePreview"></span>
                            <span class="file-size" id="fileSizePreview"></span>
                        </div>
                    </div>
                    <button type="button" class="remove-file" onclick="clearFile()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <button type="submit" name="submit_daftar" class="btn-submit" id="submitBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Kirim Bukti Hasil Tes
                </button>
            </form>
        </div>
        
        <!-- TIDAK LULUS -->
        <?php elseif(!$is_lulus): ?>
        <div class="status-card rejected">
            <div class="status-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="status-content">
                <div class="status-title">Tidak Lulus</div>
                <div class="status-message">Maaf, nilai tes Anda <?= number_format($nilai, 0) ?> belum memenuhi standar kelulusan (minimal 60).</div>
            </div>
        </div>
        
        <div class="data-card">
            <div class="data-title">Data Mahasiswa</div>
            <div class="data-grid">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Program Studi</span>
                    <span class="data-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID Pendaftaran</span>
                    <span class="data-value">PMB<?= str_pad($id_maba, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nilai Tes</span>
                    <span class="data-value"><?= number_format($nilai, 0) ?> / 100</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <a href="dashboard_maba.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
    
    <script>
        const fileInput = document.getElementById('fileInput');
        const fileNameSpan = document.getElementById('fileNamePreview');
        const fileSizeSpan = document.getElementById('fileSizePreview');
        const filePreview = document.getElementById('filePreview');
        const uploadArea = document.getElementById('uploadArea');
        const submitBtn = document.getElementById('submitBtn');
        const fileIcon = document.getElementById('fileIcon');
        
        if(uploadArea) {
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '<?= $warna_primary ?>';
                uploadArea.style.background = '<?= $warna_soft ?>';
            });
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '#E5E7EB';
                uploadArea.style.background = '#FAFAFA';
            });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#E5E7EB';
                uploadArea.style.background = '#FAFAFA';
                const file = e.dataTransfer.files[0];
                if(file) handleFile(file);
            });
        }
        
        if(fileInput) {
            fileInput.addEventListener('change', function() {
                if(this.files[0]) handleFile(this.files[0]);
                else clearFile();
            });
        }
        
        function handleFile(file) {
            if (file.size > 5 * 1024 * 1024) {
                alert('File terlalu besar! Maksimal 5MB');
                clearFile();
                return;
            }
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png', 'pdf'].includes(ext)) {
                alert('Format tidak didukung! Gunakan JPG, PNG, PDF');
                clearFile();
                return;
            }
            
            let sizeText = '';
            if (file.size < 1024) sizeText = file.size + ' B';
            else if (file.size < 1024 * 1024) sizeText = (file.size / 1024).toFixed(1) + ' KB';
            else sizeText = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
            
            if (ext === 'pdf') {
                fileIcon.className = 'fas fa-file-pdf';
                fileIcon.style.color = '#EF4444';
            } else {
                fileIcon.className = 'fas fa-file-image';
                fileIcon.style.color = '<?= $warna_primary ?>';
            }
            
            fileNameSpan.textContent = file.name;
            fileSizeSpan.textContent = sizeText;
            filePreview.classList.add('show');
            submitBtn.disabled = false;
        }
        
        function clearFile() {
            fileInput.value = '';
            filePreview.classList.remove('show');
            submitBtn.disabled = true;
        }
    </script>
</body>
</html>