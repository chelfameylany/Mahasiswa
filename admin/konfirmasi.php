<?php
include "../koneksi.php";
session_start();

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

$data = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE status='pending' OR status IS NULL ORDER BY id_maba DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Konfirmasi Calon Mahasiswa</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
    --glass: rgba(255,255,255,.25);
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
    --glass: rgba(15,23,42,.55);
}
body{
    margin:0;
    min-height:100vh;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
    color: var(--text-main);
    background: var(--bg-main);
    transition:.35s ease;
    font-size:15px;
    font-weight:400;
}

/* ===== ANIMATIONS ===== */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* ===== TOPBAR FIXED ===== */
.topbar{
    position: fixed;
    top:18px;
    left:240px;
    right:24px;
    height:74px;
    background: var(--nav-bg);
    backdrop-filter: blur(18px);
    border-radius:24px;
    display:flex;
    justify-content:center;
    align-items:center;
    gap:42px;
    z-index:9999;
    box-shadow:0 22px 55px rgba(13,59,102,.45);
    animation: fadeInUp 0.5s ease-out forwards;
}
.nav-link{
    color:white;
    text-decoration:none;
    font-weight:500;
    font-size:15px;
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 24px;
    border-radius:16px;
    transition:.25s ease;
    opacity:.95;
}
.nav-link:hover{
    opacity:1;
    background: rgba(255,255,255,.18);
    transform: translateY(-2px) scale(1.06);
}
.theme-toggle{
    position:absolute;
    right:22px;
    cursor:pointer;
    font-size:20px;
    color:white;
    opacity:.9;
    transition:.25s;
}
.theme-toggle:hover{
    transform:rotate(15deg) scale(1.25);
    opacity:1;
}

/* ===== CONTENT ===== */
.main-content{
    margin-left:220px;
    padding:120px 30px 40px;
}

/* CARD DENGAN GRADASI BIRU + BORDER GLOW */
.content-box{
    background: var(--card-bg);
    border-radius:32px;
    padding:34px;
    box-shadow: var(--shadow-main);
    backdrop-filter: blur(12px);
    position: relative;
    border: 1px solid rgba(13,110,253,0.2);
    animation: fadeInLeft 0.6s ease-out forwards;
}

/* Efek gradasi biru di border card */
.content-box::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 32px;
    padding: 1.5px;
    background: var(--card-border);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

/* Efek glow biru di belakang card */
.content-box::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 32px;
    background: radial-gradient(circle at 30% 20%, rgba(13,110,253,0.08), transparent 70%);
    pointer-events: none;
    z-index: 0;
}

.content-box > * {
    position: relative;
    z-index: 1;
}

.page-title{
    font-weight:600;
    font-size:20px;
}
.page-sub{
    font-size:13px;
    opacity:.65;
    margin-bottom:18px;
}

/* ===== SEARCH BAR ===== */
.search-wrapper {
    position: relative;
}
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
    display: flex;
    align-items: center;
    justify-content: center;
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
body.dark .search-clear {
    color: #94a3b8;
}

