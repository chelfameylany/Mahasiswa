<?php
include "../koneksi.php";
session_start();

// Inisialisasi session pendaftaran_data jika belum ada
if (!isset($_SESSION['pendaftaran_data'])) {
    $_SESSION['pendaftaran_data'] = [];
}

// Proses step 1: Simpan data diri dan pendidikan ke session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step'])) {
    if ($_POST['step'] == '1') {
        // Validasi NIK
        $nik_input = str_replace(' ', '', $_POST['nik']);
        if (strlen($nik_input) != 16 || !is_numeric($nik_input)) {
            $_SESSION['popup'] = [
                'type' => 'error',
                'title' => 'NIK Tidak Valid',
                'message' => 'NIK harus 16 digit angka.'
            ];
            header("Location: pendaftaran-user.php"); // ← PERBAIKAN: kembali ke form, bukan ke pilih_prodi
            exit;
        }
        
        // Validasi email
        $email_input = $_POST['email'];
        if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['popup'] = [
                'type' => 'error',
                'title' => 'Email Tidak Valid',
                'message' => 'Format email tidak sesuai.'
            ];
            header("Location: pendaftaran-user.php");
            exit;
        }
        
        // Cek email sudah terdaftar
        $check_email = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE email='$email_input'");
        if (mysqli_num_rows($check_email) > 0) {
            $_SESSION['popup'] = [
                'type' => 'error',
                'title' => 'Email Sudah Terdaftar',
                'message' => 'Email sudah digunakan. Silakan gunakan email lain.'
            ];
            header("Location: pendaftaran-user.php");
            exit;
        }
        
        // Simpan data ke session
        $_SESSION['pendaftaran_data'] = [
            'nama' => mysqli_real_escape_string($koneksi, $_POST['nama']),
            'kewarganegaraan' => mysqli_real_escape_string($koneksi, $_POST['kewarganegaraan']),
            'nik' => $nik_input,
            'jenis_kelamin' => mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']),
            'tempat_lahir' => mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']),
            'tanggal_lahir' => mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']),
            'alamat' => mysqli_real_escape_string($koneksi, $_POST['alamat']),
            'asal_sekolah' => mysqli_real_escape_string($koneksi, $_POST['asal_sekolah']),
            'tahun_lulus' => mysqli_real_escape_string($koneksi, $_POST['tahun_lulus']),
            'email' => $email_input,
            'agama' => mysqli_real_escape_string($koneksi, $_POST['agama'])
        ];
        
        // Redirect ke halaman pilih prodi di folder user
        header("Location: ../user/pilih_prodi.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Formulir Pendaftaran - Universitas Cendekia Nusantara</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* ===== STYLING UTAMA ===== */
:root {
  --primary-color: #0d3b66;
  --secondary-color: #0d6efd;
  --accent-color: #22c55e;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body { 
    font-family: 'Plus Jakarta Sans', sans-serif; 
    background: linear-gradient(180deg, #f8fafc 0%, #0d6efd 60%, #0d3b66 100%); 
    min-height: 100vh; 
    padding: 30px 0;
}

.register-container { 
    max-width: 900px; 
    margin: 0 auto;
}

.register-card { 
    background: white; 
    border-radius: 24px; 
    box-shadow: 0 25px 60px rgba(13,59,102,.15); 
    overflow: hidden; 
    margin-bottom: 20px;
}

.register-header { 
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
    color: white; 
    padding: 2.5rem; 
    text-align: center;
}

.register-title { 
    font-weight: 900; 
    margin-bottom: .75rem; 
    font-size: 32px;
}

.register-header p {
    font-size: 18px;
    opacity: 0.95;
}

.register-body { 
    padding: 3rem;
}

.form-label { 
    font-weight: 700; 
    color: var(--primary-color); 
    margin-bottom: .5rem;
    font-size: 16px;
}

.form-control, .form-select { 
    border-radius: 14px; 
    padding: .9rem 1.2rem; 
    border: 1.5px solid #e2e8f0; 
    transition: .25s;
    font-size: 16px;
}

.form-control:focus, .form-select:focus { 
    border-color: var(--secondary-color); 
    box-shadow: 0 0 0 4px rgba(13,110,253,.15);
}

.form-control::placeholder {
    font-size: 15px;
}

.btn-submit { 
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
    color: white; 
    font-weight: 800; 
    padding: 1.2rem 2rem; 
    border-radius: 16px; 
    border: none; 
    width: 100%; 
    font-size: 18px; 
    transition: .3s;
    margin-bottom: 15px;
}

.btn-submit:hover { 
    transform: translateY(-3px); 
    box-shadow: 0 15px 35px rgba(13,59,102,.3);
}

.btn-back-home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 16px;
    padding: 1.2rem 2rem;
    font-size: 18px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-back-home:hover {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(13,59,102,.3);
}

.btn-back-login {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border: none;
    border-radius: 16px;
    padding: 1.2rem 2rem;
    font-size: 18px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-back-login:hover {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(34,197,94,.3);
}

.button-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 25px;
}

.step-indicator { 
    display: flex; 
    justify-content: space-between; 
    margin-bottom: 2.5rem; 
    position: relative;
}

.step-indicator::before { 
    content: ''; 
    position: absolute; 
    top: 24px; 
    left: 0; 
    right: 0; 
    height: 4px; 
    background-color: #e2e8f0; 
    z-index: 1;
}

.step { 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    position: relative; 
    z-index: 2;
}

.step-circle { 
    width: 48px; 
    height: 48px; 
    border-radius: 50%; 
    background: #e2e8f0; 
    color: #64748b; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-weight: 800; 
    font-size: 18px;
    margin-bottom: .75rem; 
    transition: .3s;
}

.step.active .step-circle { 
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
    color: white;
}

.step-label { 
    font-size: 1rem; 
    font-weight: 700; 
    color: #64748b;
}

.step.active .step-label { 
    color: var(--primary-color);
}

.info-box { 
    background: #f8fafc; 
    border-left: 5px solid var(--secondary-color); 
    padding: 1.25rem; 
    border-radius: 12px; 
    margin-bottom: 2rem;
    font-size: 16px;
}

.required::after { 
    content: " *"; 
    color: #dc3545;
    font-weight: 800;
}

.form-control.is-invalid { 
    border-color: #dc3545;
}

.invalid-feedback { 
    display: none; 
    color: #dc3545; 
    font-size: 15px; 
    margin-top: .5rem;
    font-weight: 500;
}

.form-control.is-invalid + .invalid-feedback { 
    display: block;
}

/* ===== POPUP ===== */
.popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: .35s;
}

.popup-overlay.show {
    opacity: 1;
    pointer-events: auto;
}

.popup-box {
    min-width: 350px;
    max-width: 450px;
    border-radius: 28px;
    padding: 35px 30px 30px;
    text-align: center;
    color: white;
    transform: translateY(-40px) scale(.92);
    opacity: 0;
    transition: .45s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 35px 80px rgba(0,0,0,.4);
}

.popup-overlay.show .popup-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}

