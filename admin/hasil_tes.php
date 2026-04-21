<?php
include "auth_admin.php";
include "../koneksi.php";

// Ambil data hasil tes dengan status otomatis dari nilai
$data = mysqli_query($koneksi, "
    SELECT 
        cm.id_maba,
        cm.nama,
        cm.email,
        cm.jurusan,
        ht.id_hasil,
        ht.jumlah_benar,
        ht.jumlah_salah,
        ht.nilai,
        ht.durasi_detik,
        ht.tanggal_tes,
        CASE 
            WHEN ht.nilai >= 80 THEN 'lulus'
            WHEN ht.nilai < 80 AND ht.nilai IS NOT NULL THEN 'tidak_lulus'
            ELSE 'pending'
        END AS status_lulus_otomatis
    FROM calon_maba cm
    LEFT JOIN hasil_tes ht ON cm.id_maba = ht.id_maba
    ORDER BY ht.nilai DESC, cm.id_maba DESC
");

// Hitung statistik
$stat_lulus = 0;
$stat_tidak_lulus = 0;
$stat_belum_tes = 0;

mysqli_data_seek($data, 0);
while($row = mysqli_fetch_assoc($data)) {
    $status = $row['status_lulus_otomatis'];
    if($status == 'lulus') {
        $stat_lulus++;
    } elseif($status == 'tidak_lulus') {
        $stat_tidak_lulus++;
    } else {
        $stat_belum_tes++;
    }
}

mysqli_data_seek($data, 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Tes Mahasiswa</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --bg-main: linear-gradient(180deg,#0d3b66 0%, #0d6efd 45%, #ffffff 100%);
    --nav-bg: linear-gradient(180deg,#0d3b66,#0d6efd);
    --card-bg: rgba(255, 255, 255, 0.92);
    --card-border: linear-gradient(135deg, rgba(13,110,253,0.3), rgba(13,59,102,0.5));
    --text-main:#0f172a;
    --text-muted:#475569;
    --accent:#0d6efd;
    --shadow-main:0 25px 50px -12px rgba(13,59,102,0.4);
}
body.dark{
    --bg-main: linear-gradient(180deg,#020617 0%, #020b1f 55%, #020617 100%);
    --nav-bg: linear-gradient(180deg,#020617,#020b1f);
    --card-bg: rgba(2,6,23,0.85);
    --card-border: linear-gradient(135deg, rgba(96,165,250,0.3), rgba(96,165,250,0.1));
    --text-main:#e5e7eb;
    --text-muted:#9ca3af;
    --accent:#60a5fa;
    --shadow-main:0 35px 90px rgba(0,0,0,.8);
}
body{
    margin:0;
    min-height:100vh;
    font-family:'Plus Jakarta Sans',sans-serif;
    background: var(--bg-main);
    color: var(--text-main);
    transition:.35s ease;
}
.content{
    margin-left:220px;
    padding:28px 30px 40px;
}

/* ===== CARD STATISTIK (TERPISAH) ===== */
.stats-wrapper {
    margin-bottom: 30px;
}
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(12px);
    border-radius: 24px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: var(--shadow-main);
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
}
.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 35px rgba(0,0,0,0.15);
}
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}
.stat-lulus .stat-icon {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 8px 20px rgba(34,197,94,0.3);
}
.stat-tidak .stat-icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 8px 20px rgba(239,68,68,0.3);
}
.stat-belum .stat-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 8px 20px rgba(245,158,11,0.3);
}
.stat-info { flex: 1; }
.stat-value {
    font-size: 34px;
    font-weight: 800;
    margin: 0;
    color: var(--text-main);
    line-height: 1.1;
}
.stat-label {
    font-size: 13px;
    font-weight: 600;
    margin: 8px 0 0;
    color: var(--text-muted);
    letter-spacing: 0.5px;
}
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
}
.stat-lulus .progress-bar { background: linear-gradient(90deg, #22c55e, #4ade80); }
.stat-tidak .progress-bar { background: linear-gradient(90deg, #ef4444, #f87171); }
.stat-belum .progress-bar { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

/* ===== CARD TABEL (TERPISAH) ===== */
.table-wrapper {
    background: var(--card-bg);
    border-radius: 28px;
    padding: 28px;
    box-shadow: var(--shadow-main);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(13,110,253,0.2);
    position: relative;
    overflow: hidden;
}
.table-wrapper::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 28px;
    padding: 1.5px;
    background: var(--card-border);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

.page-title{
    font-size: 22px;
    font-weight: 900;
    background: linear-gradient(135deg, #0d6efd, #0d3b66);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.page-sub{
    font-size: 13px;
    opacity: .65;
    margin-bottom: 16px;
    color: var(--text-muted);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== SEARCH BAR ===== */
.search-wrapper { position: relative; }
.search-box {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 50px;
    padding: 4px 6px 4px 18px;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
    min-width: 280px;
}
.search-box:focus-within {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}
.search-icon {
    color: #6c757d;
    font-size: 16px;
    margin-right: 8px;
}
.search-input {
    background: transparent;
    border: none;
    padding: 10px 0;
    color: #212529 !important;
    font-size: 14px;
    width: 100%;
    outline: none;
}
.search-input::placeholder {
    color: #adb5bd !important;
    font-size: 13px;
}
.search-clear {
    background: transparent;
    border: none;
    color: #adb5bd;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}
.search-clear:hover {
    color: #dc3545;
    transform: scale(1.1);
}
body.dark .search-box {
    background: #1e293b;
    border-color: #475569;
}
body.dark .search-input {
    color: #f1f5f9 !important;
}
body.dark .search-input::placeholder {
    color: #94a3b8 !important;
}
body.dark .search-icon {
    color: #94a3b8;
}

.btn-export {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    padding: 10px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 13px;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.25s;
}
.btn-export:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
    color: white;
}

/* TABLE */
.table-wrap{
    overflow-x: auto;
    border-radius: 16px;
    margin-top: 20px;
}
table{
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    min-width: 800px;
}
th{
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    color: #ffffff !important;  /* DIPAKSA PUTIH */
    padding: 14px 12px;
    text-align: center;
    font-weight: 600;
    font-size: 13px;
}
td{
    padding: 12px;
    border-bottom: 1px solid rgba(0,0,0,.08);
    vertical-align: middle;
}
body.dark td{
    border-bottom: 1px solid rgba(255,255,255,.08);
}
tbody tr{
    transition: all 0.2s ease;
    animation: fadeInUp 0.3s ease-out forwards;
    opacity: 0;
}
tbody tr:nth-child(1) { animation-delay: 0.05s; }
tbody tr:nth-child(2) { animation-delay: 0.1s; }
tbody tr:nth-child(3) { animation-delay: 0.15s; }
tbody tr:nth-child(4) { animation-delay: 0.2s; }
tbody tr:nth-child(5) { animation-delay: 0.25s; }
tbody tr:hover{
    background: rgba(13,110,253,.06);
    transform: scale(1.01);
}

/* BADGE STATUS */
.badge-lulus{
    background: #22c55e;
    color: white;
    padding: 5px 14px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
}
.badge-tidak{
    background: #ef4444;
    color: white;
    padding: 5px 14px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
}
.badge-belum-tes{
    background: #94a3b8;
    color: white;
    padding: 5px 14px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
}

.nilai-high{
    color: #22c55e;
    font-weight: 800;
    font-size: 16px;
}
.nilai-low{
    color: #ef4444;
    font-weight: 800;
    font-size: 16px;
}
.nilai-normal{
    color: #0d3b66;
    font-weight: 700;
    font-size: 15px;
}
.no-result {
    text-align: center;
    padding: 60px 20px;
}
.no-result i {
    font-size: 64px;
    margin-bottom: 15px;
    opacity: 0.5;
    color: var(--text-muted);
}
.highlight {
    background: rgba(13, 110, 253, 0.3);
    border-radius: 4px;
    padding: 0 3px;
    font-weight: 600;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <!-- CARD STATISTIK (TERPISAH) -->
    <div class="stats-wrapper">
        <div class="stats-row">
            <div class="stat-card stat-lulus">
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stat_lulus ?></div>
                    <div class="stat-label">LULUS</div>
                </div>
                <div class="stat-progress"><div class="progress-bar" style="width: <?= ($stat_lulus + $stat_tidak_lulus + $stat_belum_tes) > 0 ? ($stat_lulus / ($stat_lulus + $stat_tidak_lulus + $stat_belum_tes)) * 100 : 0 ?>%"></div></div>
            </div>
            <div class="stat-card stat-tidak">
                <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stat_tidak_lulus ?></div>
                    <div class="stat-label">TIDAK LULUS</div>
                </div>
                <div class="stat-progress"><div class="progress-bar" style="width: <?= ($stat_lulus + $stat_tidak_lulus + $stat_belum_tes) > 0 ? ($stat_tidak_lulus / ($stat_lulus + $stat_tidak_lulus + $stat_belum_tes)) * 100 : 0 ?>%"></div></div>
            </div>
            <div class="stat-card stat-belum">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stat_belum_tes ?></div>
                    <div class="stat-label">BELUM TES</div>
                </div>
                <div class="stat-progress"><div class="progress-bar" style="width: <?= ($stat_lulus + $stat_tidak_lulus + $stat_belum_tes) > 0 ? ($stat_belum_tes / ($stat_lulus + $stat_tidak_lulus + $stat_belum_tes)) * 100 : 0 ?>%"></div></div>
            </div>
        </div>
    </div>

    <!-- CARD TABEL (TERPISAH) -->
    <div class="table-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="page-title">📈 Hasil Tes Calon Mahasiswa</div>
                <div class="page-sub">Nilai dan kelulusan ditentukan otomatis berdasarkan nilai (≥80 = LULUS)</div>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="search-wrapper">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau jurusan...">
                        <button class="search-clear" id="clearSearch" style="display: none;"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>
                <a href="export_hasil_tes.php" class="btn-export"><i class="bi bi-file-earmark-excel-fill"></i> Export Excel</a>
            </div>
        </div>
        
        <div class="table-wrap">
            <table class="table" id="dataTable">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Maba</th>
                        <th>Jurusan</th>
                        <th>Benar</th>
                        <th>Salah</th>
                        <th>Nilai</th>
                        <th>Status Kelulusan</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php 
                    $no = 1;
                    $has_data = false;
                    while($row = mysqli_fetch_assoc($data)):
                        $has_data = true;
                        $nilai = $row['nilai'];
                        
                        if($nilai !== null) {
                            if($nilai >= 80) {
                                $nilai_class = "nilai-high";
                                $badge_class = "badge-lulus";
                                $badge_text = "LULUS";
                            } else {
                                $nilai_class = "nilai-low";
                                $badge_class = "badge-tidak";
                                $badge_text = "TIDAK LULUS";
                            }
                        } else {
                            $nilai_class = "nilai-normal";
                            $badge_class = "badge-belum-tes";
                            $badge_text = "BELUM TES";
                        }
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?> </td>
                        <td class="nama-cell fw-bold"><?= htmlspecialchars($row['nama']) ?> </td>
                        <td class="jurusan-cell"><?= htmlspecialchars($row['jurusan']) ?> </td>
                        <td class="text-center"><?= $row['jumlah_benar'] ?? '-' ?> </td>
                        <td class="text-center"><?= $row['jumlah_salah'] ?? '-' ?> </td>
                        <td class="text-center nilai-cell">
                            <?php if($nilai !== null): ?>
                                <span class="<?= $nilai_class ?>"><?= $nilai ?></span>
                            <?php else: ?>
                                <span class="info-tes">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center status-cell">
                            <span class="<?= $badge_class ?>"><?= $badge_text ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$has_data): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #cbd5e1;"></i>
                            <p class="mt-2 text-muted">Belum ada data hasil tes</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div id="noResult" class="no-result" style="display: none;">
            <i class="bi bi-search"></i>
            <p>Tidak ada data yang sesuai dengan pencarian</p>
        </div>
        
        <div class="mt-4 text-muted small">
            <i class="bi bi-info-circle"></i> 
            <strong>Catatan:</strong> Kelulusan ditentukan otomatis oleh sistem berdasarkan nilai (≥80 = LULUS). 
            Status pendaftaran di halaman konfirmasi akan otomatis berubah sesuai kelulusan.
        </div>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearch');
const tableBody = document.getElementById('tableBody');
const noResultDiv = document.getElementById('noResult');
const tableWrap = document.querySelector('.table-wrap');
let rows = [];

function updateRows() {
    if (tableBody) {
        rows = Array.from(tableBody.querySelectorAll('tr'));
        rows = rows.filter(row => !row.querySelector('.text-center.py-5'));
    }
}

function searchTable() {
    if (!tableBody) return;
    updateRows();
    const keyword = searchInput.value.toLowerCase().trim();
    let hasResult = false;
    
    if (keyword !== '') {
        clearBtn.style.display = 'flex';
    } else {
        clearBtn.style.display = 'none';
    }
    
    rows.forEach(row => {
        const nama = row.querySelector('.nama-cell')?.textContent.toLowerCase() || '';
        const jurusan = row.querySelector('.jurusan-cell')?.textContent.toLowerCase() || '';
        const status = row.querySelector('.status-cell')?.textContent.toLowerCase() || '';
        
        const isMatch = keyword === '' || nama.includes(keyword) || jurusan.includes(keyword) || status.includes(keyword);
        
        if (isMatch) {
            row.style.display = '';
            hasResult = true;
            if (keyword !== '') {
                highlightCell(row.querySelector('.nama-cell'), keyword);
                highlightCell(row.querySelector('.jurusan-cell'), keyword);
            } else {
                removeHighlight(row.querySelector('.nama-cell'));
                removeHighlight(row.querySelector('.jurusan-cell'));
            }
        } else {
            row.style.display = 'none';
            removeHighlight(row.querySelector('.nama-cell'));
            removeHighlight(row.querySelector('.jurusan-cell'));
        }
    });
    
    if (!hasResult && keyword !== '') {
        noResultDiv.style.display = 'block';
        if (tableWrap) tableWrap.style.display = 'none';
    } else {
        noResultDiv.style.display = 'none';
        if (tableWrap) tableWrap.style.display = 'block';
    }
}

function highlightCell(cell, keyword) {
    if (!cell) return;
    const originalText = cell.textContent;
    if (!originalText || cell.innerHTML.includes('<span class="highlight">')) return;
    const regex = new RegExp(`(${keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    cell.innerHTML = originalText.replace(regex, '<span class="highlight">$1</span>');
}

function removeHighlight(cell) {
    if (!cell) return;
    cell.innerHTML = cell.textContent;
}

if (clearBtn) {
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchTable();
        searchInput.focus();
    });
}

if (searchInput) {
    searchInput.addEventListener('input', searchTable);
}

document.addEventListener('DOMContentLoaded', function() {
    updateRows();
});
</script>

</body>
</html>