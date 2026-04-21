<?php
include "../koneksi.php";
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ================= SESSION =================
if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit();
}
$username = $_SESSION['maba'];

// ================= AMBIL DATA USER =================
$query_user = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username='$username'");
$data = mysqli_fetch_assoc($query_user);
if (!$data) die("Data tidak ditemukan.");

$id_maba = $data['id_maba'];
$jurusan_asli = $data['jurusan'] ?? 'umum';

// ===== DETEKSI WARNA =====
$jurusan_lower = strtolower(trim($jurusan_asli));
if(strpos($jurusan_lower, 'teknik') !== false) {
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_gradient_bg = "linear-gradient(135deg, #f5f9f5 0%, #e8f5e9 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #2E7D32, #1B5E20)";
    $warna_soft = "#E8F5E9";
} else {
    $warna_primary = "#2563EB";
    $warna_primary_dark = "#1E40AF";
    $warna_gradient_bg = "linear-gradient(135deg, #f0f4fa 0%, #dbeafe 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #2563EB, #1E40AF)";
    $warna_soft = "#DBEAFE";
}

// ================= UPLOAD FUNCTION =================
function uploadFile($file, $folder, $allowed, $oldFile = null) {
    if (!isset($file) || $file['error'] == 4) return null;
    if ($file['error'] !== 0) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    if (!is_dir($folder)) mkdir($folder, 0777, true);
    if ($oldFile && file_exists("$folder/$oldFile")) unlink("$folder/$oldFile");
    $newName = uniqid() . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], "$folder/$newName")) return $newName;
    return null;
}

