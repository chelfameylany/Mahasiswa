<?php
// login_maba.php (di folder auth/)
session_start();
include "../koneksi.php";

// Jika sudah login, redirect ke dashboard user
if (isset($_SESSION['maba'])) {
    header("Location: ../user/dashboard_maba.php");
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // Cari user di database (bisa username atau email)
    $query = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE username = '$username' OR email = '$username'");
    
    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password
        if (password_verify($password, $user['password_hash'])) {
            // Set session
            $_SESSION['maba'] = $user['username'];
            $_SESSION['id_maba'] = $user['id_maba'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            
            // Cek apakah kolom last_login ada
            $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM calon_maba LIKE 'last_login'");
            if (mysqli_num_rows($check_column) > 0) {
                mysqli_query($koneksi, "UPDATE calon_maba SET last_login = NOW() WHERE username = '{$user['username']}'");
            }
            
            // Redirect ke dashboard di folder user
            header("Location: ../user/dashboard_maba.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username/Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Mahasiswa - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            background: #0a0a0a;
        }
        
        /* Background image dengan blur halus */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../assets/univ bg 1.jpeg') center center / cover no-repeat;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(4px) brightness(0.9);
            transform: scale(1.02);
            z-index: 0;
        }
        
        /* Overlay gradasi soft */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, 
                rgba(255, 255, 255, 0.15) 0%,
                rgba(13, 59, 102, 0.2) 100%);
            z-index: 0;
        }
        
        .login-container {
            max-width: 1100px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            margin: 0 auto;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            border-radius: 40px;
            padding: 2rem 3rem;
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border: 1px solid rgba(255, 255, 255, 0.4);
            display: flex;
            flex-direction: row;
            gap: 3rem;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 40px 70px -15px rgba(0, 0, 0, 0.35);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        /* Left Section - Branding */
        .login-branding {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 1rem;
            border-right: 2px solid rgba(13, 110, 253, 0.15);
        }
        
        /* Logo styling */
        .logo-wrapper {
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .logo-wrapper::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #0d6efd, #3b82f6, #0d6efd, transparent);
            border-radius: 3px;
        }
        
        .logo-wrapper img {
            width: 100px;
            height: auto;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }
        
        .logo-wrapper img:hover {
            transform: scale(1.08) rotate(2deg);
            filter: drop-shadow(0 8px 16px rgba(13, 110, 253, 0.2));
        }
        
        .login-title {
            font-weight: 800;
            font-size: 1.6rem;
            background: linear-gradient(135deg, #0d3b66, #0d6efd, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        
        .login-subtitle {
            color: #64748b;
            margin-bottom: 1rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .university-name {
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
        }
        
        /* Right Section - Form */
        .login-form-section {
            flex: 1.2;
            padding: 0.5rem 0;
        }
        
        .form-title {
            font-weight: 700;
            font-size: 1.5rem;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        
        .form-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.25rem;
        }
        
        .form-group .form-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 2;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .form-group .form-control {
            border-radius: 16px;
            padding: 0.9rem 1rem 0.9rem 48px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            background: white;
        }
        
        .form-group .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
            outline: none;
            transform: translateY(-1px);
        }
        
        .form-group .form-control:focus + .form-icon {
            color: #0d6efd;
            transform: translateY(-50%) scale(1.05);
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            z-index: 2;
            padding: 8px;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle-btn:hover {
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.1);
            transform: translateY(-50%) scale(1.05);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #0d3b66, #0d6efd, #3b82f6);
            background-size: 200% 200%;
            color: white;
            font-weight: 700;
            padding: 0.9rem;
            border-radius: 16px;
            border: none;
            width: 100%;
            transition: all 0.4s ease;
            font-size: 1rem;
            margin-top: 0.5rem;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .btn-login:hover {
            background-position: 100% 100%;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -8px rgba(13, 59, 102, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(1px);
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .register-link a {
            color: #0d6efd;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .register-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #0d6efd, #3b82f6);
            transition: width 0.3s ease;
        }
        
        .register-link a:hover::after {
            width: 100%;
        }
        
        .register-link a:hover {
            color: #0d3b66;
        }
        
        /* Tombol kembali ke dashboard - ukuran SAMA dengan btn-login */
        .back-home-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            text-decoration: none;
            font-weight: 700;
            padding: 0.9rem;
            border-radius: 16px;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
            width: 100%;
            cursor: pointer;
        }
        
        .back-home::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .back-home:hover::before {
            left: 100%;
        }
        
        .back-home:hover {
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
            transform: translateY(-2px);
            gap: 12px;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
        }
        
        .back-home:active {
            transform: translateY(1px);
        }
        
        .alert {
            border-radius: 16px;
            border: none;
            font-weight: 500;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn-login.loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                padding: 1.5rem;
                gap: 1.5rem;
            }
            
            .login-branding {
                border-right: none;
                border-bottom: 2px solid rgba(13, 110, 253, 0.15);
                padding-bottom: 1rem;
            }
            
            .login-container {
                max-width: 95%;
            }
            
            .login-title {
                font-size: 1.2rem;
            }
            
            .logo-wrapper img {
                width: 70px;
            }
            
            .btn-login, .form-control, .back-home {
                font-size: 0.9rem;
            }
            
            .form-title {
                font-size: 1.3rem;
            }
        }
        
        /* Desktop large screens */
        @media (min-width: 1400px) {
            .login-container {
                max-width: 1200px;
            }
            
            .login-card {
                padding: 2.5rem 4rem;
                gap: 4rem;
            }
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus ring yang lebih bagus */
        :focus-visible {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Left Section - Branding -->
            <div class="login-branding">
                <div class="logo-wrapper">
                   <img src="../assets/logokampus1.png" alt="Logo Kampus">
                </div>
                
                <h1 class="login-title">UNIVERSITAS CENDEKIA NUSANTARA</h1>
                
                <div class="university-name">
                    <i class="bi bi-star-fill" style="font-size: 0.7rem;"></i> Mencetak Intelektual Membangun Peradaban
                </div>
            </div>
            
            <!-- Right Section - Form -->
            <div class="login-form-section">
                <h2 class="form-title">Login Mahasiswa</h2>
                <p class="form-subtitle">Masuk ke dashboard akademik Anda</p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST" id="loginForm">
                    <div class="form-group">
                        <i class="bi bi-person-fill form-icon"></i>
                        <input type="text" class="form-control" name="username" required 
                               placeholder="Username atau Email" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <i class="bi bi-shield-lock-fill form-icon"></i>
                        <input type="password" class="form-control" name="password" id="password" required 
                               placeholder="Password">
                        <button type="button" class="password-toggle-btn" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    
                    <button type="submit" class="btn-login" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
                    </button>
                    
                    <div class="register-link">
                        <i class="bi bi-person-plus-fill me-1"></i>
                        Belum punya akun? <a href="pendaftaran-user.php">Daftar Sekarang</a>
                    </div>
                </form>
                
                <!-- Tombol kembali ke dashboard utama -->
                <div class="back-home-wrapper">
                    <a href="../dashboard_utama.php" class="back-home">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard Utama
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = togglePassword.querySelector('i');
        const loginBtn = document.getElementById('loginBtn');
        const loginForm = document.getElementById('loginForm');
        
        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.className = 'bi bi-eye-slash';
                togglePassword.setAttribute('title', 'Sembunyikan password');
                togglePassword.style.color = '#0d6efd';
            } else {
                eyeIcon.className = 'bi bi-eye';
                togglePassword.setAttribute('title', 'Tampilkan password');
                togglePassword.style.color = '#94a3b8';
            }
            
            passwordInput.focus();
        });
        
        // Form validation with loading effect
        loginForm.addEventListener('submit', function(e) {
            const username = document.querySelector('input[name="username"]').value.trim();
            const password = document.querySelector('input[name="password"]').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-warning alert-dismissible fade show';
                alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Harap isi username/email dan password!<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.querySelector('.login-form-section form').insertBefore(alertDiv, document.querySelector('.login-form-section form').firstChild);
                
                setTimeout(() => {
                    const alert = document.querySelector('.alert-warning');
                    if (alert) alert.remove();
                }, 3000);
                return false;
            }
            
            // Add loading effect
            loginBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';
            loginBtn.classList.add('loading');
            
            return true;
        });
        
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="username"]').focus();
            togglePassword.setAttribute('title', 'Tampilkan password');
        });
        
        // Enter key submit handler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'BUTTON') {
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT') {
                    loginForm.submit();
                }
            }
        });
        
        // Auto dismiss alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 100);
    </script>
</body>
</html>