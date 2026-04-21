<?php
session_start();
include "../koneksi.php";

// PERBAIKAN 1: PAKAI SESSION MABA (KONSISTEN)
if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit;
}

// Ambil data user dari session username
$username = $_SESSION['maba'];
$query_user = mysqli_query($koneksi, "SELECT id_maba, nama, jurusan FROM calon_maba WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);
$id_maba = $user['id_maba'];
$nama_user = $user['nama'];
$jurusan_asli = $user['jurusan'] ?? 'umum';

// ===== DETEKSI JURUSAN (PERBAIKAN LOGIKA) =====
$jurusan_mahasiswa_lower = strtolower(trim($jurusan_asli));

if(strpos($jurusan_mahasiswa_lower, 'teknik') !== false) {
    // JURUSAN TEKNIK
    $jurusan_mahasiswa = 'teknik';
    $tabel_soal = "soal_tes_teknik";
    $judul_tes = "Tes Seleksi - Jurusan Teknik";
    $icon_tes = "bi-gear-fill";
    $bg_navbar = "linear-gradient(90deg,#0b5e3a 0%,#198754 100%)";
    $warna_jurusan = "#198754";
    $teks_jurusan = "TEKNIK";
} else {
    // JURUSAN UMUM (DEFAULT)
    $jurusan_mahasiswa = 'umum';
    $tabel_soal = "soal_tes";
    $judul_tes = "Tes Seleksi - Jurusan Umum";
    $icon_tes = "bi-people-fill";
    $bg_navbar = "linear-gradient(90deg,#0b4da2 0%,#2a7de1 100%)";
    $warna_jurusan = "#0d6efd";
    $teks_jurusan = "UMUM";
}

$id_maba_formatted = "PMB" . str_pad($id_maba, 5, '0', STR_PAD_LEFT);

// CEK SUDAH PERNAH TES
$query_cek = mysqli_query($koneksi, "SELECT * FROM hasil_tes WHERE id_maba='$id_maba'");
if (mysqli_num_rows($query_cek) > 0) {
    header("Location: hasil_tes_maba.php");
    exit;
}

// CEK TABEL SOAL
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE '$tabel_soal'");
if(mysqli_num_rows($cek_tabel) == 0) {
    die("Tabel soal tidak ditemukan: " . $tabel_soal);
}

$query_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM $tabel_soal");
$total_data = mysqli_fetch_assoc($query_total)['total'];

if($total_data == 0) {
    die("Belum ada soal di tabel: " . $tabel_soal);
}

// PAGINATION
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$totalPage = ceil($total_data / $limit);
$currentSoal = $start + 1;

// Ambil semua ID soal untuk mapping
$query_ids = mysqli_query($koneksi, "SELECT id_soal FROM $tabel_soal ORDER BY id_soal");
$soal_ids = [];
while ($row_id = mysqli_fetch_assoc($query_ids)) {
    $soal_ids[] = $row_id['id_soal'];
}

// Ambil soal sesuai halaman
$soal = mysqli_query($koneksi, "SELECT * FROM $tabel_soal ORDER BY id_soal LIMIT $start,$limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= $judul_tes ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(135deg, #f5f7ff 0%, #e3eeff 100%);
    min-height: 100vh;
}

/* NAVBAR FIX */
.navbar {
    background: <?= $bg_navbar ?>;
    padding: 12px 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
}

.logo span {
    font-size: 16px;
    font-weight: 800;
    color: white;
    letter-spacing: 0.5px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.12);
    padding: 6px 18px;
    border-radius: 40px;
}

