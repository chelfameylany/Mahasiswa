<?php
session_start();

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    session_destroy();
    header("Location: login_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logout Admin - Universitas Cendekia Nusantara</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Segoe UI', 'Poppins', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 20px;
}

/* BACKGROUND - BLUR TIPIS */
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

/* OVERLAY GELAP */
.bg-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    z-index: -1;
}

/* POPUP CARD - SAMA SEPERTI LOGIN */
.logout-box {
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(12px);
    border-radius: 28px;
    padding: 40px 35px;
    text-align: center;
    box-shadow: 0 30px 50px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 10;
    border: 1px solid rgba(255, 255, 255, 0.5);
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

/* ICON TOP - WARNA BIRU */
.icon-circle {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: -65px auto 20px;
    box-shadow: 0 12px 25px rgba(37, 99, 235, 0.4);
}

.icon-circle i {
    color: white;
    font-size: 30px;
}

/* TITLE */
.logout-box h5 {
    font-weight: 800;
    margin-bottom: 15px;
    color: #1e3a8a;
    font-size: 1.5rem;
}

/* SUBTITLE */
.logout-box p {
    color: #64748b;
    margin-bottom: 30px;
    font-size: 0.95rem;
}

/* BUTTON GROUP */
.button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
}

/* BUTTON LOGOUT - WARNA BIRU */
.btn-logout {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.95rem;
    color: white;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(59, 130, 246, 0.4);
}

/* BUTTON BATAL */
.btn-cancel {
    flex: 1;
    padding: 12px;
    border: 2px solid #2563eb;
    border-radius: 14px;
    font-weight: 600;
    font-size: 0.95rem;
    color: #2563eb;
    background: transparent;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-cancel:hover {
    background: #2563eb;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}

/* BUTTON BACK KE DASHBOARD */
.btn-back {
    margin-top: 20px;
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

/* TRANSISI LOGOUT */
#logoutTransition {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, #2563eb, #1e3a8a);
    z-index: 9999;
    clip-path: circle(0% at 50% 50%);
    transition: 0.8s cubic-bezier(0.77, 0, 0.18, 1);
}

#logoutTransition.active {
    clip-path: circle(150% at 50% 50%);
}

/* RESPONSIVE */
@media (max-width: 480px) {
    .logout-box {
        padding: 35px 25px;
        max-width: 95%;
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
        margin-top: -55px;
    }
    
    .icon-circle i {
        font-size: 26px;
    }
    
    .logout-box h5 {
        font-size: 1.3rem;
    }
    
    .logout-box p {
        font-size: 0.85rem;
    }
    
    .btn-logout, .btn-cancel {
        padding: 10px;
        font-size: 0.85rem;
    }
}

/* Pencegahan zoom berlebih */
@media (max-width: 768px) {
    button, .btn {
        font-size: 16px !important;
    }
}

* {
    -webkit-tap-highlight-color: transparent;
}
</style>
</head>

<body>

<!-- BACKGROUND -->
<div class="bg"></div>
<div class="bg-overlay"></div>

<div class="logout-box">
    <div class="icon-circle">
        <i class="bi bi-box-arrow-right"></i>
    </div>

    <h5>Logout Akun</h5>
    <p>Apakah Anda yakin ingin keluar dari sistem admin?</p>

    <div class="button-group">
        <button class="btn-logout" onclick="logoutYes()">
            <i class="bi bi-check-lg"></i> Ya, Logout
        </button>
        <button class="btn-cancel" onclick="logoutNo()">
            <i class="bi bi-x-lg"></i> Batal
        </button>
    </div>
    <a href="../admin/dashboard_admin.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

<div id="logoutTransition"></div>

<script>
function logoutYes() {
    const transition = document.getElementById('logoutTransition');
    transition.classList.add('active');
    
    setTimeout(() => {
        window.location.href = "login_admin.php";
    }, 800);
}

function logoutNo() {
    window.location.href = "../admin/dashboard_admin.php"
}

// Fokus otomatis ke tombol logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.querySelector('.btn-logout');
    if (logoutBtn) {
        logoutBtn.focus();
    }
});

// Mencegah zoom berlebih pada perangkat mobile
if ('ontouchstart' in window) {
    const buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(button => {
        button.addEventListener('focus', function() {
            setTimeout(() => {
                window.scrollTo(0, 0);
            }, 100);
        });
    });
}
</script>

</body>
</html>