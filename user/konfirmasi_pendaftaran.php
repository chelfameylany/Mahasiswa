<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../koneksi.php";

if (isset($_GET['prodi']) && !empty($_GET['prodi'])) {
    $_SESSION['pendaftaran_data']['jurusan'] = $_GET['prodi'];
}

if (!isset($_SESSION['pendaftaran_data']) || empty($_SESSION['pendaftaran_data']['nama'])) {
    header("Location: ../auth/pendaftaran-user.php");
    exit;
}

if (!isset($_SESSION['pendaftaran_data']['jurusan']) || empty($_SESSION['pendaftaran_data']['jurusan'])) {
    header("Location: pilih_prodi.php");
    exit;
}

$data = $_SESSION['pendaftaran_data'];

if (isset($_POST['konfirmasi'])) {
    $nama = mysqli_real_escape_string($koneksi, $data['nama']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $nik = mysqli_real_escape_string($koneksi, $data['nik']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $data['jenis_kelamin']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $data['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $data['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $data['alamat']);
    $asal_sekolah = mysqli_real_escape_string($koneksi, $data['asal_sekolah']);
    $tahun_lulus = mysqli_real_escape_string($koneksi, $data['tahun_lulus']);
    $jurusan = mysqli_real_escape_string($koneksi, $data['jurusan']);
    $kewarganegaraan = mysqli_real_escape_string($koneksi, $data['kewarganegaraan']);
    $agama = mysqli_real_escape_string($koneksi, $data['agama']);
    
    $check_email = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['popup'] = [
            'type' => 'error',
            'title' => 'Email Sudah Terdaftar',
            'message' => 'Silakan gunakan email lain untuk melanjutkan pendaftaran.'
        ];
        header("Location: ../auth/pendaftaran-user.php");
        exit;
    }
    
    $username = strtok($email, '@');
    $check_username = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username='$username'");
    $counter = 1;
    $original_username = $username;
    while (mysqli_num_rows($check_username) > 0) {
        $username = $original_username . $counter;
        $check_username = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username='$username'");
        $counter++;
    }
    
    $password = substr(md5(rand()), 0, 8);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $tanggal_daftar = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO calon_maba (
        nama, kewarganegaraan, nik, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat,
        asal_sekolah, tahun_lulus, jurusan, email, username, password_hash, agama, status, tanggal_daftar, status_data
    ) VALUES (
        '$nama', '$kewarganegaraan', '$nik', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir',
        '$alamat', '$asal_sekolah', '$tahun_lulus', '$jurusan', '$email', '$username', '$password_hash', '$agama', 'pending', '$tanggal_daftar', 'belum_lengkap'
    )";
    
    if (mysqli_query($koneksi, $query)) {
        $id_maba = mysqli_insert_id($koneksi);
        
        $_SESSION['daftar_success'] = true;
        $_SESSION['daftar_data'] = [
            'id_maba' => $id_maba,
            'nama' => $nama,
            'email' => $email,
            'nik' => $nik,
            'username' => $username,
            'password' => $password,
            'jurusan' => $jurusan
        ];
        
        unset($_SESSION['pendaftaran_data']);
        
        // LANGSUNG REDIRECT PAKAI HEADER
        header("Location: daftar_success.php?id=" . $id_maba);
        exit();
    } else {
        $error_message = mysqli_error($koneksi);
        echo "<!DOCTYPE html>
        <html>
        <head><title>Error</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head>
        <body style='padding:50px'>
        <div class='alert alert-danger'>
        <h4>Gagal Mendaftar!</h4>
        <p>" . htmlspecialchars($error_message) . "</p>
        <a href='../auth/pendaftaran-user.php' class='btn btn-primary'>Kembali</a>
        </div>
        </body></html>";
        exit;
    }
}

// ========== TAMPILAN HTML KONFIRMASI ==========
$prodi_teknik = ['Teknik Informatika', 'Teknik Sipil', 'Teknik Mesin', 'Teknik Elektro', 'Teknik Industri', 'Teknik Kimia', 'Arsitektur', 'Teknik Lingkungan', 'Teknik Pertambangan', 'Teknik Perkapalan', 'Teknik Geodesi', 'Teknik Nuklir'];
$is_teknik = in_array($data['jurusan'], $prodi_teknik);
$prodi_color = $is_teknik ? '#059669' : '#0d3b66';
$prodi_icon = $is_teknik ? 'bi-cpu' : 'bi-globe2';
$prodi_badge_text = $is_teknik ? 'FAKULTAS TEKNIK' : 'FAKULTAS NON-TEKNIK';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #0d6efd 60%, #0d3b66 100%); min-height: 100vh; padding: 40px 20px; }
        .container-custom { max-width: 1000px; margin: 0 auto; }
        .register-card { background: white; border-radius: 28px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; }
        .register-header { background: linear-gradient(135deg, #0d3b66, #0d6efd); color: white; padding: 28px 32px; text-align: center; }
        .register-header h1 { font-weight: 800; font-size: 26px; margin-bottom: 8px; }
        .register-header p { font-size: 14px; opacity: 0.9; }
        .register-body { padding: 32px; }
        .progress-steps { display: flex; justify-content: space-between; align-items: center; max-width: 450px; margin: 0 auto 40px; position: relative; }
        .progress-steps::before { content: ''; position: absolute; top: 24px; left: 0; right: 0; height: 3px; background: #e2e8f0; z-index: 0; }
        .step-item { text-align: center; position: relative; z-index: 1; flex: 1; }
        .step-circle { width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 800; font-size: 18px; color: #94a3b8; border: 2px solid #e2e8f0; }
        .step-item.completed .step-circle { background: linear-gradient(135deg, #10b981, #34d399); color: white; border: none; }
        .step-item.completed .step-circle i { font-size: 22px; }
        .step-item.active .step-circle { background: linear-gradient(135deg, #0d3b66, #0d6efd); color: white; border: none; box-shadow: 0 4px 12px rgba(13,110,253,0.3); }
        .step-label { font-size: 11px; font-weight: 700; color: #64748b; }
        .step-item.active .step-label { color: #0d3b66; }
        .two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-bottom: 28px; }
        .data-card { background: #fafcff; border-radius: 20px; padding: 24px; border: 1px solid #eef2ff; }
        .data-card h3 { font-size: 16px; font-weight: 800; color: #0d3b66; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .data-card h3 i { color: #0d6efd; font-size: 20px; }
        .info-row { display: flex; margin-bottom: 16px; font-size: 13px; }
        .info-label { width: 120px; font-weight: 600; color: #64748b; flex-shrink: 0; }
        .info-value { flex: 1; font-weight: 600; color: #1e293b; }
        .prodi-card { background: linear-gradient(135deg, <?= $prodi_color ?> 0%, <?= $prodi_color ?>dd 100%); border-radius: 20px; padding: 24px; color: white; position: relative; overflow: hidden; }
        .prodi-icon { font-size: 44px; margin-bottom: 20px; }
        .prodi-badge { display: inline-block; padding: 6px 16px; background: rgba(255,255,255,0.2); border-radius: 30px; font-size: 12px; font-weight: 700; margin-bottom: 20px; letter-spacing: 1px; }
        .prodi-name { font-size: 26px; font-weight: 800; margin-bottom: 16px; }
        .feature-list { background: rgba(255,255,255,0.12); border-radius: 16px; padding: 16px 20px; margin-top: 20px; }
        .feature-item { display: flex; align-items: center; gap: 12px; font-size: 12px; padding: 8px 0; font-weight: 600; }
        .warning-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 14px 20px; border-radius: 14px; margin-bottom: 28px; font-size: 12px; color: #92400e; display: flex; align-items: center; gap: 12px; }
        .action-buttons { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .btn-action { padding: 12px 32px; border-radius: 50px; font-weight: 700; font-size: 13px; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none; text-decoration: none; }
        .btn-back { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-back:hover { background: #e2e8f0; transform: translateY(-2px); }
        .btn-edit { background: white; color: #0d3b66; border: 2px solid #e2e8f0; }
        .btn-edit:hover { border-color: #0d3b66; transform: translateY(-2px); }
        .btn-confirm { background: linear-gradient(135deg, #059669, #10b981); color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.3); }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(5,150,105,0.5); }
        .loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: none; justify-content: center; align-items: center; z-index: 9999; backdrop-filter: blur(5px); }
        .loading-content { background: white; padding: 30px 40px; border-radius: 20px; text-align: center; }
        .loading-spinner { width: 50px; height: 50px; border: 4px solid #e2e8f0; border-top: 4px solid #0d6efd; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h5>Memproses Pendaftaran...</h5>
            <p style="font-size: 12px; color: #64748b; margin-top: 10px;">Mohon tunggu sebentar</p>
        </div>
    </div>

    <div class="container-custom">
        <div class="register-card">
            <div class="register-header">
                <h1><i class="bi bi-clipboard-check me-2"></i>Konfirmasi Pendaftaran</h1>
                <p>Periksa kembali data yang telah Anda isi sebelum mengirimkan pendaftaran</p>
            </div>
            
            <div class="register-body">
                <div class="progress-steps">
                    <div class="step-item completed"><div class="step-circle"><i class="bi bi-check-lg"></i></div><div class="step-label">DATA DIRI</div></div>
                    <div class="step-item completed"><div class="step-circle"><i class="bi bi-check-lg"></i></div><div class="step-label">PROGRAM STUDI</div></div>
                    <div class="step-item active"><div class="step-circle">3</div><div class="step-label">KONFIRMASI</div></div>
                </div>
                
                <div class="two-columns">
                    <div class="data-card">
                        <h3><i class="bi bi-person-badge"></i> Data Calon Mahasiswa</h3>
                        <div class="info-row"><div class="info-label">Nama Lengkap</div><div class="info-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></div></div>
                        <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?= htmlspecialchars($data['email'] ?? '-') ?></div></div>
                        <div class="info-row"><div class="info-label">NIK</div><div class="info-value"><?= htmlspecialchars($data['nik'] ?? '-') ?></div></div>
                        <div class="info-row"><div class="info-label">Jenis Kelamin</div><div class="info-value"><?= ($data['jenis_kelamin'] ?? '') == 'L' ? 'Laki-laki' : 'Perempuan' ?></div></div>
                        <div class="info-row"><div class="info-label">Tempat, Tgl Lahir</div><div class="info-value"><?= htmlspecialchars($data['tempat_lahir'] ?? '-') ?>, <?= date('d F Y', strtotime($data['tanggal_lahir'] ?? 'now')) ?></div></div>
                        <div class="info-row"><div class="info-label">Agama</div><div class="info-value"><?= htmlspecialchars($data['agama'] ?? '-') ?></div></div>
                        <div class="info-row"><div class="info-label">Kewarganegaraan</div><div class="info-value"><?= htmlspecialchars($data['kewarganegaraan'] ?? '-') ?></div></div>
                        <div class="info-row"><div class="info-label">Alamat</div><div class="info-value"><?= htmlspecialchars($data['alamat'] ?? '-') ?></div></div>
                        <div class="info-row"><div class="info-label">Asal Sekolah</div><div class="info-value"><?= htmlspecialchars($data['asal_sekolah'] ?? '-') ?> (<?= htmlspecialchars($data['tahun_lulus'] ?? '-') ?>)</div></div>
                    </div>
                    
                    <div class="prodi-card">
                        <div class="prodi-icon"><i class="bi <?= $prodi_icon ?>"></i></div>
                        <div class="prodi-badge"><i class="bi bi-award me-1"></i><?= $prodi_badge_text ?></div>
                        <div class="prodi-name"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></div>
                        <div class="feature-list">
                            <div class="feature-item"><i class="bi bi-trophy-fill"></i><span>Akreditasi Unggul</span></div>
                            <div class="feature-item"><i class="bi bi-people-fill"></i><span>Alumni Tersebar di Seluruh Indonesia</span></div>
                            <div class="feature-item"><i class="bi bi-building"></i><span>Fasilitas Kampus Modern & Lengkap</span></div>
                            <div class="feature-item"><i class="bi bi-briefcase-fill"></i><span>Peluang Magang di Perusahaan Top</span></div>
                            <div class="feature-item"><i class="bi bi-globe2"></i><span>Kerjasama Internasional</span></div>
                        </div>
                    </div>
                </div>
                
                <div class="warning-box">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><strong>Perhatian!</strong> Pastikan semua data yang Anda isi sudah benar. Data tidak dapat diubah setelah pendaftaran dikirim.</span>
                </div>
                
                <div class="action-buttons">
                    <a href="pilih_prodi.php" class="btn-action btn-back"><i class="bi bi-arrow-left"></i> Kembali Pilih Prodi</a>
                    <a href="../auth/pendaftaran-user.php" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit Data Diri</a>
                    <form method="POST" action="" style="margin: 0;">
                        <button type="submit" name="konfirmasi" class="btn-action btn-confirm">
                            <i class="bi bi-check-circle"></i> Konfirmasi & Daftar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // SCRIPT SEDERHANA UNTUK LOADING
        const form = document.querySelector('form');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        if (form) {
            form.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });
        }
    </script>
</body>
</html>