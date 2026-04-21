<?php
session_start();

$conn = mysqli_connect("localhost","root","","mahasiswa",8111);
if(!$conn){
    die("Koneksi gagal: ".mysqli_connect_error());
}

$total     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM calon_maba"))[0];
$pending   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM calon_maba WHERE status='pending' OR status IS NULL"))[0];
$diterima  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM calon_maba WHERE status='diterima'"))[0];
$ditolak   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM calon_maba WHERE status='ditolak'"))[0];

// Data untuk diagram (per jurusan)
$jurusan_data = [];
$query_jurusan = mysqli_query($conn, "SELECT jurusan, COUNT(*) as jumlah FROM calon_maba GROUP BY jurusan");
while ($row = mysqli_fetch_assoc($query_jurusan)) {
    $jurusan_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin - Universitas Cendekia Nusantara</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root{
    --bg-main: linear-gradient(180deg,#0d3b66 0%, #0d6efd 45%, #ffffff 100%);
    --nav-bg: linear-gradient(180deg,#0d3b66,#0d6efd);
    --card-bg:#ffffff;
    --text-main:#0f172a;
    --text-muted:#475569;
    --accent:#0d6efd;
    --shadow-main:0 18px 45px rgba(13,59,102,.28);
    --glass: rgba(255,255,255,.25);
}

body.dark{
    --bg-main: linear-gradient(180deg,#020617 0%, #020b1f 55%, #020617 100%);
    --nav-bg: linear-gradient(180deg,#020617,#020b1f);
    --card-bg: rgba(2,6,23,.75);
    --text-main:#e5e7eb;
    --text-muted:#9ca3af;
    --accent:#60a5fa;
    --shadow-main:0 35px 90px rgba(0,0,0,.8);
    --glass: rgba(15,23,42,.55);
}

body{
    margin:0;
    min-height:100vh;
    font-family:'Plus Jakarta Sans','Segoe UI',Arial,sans-serif;
    background: var(--bg-main);
    color: var(--text-main);
    transition:.35s ease;
}

/* ===== ANIMASI ===== */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInRight {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse {
    0% { transform: scale(1); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    50% { transform: scale(1.02); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
    100% { transform: scale(1); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
}

@keyframes shimmer {
    0% { background-position: -200px 0; }
    100% { background-position: 200px 0; }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

/* Topbar Animation */
.topbar {
    animation: fadeInDown 0.6s ease-out forwards;
}

/* Kartu Statistik */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: var(--shadow-main);
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

.stat-card:hover {
    animation: pulse 0.5s ease-in-out;
    transform: translateY(-5px);
    box-shadow: 0 20px 35px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon i {
    transform: scale(1.2) rotate(5deg);
}

.stat-total .stat-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.stat-pending .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-accepted .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
.stat-rejected .stat-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }

.stat-info { flex: 1; }
.stat-value { font-size: 28px; font-weight: 800; margin: 0; color: var(--text-main); line-height: 1.2; }
.stat-label { font-size: 12px; font-weight: 600; margin: 5px 0 0; color: var(--text-muted); letter-spacing: 0.5px; }

.stat-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(0,0,0,0.08);
}

.progress-bar {
    height: 100%;
    transition: width 0.5s ease;
    background-size: 200px 100%;
    animation: shimmer 2s ease-in-out infinite;
}

.stat-total .progress-bar { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.stat-pending .progress-bar { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.stat-accepted .progress-bar { background: linear-gradient(90deg, #10b981, #34d399); }
.stat-rejected .progress-bar { background: linear-gradient(90deg, #ef4444, #f87171); }

/* Container Chart */
.chart-container {
    background: var(--card-bg);
    backdrop-filter: blur(12px);
    border-radius: 24px;
    padding: 20px 25px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-main);
    border: 1px solid rgba(255,255,255,0.1);
    animation: fadeInRight 0.7s ease-out forwards;
    opacity: 0;
    animation-delay: 0.5s;
}

.chart-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-main);
}

.chart-title i {
    font-size: 24px;
    background: linear-gradient(135deg, #0d6efd, #0d3b66);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: bounce 2s ease-in-out infinite;
}

.chart-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 320px;
}

canvas#jurusanChart {
    max-width: 100%;
    max-height: 300px;
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
    animation-delay: 0.55s;
}

/* Content Box */
.content-box {
    background: var(--card-bg);
    border-radius: 24px;
    padding: 25px 30px;
    box-shadow: var(--shadow-main);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    animation: fadeInLeft 0.7s ease-out forwards;
    opacity: 0;
    animation-delay: 0.6s;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    animation: bounce 2s ease-in-out infinite;
}

/* Tabel Ringkasan */
.table-summary {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;
    overflow: hidden;
}

.table-summary th {
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    color: white;
    padding: 12px;
    text-align: center;
    font-weight: 600;
    font-size: 13px;
}

.table-summary td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    color: var(--text-main);
    font-size: 13px;
}

body.dark .table-summary td { border-bottom-color: rgba(255,255,255,0.06); }

.table-summary tbody tr {
    animation: fadeInUp 0.4s ease-out forwards;
    opacity: 0;
    transition: all 0.3s ease;
}

.table-summary tbody tr:nth-child(1) { animation-delay: 0.7s; }
.table-summary tbody tr:nth-child(2) { animation-delay: 0.8s; }
.table-summary tbody tr:nth-child(3) { animation-delay: 0.9s; }
.table-summary tbody tr:nth-child(4) { animation-delay: 1.0s; }
.table-summary tbody tr:nth-child(5) { animation-delay: 1.1s; }

.table-summary tbody tr:hover {
    transform: translateX(5px);
    background: rgba(13,110,253,0.05);
}

.badge-jurusan {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.badge-jurusan:hover {
    transform: scale(1.05) rotate(2deg);
}

/* Topbar */
.topbar {
    position: fixed;
    top: 18px;
    left: 240px;
    right: 24px;
    height: 74px;
    background: var(--nav-bg);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 42px;
    z-index: 100;
    box-shadow: 0 22px 55px rgba(13,59,102,.45);
}

.nav-link {
    color: white;
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: 16px;
    transition: .25s ease;
    opacity: .95;
}

.nav-link:hover {
    opacity: 1;
    background: rgba(255,255,255,.18);
    transform: translateY(-2px) scale(1.06);
}

.theme-toggle {
    position: absolute;
    right: 22px;
    cursor: pointer;
    font-size: 20px;
    color: white;
    opacity: .9;
    transition: .25s;
}

.theme-toggle:hover {
    transform: rotate(15deg) scale(1.25);
    opacity: 1;
}

.main-content {
    margin-left: 220px;
    padding: 130px 30px 40px;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .main-content { margin-left: 0; padding: 100px 15px 30px; }
    .topbar { left: 15px; right: 15px; gap: 15px; }
    .nav-link { padding: 8px 12px; font-size: 12px; }
}

@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="topbar">
    <a href="kelola_maba.php" class="nav-link"><i class="bi bi-speedometer2"></i> Kelola Maba</a>
    <a href="konfirmasi.php" class="nav-link"><i class="bi bi-check-circle-fill"></i> Konfirmasi Maba</a>
    <div class="theme-toggle" onclick="toggleTheme()" title="Mode Gelap / Terang">
        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
    </div>
</div>

<div class="main-content">
    
    <!-- 4 KARTU STATISTIK -->
    <div class="stats-row">
        <div class="stat-card stat-total">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value" data-target="<?= $total ?>">0</div>
                <div class="stat-label">TOTAL PENDAFTAR</div>
            </div>
            <div class="stat-progress"><div class="progress-bar" style="width: 100%"></div></div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="stat-info">
                <div class="stat-value" data-target="<?= $pending ?>">0</div>
                <div class="stat-label">MENUNGGU</div>
            </div>
            <div class="stat-progress"><div class="progress-bar" style="width: <?= $total > 0 ? ($pending/$total)*100 : 0 ?>%"></div></div>
        </div>
        <div class="stat-card stat-accepted">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value" data-target="<?= $diterima ?>">0</div>
                <div class="stat-label">DITERIMA</div>
            </div>
            <div class="stat-progress"><div class="progress-bar" style="width: <?= $total > 0 ? ($diterima/$total)*100 : 0 ?>%"></div></div>
        </div>
        <div class="stat-card stat-rejected">
            <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value" data-target="<?= $ditolak ?>">0</div>
                <div class="stat-label">DITOLAK</div>
            </div>
            <div class="stat-progress"><div class="progress-bar" style="width: <?= $total > 0 ? ($ditolak/$total)*100 : 0 ?>%"></div></div>
        </div>
    </div>

    <!-- DIAGRAM BATANG (BAR CHART) -->
    <div class="chart-container">
        <div class="chart-title">
            <i class="bi bi-bar-chart-steps"></i>
            Statistik Pendaftar Per Jurusan
        </div>
        <div class="chart-wrapper">
            <canvas id="jurusanChart"></canvas>
        </div>
    </div>

    <!-- TABEL RINGKASAN PER JURUSAN -->
    <div class="content-box">
        <div class="section-title">
            <i class="bi bi-table"></i>
            Rincian Pendaftar Per Jurusan
        </div>
        <div class="table-responsive">
            <table class="table-summary">
                <thead>
                    <tr><th>No</th><th>Jurusan</th><th>Jumlah Pendaftar</th><th>Persentase</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach($jurusan_data as $j): 
                        $persen = $total > 0 ? round(($j['jumlah'] / $total) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge-jurusan" style="background:<?php 
                            $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'];
                            echo $colors[($no-2) % count($colors)];
                        ?>; color:white;"><?= htmlspecialchars($j['jurusan']) ?></span></td>
                        <td><strong><?= $j['jumlah'] ?></strong> orang</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar" style="width: <?= $persen ?>%; background: <?= $colors[($no-2) % count($colors)] ?>;"></div>
                                </div>
                                <span style="font-size:12px;"><?= $persen ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($jurusan_data)): ?>
                    <tr><td colspan="4" class="text-center py-4">Belum ada data pendaftar</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// DIAGRAM BATANG (Bar Chart)
const jurusanData = <?= json_encode($jurusan_data) ?>;
const labels = jurusanData.map(item => item.jurusan);
const dataCount = jurusanData.map(item => item.jumlah);

const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#d946ef', '#14b8a6', '#f43f5e'];

const ctx = document.getElementById('jurusanChart').getContext('2d');
let jurusanChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Jumlah Pendaftar',
            data: dataCount,
            backgroundColor: colors.slice(0, labels.length),
            borderRadius: 10,
            barPercentage: 0.65,
            categoryPercentage: 0.8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: getComputedStyle(document.body).getPropertyValue('--text-main').trim(),
                    font: { size: 12, family: 'Plus Jakarta Sans', weight: '600' },
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 15
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.85)',
                titleColor: 'white',
                bodyColor: '#e2e8f0',
                padding: 12,
                cornerRadius: 12,
                callbacks: {
                    label: function(context) { return `📊 Jumlah: ${context.raw} orang`; }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.06)', drawBorder: false },
                ticks: { stepSize: 1, color: getComputedStyle(document.body).getPropertyValue('--text-muted').trim(), font: { size: 11 }, callback: function(value) { return value + ' org'; } },
                title: { display: true, text: 'Jumlah Pendaftar', color: getComputedStyle(document.body).getPropertyValue('--text-muted').trim(), font: { size: 11, weight: '500' } }
            },
            x: { grid: { display: false }, ticks: { color: getComputedStyle(document.body).getPropertyValue('--text-main').trim(), font: { size: 11, weight: '500' }, rotation: 0 } }
        },
        layout: { padding: { top: 15, bottom: 10, left: 10, right: 10 } },
        animation: { duration: 1000, easing: 'easeOutQuart' }
    }
});

// ANIMASI COUNTER ANGKA
function animateNumber(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (end - start) + start);
        element.textContent = currentValue;
        if (progress < 1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
}

// Jalankan animasi counter saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((el) => {
        const target = parseInt(el.getAttribute('data-target'));
        if (!isNaN(target)) animateNumber(el, 0, target, 1000);
    });
});

// Dark mode toggle
function toggleTheme(){
    const body = document.body;
    const icon = document.getElementById('themeIcon');
    body.classList.toggle('dark');
    icon.className = body.classList.contains('dark') ? "bi bi-sun-fill" : "bi bi-moon-stars-fill";
    
    setTimeout(() => {
        if (jurusanChart) {
            const textColor = getComputedStyle(body).getPropertyValue('--text-main').trim();
            const mutedColor = getComputedStyle(body).getPropertyValue('--text-muted').trim();
            jurusanChart.options.plugins.legend.labels.color = textColor;
            jurusanChart.options.scales.y.ticks.color = mutedColor;
            jurusanChart.options.scales.x.ticks.color = textColor;
            if (jurusanChart.options.scales.y.title) jurusanChart.options.scales.y.title.color = mutedColor;
            jurusanChart.update();
        }
    }, 100);
}
</script>
</body>
</html>