// ================= DELETE FILE =================
if (isset($_POST['action']) && $_POST['action'] == 'delete_file') {
    $field = $_POST['field'];
    $folderMap = [
        'foto' => 'uploads/foto',
        'ijazah_pdf' => 'uploads/ijazah',
        'kartu_keluarga' => 'uploads/kk',
        'akte_kelahiran' => 'uploads/akte',
        'kartu_pelajar_ktp' => 'uploads/ktp'
    ];
    if (isset($folderMap[$field]) && !empty($data[$field])) {
        $path = $folderMap[$field] . "/" . $data[$field];
        if (file_exists($path)) unlink($path);
        mysqli_query($koneksi, "UPDATE calon_maba SET $field = NULL WHERE id_maba='$id_maba'");
    }
    mysqli_query($koneksi, "UPDATE calon_maba SET status_data='belum_lengkap' WHERE id_maba='$id_maba'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ================= CEK KELENGKAPAN =================
$wajib = ['foto','ijazah_pdf','kartu_keluarga','akte_kelahiran','kartu_pelajar_ktp'];
$isLengkap = true;
$dokumenKosong = [];
foreach ($wajib as $r) {
    if (empty($data[$r])) { 
        $isLengkap = false; 
        $dokumenKosong[] = $r;
    }
}

// ================= PROSES UPLOAD =================
$showSuccessPopup = false;
$showWarningPopup = false;

if (!$isLengkap && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload') {
    $updates = [];
    $fileChanged = false;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $fotoUpload = uploadFile($_FILES['foto'], "uploads/foto", ['jpg','jpeg','png'], $data['foto']);
        if ($fotoUpload) { $updates[] = "foto = '$fotoUpload'"; $fileChanged = true; }
    }
    if (isset($_FILES['ijazah_pdf']) && $_FILES['ijazah_pdf']['error'] == 0) {
        $ijazahUpload = uploadFile($_FILES['ijazah_pdf'], "uploads/ijazah", ['pdf'], $data['ijazah_pdf']);
        if ($ijazahUpload) { $updates[] = "ijazah_pdf = '$ijazahUpload'"; $fileChanged = true; }
    }
    if (isset($_FILES['kartu_keluarga']) && $_FILES['kartu_keluarga']['error'] == 0) {
        $kkUpload = uploadFile($_FILES['kartu_keluarga'], "uploads/kk", ['pdf'], $data['kartu_keluarga']);
        if ($kkUpload) { $updates[] = "kartu_keluarga = '$kkUpload'"; $fileChanged = true; }
    }
    if (isset($_FILES['akte_kelahiran']) && $_FILES['akte_kelahiran']['error'] == 0) {
        $akteUpload = uploadFile($_FILES['akte_kelahiran'], "uploads/akte", ['pdf'], $data['akte_kelahiran']);
        if ($akteUpload) { $updates[] = "akte_kelahiran = '$akteUpload'"; $fileChanged = true; }
    }
    if (isset($_FILES['kartu_pelajar_ktp']) && $_FILES['kartu_pelajar_ktp']['error'] == 0) {
        $ktpUpload = uploadFile($_FILES['kartu_pelajar_ktp'], "uploads/ktp", ['pdf'], $data['kartu_pelajar_ktp']);
        if ($ktpUpload) { $updates[] = "kartu_pelajar_ktp = '$ktpUpload'"; $fileChanged = true; }
    }
    if ($fileChanged && !empty($updates)) {
        $sql = "UPDATE calon_maba SET " . implode(', ', $updates) . " WHERE id_maba = '$id_maba'";
        mysqli_query($koneksi, $sql);
    }
    $query_user = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba='$id_maba'");
    $data = mysqli_fetch_assoc($query_user);
    $isLengkap = true;
    foreach ($wajib as $r) if (empty($data[$r])) $isLengkap = false;
    if ($isLengkap) {
        mysqli_query($koneksi, "UPDATE calon_maba SET status_data='lengkap' WHERE id_maba='$id_maba'");
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?warning=1");
        exit();
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) $showSuccessPopup = true;
if (isset($_GET['warning']) && $_GET['warning'] == 1) $showWarningPopup = true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isLengkap ? 'Data Lengkap' : 'Lengkapi Data' ?> - UCN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            padding: 40px 24px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* CARD UTAMA */
        .card-utama {
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        /* HEADER DI ATAS */
        .header-card {
            background: <?= $warna_gradient_card ?>;
            padding: 32px;
            text-align: center;
            color: white;
        }
        .header-card h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .header-card h1 i {
            margin-right: 12px;
        }
        .header-card p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        /* BODY ISI */
        .body-card {
            padding: 32px;
        }
        
        /* ALERT */
        .alert-box {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        .alert-info {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
        }
        
        /* UPLOAD BOX */
        .upload-box {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .upload-box:hover {
            border-color: <?= $warna_primary ?>;
            background: #f1f5f9;
        }
        .upload-label {
            font-weight: 700;
            color: <?= $warna_primary ?>;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .required {
            color: #ef4444;
        }
        
        /* PREVIEW */
        .preview-area {
            margin-top: 16px;
        }
        .preview-flex {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px;
            background: white;
            border-radius: 16px;
            flex-wrap: wrap;
        }
        .preview-thumb {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            cursor: pointer;
        }
        .preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-thumb i {
            font-size: 32px;
            color: #dc2626;
        }
        .file-detail {
            flex: 1;
        }
        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .badge-uploaded {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-empty {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-new {
            background: <?= $warna_soft ?>;
            color: <?= $warna_primary ?>;
        }
        .file-name {
            font-size: 13px;
            font-weight: 500;
            word-break: break-all;
        }
        .file-size {
            font-size: 11px;
            color: #94a3b8;
        }
        .btn-group {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-view {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-download {
            background: #d1fae5;
            color: #065f46;
        }
        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* BUTTON UTAMA */
        .btn-primary-custom {
            background: <?= $warna_gradient_card ?>;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            width: 100%;
        }
        .btn-secondary-custom {
            background: #e2e8f0;
            color: #475569;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            width: 100%;
            text-align: center;
            display: block;
            text-decoration: none;
        }
        
        /* GRID 2 KOLOM */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
        
        /* FOTO PREVIEW LARGE - UKURAN 3x4 & TANPA BINGKAI */
        .foto-large {
            width: 150px;
            height: 200px;
            object-fit: cover;
            margin-bottom: 16px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* TABLE */
        .table-dokumen {
            width: 100%;
            border-collapse: collapse;
        }
        .table-dokumen th, .table-dokumen td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .table-dokumen th {
            background: <?= $warna_soft ?>;
            font-weight: 700;
            color: <?= $warna_primary ?>;
        }
        
        /* POPUP */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }
        .popup-card {
            background: white;
            border-radius: 24px;
            max-width: 400px;
            width: 90%;
            overflow: hidden;
        }
        .popup-header {
            background: <?= $warna_gradient_card ?>;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .popup-body {
            padding: 24px;
            text-align: center;
        }
        .popup-footer {
            padding: 0 24px 24px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn-popup {
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .btn-popup-primary {
            background: <?= $warna_gradient_card ?>;
            color: white;
        }
        .btn-popup-secondary {
            background: #e2e8f0;
            color: #475569;
        }
        
        /* NOTIF */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 10001;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { right: -300px; opacity: 0; }
            to { right: 20px; opacity: 1; }
        }
    </style>
</head>
<body>

<!-- POPUP -->
<div class="popup-overlay" id="confirmPopup">
    <div class="popup-card">
        <div class="popup-header">
            <h4><i class="bi bi-shield-lock-fill me-2"></i> PERHATIAN!</h4>
        </div>
        <div class="popup-body">
            <p>Setelah semua data lengkap, data akan <strong>terkunci permanen</strong> dan tidak dapat diubah lagi.</p>
            <p class="mt-2">Apakah Anda yakin semua file sudah benar?</p>
        </div>
        <div class="popup-footer">
            <button class="btn-popup btn-popup-secondary" id="btnCancelSave">Batal</button>
            <button class="btn-popup btn-popup-primary" id="btnConfirmSave">Ya, Simpan</button>
        </div>
    </div>
</div>

<?php if ($showSuccessPopup): ?>
<div class="popup-overlay" style="display: flex;">
    <div class="popup-card">
        <div class="popup-header">
            <h4><i class="bi bi-check-circle-fill me-2"></i> DATA LENGKAP!</h4>
        </div>
        <div class="popup-body">
            <p>🎉 Selamat! Semua dokumen sudah lengkap.</p>
            <p><strong>Data Anda telah tersimpan dengan aman.</strong></p>
        </div>
        <div class="popup-footer">
            <a href="dashboard_maba.php" class="btn-popup btn-popup-primary" style="text-decoration: none;">Ke Dashboard</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($showWarningPopup): ?>
<div class="notification">
    <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b; font-size: 20px;"></i>
    <div>
        <strong>Perhatian!</strong><br>
        <small>Masih ada dokumen yang belum lengkap.</small>
    </div>
</div>
<script>setTimeout(() => { const n = document.querySelector('.notification'); if(n) n.style.display = 'none'; }, 4000);</script>
<?php endif; ?>

<div class="container">
    <div class="card-utama">
        <!-- HEADER DI ATAS -->
        <div class="header-card">
            <h1>
                <i class="bi bi-folder-check"></i> 
                <?= $isLengkap ? 'Data Lengkap' : 'Lengkapi Data' ?>
            </h1>
            <p><?= $isLengkap ? 'Semua dokumen Anda sudah lengkap' : 'Upload dokumen persyaratan untuk melanjutkan tes online' ?></p>
        </div>
        
        <!-- BODY ISI -->
        <div class="body-card">
            <?php if ($isLengkap): ?>
                <!-- TAMPILAN DATA LENGKAP -->
                <div class="alert-box alert-success">
                    <i class="bi bi-check-circle-fill" style="font-size: 22px; color: #10b981;"></i>
                    <div>
                        <strong>✅ Semua Dokumen Sudah Lengkap!</strong><br>
                        <small>Data Anda telah terkunci dan tersimpan dengan aman.</small>
                    </div>
                </div>
                
                <div class="grid-2">
                    <!-- FOTO - TANPA BINGKAI, UKURAN 3x4 -->
                    <div style="background: #f8fafc; border-radius: 20px; padding: 20px; text-align: center;">
                        <h5 style="font-weight: 700; color: <?= $warna_primary ?>; margin-bottom: 16px;">
                            <i class="bi bi-camera me-2"></i> Foto Profil
                        </h5>
                        <?php 
                        $fotoPath = "uploads/foto/" . ($data['foto'] ?? '');
                        if(!empty($data['foto']) && file_exists($fotoPath)): ?>
                            <img src="<?= $fotoPath ?>?t=<?= time() ?>" class="foto-large">
                            <div class="btn-group" style="justify-content: center;">
                                <button onclick="window.open('<?= $fotoPath ?>', '_blank')" class="btn-sm btn-view"><i class="bi bi-eye"></i> Lihat</button>
                                <a href="<?= $fotoPath ?>" download class="btn-sm btn-download"><i class="bi bi-download"></i> Download</a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><i class="bi bi-image"></i> Foto tidak tersedia</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- DOKUMEN -->
                    <div style="background: #f8fafc; border-radius: 20px; padding: 20px;">
                        <h5 style="font-weight: 700; color: <?= $warna_primary ?>; margin-bottom: 16px;">
                            <i class="bi bi-file-pdf me-2"></i> Dokumen Persyaratan
                        </h5>
                        <table class="table-dokumen">
                            <thead><tr><th>Dokumen</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php 
                                $pdfFiles = [
                                    'ijazah_pdf' => ['Ijazah', 'ijazah'],
                                    'kartu_keluarga' => ['Kartu Keluarga', 'kk'],
                                    'akte_kelahiran' => ['Akte Kelahiran', 'akte'],
                                    'kartu_pelajar_ktp' => ['KTP/Kartu Pelajar', 'ktp']
                                ];
                                foreach($pdfFiles as $key => $info):
                                    $label = $info[0];
                                    $folder = $info[1];
                                    $filePath = "uploads/$folder/" . ($data[$key] ?? '');
                                    if(!empty($data[$key]) && file_exists($filePath)):
                                ?>
                                <tr>
                                    <td><i class="bi bi-file-earmark-pdf text-danger me-2"></i> <?= $label ?></td>
                                    <td>
                                        <div class="btn-group" style="margin: 0;">
                                            <button onclick="window.open('<?= $filePath ?>', '_blank')" class="btn-sm btn-view"><i class="bi bi-eye"></i></button>
                                            <a href="<?= $filePath ?>" download class="btn-sm btn-download"><i class="bi bi-download"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="dashboard_maba.php" class="btn-primary-custom" style="display: inline-block; text-decoration: none; width: auto; padding: 12px 32px;">
                        <i class="bi bi-house-door me-2"></i> Kembali ke Dashboard
                    </a>
                </div>
                
            <?php else: ?>
                <!-- FORM UPLOAD -->
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="action" value="upload">
                    
                    <div class="alert-box alert-warning">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #f59e0b;"></i>
                        <div>
                            <strong><?= count($dokumenKosong) ?> Dokumen Masih Kosong</strong><br>
                            <small>Lengkapi semua dokumen di bawah ini</small>
                        </div>
                    </div>
                    
                    <div class="alert-box alert-info">
                        <i class="bi bi-info-circle-fill" style="font-size: 20px; color: #3b82f6;"></i>
                        <div>
                            <strong>Informasi Penting!</strong><br>
                            <small>Setelah lengkap, data akan <strong>terkunci permanen</strong> dan tidak bisa diubah.</small>
                        </div>
                    </div>
                    
                    <!-- FOTO -->
                    <div class="upload-box">
                        <div class="upload-label"><i class="bi bi-camera"></i> Foto Profil (3x4) <span class="required">*</span></div>
                        <input type="file" class="form-control" name="foto" accept="image/*" id="fotoInput" style="border-radius: 12px;">
                        <div class="preview-area" id="fotoPreview">
                            <?php 
                            $fotoPath = "uploads/foto/" . ($data['foto'] ?? '');
                            if(!empty($data['foto']) && file_exists($fotoPath)): ?>
                                <div class="preview-flex">
                                    <div class="preview-thumb" onclick="window.open('<?= $fotoPath ?>', '_blank')">
                                        <img src="<?= $fotoPath ?>?t=<?= time() ?>">
                                    </div>
                                    <div class="file-detail">
                                        <span class="badge-status badge-uploaded"><i class="bi bi-check-circle me-1"></i> Sudah Upload</span>
                                        <div class="file-name"><?= htmlspecialchars($data['foto']) ?></div>
                                        <div class="btn-group">
                                            <button type="button" class="btn-sm btn-view" onclick="window.open('<?= $fotoPath ?>', '_blank')">Preview</button>
                                            <a href="<?= $fotoPath ?>" download class="btn-sm btn-download">Download</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete_file">
                                                <input type="hidden" name="field" value="foto">
                                                <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Hapus foto ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge-status badge-empty"><i class="bi bi-x-circle me-1"></i> Belum upload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- IJAZAH -->
                    <div class="upload-box">
                        <div class="upload-label"><i class="bi bi-file-earmark-text"></i> Ijazah (PDF) <span class="required">*</span></div>
                        <input type="file" class="form-control" name="ijazah_pdf" accept="application/pdf" id="ijazahInput" style="border-radius: 12px;">
                        <div class="preview-area" id="ijazahPreview">
                            <?php 
                            $ijazahPath = "uploads/ijazah/" . ($data['ijazah_pdf'] ?? '');
                            if(!empty($data['ijazah_pdf']) && file_exists($ijazahPath)): ?>
                                <div class="preview-flex">
                                    <div class="preview-thumb" onclick="window.open('<?= $ijazahPath ?>', '_blank')"><i class="bi bi-file-earmark-pdf"></i></div>
                                    <div class="file-detail">
                                        <span class="badge-status badge-uploaded"><i class="bi bi-check-circle me-1"></i> Sudah Upload</span>
                                        <div class="file-name"><?= htmlspecialchars($data['ijazah_pdf']) ?></div>
                                        <div class="btn-group">
                                            <button type="button" class="btn-sm btn-view" onclick="window.open('<?= $ijazahPath ?>', '_blank')">Preview</button>
                                            <a href="<?= $ijazahPath ?>" download class="btn-sm btn-download">Download</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete_file">
                                                <input type="hidden" name="field" value="ijazah_pdf">
                                                <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Hapus ijazah ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge-status badge-empty"><i class="bi bi-x-circle me-1"></i> Belum upload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- KK -->
                    <div class="upload-box">
                        <div class="upload-label"><i class="bi bi-file-earmark-pdf"></i> Kartu Keluarga (PDF) <span class="required">*</span></div>
                        <input type="file" class="form-control" name="kartu_keluarga" accept="application/pdf" id="kkInput" style="border-radius: 12px;">
                        <div class="preview-area" id="kkPreview">
                            <?php 
                            $kkPath = "uploads/kk/" . ($data['kartu_keluarga'] ?? '');
                            if(!empty($data['kartu_keluarga']) && file_exists($kkPath)): ?>
                                <div class="preview-flex">
                                    <div class="preview-thumb" onclick="window.open('<?= $kkPath ?>', '_blank')"><i class="bi bi-file-earmark-pdf"></i></div>
                                    <div class="file-detail">
                                        <span class="badge-status badge-uploaded"><i class="bi bi-check-circle me-1"></i> Sudah Upload</span>
                                        <div class="file-name"><?= htmlspecialchars($data['kartu_keluarga']) ?></div>
                                        <div class="btn-group">
                                            <button type="button" class="btn-sm btn-view" onclick="window.open('<?= $kkPath ?>', '_blank')">Preview</button>
                                            <a href="<?= $kkPath ?>" download class="btn-sm btn-download">Download</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete_file">
                                                <input type="hidden" name="field" value="kartu_keluarga">
                                                <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Hapus KK ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge-status badge-empty"><i class="bi bi-x-circle me-1"></i> Belum upload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- AKTE -->
                    <div class="upload-box">
                        <div class="upload-label"><i class="bi bi-file-earmark-pdf"></i> Akte Kelahiran (PDF) <span class="required">*</span></div>
                        <input type="file" class="form-control" name="akte_kelahiran" accept="application/pdf" id="akteInput" style="border-radius: 12px;">
                        <div class="preview-area" id="aktePreview">
                            <?php 
                            $aktePath = "uploads/akte/" . ($data['akte_kelahiran'] ?? '');
                            if(!empty($data['akte_kelahiran']) && file_exists($aktePath)): ?>
                                <div class="preview-flex">
                                    <div class="preview-thumb" onclick="window.open('<?= $aktePath ?>', '_blank')"><i class="bi bi-file-earmark-pdf"></i></div>
                                    <div class="file-detail">
                                        <span class="badge-status badge-uploaded"><i class="bi bi-check-circle me-1"></i> Sudah Upload</span>
                                        <div class="file-name"><?= htmlspecialchars($data['akte_kelahiran']) ?></div>
                                        <div class="btn-group">
                                            <button type="button" class="btn-sm btn-view" onclick="window.open('<?= $aktePath ?>', '_blank')">Preview</button>
                                            <a href="<?= $aktePath ?>" download class="btn-sm btn-download">Download</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete_file">
                                                <input type="hidden" name="field" value="akte_kelahiran">
                                                <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Hapus akte ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge-status badge-empty"><i class="bi bi-x-circle me-1"></i> Belum upload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- KTP -->
                    <div class="upload-box">
                        <div class="upload-label"><i class="bi bi-file-earmark-pdf"></i> Kartu Pelajar / KTP (PDF) <span class="required">*</span></div>
                        <input type="file" class="form-control" name="kartu_pelajar_ktp" accept="application/pdf" id="ktpInput" style="border-radius: 12px;">
                        <div class="preview-area" id="ktpPreview">
                            <?php 
                            $ktpPath = "uploads/ktp/" . ($data['kartu_pelajar_ktp'] ?? '');
                            if(!empty($data['kartu_pelajar_ktp']) && file_exists($ktpPath)): ?>
                                <div class="preview-flex">
                                    <div class="preview-thumb" onclick="window.open('<?= $ktpPath ?>', '_blank')"><i class="bi bi-file-earmark-pdf"></i></div>
                                    <div class="file-detail">
                                        <span class="badge-status badge-uploaded"><i class="bi bi-check-circle me-1"></i> Sudah Upload</span>
                                        <div class="file-name"><?= htmlspecialchars($data['kartu_pelajar_ktp']) ?></div>
                                        <div class="btn-group">
                                            <button type="button" class="btn-sm btn-view" onclick="window.open('<?= $ktpPath ?>', '_blank')">Preview</button>
                                            <a href="<?= $ktpPath ?>" download class="btn-sm btn-download">Download</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete_file">
                                                <input type="hidden" name="field" value="kartu_pelajar_ktp">
                                                <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Hapus file ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge-status badge-empty"><i class="bi bi-x-circle me-1"></i> Belum upload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mt-4" style="display: flex; gap: 16px;">
                        <div style="flex: 1;">
                            <a href="dashboard_maba.php" class="btn-secondary-custom" style="text-decoration: none;">
                                <i class="bi bi-arrow-left me-2"></i> Kembali
                            </a>
                        </div>
                        <div style="flex: 1;">
                            <button type="button" id="btnSubmitForm" class="btn-primary-custom">
                                <i class="bi bi-save me-2"></i> Simpan Data
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// PREVIEW FOTO
const fotoInput = document.getElementById('fotoInput');
if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const preview = document.getElementById('fotoPreview');
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="preview-flex">
                        <div class="preview-thumb"><img src="${e.target.result}"></div>
                        <div class="file-detail">
                            <span class="badge-status badge-new"><i class="bi bi-arrow-up-circle me-1"></i> File Baru</span>
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${(file.size / 1024).toFixed(2)} KB</div>
                        </div>
                    </div>
                `;
            }
            reader.readAsDataURL(file);
        }
    });
}

// PREVIEW PDF
const pdfItems = [
    { input: 'ijazahInput', preview: 'ijazahPreview' },
    { input: 'kkInput', preview: 'kkPreview' },
    { input: 'akteInput', preview: 'aktePreview' },
    { input: 'ktpInput', preview: 'ktpPreview' }
];
pdfItems.forEach(function(item) {
    const input = document.getElementById(item.input);
    if (input) {
        input.addEventListener('change', function() {
            const preview = document.getElementById(item.preview);
            if (this.files && this.files[0]) {
                const file = this.files[0];
                preview.innerHTML = `
                    <div class="preview-flex">
                        <div class="preview-thumb"><i class="bi bi-file-earmark-pdf"></i></div>
                        <div class="file-detail">
                            <span class="badge-status badge-new"><i class="bi bi-arrow-up-circle me-1"></i> File Baru</span>
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${(file.size / 1024).toFixed(2)} KB</div>
                        </div>
                    </div>
                `;
            }
        });
    }
});

// SUBMIT CONFIRM
const btnSubmit = document.getElementById('btnSubmitForm');
const confirmPopup = document.getElementById('confirmPopup');
const btnCancel = document.getElementById('btnCancelSave');
const btnConfirm = document.getElementById('btnConfirmSave');
const uploadForm = document.getElementById('uploadForm');

if (btnSubmit && confirmPopup && btnCancel && btnConfirm && uploadForm) {
    btnSubmit.addEventListener('click', function(e) {
        e.preventDefault();
        confirmPopup.style.display = 'flex';
    });
    btnCancel.addEventListener('click', function() {
        confirmPopup.style.display = 'none';
    });
    btnConfirm.addEventListener('click', function() {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Menyimpan...';
        uploadForm.submit();
    });
    confirmPopup.addEventListener('click', function(e) {
        if (e.target === confirmPopup) confirmPopup.style.display = 'none';
    });
}
</script>
</body>
</html>