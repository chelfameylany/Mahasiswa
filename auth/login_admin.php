<?php
session_start();
if (isset($_SESSION['admin_login'])) {
    header("Location: /admin/dashboard_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login Admin - Universitas Cendekia Nusantara</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 20px;
}

/* BACKGROUND - BLUR TIPIS (PATH SUDAH DIPERBAIKI) */
.bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('../assets/univ bg 1.jpeg') center center / cover no-repeat;
    z-index: -2;
    filter: blur(3px);
    transform: scale(1);
}

/* OVERLAY GELAP BIAR CARD LEBIH KELIHATAN */
.bg-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    z-index: -1;
}

/* CARD LOGIN - SPLIT LAYOUT (kiri branding, kanan form) */
.login-container {
    max-width: 1000px;
    width: 100%;
    position: relative;
    z-index: 10;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-card {
    display: flex;
    flex-direction: row;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 30px 50px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.4);
}

/* LEFT SECTION - BRANDING */
.login-branding {
    flex: 1;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(30, 64, 175, 0.08));
    padding: 40px 30px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-right: 1px solid rgba(0, 0, 0, 0.08);
}

.logo-wrapper {
    margin-bottom: 20px;
}

.logo-wrapper img {
    width: 85px;
    height: auto;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    transition: transform 0.3s ease;
}

.logo-wrapper img:hover {
    transform: scale(1.05);
}

.login-title {
    font-weight: 800;
    font-size: 1.8rem;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 8px;
}

.login-subtitle {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 15px;
}

.admin-badge {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    padding: 5px 16px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 20px;
}

.university-name {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 10px;
}

.university-name i {
    color: #2563eb;
}

/* RIGHT SECTION - FORM */
.login-form {
    flex: 1.2;
    padding: 40px 35px;
}

.form-header {
    text-align: center;
    margin-bottom: 25px;
}

.form-header h2 {
    font-weight: 700;
    font-size: 1.5rem;
    color: #0f172a;
    margin-bottom: 5px;
}

.form-header p {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 500;
}

/* ICON TOP (pindah ke dalam form) */
.icon-circle {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
}

.icon-circle i {
    color: white;
    font-size: 28px;
}

/* INPUT GROUP */
.input-group {
    margin-bottom: 18px;
}

.input-group-text {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 14px 0 0 14px;
    color: #2563eb;
    padding-left: 18px;
    padding-right: 18px;
    font-size: 1rem;
}

.form-control {
    border: 1px solid #e2e8f0;
    border-left: none;
    background: #f8fafc;
    border-radius: 0 14px 14px 0;
    padding: 12px 15px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    box-shadow: none;
    background: white;
    border-color: #2563eb;
}

.input-group:focus-within .input-group-text {
    background: white;
    border-color: #2563eb;
}

/* Password Toggle Button */
.password-toggle-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: none;
    border-radius: 0 14px 14px 0;
    color: #64748b;
    cursor: pointer;
    padding: 0 15px;
    transition: all 0.3s;
}

.password-toggle-btn:hover {
    background: white;
    color: #2563eb;
}

/* BUTTON LOGIN */
.btn-login {
    margin-top: 10px;
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1rem;
    color: white;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(59, 130, 246, 0.4);
}