.user-id {
    color: white;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.jurusan-badge {
    background: <?= $warna_jurusan ?>;
    padding: 6px 18px;
    border-radius: 40px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    color: white;
}

.soal-counter {
    background: rgba(255,255,255,0.2);
    padding: 6px 20px;
    border-radius: 40px;
    color: white;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.timer-container {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
    padding: 6px 22px;
    border-radius: 40px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-weight: 800;
}

.timer-container span {
    font-size: 20px;
    font-family: 'Courier New', monospace;
    font-weight: 800;
    letter-spacing: 1px;
}

.container {
    max-width: 1100px;
    margin: 25px auto;
    padding: 30px;
    background: white;
    border-radius: 24px;
    box-shadow: 0 15px 50px rgba(0,0,0,.1);
}

.progress-section {
    margin-bottom: 35px;
}
.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.progress-text {
    font-size: 14px;
    color: #444;
    font-weight: 600;
}
.progress-bar {
    height: 10px;
    background: #e8eef7;
    border-radius: 10px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: <?= $warna_jurusan ?>;
    border-radius: 10px;
    transition: width .5s ease;
}

.soal-card {
    background: #fff;
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 25px;
    border: 1px solid #e6efff;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}
.soal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f5ff;
}
.soal-number {
    color: <?= $warna_jurusan ?>;
    font-size: 18px;
    font-weight: 800;
}
.ragu-btn-soal {
    background: linear-gradient(90deg,#ff9a76 0%,#ffcc81 100%);
    color: white;
    border: none;
    padding: 7px 18px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 700;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ragu-btn-soal.active {
    background: linear-gradient(90deg,#ff8c00 0%,#ffb347 100%);
}
.soal-text {
    font-size: 16px;
    line-height: 1.7;
    color: #333;
    margin-bottom: 18px;
}
.soal-gambar {
    max-width: 100%;
    max-height: 300px;
    margin: 15px 0;
    border-radius: 12px;
    display: block;
    margin-left: auto;
    margin-right: auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.opsi-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.opsi-item {
    position: relative;
}
.opsi-item input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.opsi-label {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    background: #f8faff;
    border: 2px solid #e0e9ff;
    border-radius: 16px;
    cursor: pointer;
    gap: 18px;
}
.opsi-label:hover {
    background: #f0f5ff;
}
.opsi-circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid #b8c7e0;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.opsi-item input[type="radio"]:checked + .opsi-label {
    background: #f0f7ff;
    border-color: <?= $warna_jurusan ?>;
}
.opsi-item input[type="radio"]:checked + .opsi-label .opsi-circle {
    border-color: <?= $warna_jurusan ?>;
    background: <?= $warna_jurusan ?>;
}
.opsi-item input[type="radio"]:checked + .opsi-label .opsi-circle::after {
    content: '';
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: white;
    display: block;
}
.opsi-text {
    flex: 1;
    font-size: 15px;
    color: #444;
}
.opsi-letter {
    color: <?= $warna_jurusan ?>;
    font-weight: 800;
    margin-right: 10px;
}

.nav-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 35px;
    padding-top: 25px;
    border-top: 2px solid #f0f5ff;
}
.nav-btn {
    padding: 14px 32px;
    border-radius: 50px;
    border: none;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    transition: .25s;
    display: flex;
    align-items: center;
    gap: 12px;
}
.btn-prev {
    background: linear-gradient(135deg, #6c8cd5, #8ab4f8);
    color: white;
}
.btn-next {
    background: linear-gradient(135deg, #ffffff, <?= $warna_jurusan ?>);
    color: <?= $warna_jurusan ?>;
    border: 1px solid rgba(13,110,253,0.3);
}
.btn-next:hover {
    background: linear-gradient(135deg, #f0f7ff, <?= $warna_jurusan ?>);
    color: white;
}
.btn-submit {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    color: white;
}
.nav-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,.15);
}

.preview-modal {
    display: none;
    position: fixed;
    top: 0;
    right: 0;
    width: 360px;
    height: 100vh;
    background: white;
    box-shadow: -6px 0 40px rgba(0,0,0,.18);
    z-index: 1001;
    overflow-y: auto;
    padding: 25px;
}
.preview-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-bottom: 25px;
}
.preview-item {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    border: 2px solid #e6efff;
    font-size: 14px;
}
.preview-item.answered {
    background: <?= $warna_jurusan ?>;
    color: white;
    border-color: <?= $warna_jurusan ?>;
}
.preview-item.ragu {
    background: #ffcc00;
    color: #333;
    border-color: #ffaa00;
}
.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
}
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,.95);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    flex-direction: column;
    gap: 18px;
}
.spinner {
    width: 55px;
    height: 55px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid <?= $warna_jurusan ?>;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.submit-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(8px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}
.submit-modal {
    background: linear-gradient(135deg,<?= $warna_jurusan ?>,<?= $jurusan_mahasiswa == 'teknik' ? '#38bdf8' : '#4b90ff' ?>);
    width: 380px;
    border-radius: 32px;
    padding: 32px 28px;
    text-align: center;
    color: white;
}
.submit-modal-icon {
    width: 70px;
    height: 70px;
    background: rgba(255,255,255,.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
}
.submit-modal-icon i { font-size: 32px; }
.submit-modal h4 { font-size: 22px; margin-bottom: 10px; font-weight: 800; }
.submit-modal p { font-size: 14px; margin-bottom: 25px; opacity: 0.9; }
.submit-modal-buttons {
    display: flex;
    gap: 15px;
}
.submit-btn {
    flex: 1;
    border: none;
    border-radius: 20px;
    padding: 12px 0;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
}
.submit-btn.confirm {
    background: white;
    color: <?= $warna_jurusan ?>;
}
.submit-btn.cancel {
    background: rgba(255,255,255,.25);
    color: white;
}

@media (max-width: 900px) {
    .navbar { flex-direction: column; gap: 10px; }
    .navbar .logo { justify-content: center; }
    .navbar > div:not(.logo) { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
}
</style>

<script>
// ===== BERSIHKAN LOCALSTORAGE UNTUK USER BARU =====
const currentUserId = "<?= $id_maba ?>";
const currentJurusan = "<?= $jurusan_mahasiswa ?>";
const lastUserKey = `last_user_tes_${currentJurusan}`;
const lastUser = localStorage.getItem(lastUserKey);

if (lastUser !== currentUserId) {
    localStorage.removeItem(`tes_answers_${currentJurusan}`);
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(`ragu_soal_${currentJurusan}_`)) {
            localStorage.removeItem(key);
        }
    }
    localStorage.removeItem(lastUserKey);
    localStorage.setItem(lastUserKey, currentUserId);
    console.log("✅ Data tes lama dibersihkan untuk user baru: " + currentUserId);
}

let totalSeconds = 7200; // 2 jam
let timerInterval;
let allAnswers = {};
let jurusan = currentJurusan;

function startTimer(){
    updateTimerDisplay();
    timerInterval = setInterval(updateTimer,1000);
}
function updateTimer(){
    if(totalSeconds <= 0){
        clearInterval(timerInterval);
        submitTes();
        return;
    }
    totalSeconds--;
    updateTimerDisplay();
}
function updateTimerDisplay(){
    let h = Math.floor(totalSeconds / 3600);
    let m = Math.floor((totalSeconds % 3600) / 60);
    let s = Math.floor(totalSeconds % 60);
    document.getElementById("timer").innerHTML = `${h}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
    if(totalSeconds <= 300){
        document.querySelector(".timer-container").style.background = "linear-gradient(135deg,#ff0000 0%,#ff3333 100%)";
    } else if(totalSeconds <= 600){
        document.querySelector(".timer-container").style.background = "linear-gradient(135deg,#ff8c00 0%,#ffb347 100%)";
    }
}
function countAllAnswered(){
    let savedAnswers = localStorage.getItem(`tes_answers_${jurusan}`);
    if(savedAnswers){
        try{
            let parsedAnswers = JSON.parse(savedAnswers);
            allAnswers = parsedAnswers;
            return Object.keys(parsedAnswers).length;
        }catch(e){ return 0; }
    }
    return 0;
}
function updateProgress(){
    let answered = countAllAnswered();
    let total = <?= $total_data ?>;
    let percent = total > 0 ? Math.round((answered/total)*100) : 0;
    document.getElementById("progressFill").style.width = percent+"%";
    document.getElementById("progressText").innerHTML = `<i class="fas fa-check-circle"></i> Progress: ${answered} dari ${total} soal terjawab (${percent}%)`;
    updateSoalCounter();
    updatePreviewItems();
}
function updateSoalCounter(){
    let currentSoal = <?= $currentSoal ?>;
    document.getElementById("soalCounter").innerHTML = `<i class="fas fa-file-alt"></i> Soal ${currentSoal}/<?= $total_data ?>`;
}
function toggleRaguSoal(soalId, btn){
    let isActive = btn.classList.toggle('active');
    if(isActive){
        btn.innerHTML = '<i class="fas fa-flag"></i> Ragu';
    } else {
        btn.innerHTML = '<i class="far fa-flag"></i> Tandai Ragu';
    }
    localStorage.setItem(`ragu_soal_${jurusan}_${soalId}`, isActive);
    updatePreviewItems();
}
function updatePreviewItems(){
    let items = document.querySelectorAll('.preview-item');
    items.forEach((item,index)=>{
        let soalNumber = index+1;
        let soalId = getSoalIdByNumber(soalNumber);
        item.classList.remove('answered','ragu');
        if(checkIfAnswered(soalId)) item.classList.add('answered');
        let isRagu = localStorage.getItem(`ragu_soal_${jurusan}_${soalId}`) === 'true';
        if(isRagu) item.classList.add('ragu');
    });
}
function checkIfAnswered(soalId){
    let savedAnswers = localStorage.getItem(`tes_answers_${jurusan}`);
    if(savedAnswers){
        try{
            let answers = JSON.parse(savedAnswers);
            return answers[`jawaban[${soalId}]`] !== undefined && answers[`jawaban[${soalId}]`] !== '';
        }catch(e){ return false; }
    }
    return false;
}
function getSoalIdByNumber(soalNumber){
    try{
        let soalIds = <?= json_encode($soal_ids) ?>;
        if(soalIds && soalIds.length >= soalNumber) return soalIds[soalNumber-1];
    }catch(e){}
    return 0;
}
function saveAnswersToLocalStorage(){
    let currentAnswers = {};
    document.querySelectorAll('input[type="radio"]:checked').forEach(input=>{
        if(input.name && input.value) currentAnswers[input.name] = input.value;
    });
    let savedAnswers = localStorage.getItem(`tes_answers_${jurusan}`);
    if(savedAnswers){
        try{
            let existingAnswers = JSON.parse(savedAnswers);
            allAnswers = {...existingAnswers, ...currentAnswers};
        }catch(e){ allAnswers = currentAnswers; }
    } else { allAnswers = currentAnswers; }
    localStorage.setItem(`tes_answers_${jurusan}`, JSON.stringify(allAnswers));
    return allAnswers;
}
function togglePreview(){
    saveAnswersToLocalStorage();
    updatePreviewItems();
    document.getElementById('previewModal').style.display='block';
    document.getElementById('overlay').style.display='block';
}
function closePreview(){
    document.getElementById('previewModal').style.display='none';
    document.getElementById('overlay').style.display='none';
}
function goToPage(page){
    saveAnswersToLocalStorage();
    if(page >= 1 && page <= <?= $totalPage ?>){
        showLoader();
        window.location.href = `?page=${page}`;
    }
}
function showLoader(){ document.getElementById('loader').style.display='flex'; }
function submitTes(){ document.getElementById('submitModal').style.display='flex'; }
function closeSubmitModal(){ document.getElementById('submitModal').style.display='none'; }
function confirmSubmitTes(){
    closeSubmitModal();
    showLoader();
    let finalAnswers = saveAnswersToLocalStorage();
    let sisaWaktuInput = document.createElement('input');
    sisaWaktuInput.type = 'hidden';
    sisaWaktuInput.name = 'sisa_waktu';
    sisaWaktuInput.value = totalSeconds;
    document.getElementById('formTes').appendChild(sisaWaktuInput);
    let jurusanInput = document.createElement('input');
    jurusanInput.type = 'hidden';
    jurusanInput.name = 'jurusan';
    jurusanInput.value = jurusan;
    document.getElementById('formTes').appendChild(jurusanInput);
    document.getElementById('jawabanJson').value = JSON.stringify(finalAnswers);
    setTimeout(()=>{ document.getElementById('formTes').submit(); },500);
}
function loadSavedAnswersForCurrentPage(){
    let savedAnswers = localStorage.getItem(`tes_answers_${jurusan}`);
    if(savedAnswers){
        try{
            let answers = JSON.parse(savedAnswers);
            document.querySelectorAll('input[type="radio"]').forEach(input=>{
                if(answers[input.name] === input.value) input.checked = true;
            });
        }catch(e){}
    }
}
function loadRaguStatusForCurrentPage(){
    document.querySelectorAll('.ragu-btn-soal').forEach(btn=>{
        let soalId = btn.id.replace('ragu-btn-','');
        if(soalId){
            let savedRagu = localStorage.getItem(`ragu_soal_${jurusan}_${soalId}`);
            if(savedRagu === 'true'){
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-flag"></i> Ragu';
            }
        }
    });
}
document.addEventListener('DOMContentLoaded',function(){
    startTimer();
    loadSavedAnswersForCurrentPage();
    loadRaguStatusForCurrentPage();
    updateProgress();
    setInterval(function(){ saveAnswersToLocalStorage(); updateProgress(); },3000);
    document.querySelectorAll('input[type="radio"]').forEach(input=>{
        input.addEventListener('change',function(){ saveAnswersToLocalStorage(); updateProgress(); });
    });
});
</script>
</head>
<body>

<!-- LOADER -->
<div class="loader" id="loader">
    <div class="spinner"></div>
    <div class="loader-text">Memproses jawaban...</div>
</div>

<!-- SUBMIT MODAL -->
<div class="submit-modal-overlay" id="submitModal">
    <div class="submit-modal">
        <div class="submit-modal-icon"><i class="fas fa-paper-plane"></i></div>
        <h4>Kumpulkan Tes?</h4>
        <p>Pastikan semua soal sudah dijawab sebelum mengirimkan jawaban.</p>
        <div class="submit-modal-buttons">
            <button class="submit-btn cancel" onclick="closeSubmitModal()">Batal</button>
            <button class="submit-btn confirm" onclick="confirmSubmitTes()">Kirim</button>
        </div>
    </div>
</div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">
        <img src="../assets/logokampus1.png" alt="Logo UCN" class="logo-img" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2744%27 height=%2744%27%3E%3Crect width=%2744%27 height=%2744%27 fill=%27%23ffffff%27/%3E%3Ctext x=%2722%27 y=%2728%27 text-anchor=%27middle%27 fill=%27%23333%27 font-size=%2720%27%3E🎓%3C/text%3E%3C/svg%3E'">
        <span>UNIVERSITAS CENDEKIA NUSANTARA</span>
    </div>
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div class="user-info">
            <div class="user-id">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($id_maba_formatted) ?> - <?= htmlspecialchars($nama_user) ?>
            </div>
        </div>
        
        <div class="jurusan-badge">
            <i class="bi <?= $icon_tes ?>"></i>
            <span><?= $teks_jurusan ?></span>
        </div>
        
        <div class="soal-counter" id="soalCounter">
            <i class="fas fa-file-alt"></i> Soal <?= $currentSoal ?>/<?= $total_data ?>
        </div>
        
        <div class="timer-container">
            <i class="fas fa-clock"></i>
            <span id="timer">2:00:00</span>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">
    <div class="progress-section">
        <div class="progress-info">
            <div class="progress-text" id="progressText">
                <i class="fas fa-check-circle"></i> Progress: 0 dari <?= $total_data ?> soal terjawab (0%)
            </div>
            <button onclick="togglePreview()" style="background:linear-gradient(90deg,<?= $warna_jurusan ?>,<?= $jurusan_mahasiswa == 'teknik' ? '#38bdf8' : '#4b90ff' ?>);color:white;border:none;padding:8px 20px;border-radius:30px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="fas fa-list-ul"></i> Daftar Soal
            </button>
        </div>
        <div class="progress-bar">
            <div id="progressFill" class="progress-fill" style="width:0%"></div>
        </div>
    </div>
    
    <form method="POST" action="submit_tes.php" id="formTes">
        <input type="hidden" name="jawaban_json" id="jawabanJson">
        <input type="hidden" name="jurusan" value="<?= $jurusan_mahasiswa ?>">
        
        <?php 
        $no = $start + 1;
        while ($row = mysqli_fetch_assoc($soal)) { 
        ?>
        <div class="soal-card">
            <div class="soal-header">
                <div class="soal-number">Soal <?= $no ?></div>
                <button type="button" class="ragu-btn-soal" onclick="toggleRaguSoal(<?= $row['id_soal'] ?>, this)" id="ragu-btn-<?= $row['id_soal'] ?>">
                    <i class="far fa-flag"></i> Tandai Ragu
                </button>
            </div>
            
            <div class="soal-text"><?= nl2br(htmlspecialchars($row['pertanyaan'])) ?></div>
            
            <!-- PERBAIKAN GAMBAR - VERSION FIX -->
            <?php if (!empty($row['gambar'])): ?>
            <div class="text-center">
                <?php 
                // Ambil nama file dari database (langsung pake apa adanya)
                $nama_gambar = htmlspecialchars($row['gambar']);
                // Path langsung ke folder uploads
                $path_gambar = "../admin/uploads/" . $nama_gambar;
                ?>
                <img src="<?= $path_gambar ?>" class="soal-gambar" alt="Gambar Soal" 
                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27300%27 height=%27200%27%3E%3Crect width=%27300%27 height=%27200%27 fill=%27%23fff3cd%27/%3E%3Ctext x=%27150%27 y=%2790%27 text-anchor=%27middle%27 fill=%27%2385604e%27 font-size=%2714%27%3E⚠️ Gambar tidak ditemukan%3C/text%3E%3Ctext x=%27150%27 y=%27115%27 text-anchor=%27middle%27 fill=%27%2385604e%27 font-size=%2712%27%3E<?= $nama_gambar ?>%3C/text%3E%3C/svg%3E'">
            </div>
            <?php endif; ?>
            
            <div class="opsi-container">
                <?php if(!empty($row['opsi_a'])): ?>
                <div class="opsi-item">
                    <input type="radio" name="jawaban[<?= $row['id_soal'] ?>]" value="A" id="jawaban_<?= $row['id_soal'] ?>_A">
                    <label class="opsi-label" for="jawaban_<?= $row['id_soal'] ?>_A">
                        <div class="opsi-circle"></div>
                        <div class="opsi-text"><span class="opsi-letter">A.</span> <?= nl2br(htmlspecialchars($row['opsi_a'])) ?></div>
                    </label>
                </div>
                <?php endif; ?>
                <?php if(!empty($row['opsi_b'])): ?>
                <div class="opsi-item">
                    <input type="radio" name="jawaban[<?= $row['id_soal'] ?>]" value="B" id="jawaban_<?= $row['id_soal'] ?>_B">
                    <label class="opsi-label" for="jawaban_<?= $row['id_soal'] ?>_B">
                        <div class="opsi-circle"></div>
                        <div class="opsi-text"><span class="opsi-letter">B.</span> <?= nl2br(htmlspecialchars($row['opsi_b'])) ?></div>
                    </label>
                </div>
                <?php endif; ?>
                <?php if(!empty($row['opsi_c'])): ?>
                <div class="opsi-item">
                    <input type="radio" name="jawaban[<?= $row['id_soal'] ?>]" value="C" id="jawaban_<?= $row['id_soal'] ?>_C">
                    <label class="opsi-label" for="jawaban_<?= $row['id_soal'] ?>_C">
                        <div class="opsi-circle"></div>
                        <div class="opsi-text"><span class="opsi-letter">C.</span> <?= nl2br(htmlspecialchars($row['opsi_c'])) ?></div>
                    </label>
                </div>
                <?php endif; ?>
                <?php if(!empty($row['opsi_d'])): ?>
                <div class="opsi-item">
                    <input type="radio" name="jawaban[<?= $row['id_soal'] ?>]" value="D" id="jawaban_<?= $row['id_soal'] ?>_D">
                    <label class="opsi-label" for="jawaban_<?= $row['id_soal'] ?>_D">
                        <div class="opsi-circle"></div>
                        <div class="opsi-text"><span class="opsi-letter">D.</span> <?= nl2br(htmlspecialchars($row['opsi_d'])) ?></div>
                    </label>
                </div>
                <?php endif; ?>
                <?php if(!empty($row['opsi_e'])): ?>
                <div class="opsi-item">
                    <input type="radio" name="jawaban[<?= $row['id_soal'] ?>]" value="E" id="jawaban_<?= $row['id_soal'] ?>_E">
                    <label class="opsi-label" for="jawaban_<?= $row['id_soal'] ?>_E">
                        <div class="opsi-circle"></div>
                        <div class="opsi-text"><span class="opsi-letter">E.</span> <?= nl2br(htmlspecialchars($row['opsi_e'])) ?></div>
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            $no++;
        }
        ?>
        
        <div class="nav-buttons">
            <?php if ($page > 1) { ?>
            <button type="button" class="nav-btn btn-prev" onclick="goToPage(<?= $page-1 ?>)">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <?php } else { ?>
            <div></div>
            <?php } ?>
            
            <?php if ($page < $totalPage) { ?>
            <button type="button" class="nav-btn btn-next" onclick="goToPage(<?= $page+1 ?>)">
                Lanjut <i class="fas fa-arrow-right"></i>
            </button>
            <?php } else { ?>
            <button type="button" class="nav-btn btn-submit" onclick="submitTes()">
                <i class="fas fa-paper-plane"></i> Submit Tes
            </button>
            <?php } ?>
        </div>
    </form>
</div>

<!-- PREVIEW MODAL -->
<div class="overlay" id="overlay" onclick="closePreview()"></div>
<div class="preview-modal" id="previewModal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #eef4ff;">
        <div style="font-size:18px;font-weight:800;color:<?= $warna_jurusan ?>;"><i class="fas fa-list-ol"></i> Daftar Soal</div>
        <button onclick="closePreview()" style="background:#f0f5ff;border:none;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:18px;">&times;</button>
    </div>
    <div class="preview-grid">
        <?php for($i=1;$i<=$total_data;$i++){ ?>
        <div class="preview-item" onclick="goToPage(<?= ceil($i/10) ?>)"><?= $i ?></div>
        <?php } ?>
    </div>
    <div class="preview-status">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <div style="width:14px;height:14px;background:<?= $warna_jurusan ?>;border-radius:4px;"></div>
            <span style="font-size:12px;">Sudah Dijawab</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <div style="width:14px;height:14px;background:#ffcc00;border-radius:4px;"></div>
            <span style="font-size:12px;">Ragu-Ragu</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:14px;height:14px;background:white;border:2px solid #e6efff;border-radius:4px;"></div>
            <span style="font-size:12px;">Belum Dijawab</span>
        </div>
    </div>
</div>

</body>
</html>