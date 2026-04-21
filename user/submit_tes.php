<?php
session_start();
include "../koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// ===== PAKAI SESSION MABA (KONSISTEN DENGAN DASHBOARD) =====
if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit;
}

// Ambil id_maba dari session username
$username = $_SESSION['maba'];
$query_user = mysqli_query($koneksi, "SELECT id_maba, nama FROM calon_maba WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    header("Location: logout_maba.php");
    exit;
}

$id_maba = $user['id_maba'];
$nama_user = $user['nama'];

// ===== CEK SUDAH PERNAH TES =====
$cek = mysqli_query($koneksi, "SELECT * FROM hasil_tes WHERE id_maba='$id_maba'");
if (mysqli_num_rows($cek) > 0) {
    header("Location: hasil_tes_maba.php");
    exit;
}

// ===== PROSES JAWABAN =====
$total_soal = 0;
$total_benar = 0;

// Ambil data jawaban dari POST
$jawaban_json = $_POST['jawaban_json'] ?? '';
$jawaban_data = [];

if (!empty($jawaban_json)) {
    $jawaban_data = json_decode($jawaban_json, true);
}

if (empty($jawaban_data) && isset($_POST['jawaban'])) {
    $jawaban_data = $_POST['jawaban'];
}

// Jika masih kosong, coba ambil dari $_POST langsung
if (empty($jawaban_data)) {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'jawaban') !== false && !is_array($value)) {
            $id_soal = str_replace('jawaban', '', $key);
            $id_soal = preg_replace('/[^0-9]/', '', $id_soal);
            if (!empty($id_soal)) {
                $jawaban_data['jawaban[' . $id_soal . ']'] = $value;
            }
        }
    }
}

// Proses setiap jawaban
$jurusan = $_POST['jurusan'] ?? 'umum';
$tabel_soal = ($jurusan == 'teknik') ? 'soal_tes_teknik' : 'soal_tes';
$tabel_jawaban = ($jurusan == 'teknik') ? 'jawaban_maba_teknik' : 'jawaban_maba';