/* Back Button */
.btn-back {
    margin-top: 15px;
    width: 100%;
    padding: 11px;
    border: 2px solid #2563eb;
    border-radius: 14px;
    font-weight: 600;
    color: #2563eb;
    background: transparent;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-back:hover {
    background: #2563eb;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}

/* ERROR ALERT */
.alert {
    border-radius: 14px;
    font-size: 0.8rem;
    padding: 10px 15px;
    margin-bottom: 20px;
    border: none;
    background: #fee2e2;
    color: #991b1b;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* LOGIN TRANSITION */
#loginTransition {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, #2563eb, #1e3a8a);
    z-index: 9999;
    clip-path: circle(0% at 50% 50%);
    transition: 0.8s cubic-bezier(0.77, 0, 0.18, 1);
}

#loginTransition.active {
    clip-path: circle(150% at 50% 50%);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .login-card {
        flex-direction: column;
    }
    
    .login-branding {
        border-right: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        padding: 30px 20px;
    }
    
    .login-form {
        padding: 30px 25px;
    }
    
    .login-title {
        font-size: 1.5rem;
    }
    
    .logo-wrapper img {
        width: 65px;
    }
}

@media (max-width: 480px) {
    .login-form {
        padding: 25px 20px;
    }
    
    .form-header h2 {
        font-size: 1.3rem;
    }
    
    .icon-circle {
        width: 50px;
        height: 50px;
    }
    
    .icon-circle i {
        font-size: 24px;
    }
    
    .btn-login, .btn-back {
        font-size: 0.9rem;
    }
}

/* Pencegahan zoom berlebih pada input di mobile */
@media (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}

/* Animasi smooth */
* {
    -webkit-tap-highlight-color: transparent;
}
</style>
</head>

<body>

<!-- BACKGROUND - BLUR TIPIS -->
<div class="bg"></div>
<div class="bg-overlay"></div>

<div class="login-container">
    <div class="login-card">
        
        <!-- LEFT SECTION - BRANDING (PATH GAMBAR SUDAH DIPERBAIKI) -->
        <div class="login-branding">
            <div class="logo-wrapper">
              <img src="../assets/logokampus1.png" alt="Logo Kampus">
            </div>
            <h1 class="login-title">WELCOME ADMIN</h1>
            <p class="login-subtitle">Admin Universitas Cendekia Nusantara</p>
            <div class="admin-badge">
                <i class="bi bi-shield-lock-fill"></i> Panel Administrator
            </div>
            <div class="university-name">
                <i class="bi bi-building"></i> Universitas Cendekia Nusantara<br>
                <i class="bi bi-star-fill" style="font-size: 0.6rem;"></i> Mencetak Intelektual Membangun Peradaban
            </div>
        </div>
        
        <!-- RIGHT SECTION - FORM -->
        <div class="login-form">
            <div class="icon-circle">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            
            <div class="form-header">
                <h2>Login Admin</h2>
                <p>Masuk ke panel administrator</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Username atau password salah
                </div>
            <?php endif; ?>

            <form action="proses_login_admin.php" method="POST" id="loginForm">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="off">
                </div>

                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                    <button type="button" class="password-toggle-btn" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="button" class="btn-login" onclick="loginAnim()">
                    <i class="bi bi-box-arrow-in-right me-2"></i>LOGIN
                </button>

                <a href="../dashboard_utama.php" class="btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                </a>
            </form>
        </div>
        
    </div>
</div>

<div id="loginTransition"></div>

<script>
// Toggle show/hide password
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const eyeIcon = togglePassword.querySelector('i');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
            eyeIcon.className = 'bi bi-eye-slash';
            togglePassword.setAttribute('title', 'Sembunyikan password');
        } else {
            eyeIcon.className = 'bi bi-eye';
            togglePassword.setAttribute('title', 'Tampilkan password');
        }
        
        passwordInput.focus();
    });
}

// Login animation
function loginAnim() {
    const username = document.querySelector('input[name="username"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();
    
    if (!username || !password) {
        const alertBox = document.createElement('div');
        alertBox.className = 'alert';
        alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Harap isi username dan password!';
        alertBox.style.position = 'fixed';
        alertBox.style.top = '20px';
        alertBox.style.left = '50%';
        alertBox.style.transform = 'translateX(-50%)';
        alertBox.style.zIndex = '10000';
        alertBox.style.background = '#fee2e2';
        alertBox.style.color = '#991b1b';
        alertBox.style.padding = '12px 24px';
        alertBox.style.borderRadius = '12px';
        alertBox.style.boxShadow = '0 5px 20px rgba(0,0,0,0.2)';
        alertBox.style.fontWeight = '600';
        document.body.appendChild(alertBox);
        
        setTimeout(() => {
            alertBox.remove();
        }, 3000);
        return;
    }
    
    const transition = document.getElementById('loginTransition');
    transition.classList.add('active');
    
    setTimeout(() => {
        document.getElementById('loginForm').submit();
    }, 800);
}

// Enter key untuk submit
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        loginAnim();
    }
});

// Focus pada username saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.querySelector('input[name="username"]');
    if (usernameInput) {
        usernameInput.focus();
    }
    if (togglePassword) {
        togglePassword.setAttribute('title', 'Tampilkan password');
    }
});

// Cegah zoom berlebih pada input di perangkat mobile
if ('ontouchstart' in window) {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            setTimeout(() => {
                window.scrollTo(0, 0);
            }, 100);
        });
    });
}
</script>

</body>
</html>