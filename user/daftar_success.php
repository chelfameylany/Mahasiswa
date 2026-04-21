<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../koneksi.php";

$id_maba = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_maba > 0) {
    $query = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba = '$id_maba'");
    $data = mysqli_fetch_assoc($query);
    
    if ($data && isset($_SESSION['daftar_data']['password'])) {
        $data['password'] = $_SESSION['daftar_data']['password'];
    }
}

// Jika tidak ada data, tampilkan error
if (!$data) {
    die("Data tidak ditemukan. ID: $id_maba");
}

// ... sisanya HTML sama seperti sebelumnya
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Berhasil - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #0d3b66; --secondary-color: #0d6efd; --success-color: #22c55e; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #0d6efd 60%, #0d3b66 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .success-container { max-width: 700px; width: 100%; }
        .success-card { background: white; border-radius: 24px; padding: 3rem; box-shadow: 0 25px 60px rgba(13, 59, 102, 0.15); text-align: center; }
        .success-icon { font-size: 5rem; color: var(--success-color); margin-bottom: 1.5rem; animation: bounce 1s ease infinite alternate; }
        @keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-10px); } }
        .success-title { font-weight: 900; color: var(--primary-color); margin-bottom: 1rem; }
        .success-subtitle { color: #64748b; margin-bottom: 2rem; font-size: 1.1rem; }
        .info-card { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 16px; padding: 2rem; margin: 2rem 0; text-align: left; border-left: 5px solid var(--secondary-color); }
        .info-item { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .info-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .info-label { font-weight: 600; color: var(--primary-color); display: block; margin-bottom: 0.25rem; }
        .info-value { color: #475569; font-size: 1.1rem; }
        .password-box { background: #0d3b66; color: white; padding: 1rem; border-radius: 10px; font-family: monospace; font-size: 1.2rem; letter-spacing: 2px; margin: 1rem 0; cursor: pointer; transition: all 0.3s ease; }
        .password-box:hover { background: #0d6efd; transform: scale(1.02); }
        .alert-warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 0.875rem 2rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(13, 59, 102, 0.2); }
        .btn-outline-primary { border-color: var(--primary-color); color: var(--primary-color); padding: 0.875rem 2rem; border-radius: 12px; font-weight: 600; }
        .btn-outline-primary:hover { background: var(--primary-color); color: white; }
        .step-guide { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0; }
        .step-item { display: flex; align-items: center; margin-bottom: 1rem; }
        .step-number { background: var(--secondary-color); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-right: 1rem; }
        @media print { .btn-primary, .btn-outline-primary, .action-buttons { display: none !important; } body { background: white; padding: 0; } .success-card { box-shadow: none; padding: 0; } }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h1 class="success-title">Pendaftaran Berhasil!</h1>
            <p class="success-subtitle">Selamat, Anda telah terdaftar sebagai calon mahasiswa Universitas Cendekia Nusantara</p>
            
            <div class="info-card">
                <h5 class="mb-4" style="color: var(--primary-color);">
                    <i class="bi bi-person-badge me-2"></i>Informasi Pendaftaran Anda
                </h5>
                
                <div class="info-item">
                    <span class="info-label">ID Pendaftaran</span>
                    <span class="info-value">PMB<?= str_pad($data['id_maba'], 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nama Lengkap</span>
                    <span class="info-value"><?= htmlspecialchars($data['nama']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($data['email']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">NIK</span>
                    <span class="info-value"><?= htmlspecialchars($data['nik'] ?? '-') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Program Studi</span>
                    <span class="info-value"><?= htmlspecialchars($data['jurusan'] ?? '-') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Username untuk Login</span>
                    <span class="info-value"><?= htmlspecialchars($data['username']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Password Sementara</span>
                    <div class="password-box" id="passwordBox"><?= htmlspecialchars($data['password']) ?></div>
                    <small class="text-muted"><i class="bi bi-info-circle"></i> Klik password untuk menyalin</small>
                </div>
                <div class="alert alert-warning mt-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                        <div><strong class="d-block mb-1">Catat dan Simpan Password Anda!</strong><span>Password ini digunakan untuk login ke sistem. Password tidak dapat diambil kembali jika lupa.</span></div>
                    </div>
                </div>
            </div>
            
            <p class="text-muted mb-4"><i class="bi bi-info-circle me-2"></i>Tim admin kami akan menghubungi Anda melalui email untuk informasi selanjutnya.</p>
            
            <div class="step-guide">
                <h6 class="mb-3" style="color: var(--primary-color);"><i class="bi bi-list-ol me-2"></i>Langkah Selanjutnya</h6>
                <div class="step-item"><div class="step-number">1</div><span>Login ke sistem dengan username dan password di atas</span></div>
                <div class="step-item"><div class="step-number">2</div><span>Ikuti tes online sesuai jadwal yang diberikan</span></div>
                <div class="step-item"><div class="step-number">3</div><span>Tunggu pengumuman hasil seleksi melalui email</span></div>
            </div>
            
         <div class="d-grid gap-3 mt-4 action-buttons">
    <a href="../auth/login_maba.php" class="btn btn-primary">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login ke Sistem Tes Online
    </a>
    <a href="../dashboard_utama.php" class="btn btn-outline-primary">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard Utama
    </a>
</div>
        
        <div class="text-center text-muted mt-4">
            <small><i class="bi bi-headset me-1"></i>Butuh bantuan? Hubungi panitia PMB: (021) 1234-5678 | pmb@ucn.ac.id</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const passwordBox = document.getElementById('passwordBox');
        if (passwordBox) {
            passwordBox.addEventListener('click', function() {
                const password = this.textContent.trim();
                navigator.clipboard.writeText(password).then(function() {
                    const originalText = passwordBox.textContent;
                    const originalBg = passwordBox.style.background;
                    passwordBox.textContent = '✓ Tersalin ke clipboard!';
                    passwordBox.style.background = '#22c55e';
                    setTimeout(() => {
                        passwordBox.textContent = originalText;
                        passwordBox.style.background = originalBg;
                    }, 2000);
                }).catch(function() {
                    alert('Gagal menyalin password. Silakan salin secara manual.');
                });
            });
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                if (confirm('Cetak informasi pendaftaran?')) window.print();
            }
        });
    </script>
</body>
</html>