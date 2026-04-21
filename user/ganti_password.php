<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit();
}

$username = $_SESSION['maba'];
$query = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username = '$username'");
$maba = mysqli_fetch_assoc($query);

// ===== DETEKSI WARNA BERDASARKAN JURUSAN =====
$jurusan_asli = $maba['jurusan'] ?? 'umum';
$jurusan_lower = strtolower(trim($jurusan_asli));

if(strpos($jurusan_lower, 'teknik') !== false) {
    // WARNA HIJAU UNTUK TEKNIK
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #2E7D32, #1B5E20)";
    $warna_soft = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan = "fa-gear";
} else {
    // WARNA BIRU UNTUK UMUM
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #DBEAFE 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #3B82F6, #2563EB)";
    $warna_soft = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan = "fa-users";
}

$popup = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $popup = ['type'=>'error','title'=>'Error','message'=>'Semua field harus diisi!'];
    } elseif ($password_baru != $konfirmasi_password) {
        $popup = ['type'=>'error','title'=>'Error','message'=>'Password baru dan konfirmasi tidak cocok!'];
    } elseif (strlen($password_baru) < 6) {
        $popup = ['type'=>'error','title'=>'Error','message'=>'Password baru minimal 6 karakter!'];
    } elseif ($password_baru == $password_lama) {
        $popup = ['type'=>'error','title'=>'Error','message'=>'Password baru harus berbeda dengan password lama!'];
    } else {
        if (password_verify($password_lama, $maba['password_hash'])) {
            $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $update_query = "UPDATE calon_maba SET password_hash='$password_hash_baru' WHERE username='$username'";
            if (mysqli_query($koneksi, $update_query)) {
                $popup = ['type'=>'success','title'=>'Berhasil','message'=>'Password berhasil diubah!'];
            } else {
                $popup = ['type'=>'error','title'=>'Error','message'=>'Gagal mengubah password: '.mysqli_error($koneksi)];
            }
        } else {
            $popup = ['type'=>'error','title'=>'Error','message'=>'Password lama salah!'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ganti Password - Universitas Cendekia Nusantara</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
body {
    font-family:'Plus Jakarta Sans',sans-serif;
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: <?= $warna_gradient_bg ?>;
    padding:20px;
}

.card-password {
    background:white;
    border-radius:28px;
    padding:36px 40px;
    box-shadow:0 30px 80px rgba(0,0,0,0.15);
    width:600px;
    max-width:95%;
    position:relative;
    overflow:hidden;
}

.card-title {
    font-size:1.8rem;
    font-weight:800;
    color:#1F2937;
    margin-bottom:1.5rem;
    display:flex;
    align-items:center;
}

.card-title i { 
    margin-right:12px; 
    font-size:2rem; 
    color: <?= $warna_primary ?>;
}

.form-label { 
    font-weight:600; 
    margin-bottom:0.5rem; 
    color:#334155; 
}

.input-group { 
    position:relative; 
}

.password-toggle {
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    background:none;
    border:none;
    color:#94a3b8;
    z-index:10;
}

.form-control { 
    border-radius:12px; 
    padding:.875rem 1rem; 
    border:1px solid #e2e8f0; 
    transition:.3s; 
}

.form-control:focus { 
    border-color: <?= $warna_primary ?>; 
    box-shadow: 0 0 0 3px rgba(<?= $warna_primary == "#2E7D32" ? "46,125,50" : "59,130,246" ?>, 0.12);
}

.btn-submit {
    width:100%;
    padding:.875rem 0;
    font-weight:700;
    border-radius:14px;
    border:none;
    background: <?= $warna_gradient_card ?>;
    color:white;
    font-size:1.1rem;
    transition:.3s;
}
.btn-submit:hover { 
    transform:translateY(-2px); 
    box-shadow:0 12px 30px rgba(0,0,0,0.2); 
}

.btn-back { 
    margin-top:15px; 
    display:block; 
    width:100%; 
    text-align:center; 
    padding:.75rem 0; 
    font-weight:600; 
    border-radius:12px; 
    text-decoration:none; 
    color: <?= $warna_primary ?>;
    background:white;
    border: 1.5px solid <?= $warna_primary ?>;
    transition:.2s;
}
.btn-back:hover { 
    transform:scale(1.02); 
    background: <?= $warna_primary ?>;
    color:white;
}

.password-strength { 
    height:6px; 
    border-radius:6px; 
    margin-top:8px; 
    background:#e2e8f0; 
    overflow:hidden; 
}

.strength-meter { 
    height:100%; 
    width:0%; 
    border-radius:6px; 
    transition:.3s; 
}

.strength-weak { background:#ef4444; }
.strength-medium { background:#f59e0b; }
.strength-strong { background:#22c55e; }

.password-rules { 
    background:#f8fafc; 
    border-radius:12px; 
    padding:1rem; 
    font-size:.9rem; 
    margin-top:1rem; 
}

.password-rules ul { 
    padding-left:1.2rem; 
    margin-bottom:0; 
}

.password-rules li { 
    margin-bottom:0.25rem; 
}

.rule-valid { color:#22c55e; }
.rule-invalid { color:#94a3b8; }

/* Popup */
.popup-overlay { 
    position:fixed; 
    inset:0; 
    background:rgba(0,0,0,.45); 
    display:flex; 
    justify-content:center; 
    align-items:center; 
    z-index:9999; 
    opacity:0; 
    pointer-events:none; 
    transition:.35s; 
}

.popup-overlay.show { 
    opacity:1; 
    pointer-events:auto; 
}

.popup-box { 
    background:white; 
    border-radius:24px; 
    min-width:320px; 
    max-width:400px; 
    text-align:center; 
    padding:30px 26px; 
    transform:translateY(-40px) scale(.92); 
    opacity:0; 
    transition:.45s cubic-bezier(.34,1.56,.64,1); 
    box-shadow:0 30px 70px rgba(0,0,0,.25); 
}

.popup-overlay.show .popup-box { 
    transform:translateY(0) scale(1); 
    opacity:1; 
}

.popup-title { 
    font-weight:800; 
    font-size:1.3rem; 
    margin-bottom:10px; 
}

.popup-message { 
    font-size:.95rem; 
    opacity:.95; 
}

.popup-btn { 
    margin-top:20px; 
    border:none; 
    padding:10px 24px; 
    border-radius:999px; 
    font-weight:700; 
    transition:.2s; 
    cursor:pointer; 
}

.popup-success .popup-btn { 
    background: <?= $warna_primary ?>; 
    color:white; 
}

.popup-error .popup-btn { 
    background:#dc2626; 
    color:white; 
}

.popup-btn:hover { 
    transform:translateY(-2px); 
}
</style>
</head>
<body>

<div class="card-password">
    <div class="card-title">
        <i class="bi bi-key"></i> Ganti Password
    </div>

    <form method="POST" id="passwordForm">
        <div class="mb-4">
            <label class="form-label">Password Lama</label>
            <div class="input-group">
                <input type="password" class="form-control" name="password_lama" id="password_lama" required>
                <button type="button" class="password-toggle" data-target="password_lama"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Password Baru</label>
            <div class="input-group">
                <input type="password" class="form-control" name="password_baru" id="password_baru" required>
                <button type="button" class="password-toggle" data-target="password_baru"><i class="bi bi-eye"></i></button>
            </div>
            <div class="password-strength"><div class="strength-meter" id="strengthMeter"></div></div>
            <div class="password-rules">
                <ul>
                    <li id="rule-length" class="rule-invalid">Minimal 6 karakter</li>
                    <li id="rule-uppercase" class="rule-invalid">Mengandung huruf besar</li>
                    <li id="rule-lowercase" class="rule-invalid">Mengandung huruf kecil</li>
                    <li id="rule-number" class="rule-invalid">Mengandung angka</li>
                </ul>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Konfirmasi Password Baru</label>
            <div class="input-group">
                <input type="password" class="form-control" name="konfirmasi_password" id="konfirmasi_password" required>
                <button type="button" class="password-toggle" data-target="konfirmasi_password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="mt-2" id="passwordMatch"></div>
        </div>

        <button type="submit" class="btn-submit">Simpan Password Baru</button>
        <a href="dashboard_maba.php" class="btn-back">Kembali ke Dashboard</a>
    </form>
</div>

<!-- Popup -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup-box" id="popupBox">
        <div class="popup-title" id="popupTitle"></div>
        <div class="popup-message" id="popupMessage"></div>
        <button class="popup-btn" id="popupBtn">Oke</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.password-toggle').forEach(btn=>{
    btn.addEventListener('click',()=>{ 
        const input=document.getElementById(btn.dataset.target);
        const icon=btn.querySelector('i');
        if(input.type==='password'){ input.type='text'; icon.className='bi bi-eye-slash'; }
        else{ input.type='password'; icon.className='bi bi-eye'; }
    });
});

const passwordInput=document.getElementById('password_baru');
const strengthMeter=document.getElementById('strengthMeter');
const rules={
    length: document.getElementById('rule-length'),
    uppercase: document.getElementById('rule-uppercase'),
    lowercase: document.getElementById('rule-lowercase'),
    number: document.getElementById('rule-number')
};

function updateRule(el,val){ 
    el.classList.remove('rule-valid','rule-invalid'); 
    el.classList.add(val?'rule-valid':'rule-invalid'); 
}

passwordInput.addEventListener('input',function(){
    const p=this.value;
    let s=0;
    const l=p.length>=6,u=/[A-Z]/.test(p),lc=/[a-z]/.test(p),n=/[0-9]/.test(p);
    updateRule(rules.length,l); 
    updateRule(rules.uppercase,u); 
    updateRule(rules.lowercase,lc); 
    updateRule(rules.number,n);
    if(l)s+=25;if(u)s+=25;if(lc)s+=25;if(n)s+=25;
    strengthMeter.style.width=s+'%';
    strengthMeter.className='strength-meter '+(s<50?'strength-weak':s<75?'strength-medium':'strength-strong');
});

const confirmInput=document.getElementById('konfirmasi_password');
const passwordMatch=document.getElementById('passwordMatch');
confirmInput.addEventListener('input',function(){
    const p=passwordInput.value,c=this.value;
    if(!c){ passwordMatch.innerHTML=''; return; }
    passwordMatch.innerHTML=p===c?'<span class="text-success"><i class="bi bi-check-circle me-1"></i>Password cocok</span>':'<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Password tidak cocok</span>';
});

document.getElementById('passwordForm').addEventListener('submit',function(e){
    const pL=document.getElementById('password_lama').value,
          pB=passwordInput.value,
          k=confirmInput.value;
    if(!pL||!pB||!k){ e.preventDefault(); alert('Semua field harus diisi!'); return false;}
    if(pB.length<6){ e.preventDefault(); alert('Password minimal 6 karakter!'); return false;}
    if(pB!==k){ e.preventDefault(); alert('Password dan konfirmasi tidak cocok!'); return false;}
    if(pB===pL){ e.preventDefault(); alert('Password baru harus berbeda dari lama!'); return false;}
    return true;
});

/* Popup session */
<?php if($popup): ?>
showPopup("<?= $popup['type'] ?>","<?= $popup['title'] ?>","<?= $popup['message'] ?>");
<?php endif; ?>

function showPopup(type,title,message){
    const overlay=document.getElementById('popupOverlay');
    const box=document.getElementById('popupBox');
    const titleEl=document.getElementById('popupTitle');
    const msgEl=document.getElementById('popupMessage');
    const btn=document.getElementById('popupBtn');

    titleEl.textContent=title;
    msgEl.textContent=message;
    box.className='popup-box popup-'+type;
    overlay.classList.add('show');

    btn.onclick=()=>overlay.classList.remove('show');
}
</script>
</body>
</html>