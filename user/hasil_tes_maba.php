<?php
session_start();
include "../koneksi.php";

// ===== PERBAIKAN: PAKAI SESSION MABA (KONSISTEN DENGAN DASHBOARD) =====
if(!isset($_SESSION['maba'])){
    header("Location: login_maba.php");
    exit;
}

// Ambil id_maba dari session username
$username = $_SESSION['maba'];
$query_user = mysqli_query($koneksi, "SELECT id_maba, jurusan FROM calon_maba WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);

if(!$user){
    header("Location: login_maba.php");
    exit;
}

$id_maba = $user['id_maba'];
$jurusan_asli = $user['jurusan'] ?? 'umum';

// ===== DETEKSI JURUSAN UNTUK WARNA =====
$jurusan_mahasiswa_lower = strtolower(trim($jurusan_asli));
if(strpos($jurusan_mahasiswa_lower, 'teknik') !== false) {
    // WARNA HIJAU UNTUK TEKNIK
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";
    $warna_gradient = "linear-gradient(135deg, #2E7D32, #1B5E20)";
    $warna_soft = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan = "fa-gear";
} else {
    // WARNA BIRU UNTUK UMUM
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #DBEAFE 0%, #FFFFFF 100%)";
    $warna_gradient = "linear-gradient(135deg, #3B82F6, #2563EB)";
    $warna_soft = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan = "fa-users";
}

// ===== CEK APAKAH ADA HASIL TES =====
$query_hasil = mysqli_query($koneksi, "SELECT * FROM hasil_tes WHERE id_maba='$id_maba' ORDER BY tanggal_tes DESC LIMIT 1");
$hasil = mysqli_fetch_assoc($query_hasil);

// JIKA BELUM ADA HASIL TES
if(!$hasil){
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Belum Ada Hasil Tes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    background: <?= $warna_gradient_bg ?>;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.popup-card {
    background: white;
    border-radius: 26px;
    padding: 40px 36px;
    width: 420px;
    text-align: center;
    box-shadow: 0 30px 80px rgba(0,0,0,0.15);
}
.popup-icon { font-size: 64px; margin-bottom: 12px; color: <?= $warna_primary ?>; }
.popup-card h4 { font-weight: 800; color: #1F2937; margin-bottom: 10px; }
.btn-dashboard-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: white;
    color: <?= $warna_primary ?>;
    border: 1.5px solid <?= $warna_primary ?>;
    border-radius: 14px;
    padding: 14px 26px;
    font-weight: 600;
    text-decoration: none;
    width: 100%;
    transition: all 0.2s;
}
.btn-dashboard-outline:hover { background: <?= $warna_primary ?>; color: white; }
</style>
</head>
<body>
<div class="popup-card">
    <div class="popup-icon"><i class="bi bi-pencil-square"></i></div>
    <h4>Belum Ada Hasil Tes</h4>
    <p style="margin-bottom: 22px;">Silakan kerjakan tes terlebih dahulu untuk melihat hasil seleksi kamu.</p>
    <a href="dashboard_maba.php" class="btn-dashboard-outline"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
</div>
</body>
</html>
<?php
exit;
}

// ===== CEK APAKAH MASIH DALAM MASA TUNGGU 5 MENIT =====
$tanggal_tes = strtotime($hasil['tanggal_tes']);
$waktu_buka = $tanggal_tes + (5 * 60); // +5 menit
$waktu_sekarang = time();
$sisa_detik = $waktu_buka - $waktu_sekarang;

// JIKA MASIH DALAM 5 MENIT TUNGGU
if($sisa_detik > 0 && $sisa_detik <= 300){
    $tanggal_tes_format = date('d/m/Y H:i:s', $tanggal_tes);
    $tanggal_buka_format = date('d/m/Y H:i:s', $waktu_buka);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Menunggu Hasil Tes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    background: <?= $warna_gradient_bg ?>;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.popup-card {
    background: white;
    border-radius: 26px;
    padding: 40px 36px;
    width: 500px;
    max-width: 95%;
    box-shadow: 0 30px 80px rgba(0,0,0,0.15);
    text-align: center;
}
.popup-card h4 { font-weight: 800; color: #1F2937; margin-bottom: 15px; font-size: 24px; }
.popup-icon { font-size: 70px; margin-bottom: 15px; color: #f59e0b; }
.timer-box {
    background: <?= $warna_gradient ?>;
    color: white;
    padding: 30px 20px;
    border-radius: 20px;
    margin: 25px 0;
}
.timer-display { font-size: 72px; font-weight: 800; font-family: 'Courier New', monospace; letter-spacing: 8px; }
.timer-label { font-size: 16px; font-weight: 600; margin-bottom: 10px; }
.info-tes {
    background: #f8fafc;
    border-radius: 16px;
    padding: 15px;
    margin: 20px 0;
    text-align: left;
}
.info-tes-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #e2e8f0;
}
.info-tes-item:last-child { border-bottom: none; }
.info-tes-label { color: #64748b; font-size: 13px; }
.info-tes-value { font-weight: 700; color: #1F2937; font-size: 14px; }
.btn-dashboard-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: white;
    color: <?= $warna_primary ?>;
    border: 1.5px solid <?= $warna_primary ?>;
    border-radius: 14px;
    padding: 14px 26px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    width: 100%;
    transition: all 0.2s ease;
}
.btn-dashboard-outline:hover { background: <?= $warna_primary ?>; color: white; }
.alert-info {
    background: <?= $warna_soft ?>;
    color: <?= $warna_primary ?>;
    border-radius: 12px;
    padding: 12px;
    font-size: 13px;
    margin-top: 15px;
}
</style>
</head>
<body>
<div class="popup-card">
    <div class="popup-icon"><i class="bi bi-hourglass-split"></i></div>
    <h4>Hasil Tes Sedang Diproses</h4>
    <p>Harap tunggu <strong>5 menit</strong> setelah tes selesai untuk melihat hasil</p>
    <div class="info-tes">
        <div class="info-tes-item">
            <span class="info-tes-label">Waktu Selesai Tes</span>
            <span class="info-tes-value"><?= $tanggal_tes_format ?> WIB</span>
        </div>
        <div class="info-tes-item">
            <span class="info-tes-label">Hasil Akan Dibuka</span>
            <span class="info-tes-value"><?= $tanggal_buka_format ?> WIB</span>
        </div>
    </div>
    <div class="timer-box">
        <div class="timer-label">⏰ Waktu menuju pembukaan hasil:</div>
        <div class="timer-display" id="timerDisplay">05:00</div>
    </div>
    <div class="alert-info"><i class="bi bi-info-circle-fill"></i> Halaman akan otomatis refresh saat hasil sudah bisa dilihat</div>
    <a href="dashboard_maba.php" class="btn-dashboard-outline"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
</div>
<script>
let totalDetik = <?= $sisa_detik ?>;
const timerDisplay = document.getElementById('timerDisplay');
function updateTimer() {
    if (totalDetik <= 0) { window.location.reload(); return; }
    let menit = Math.floor(totalDetik / 60);
    let detik = totalDetik % 60;
    timerDisplay.textContent = String(menit).padStart(2,'0') + ':' + String(detik).padStart(2,'0');
    totalDetik--;
}
setInterval(updateTimer, 1000);
updateTimer();
</script>
</body>
</html>
<?php
exit;
}

// ===== AMBIL DATA LENGKAP UNTUK TAMPILAN HASIL =====
$query = "
SELECT 
  c.nama,
  c.email,
  c.jurusan,
  h.nilai,
  h.jumlah_benar,
  h.jumlah_salah,
  h.total_soal,
  h.durasi_detik,
  h.tanggal_tes,
  h.status_lulus
FROM calon_maba c
LEFT JOIN hasil_tes h ON c.id_maba = h.id_maba
WHERE c.id_maba = $id_maba
ORDER BY h.tanggal_tes DESC
LIMIT 1
";

$data = mysqli_query($koneksi, $query);
$row = mysqli_fetch_assoc($data);

// ===== TAMPILKAN HASIL =====
$nilai = floatval($row['nilai']);
$is_lulus = ($row['status_lulus'] == 'lulus');
$status = $is_lulus ? "LULUS" : "TIDAK LULUS";
$badge_class = $is_lulus ? "badge-lulus" : "badge-tidak";
$score_class = $is_lulus ? "score-green" : "score-red";

$menit = floor($row['durasi_detik'] / 60);
$detik = $row['durasi_detik'] % 60;
$inisial = strtoupper(substr($row['nama'], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Tes Saya</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    background: <?= $warna_gradient_bg ?>;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.card-main {
    background: white;
    border-radius: 26px;
    padding: 34px 38px;
    width: 720px;
    max-width: 95%;
    box-shadow: 0 30px 80px rgba(0,0,0,0.15);
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}
.header h4 { font-weight: 800; margin: 0; color: #1F2937; font-size: 24px; }
.badge-status-top { padding: 7px 18px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.badge-lulus { background: <?= $warna_primary ?>; color: white; }
.badge-tidak { background: #ef4444; color: white; }
.profile { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
.avatar {
    width: 56px; height: 56px; border-radius: 50%;
    background: <?= $warna_gradient ?>;
    color: white; display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 600;
}
.profile-info b { display: block; font-size: 18px; color: #1F2937; }
.profile-info span { font-size: 13px; color: #64748b; }
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    margin: 22px 0;
}
.info-box {
    background: #f8fafc;
    border-radius: 14px;
    padding: 12px 16px;
    border-left: 4px solid <?= $warna_primary ?>;
}
.info-box span { font-size: 12px; color: #64748b; display: block; margin-bottom: 4px; text-transform: uppercase; }
.info-box b { font-size: 16px; font-weight: 700; color: #1F2937; }
.score-wrap {
    background: <?= $warna_gradient ?>;
    border-radius: 22px;
    padding: 26px;
    text-align: center;
    color: white;
    margin-top: 10px;
}
.score-wrap h1 { font-size: 64px; margin: 0; font-weight: 900; }
.score-green { background: <?= $warna_gradient ?> !important; }
.score-red { background: linear-gradient(135deg, #991B1B, #EF4444) !important; }
.button-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 28px;
}
.btn-payment {
    background: <?= $warna_primary ?>;
    color: white;
    border-radius: 14px;
    padding: 14px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
}
.btn-payment:hover { background: <?= $warna_primary_dark ?>; color: white; }
.btn-payment-disabled {
    background: #ef4444;
    color: white;
    border-radius: 14px;
    padding: 14px;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    cursor: not-allowed;
}
.btn-dashboard-outline {
    background: white;
    color: <?= $warna_primary ?>;
    border: 1.5px solid <?= $warna_primary ?>;
    border-radius: 14px;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
}
.btn-dashboard-outline:hover { background: <?= $warna_primary ?>; color: white; }
@media (max-width: 576px) {
    .info-grid { grid-template-columns: 1fr; }
    .card-main { padding: 24px; }
    .score-wrap h1 { font-size: 48px; }
    .header { flex-direction: column; align-items: start; gap: 10px; }
}
</style>
</head>
<body>
<div class="card-main">
    <div class="header">
        <h4><i class="bi bi-graph-up"></i> Hasil Tes Masuk</h4>
        <div class="badge-status-top <?= $badge_class ?>"><?= $status ?></div>
    </div>
    <div class="profile">
        <div class="avatar"><?= $inisial ?></div>
        <div class="profile-info">
            <b><?= htmlspecialchars($row['nama']) ?></b>
            <span><?= htmlspecialchars($row['email']) ?></span>
        </div>
    </div>
    <div class="info-grid">
        <div class="info-box"><span>Jurusan</span><b><?= htmlspecialchars($row['jurusan']) ?></b></div>
        <div class="info-box"><span>Tanggal Tes</span><b><?= date('d/m/Y H:i:s', strtotime($row['tanggal_tes'])) ?></b></div>
        <div class="info-box"><span>Jawaban Benar</span><b><?= $row['jumlah_benar'] ?> dari <?= $row['total_soal'] ?></b></div>
        <div class="info-box"><span>Jawaban Salah</span><b><?= $row['jumlah_salah'] ?></b></div>
        <div class="info-box"><span>Waktu Pengerjaan</span><b><?= $menit ?> menit <?= $detik ?> detik</b></div>
        <div class="info-box"><span>Status Seleksi</span><b><?= $status ?></b></div>
    </div>
    <div class="score-wrap <?= $score_class ?>">
        <h1><?= number_format($nilai, 0) ?></h1>
        <span>NILAI AKHIR</span>
    </div>
    <div class="button-container">
        <?php if($is_lulus): ?>
            <a href="panduan_pembayarantest.php" class="btn-payment"><i class="bi bi-cash"></i> Lanjut Pembayaran</a>
        <?php else: ?>
            <div class="btn-payment-disabled" onclick="alert('Maaf, Anda tidak lulus seleksi.')"><i class="bi bi-x-circle"></i> Pembayaran Tidak Tersedia</div>
        <?php endif; ?>
        <a href="dashboard_maba.php" class="btn-dashboard-outline"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>