foreach ($jawaban_data as $key => $jawaban_user) {
    preg_match('/jawaban\[(\d+)\]/', $key, $matches);
    
    if (isset($matches[1])) {
        $id_soal = mysqli_real_escape_string($koneksi, $matches[1]);
        $jawaban_user = mysqli_real_escape_string($koneksi, $jawaban_user);
        
        // Hapus jawaban lama jika ada
        mysqli_query($koneksi, "DELETE FROM $tabel_jawaban WHERE id_maba='$id_maba' AND id_soal='$id_soal'");
        
        // Simpan jawaban baru
        mysqli_query($koneksi, "INSERT INTO $tabel_jawaban (id_maba, id_soal, jawaban)
                                VALUES ('$id_maba', '$id_soal', '$jawaban_user')");
        
        // Cek kebenaran jawaban
        $query_cek = mysqli_query($koneksi, "SELECT jawaban_benar FROM $tabel_soal WHERE id_soal='$id_soal'");
        $soal = mysqli_fetch_assoc($query_cek);
        
        $total_soal++;
        
        if ($soal && trim($jawaban_user) == trim($soal['jawaban_benar'])) {
            $total_benar++;
        }
    }
}

// ===== HITUNG NILAI =====
$nilai = ($total_soal > 0) ? ($total_benar / $total_soal) * 100 : 0;
$nilai_format = number_format($nilai, 2);

// ===== MINIMAL LULUS 80 =====
$status_lulus = ($nilai >= 80) ? 'lulus' : 'tidak_lulus';

// ===== DURASI PENGERJAAN =====
$durasi_total = 7200; // 2 jam
$sisa_waktu = $_POST['sisa_waktu'] ?? $durasi_total;
$durasi_detik = $durasi_total - intval($sisa_waktu);
if ($durasi_detik < 0) $durasi_detik = 0;

$total_salah = $total_soal - $total_benar;

// ===== SIMPAN HASIL TES =====
$insert = mysqli_query($koneksi, "INSERT INTO hasil_tes 
    (id_maba, jumlah_benar, jumlah_salah, nilai, status_lulus, tanggal_tes, total_soal, durasi_detik) 
    VALUES 
    ('$id_maba', '$total_benar', '$total_salah', '$nilai_format', '$status_lulus', NOW(), '$total_soal', '$durasi_detik')");

// ===== CEK APAKAH INSERT BERHASIL =====
if (!$insert) {
    die("Error menyimpan hasil tes: " . mysqli_error($koneksi));
}

// ===== WAKTU TUNGGU 5 MENIT (300 DETIK) =====
$waktu_tunggu_detik = 300; // 5 menit
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Tes Diproses - Universitas Nusantara Mandiri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, #ffffff 0%, #0d6efd 55%, #0d3b66 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container-guide {
            max-width: 500px;
            width: 100%;
            animation: fadeInUp 0.8s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .guide-card {
            background: white;
            border-radius: 40px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(13, 59, 102, 0.4);
            text-align: center;
        }
        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0d6efd, #0d3b66);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }
        h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0d3b66;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .timer-box {
            background: linear-gradient(135deg, #0d3b66, #0d6efd);
            border-radius: 24px;
            padding: 30px;
            color: white;
            margin: 25px 0;
        }
        .timer-display {
            font-size: 64px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            letter-spacing: 5px;
        }
        .timer-label {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 10px;
        }
        .info-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-size: 14px;
        }
        .info-value {
            font-weight: 700;
            color: #0d3b66;
            font-size: 15px;
        }
        .alert-info {
            background: #e6f4ff;
            color: #0056b3;
            border-radius: 16px;
            padding: 12px;
            font-size: 13px;
            margin: 20px 0;
        }
        .btn-dashboard {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #0d6efd, #0d3b66);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 16px;
            font-weight: 600;
            text-decoration: none;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-dashboard:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(13, 59, 102, 0.3);
            color: white;
        }
        .check-animation {
            width: 60px;
            height: 60px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: white;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spinner-circle {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        .text-muted-small {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container-guide">
    <div class="guide-card">
        <div class="spinner-circle"></div>
        
        <h1>✓ Tes Berhasil Dikirim</h1>
        <p class="subtitle">Terima kasih telah mengerjakan tes seleksi, <?= htmlspecialchars($nama_user) ?></p>
        
        <div class="info-card">
            <div class="info-item">
                <span class="info-label"><i class="fas fa-file-alt me-2"></i> Total Soal Dijawab</span>
                <span class="info-value"><?= $total_soal ?> Soal</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-check-circle me-2"></i> Jawaban Terekam</span>
                <span class="info-value"><?= $total_soal ?> Jawaban</span>
            </div>
        </div>
        
        <div class="timer-box">
            <div class="timer-display" id="timerDisplay">05:00</div>
            <div class="timer-label">⏰ Hasil akan tersedia dalam</div>
        </div>
        
        <div class="alert-info">
            <i class="fas fa-info-circle me-2"></i> 
            Hasil tes akan muncul otomatis setelah <strong>5 menit</strong>. 
            Halaman akan refresh otomatis.
        </div>
        
        <a href="dashboard_maba.php" class="btn-dashboard">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
        
        <div class="text-muted-small">
            <i class="fas fa-shield-alt me-1"></i> Data jawaban Anda telah tersimpan dengan aman
        </div>
    </div>
</div>

<script>
    // Timer 5 MENIT = 300 detik
    let totalDetik = <?= $waktu_tunggu_detik ?>;
    const timerDisplay = document.getElementById('timerDisplay');
    const jurusan = "<?= $jurusan ?>";
    
    function updateTimer() {
        if (totalDetik <= 0) {
            // Timer habis, redirect ke halaman hasil
            window.location.href = "hasil_tes_maba.php";
            return;
        }
        
        let menit = Math.floor(totalDetik / 60);
        let detik = totalDetik % 60;
        
        let menitStr = menit.toString().padStart(2, '0');
        let detikStr = detik.toString().padStart(2, '0');
        
        timerDisplay.textContent = menitStr + ':' + detikStr;
        totalDetik--;
    }
    
    // Hapus data localStorage setelah submit
    localStorage.removeItem(`tes_answers_${jurusan}`);
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(`ragu_soal_${jurusan}`)) {
            localStorage.removeItem(key);
        }
    }
    
    // Jalankan timer
    setInterval(updateTimer, 1000);
    updateTimer();
</script>

</body>
</html>
<?php
exit;
?>