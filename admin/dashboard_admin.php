<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login_admin.php");
    exit();
}
$admin = $_SESSION['admin'];
$query = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$admin'");
$data_admin = mysqli_fetch_assoc($query);

if (!$data_admin) {
    die("Gagal ambil data admin dari database ❌");
}

// ================= STATISTIK =================
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM calon_maba"))['total'];
$mahasiswa_baru = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM calon_maba WHERE DATE(tanggal_daftar) = CURDATE()"))['total'];
$mahasiswa_diterima = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM calon_maba WHERE status = 'diterima'"))['total'];
$mahasiswa_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM calon_maba WHERE status = 'pending'"))['total'];
$mahasiswa_ditolak = $total_mahasiswa - $mahasiswa_diterima - $mahasiswa_pending;

$mahasiswa_terbaru = mysqli_query($koneksi, "SELECT * FROM calon_maba ORDER BY tanggal_daftar DESC LIMIT 5");

// Hitung persentase verifikasi
$persentase_verifikasi = $total_mahasiswa > 0 ? round(($mahasiswa_diterima / $total_mahasiswa) * 100) : 0;

date_default_timezone_set('Asia/Jakarta');
$last_update = date('d/m/Y H:i:s');

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root{
    --bg-main: linear-gradient(180deg,#0d3b66 0%, #0d6efd 45%, #f0f4f8 100%);
    --nav-bg: linear-gradient(180deg,#0d3b66,#0d6efd);
    --card-bg:#ffffff;
    --text-main:#0f172a;
    --shadow-main:0 18px 45px rgba(13,59,102,.28);
    --shadow-hover:0 25px 50px rgba(13,59,102,.35);
    
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --primary-color: #0d3b66;
    --info-color: #0d6efd;
}

body{
    margin:0;
    min-height:100vh;
    font-family:'Plus Jakarta Sans',sans-serif;
    background:var(--bg-main);
    color:var(--text-main);
}

/* ===== HEADER ===== */
.univ-header{
    position:fixed;
    top:18px;
    left:240px;
    right:24px;
    height:90px;
    background:linear-gradient(135deg,#0d3b66,#0d6efd,#1e40af);
    border-radius:28px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    box-shadow:0 20px 45px rgba(13,59,102,.4);
    z-index:300;
    text-align:center;
    backdrop-filter: blur(2px);
}

.univ-title{
    font-size:2rem;
    font-weight:900;
    letter-spacing:1px;
    color:white;
    text-shadow:0 4px 12px rgba(0,0,0,.25);
}

.univ-subtitle{
    font-size:0.9rem;
    font-weight:600;
    margin-top:4px;
    color:rgba(255,255,255,.9);
}

.content{
    margin-left:220px;
    padding:140px 30px 40px;
}

/* ===== WELCOME CARD ===== */
.welcome-card {
    background: white;
    border-radius: 24px;
    padding: 0;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(13,59,102,.12);
    overflow: hidden;
}

.welcome-header {
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    padding: 20px 25px;
    color: white;
}

.welcome-header h2 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0 0 5px;
}

.welcome-header p {
    font-size: 0.85rem;
    margin: 0;
    opacity: 0.9;
}

.welcome-body {
    padding: 20px 25px;
}

.progress-container {
    background: #e2e8f0;
    border-radius: 12px;
    height: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar-custom {
    background: linear-gradient(90deg, #0d3b66, #0d6efd);
    width: <?= $persentase_verifikasi ?>%;
    height: 100%;
    border-radius: 12px;
    transition: width 1s ease;
}

.progress-stats {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.progress-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    padding: 8px 18px;
    border-radius: 40px;
    font-size: 0.85rem;
    font-weight: 600;
}

.progress-item .dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.dot-success { background: #22c55e; box-shadow: 0 0 5px #22c55e; }
.dot-warning { background: #f59e0b; box-shadow: 0 0 5px #f59e0b; }
.dot-danger { background: #ef4444; box-shadow: 0 0 5px #ef4444; }

/* ===== 4 CARD STATISTIK MINI DI BAWAH WELCOME ===== */
.stats-mini {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-mini-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.stat-mini-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(13,59,102,.15);
}

.stat-mini-left .stat-mini-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.stat-mini-left .stat-mini-number {
    font-size: 1.8rem;
    font-weight: 900;
    color: #0d3b66;
    line-height: 1;
}

.stat-mini-icon {
    width: 50px;
    height: 50px;
    background: rgba(13,59,102,0.1);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #0d3b66;
}

.stat-mini-card:nth-child(2) .stat-mini-icon { background: rgba(34,197,94,0.1); color: #22c55e; }
.stat-mini-card:nth-child(3) .stat-mini-icon { background: rgba(245,158,11,0.1); color: #f59e0b; }
.stat-mini-card:nth-child(4) .stat-mini-icon { background: rgba(239,68,68,0.1); color: #ef4444; }

/* 2 Kolom Layout */
.two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

/* Card Style */
.card-custom {
    background: white;
    border-radius: 24px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(13,59,102,.1);
    transition: transform 0.3s ease;
}

.card-custom:hover {
    transform: translateY(-3px);
}

.card-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
}

.card-header-custom h4 {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0d3b66;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-link-custom {
    color: #0d6efd;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 600;
}

.btn-link-custom:hover {
    text-decoration: underline;
}

/* Student List */
.student-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.student-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.student-item:hover {
    background: #f1f5f9;
    transform: translateX(5px);
}

.student-avatar-sm {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 1.1rem;
}

.student-detail h5 {
    font-weight: 700;
    font-size: 0.95rem;
    margin: 0 0 3px;
    color: #0f172a;
}

.student-detail p {
    font-size: 0.7rem;
    color: #64748b;
    margin: 0;
}

.student-status {
    margin-left: auto;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
}

/* DONUT CHART */
.donut-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.donut-wrapper {
    position: relative;
    width: 220px;
    height: 220px;
    margin: 0 auto;
}

.donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.donut-center .total-number {
    font-size: 2.2rem;
    font-weight: 900;
    color: #0d3b66;
    line-height: 1;
}

.donut-center .total-label {
    font-size: 0.7rem;
    color: #64748b;
}

.chart-legend {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    font-weight: 600;
}

.legend-color {
    width: 14px;
    height: 14px;
    border-radius: 4px;
}

/* 3 Kotak Aksi */
.three-columns {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    margin-bottom: 25px;
}

.action-card {
    background: white;
    border-radius: 20px;
    padding: 25px 20px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
    display: block;
}

.action-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(13,59,102,.15);
    border-color: #0d6efd;
}

.action-icon {
    width: 70px;
    height: 70px;
    background: rgba(13,59,102,0.1);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}

.action-icon i {
    font-size: 2.2rem;
    color: #0d6efd;
}

.action-card h5 {
    font-weight: 800;
    font-size: 1rem;
    color: #0f172a;
    margin-bottom: 5px;
}

.action-card p {
    font-size: 0.7rem;
    color: #94a3b8;
    margin: 0;
}

/* System Info */
.system-info {
    background: white;
    border-radius: 20px;
    padding: 20px;
}

.system-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.system-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #f8fafc;
    border-radius: 14px;
    transition: all 0.3s ease;
}

.system-item:hover {
    background: #f1f5f9;
}

.system-item i {
    font-size: 1.2rem;
    color: #0d6efd;
}

.system-item span {
    font-size: 0.8rem;
    color: #475569;
}

/* Responsive */
@media (max-width: 992px) {
    .two-columns { grid-template-columns: 1fr; }
    .three-columns { grid-template-columns: repeat(2, 1fr); }
    .stats-mini { grid-template-columns: repeat(2, 1fr); }
    .system-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .content { margin-left: 0; padding: 170px 15px 20px; }
    .univ-header { left: 15px; right: 15px; }
    .univ-title { font-size: 1.2rem; }
    .three-columns { grid-template-columns: 1fr; }
    .stats-mini { grid-template-columns: 1fr; }
}

/* Animasi */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-card, .stats-mini, .card-custom, .action-card, .system-info {
    animation: fadeInUp 0.5s ease forwards;
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- ===== HEADER KAMPUS ===== -->
<div class="univ-header">
    <div class="univ-title">UNIVERSITAS CENDEKIA NUSANTARA</div>
    <div class="univ-subtitle">★ Mencetak Intelektual Membangun Peradaban ★</div>
</div>

<div class="content">

<!-- WELCOME CARD -->
<div class="welcome-card">
    <div class="welcome-header">
       
        <h2>WELCOME BACK <?= htmlspecialchars($data_admin['nama'] ?? 'ADMIN') ?></h2>
    </div>
    <div class="welcome-body">
        <p class="mb-3" style="color: #475569;">Selamat datang di Sistem Admin Universitas Cendekia Nusantara. Berikut progress hari ini:</p>
        
        <div class="progress-container">
            <div class="progress-bar-custom"></div>
        </div>
        
        <div class="progress-stats">
            <div class="progress-item">
                <div class="dot dot-success"></div>
                <span><strong><?= $persentase_verifikasi ?>%</strong> Verifikasi selesai</span>
            </div>
            <div class="progress-item">
                <div class="dot dot-warning"></div>
                <span><strong><?= $mahasiswa_pending ?></strong> Pending</span>
            </div>
            <div class="progress-item">
                <div class="dot dot-danger"></div>
                <span><strong><?= $mahasiswa_ditolak > 0 ? $mahasiswa_ditolak : 0 ?></strong> Ditolak</span>
            </div>
        </div>
    </div>
</div>

<!-- ===== 4 CARD STATISTIK MINI ===== -->
<div class="stats-mini">
    <div class="stat-mini-card">
        <div class="stat-mini-left">
            <div class="stat-mini-label">Total Mahasiswa</div>
            <div class="stat-mini-number"><?= $total_mahasiswa ?></div>
        </div>
        <div class="stat-mini-icon"><i class="bi bi-people-fill"></i></div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-left">
            <div class="stat-mini-label">Diterima</div>
            <div class="stat-mini-number"><?= $mahasiswa_diterima ?></div>
        </div>
        <div class="stat-mini-icon"><i class="bi bi-check-circle-fill"></i></div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-left">
            <div class="stat-mini-label">Pending</div>
            <div class="stat-mini-number"><?= $mahasiswa_pending ?></div>
        </div>
        <div class="stat-mini-icon"><i class="bi bi-clock-fill"></i></div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-left">
            <div class="stat-mini-label">Ditolak</div>
            <div class="stat-mini-number"><?= $mahasiswa_ditolak > 0 ? $mahasiswa_ditolak : 0 ?></div>
        </div>
        <div class="stat-mini-icon"><i class="bi bi-x-circle-fill"></i></div>
    </div>
</div>

<!-- 2 KOLOM: Mahasiswa Terbaru + Donut Chart -->
<div class="two-columns">
    <!-- Mahasiswa Terbaru -->
    <div class="card-custom">
        <div class="card-header-custom">
            <h4><i class="bi bi-person-plus-fill"></i> Mahasiswa Terbaru</h4>
            <a href="kelola_maba.php" class="btn-link-custom">Lihat Semua →</a>
        </div>
        <div class="student-list">
            <?php while($row = mysqli_fetch_assoc($mahasiswa_terbaru)): ?>
            <div class="student-item">
                <div class="student-avatar-sm">
                    <?= strtoupper(substr($row['nama'], 0, 2)) ?>
                </div>
                <div class="student-detail">
                    <h5><?= htmlspecialchars($row['nama']) ?></h5>
                    <p><?= htmlspecialchars($row['jurusan'] ?? 'Belum pilih') ?></p>
                </div>
                <div class="student-status" style="background: <?= $row['status'] == 'diterima' ? '#d1fae5' : ($row['status'] == 'pending' ? '#fef3c7' : '#fee2e2') ?>; color: <?= $row['status'] == 'diterima' ? '#059669' : ($row['status'] == 'pending' ? '#d97706' : '#dc2626') ?>">
                    <?= strtoupper($row['status']) ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- DONUT CHART -->
    <div class="card-custom">
        <div class="card-header-custom">
            <h4><i class="bi bi-pie-chart-fill"></i> Statistik Mahasiswa</h4>
        </div>
        <div class="donut-container">
            <div class="donut-wrapper">
                <canvas id="donutChart" width="200" height="200"></canvas>
                <div class="donut-center">
                    <div class="total-number"><?= $total_mahasiswa ?></div>
                    <div class="total-label">Total</div>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #22c55e;"></div>
                    <span>Diterima (<?= $mahasiswa_diterima ?>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f59e0b;"></div>
                    <span>Pending (<?= $mahasiswa_pending ?>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ef4444;"></div>
                    <span>Ditolak (<?= $mahasiswa_ditolak > 0 ? $mahasiswa_ditolak : 0 ?>)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3 KOTAK AKSI -->
<div class="three-columns">
    <a href="konfirmasi.php" class="action-card">
        <div class="action-icon"><i class="bi bi-people-fill"></i></div>
        <h5>Konfirmasi Mahasiswa</h5>
        <p>Verifikasi calon mahasiswa baru</p>
    </a>
    <a href="verifikasi_daftar_ulang.php" class="action-card">
        <div class="action-icon"><i class="bi bi-check-circle-fill"></i></div>
        <h5>Verifikasi Data</h5>
        <p>Cek kelengkapan berkas</p>
    </a>
    <a href="hasil_tes.php" class="action-card">
        <div class="action-icon"><i class="bi bi-clipboard-data-fill"></i></div>
        <h5>Input Hasil Tes</h5>
        <p>Masukkan nilai ujian masuk</p>
    </a>
</div>

<!-- SYSTEM INFO -->
<div class="system-info">
    <div class="system-grid">
        <div class="system-item">
            <i class="bi bi-envelope-fill"></i>
            <span>Email: admin@ucn.ac.id</span>
        </div>
        <div class="system-item">
            <i class="bi bi-building"></i>
            <span>Universitas Cendekia Nusantara</span>
        </div>
        <div class="system-item">
            <i class="bi bi-clock-fill"></i>
            <span>Terakhir diperiksa: <?= $last_update ?></span>
        </div>
        <div class="system-item">
            <i class="bi bi-shield-check"></i>
            <span>Sistem: Berjalan Normal</span>
        </div>
    </div>
</div>

</div>

<script>
// DONUT CHART
const ctx = document.getElementById('donutChart').getContext('2d');
const donutChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Diterima', 'Pending', 'Ditolak'],
        datasets: [{
            data: [<?= $mahasiswa_diterima ?>, <?= $mahasiswa_pending ?>, <?= $mahasiswa_ditolak > 0 ? $mahasiswa_ditolak : 0 ?>],
            backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            cutout: '70%',
            hoverOffset: 15,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1e293b',
                titleColor: '#ffffff',
                bodyColor: '#cbd5e1',
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = <?= $total_mahasiswa ?>;
                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        },
        animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1500,
            easing: 'easeOutBounce'
        }
    }
});
</script>

</body>
</html>