.table-wrap{
    overflow-x:auto;
    border-radius:20px;
    margin-top:20px;
}
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
    min-width: 900px;
}
th {
    background: linear-gradient(135deg, #0d3b66, #0d6efd, #0d3b66);
    background-size: 200% 200%;
    animation: gradientMove 5s ease infinite;
    color: #ffffff !important;
    font-weight: 600;
    padding: 14px 12px;
    text-align: center;
    font-size: 13px;
    letter-spacing: 0.3px;
}
body.dark th{
    background: linear-gradient(135deg, #0f172a, #1e293b, #0f172a);
    background-size: 200% 200%;
}
td{
    padding:12px;
    border-bottom:1px solid rgba(0,0,0,.08);
    vertical-align:middle;
    font-weight:400;
}
body.dark td{
    border-bottom:1px solid rgba(255,255,255,.08);
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
body.dark tbody tr:hover{
    background: rgba(96,165,250,.1);
}

.badge-status{
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:500;
    display:inline-block;
    white-space: nowrap;
}
.status-pending{ 
    background:#fde047; 
    color:#78350f; 
}
.status-diterima{ 
    background:#22c55e; 
    color:white; 
}
.status-ditolak{ 
    background:#ef4444; 
    color:white; 
}

.btn-acc, .btn-tolak{
    border-radius:40px;
    padding:8px 16px;
    font-size:12px;
    font-weight:600;
    color:white; 
    display:inline-flex; 
    align-items:center; 
    justify-content: center;
    gap:8px; 
    transition:.25s;
    border: none;
    cursor: pointer;
    min-width: 90px;
    letter-spacing:0.3px;
}
.btn-acc{
    background: linear-gradient(135deg, #22c55e, #16a34a);
}
.btn-tolak{
    background: linear-gradient(135deg, #ef4444, #dc2626);
}
.btn-acc:hover, .btn-tolak:hover{
    transform:scale(1.05);
    filter: brightness(1.05);
}
.btn-acc:disabled, .btn-tolak:disabled{
    opacity:0.6;
    transform:none;
    cursor:not-allowed;
}
.action-buttons{
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
    flex-wrap: nowrap;
}
.btn-export{
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    padding: 8px 22px;
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
.btn-export:hover{
    transform: translateY(-2px);
    filter: brightness(1.05);
    color: white;
}
.empty-state{
    text-align: center;
    padding: 50px 20px;
}
.empty-state i{
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    opacity: 0.5;
}
.empty-state p{
    font-size: 1rem;
    color: var(--text-muted);
}

/* No Result */
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
.no-result p {
    color: var(--text-muted);
}

/* Highlight search */
.highlight {
    background: rgba(13, 110, 253, 0.3);
    border-radius: 4px;
    padding: 0 3px;
    font-weight: 600;
}
body.dark .highlight {
    background: rgba(96, 165, 250, 0.4);
}

/* MODAL POPUP */
.modal-confirm {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    justify-content: center;
    align-items: center;
}
.modal-confirm.show {
    display: flex;
}
.modal-content-confirm {
    background: linear-gradient(145deg, #ffffff, #f0f4ff);
    border-radius: 28px;
    padding: 28px 32px;
    width: 400px;
    max-width: 90%;
    text-align: center;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
    animation: modalPop 0.3s ease-out;
    border: 1px solid rgba(13,110,253,0.2);
}
body.dark .modal-content-confirm {
    background: linear-gradient(145deg, #1e293b, #0f172a);
    border: 1px solid rgba(96,165,250,0.3);
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.modal-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2rem;
}
.modal-icon.accept {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    box-shadow: 0 8px 20px rgba(34,197,94,0.3);
}
.modal-icon.reject {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    box-shadow: 0 8px 20px rgba(239,68,68,0.3);
}
.modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 12px;
    color: #0f172a;
}
body.dark .modal-title {
    color: #f1f5f9;
}
.modal-message {
    font-size: 0.9rem;
    color: #475569;
    margin-bottom: 24px;
    line-height: 1.5;
}
body.dark .modal-message {
    color: #cbd5e1;
}
.modal-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}
.btn-modal-confirm {
    padding: 10px 24px;
    border-radius: 40px;
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
    cursor: pointer;
    transition: 0.2s;
}
.btn-modal-yes {
    background: linear-gradient(135deg, #0d6efd, #0d3b66);
    color: white;
}
.btn-modal-yes:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
}
.btn-modal-no {
    background: #e2e8f0;
    color: #334155;
}
.btn-modal-no:hover {
    background: #cbd5e1;
    transform: translateY(-2px);
}
body.dark .btn-modal-no {
    background: #334155;
    color: #e2e8f0;
}
body.dark .btn-modal-no:hover {
    background: #475569;
}

/* Responsive */
@media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 100px 15px 30px; }
    .topbar { left: 15px; right: 15px; gap: 15px; }
    .nav-link { padding: 8px 12px; font-size: 12px; }
    .search-box { min-width: 200px; }
    .d-flex.gap-3 { flex-direction: column; align-items: stretch; }
    .btn-export { justify-content: center; }
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
<div class="content-box">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-title">🎓 Konfirmasi Calon Mahasiswa</div>
            <div class="page-sub">Klik ACC / Tolak untuk update status calon maba</div>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <!-- SEARCH BAR -->
            <div class="search-wrapper">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, email, atau jurusan...">
                    <button class="search-clear" id="clearSearch" style="display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
            <!-- EXPORT BUTTON -->
            <a href="export_konfirmasi_maba.php" class="btn-export">
                <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table" id="dataTable">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jurusan</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                    <th width="200">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php 
                $no=1; 
                if(mysqli_num_rows($data) > 0) {
                    while($row=mysqli_fetch_assoc($data)): 
                        $tanggal_daftar = !empty($row['tanggal_daftar']) ? date('d/m/Y H:i', strtotime($row['tanggal_daftar'])) : '-';
                        if($tanggal_daftar == '-' && !empty($row['created_at'])) {
                            $tanggal_daftar = date('d/m/Y H:i', strtotime($row['created_at']));
                        }
                ?>
                <tr id="row<?= $row['id_maba'] ?>">
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="nama-cell"><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                    <td class="email-cell"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="jurusan-cell"><?= htmlspecialchars($row['jurusan']) ?></td>
                    <td class="text-center tanggal-cell"><?= $tanggal_daftar ?></td>
                    <td class="text-center status-cell">
                        <span class="badge-status status-pending" id="status<?= $row['id_maba'] ?>">
                            MENUNGGU
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="action-buttons">
                            <button class="btn-acc" onclick="showConfirmModal(<?= $row['id_maba'] ?>,'diterima','<?= addslashes($row['nama']) ?>')">
                                ACC
                            </button>
                            <button class="btn-tolak" onclick="showConfirmModal(<?= $row['id_maba'] ?>,'ditolak','<?= addslashes($row['nama']) ?>')">
                                Tolak
                            </button>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                } else {
                    echo '<tr><td colspan="7" class="empty-state"><div><i class="bi bi-inbox"></i><p>Tidak ada calon mahasiswa yang menunggu konfirmasi</p></div></td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- No Result Message -->
    <div id="noResult" class="no-result" style="display: none;">
        <i class="bi bi-search"></i>
        <p>Tidak ada data yang sesuai dengan pencarian</p>
    </div>
</div>
</div>

<!-- MODAL POPUP KONFIRMASI -->
<div id="confirmModal" class="modal-confirm">
    <div class="modal-content-confirm">
        <div class="modal-icon" id="modalIcon">!</div>
        <div class="modal-title" id="modalTitle">Konfirmasi</div>
        <div class="modal-message" id="modalMessage">Apakah Anda yakin?</div>
        <div class="modal-buttons">
            <button class="btn-modal-confirm btn-modal-no" onclick="closeModal()">Batal</button>
            <button class="btn-modal-confirm btn-modal-yes" id="modalYesBtn">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script>
// ===== SEARCH FUNCTION =====
const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearch');
const tableBody = document.getElementById('tableBody');
const noResultDiv = document.getElementById('noResult');
const tableWrap = document.querySelector('.table-wrap');
let rows = [];

function updateRows() {
    if (tableBody) {
        rows = Array.from(tableBody.querySelectorAll('tr'));
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
        const email = row.querySelector('.email-cell')?.textContent.toLowerCase() || '';
        const jurusan = row.querySelector('.jurusan-cell')?.textContent.toLowerCase() || '';
        const status = row.querySelector('.status-cell')?.textContent.toLowerCase() || '';
        
        const isMatch = keyword === '' || 
            nama.includes(keyword) || 
            email.includes(keyword) || 
            jurusan.includes(keyword) || 
            status.includes(keyword);
        
        if (isMatch) {
            row.style.display = '';
            hasResult = true;
            if (keyword !== '') {
                highlightCell(row.querySelector('.nama-cell'), keyword);
                highlightCell(row.querySelector('.email-cell'), keyword);
                highlightCell(row.querySelector('.jurusan-cell'), keyword);
            } else {
                removeHighlight(row.querySelector('.nama-cell'));
                removeHighlight(row.querySelector('.email-cell'));
                removeHighlight(row.querySelector('.jurusan-cell'));
            }
        } else {
            row.style.display = 'none';
            removeHighlight(row.querySelector('.nama-cell'));
            removeHighlight(row.querySelector('.email-cell'));
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

// ===== CONFIRM MODAL =====
let pendingId = null;
let pendingStatus = null;
let pendingNama = null;

function showConfirmModal(id, status, nama) {
    pendingId = id;
    pendingStatus = status;
    pendingNama = nama;
    
    const modal = document.getElementById('confirmModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const yesBtn = document.getElementById('modalYesBtn');
    
    if(status === 'diterima') {
        modalIcon.className = 'modal-icon accept';
        modalIcon.innerHTML = '✓';
        modalTitle.innerHTML = 'Konfirmasi ACC';
        modalMessage.innerHTML = `Apakah Anda yakin ingin MENERIMA mahasiswa ${nama}?`;
        yesBtn.innerHTML = 'Ya, Terima';
    } else {
        modalIcon.className = 'modal-icon reject';
        modalIcon.innerHTML = '✗';
        modalTitle.innerHTML = 'Konfirmasi TOLAK';
        modalMessage.innerHTML = `Apakah Anda yakin ingin MENOLAK mahasiswa ${nama}?`;
        yesBtn.innerHTML = 'Ya, Tolak';
    }
    
    modal.classList.add('show');
}

function closeModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('show');
    pendingId = null;
    pendingStatus = null;
    pendingNama = null;
}

function executeUpdate() {
    if(!pendingId || !pendingStatus) return;
    
    const id = pendingId;
    const status = pendingStatus;
    
    const btnAcc = document.querySelector(`#row${id} .btn-acc`);
    const btnTolak = document.querySelector(`#row${id} .btn-tolak`);
    if(btnAcc) btnAcc.disabled = true;
    if(btnTolak) btnTolak.disabled = true;
    
    fetch('ajax_update_status.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(res => {
        if(res.success){
            const badge = document.getElementById('status'+id);
            badge.className = 'badge-status status-' + status;
            badge.innerHTML = status === 'diterima' ? 'DITERIMA' : 'DITOLAK';
            
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            alert('Gagal update status: ' + (res.message || 'Unknown error'));
            if(btnAcc) btnAcc.disabled = false;
            if(btnTolak) btnTolak.disabled = false;
        }
        closeModal();
    })
    .catch(err => {
        alert('Terjadi kesalahan: ' + err.message);
        if(btnAcc) btnAcc.disabled = false;
        if(btnTolak) btnTolak.disabled = false;
        closeModal();
    });
}

if (document.getElementById('modalYesBtn')) {
    document.getElementById('modalYesBtn').onclick = executeUpdate;
}

window.onclick = function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target == modal) {
        closeModal();
    }
}

function toggleTheme(){
    const body = document.body;
    const icon = document.getElementById('themeIcon');
    body.classList.toggle('dark');
    icon.className = body.classList.contains('dark') ? "bi bi-sun-fill" : "bi bi-moon-stars-fill";
}

// Initialize search on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRows();
});
</script>

</body>
</html>