.popup-success { 
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
}

.popup-error { 
    background: linear-gradient(135deg, #7c0a02, #dc3545);
}

.popup-warning { 
    background: linear-gradient(135deg, #0d6efd, #38bdf8);
}

.popup-icon { 
    font-size: 72px; 
    margin-bottom: 18px; 
    animation: popIcon .6s ease;
}

@keyframes popIcon {
    0% { transform: scale(.3); opacity: 0; }
    70% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

.popup-title { 
    font-weight: 900; 
    font-size: 1.5rem; 
    margin-bottom: 10px;
}

.popup-message { 
    font-size: 1rem; 
    opacity: .95;
    line-height: 1.5;
}

.popup-btn {
    margin-top: 25px;
    background: white;
    color: #0d6efd;
    border: none;
    padding: 12px 32px;
    border-radius: 50px;
    font-weight: 800;
    font-size: 16px;
    transition: .2s;
}

.popup-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(255,255,255,.3);
}

/* ===== TEXT STYLES ===== */
h4 {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 1.5rem !important;
}

small.text-muted {
    font-size: 14px;
    margin-top: 6px;
    display: block;
}

.form-check-label {
    font-size: 16px;
    font-weight: 500;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .register-body {
        padding: 2rem;
    }
    
    .register-title {
        font-size: 28px;
    }
    
    .step-label {
        font-size: .9rem;
    }
    
    h4 {
        font-size: 22px;
    }
}
</style>
</head>
<body>

<div class="container register-container">
    <div class="register-card">
        <div class="register-header">
            <h1 class="register-title">Formulir Pendaftaran Mahasiswa Baru</h1>
            <p>Universitas Cendekia Nusantara - Tahun Akademik 2024/2025</p>
        </div>
        
        <div class="register-body">
            <div class="info-box">
                <p class="mb-0"><i class="bi bi-info-circle me-2"></i>Harap isi semua kolom yang bertanda (<span class="required"></span>) dengan benar dan lengkap.</p>
            </div>
            
            <div class="step-indicator">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-label">Data Diri & Pendidikan</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="step-label">Pilih Program Studi</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">Konfirmasi</div>
                </div>
            </div>

            <form action="" method="POST" id="registrationForm">
                <input type="hidden" name="step" value="1">
                
                <h4>
                    <i class="bi bi-person-circle me-2"></i>Data Pribadi Calon Mahasiswa
                </h4>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" required placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($_SESSION['pendaftaran_data']['nama'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">Kewarganegaraan</label>
                        <select class="form-select" name="kewarganegaraan" required>
                            <option value="">Pilih Kewarganegaraan</option>
                            <option value="WNI" <?= (($_SESSION['pendaftaran_data']['kewarganegaraan'] ?? '') == 'WNI') ? 'selected' : '' ?>>WNI</option>
                            <option value="WNA" <?= (($_SESSION['pendaftaran_data']['kewarganegaraan'] ?? '') == 'WNA') ? 'selected' : '' ?>>WNA</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">NIK</label>
                        <input type="text" class="form-control" name="nik" id="nik" required placeholder="16 digit NIK" maxlength="19" value="<?= htmlspecialchars($_SESSION['pendaftaran_data']['nik'] ?? '') ?>">
                        <small class="text-muted">Contoh: 3275 0101 0101 0001</small>
                        <div class="invalid-feedback" id="nik-error">NIK harus 16 digit angka</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="nama@gmail.com" value="<?= htmlspecialchars($_SESSION['pendaftaran_data']['email'] ?? '') ?>">
                        <small class="text-muted">Masukkan email yang aktif</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin" required>
                            <option value="">Pilih</option>
                            <option value="L" <?= (($_SESSION['pendaftaran_data']['jenis_kelamin'] ?? '') == 'L') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= (($_SESSION['pendaftaran_data']['jenis_kelamin'] ?? '') == 'P') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">Tempat Lahir</label>
                        <input type="text" class="form-control" name="tempat_lahir" required placeholder="Kota lahir" value="<?= htmlspecialchars($_SESSION['pendaftaran_data']['tempat_lahir'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir" required value="<?= htmlspecialchars($_SESSION['pendaftaran_data']['tanggal_lahir'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label required">Agama</label>
                        <select class="form-select" name="agama" required>
                            <option value="">Pilih Agama</option>
                            <option value="Islam" <?= (($_SESSION['pendaftaran_data']['agama'] ?? '') == 'Islam') ? 'selected' : '' ?>>Islam</option>
                            <option value="Kristen" <?= (($_SESSION['pendaftaran_data']['agama'] ?? '') == 'Kristen') ? 'selected' : '' ?>>Kristen</option>
                            <option value="Katolik" <?= (($_SESSION['pendaftaran_data']['agama'] ?? '') == 'Katolik') ? 'selected' : '' ?>>Katolik</option>
                            <option value="Hindu" <?= (($_SESSION['pendaftaran_data']['agama'] ?? '') == 'Hindu') ? 'selected' : '' ?>>Hindu</option>
                            <option value="Buddha" <?= (($_SESSION['pendaftaran_data']['agama'] ?? '') == 'Buddha') ? 'selected' : '' ?>>Buddha</option>
                            <option value="Konghucu" <?= (($_SESSION['pendaftaran_data']['agama'] ?? '') == 'Konghucu') ? 'selected' : '' ?>>Konghucu</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label required">Alamat Lengkap</label>
                        <textarea class="form-control" name="alamat" rows="3" required placeholder="Alamat lengkap"><?= htmlspecialchars($_SESSION['pendaftaran_data']['alamat'] ?? '') ?></textarea>
                    </div>
                </div>

                <hr class="my-4">
                
                <h4>
                    <i class="bi bi-mortarboard me-2"></i>Data Pendidikan
                </h4>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label required">Asal Sekolah</label>
                        <input type="text" class="form-control" name="asal_sekolah" required placeholder="Nama sekolah" value="<?= htmlspecialchars($_SESSION['pendaftaran_data']['asal_sekolah'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Tahun Lulus</label>
                        <select class="form-select" name="tahun_lulus" required>
                            <option value="">Pilih</option>
                            <?php for($y = 2020; $y <= 2025; $y++): ?>
                                <option value="<?= $y ?>" <?= (($_SESSION['pendaftaran_data']['tahun_lulus'] ?? '') == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-check mb-4 mt-4">
                    <input class="form-check-input" type="checkbox" required id="agreeTerms" style="width: 18px; height: 18px;">
                    <label class="form-check-label" for="agreeTerms">Saya menyatakan bahwa data yang saya berikan adalah benar dan dapat dipertanggungjawabkan.</label>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-arrow-right-circle me-2"></i>Lanjut ke Pemilihan Program Studi
                    </button>
                    
                    <a href="../dashboard_utama.php" class="btn-back-home">
                        <i class="bi bi-house-door me-2"></i>Kembali ke Beranda
                    </a>
                    <a href="login_maba.php" class="btn-back-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sudah Punya Akun? Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-white mt-4" style="opacity: 0.9; font-size: 15px;">
        <small><i class="bi bi-shield-check me-1"></i>Data Anda aman dan terlindungi sesuai dengan kebijakan privasi kami.</small>
    </div>
</div>

<!-- ===== POPUP ===== -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup-box" id="popupBox">
        <div class="popup-icon" id="popupIcon"></div>
        <div class="popup-title" id="popupTitle"></div>
        <div class="popup-message" id="popupMessage"></div>
        <div style="margin-top:25px; display:flex; justify-content:center; gap:15px;">
            <button class="popup-btn" id="popupBtnOk">OK</button>
            <button class="popup-btn" id="popupBtnCancel" style="background:#dc3545;color:white;">Batal</button>
        </div>
    </div>
</div>

<script>
// ===== FUNGSI POPUP =====
function showPopup(type, title, message, callbackOk = null, callbackCancel = null) {
    const overlay = document.getElementById('popupOverlay');
    const box = document.getElementById('popupBox');
    const icon = document.getElementById('popupIcon');
    const btnOk = document.getElementById('popupBtnOk');
    const btnCancel = document.getElementById('popupBtnCancel');

    box.className = 'popup-box popup-' + type;
    
    if (type === 'success') icon.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
    if (type === 'error') icon.innerHTML = '<i class="bi bi-x-circle-fill"></i>';
    if (type === 'warning') icon.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>';

    document.getElementById('popupTitle').innerText = title;
    document.getElementById('popupMessage').innerText = message;
    overlay.classList.add('show');

    // Reset event listeners
    btnOk.onclick = () => { overlay.classList.remove('show'); if (callbackOk) callbackOk(); };
    btnCancel.onclick = () => { overlay.classList.remove('show'); if (callbackCancel) callbackCancel(); };
}

// ===== FORMAT & VALIDASI NIK =====
function formatNIK(value) { 
    let digits = value.replace(/\D/g, '').substring(0, 16); 
    return digits.replace(/(.{4})/g, '$1 ').trim();
}

function validateNIK(nikValue) { 
    const digits = nikValue.replace(/\s/g, ''); 
    if (!/^\d+$/.test(digits)) return { valid: false, message: 'NIK hanya boleh angka' }; 
    if (digits.length !== 16) return { valid: false, message: 'NIK harus 16 digit' }; 
    return { valid: true }; 
}

document.addEventListener('DOMContentLoaded', () => {
    const nikInput = document.getElementById('nik');
    const nikError = document.getElementById('nik-error');
    const form = document.getElementById('registrationForm');
    const today = new Date().toISOString().split('T')[0];
    const tanggalInput = document.querySelector('input[name="tanggal_lahir"]');
    if (tanggalInput) tanggalInput.max = today;

    // Format NIK
    if (nikInput) {
        nikInput.addEventListener('input', () => {
            nikInput.value = formatNIK(nikInput.value);
            const v = validateNIK(nikInput.value);
            if (!v.valid && nikInput.value.replace(/\s/g, '').length > 0) { 
                nikInput.classList.add('is-invalid'); 
                nikError.textContent = v.message;
            } else {
                nikInput.classList.remove('is-invalid');
            }
        });
    }

    // Submit form
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        let valid = true;
        
        // Validasi NIK
        const nikVal = validateNIK(nikInput.value);
        if (!nikVal.valid) { 
            nikInput.classList.add('is-invalid'); 
            nikError.textContent = nikVal.message; 
            nikInput.focus(); 
            valid = false;
        }
        
        // Validasi tanggal lahir
        const tanggal = document.querySelector('input[name="tanggal_lahir"]').value;
        if (tanggal > today && tanggal !== '') { 
            showPopup('error', 'Tanggal Tidak Valid', 'Tanggal lahir tidak boleh lebih dari hari ini.'); 
            valid = false;
        }
        
        // Validasi email
        const email = document.querySelector('input[name="email"]');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value)) { 
            showPopup('error', 'Email Tidak Valid', 'Format email tidak sesuai.'); 
            email.focus(); 
            valid = false;
        }
        
        // Validasi checkbox
        const agreeTerms = document.getElementById('agreeTerms');
        if (!agreeTerms.checked) {
            showPopup('error', 'Konfirmasi Diperlukan', 'Anda harus menyetujui pernyataan data yang diisi benar.');
            valid = false;
        }
        
        if (!valid) return;
        
        // Bersihkan NIK dari spasi
        nikInput.value = nikInput.value.replace(/\s/g, '');
        
        // Konfirmasi sebelum lanjut
        showPopup('warning', 'Konfirmasi Data', 'Apakah data diri dan pendidikan yang Anda isi sudah benar?', () => { 
            form.submit(); 
        });
    });
});

<?php if (isset($_SESSION['popup'])): ?>
showPopup("<?= $_SESSION['popup']['type'] ?>", "<?= $_SESSION['popup']['title'] ?>", "<?= $_SESSION['popup']['message'] ?>");
<?php unset($_SESSION['popup']); endif; ?>
</script>

</body>
</html>