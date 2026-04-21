<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Mahasiswa Baru - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0d3b66;
            --secondary: #0d6efd;
            --accent: #22c55e;
            --light: #f8fafc;
            --dark: #0f172a;
            --gray: #64748b;
            --shadow: 0 10px 30px rgba(13, 59, 102, 0.1);
            --gradient-primary: linear-gradient(135deg, #0d3b66 0%, #0d6efd 100%);
            --gradient-accent: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background: linear-gradient(180deg, #d4e6f1 0%, #ffffff 40%, #eaf2f8 100%);
            overflow-x: hidden;
        }
        
       /* NAVBAR TETAP (FIXED & SLIM) */
.navbar {
    background: linear-gradient(135deg, #5dade2 0%, #85c1e9 40%, #ebf5fb 100%);
    
    padding: 0.4rem 0; /* INI YANG DIGANTI */
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1000;

    backdrop-filter: blur(6px);
}
        /* HERO VIDEO - LANGSUNG DI BAWAH NAVBAR */
        .hero-video-container {
    width: 100%;
    height: 86vh; /* LEBIH PENDEK & ELEGAN */
    position: relative;
    overflow: hidden;
    margin-top: 80px;
}
        
        .hero-video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }
        
        .logo-img {
            height: 70px;
            width: auto;
            border-radius: 14px;
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .logo-img:hover {
            transform: scale(1.05);
        }
        
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        
        .brand-text .univ-name {
            font-weight: 800;
            font-size: 1.3rem;
            color: #0a2a4a;
            letter-spacing: 0.5px;
        }
        
        .brand-text .univ-tagline {
            font-size: 0.7rem;
            color: #1a4d7a;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }
        
        .nav-link {
            color: #0a2a4a !important;
            font-weight: 600;
            padding: 0.6rem 1.2rem !important;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .nav-link i {
            margin-right: 6px;
            font-size: 1rem;
            color: #0a2a4a;
        }
        
        .nav-link:hover {
            background: rgba(10, 42, 74, 0.1);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: rgba(10, 42, 74, 0.15);
        }
        
        .btn-register {
            background: var(--gradient-accent);
            color: white;
            font-weight: 700;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }
        
        .dropdown-login {
            position: relative;
        }
        
        .dropdown-toggle-login {
            background: rgba(10, 42, 74, 0.1);
            color: #0a2a4a;
            border: 1px solid rgba(10, 42, 74, 0.2);
            border-radius: 30px;
            padding: 0.7rem 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .dropdown-toggle-login:hover {
            background: rgba(10, 42, 74, 0.2);
            transform: translateY(-2px);
        }
        
        .dropdown-menu-login {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            min-width: 280px;
            padding: 0.8rem 0;
            display: none;
            z-index: 1001;
        }
        
        .dropdown-menu-login.show {
            display: block;
        }
        
        .dropdown-item-login {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark);
            text-decoration: none;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }
        
        .dropdown-item-login:last-child {
            border-bottom: none;
        }
        
        .dropdown-item-login:hover {
            background: linear-gradient(90deg, rgba(13, 110, 253, 0.08), transparent);
            color: var(--secondary);
            padding-left: 1.8rem;
        }
        
        .login-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background: var(--gradient-primary);
            color: white;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: auto;
        }
        
        /* SECTION TENTANG UNIVERSITAS */
        .about-section {
            padding: 5rem 0;
        }
        
        .about-title {
            font-weight: 900;
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            display: inline-block;
            width: auto;
        }
        
        .about-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }
        
        .about-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .about-content {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(13, 59, 102, 0.1);
        }
        
        .about-text {
            font-size: 1.1rem;
            color: var(--dark);
            line-height: 1.8;
            text-align: justify;
        }
        
        .about-text p {
            margin-bottom: 1.5rem;
        }
        
        .about-highlight {
            background: linear-gradient(120deg, rgba(34, 197, 94, 0.1), transparent);
            border-left: 4px solid var(--accent);
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
        }
        
        .stats-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 2rem;
            margin: 2.5rem 0;
        }
        
        .stat-about {
            text-align: center;
            flex: 1;
            min-width: 120px;
        }
        
        .stat-about-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--accent);
        }
        
        .stat-about-label {
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .visi-misi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .visi-card, .misi-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1.8rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .visi-card:hover, .misi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(13, 59, 102, 0.15);
        }
        
        .visi-card h3, .misi-card h3 {
            color: var(--primary);
            font-weight: 800;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .visi-card h3 i, .misi-card h3 i {
            color: var(--accent);
            font-size: 1.8rem;
        }
        
        .misi-list {
            list-style: none;
            padding-left: 0;
        }
        
        .misi-list li {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .misi-list li i {
            color: var(--accent);
            margin-top: 0.2rem;
        }
        
        /* FEATURES SECTION */
        .features-section {
            padding: 4rem 0;
        }
        
        .section-title {
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 1rem;
            text-align: center;
            font-size: 2.5rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }
        
        .section-subtitle {
            color: var(--gray);
            text-align: center;
            max-width: 700px;
            margin: 0 auto 2.5rem;
            font-size: 1.1rem;
        }
        
        /* CARD PROGRAM STUDI */
        .program-card {
            background: white;
            border-radius: 20px;
            padding: 2rem 1rem;
            box-shadow: var(--shadow);
            transition: all 0.4s ease;
            height: 100%;
            text-align: center;
            border: 1px solid rgba(13, 59, 102, 0.08);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .program-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .program-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(13, 59, 102, 0.15);
        }
        
        .program-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
            color: white;
            font-size: 2.2rem;
            transition: all 0.3s ease;
        }
        
        .program-card:hover .program-icon {
            transform: rotate(10deg) scale(1.1);
            background: var(--gradient-accent);
        }
        
        .program-title {
            font-weight: 800;
            color: var(--primary);
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .program-desc {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 0.25rem;
            line-height: 1.4;
        }
        
        /* SIDEBAR UNTUK JUDUL BIDANG */
        .program-sidebar {
            background: linear-gradient(135deg, rgba(13, 59, 102, 0.04), rgba(13, 110, 253, 0.04));
            border-radius: 24px;
            padding: 2rem 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            border: 1px solid rgba(13, 59, 102, 0.08);
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s ease;
        }
        
        .program-sidebar.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .program-sidebar h3 {
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .program-sidebar p {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .program-sidebar i {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        .divider-custom {
            width: 60px;
            height: 3px;
            background: var(--gradient-accent);
            margin: 1rem auto;
        }
        
        /* STEPS SECTION */
        .steps-section {
            padding: 4rem 0;
        }
        
        .step-card {
            background: white;
            border-radius: 20px;
            padding: 2rem 2rem 2rem 3rem;
            box-shadow: var(--shadow);
            position: relative;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(13, 59, 102, 0.08);
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(13, 59, 102, 0.15);
        }
        
        .step-number {
            position: absolute;
            top: -15px;
            left: -15px;
            width: 55px;
            height: 55px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 1.6rem;
            border: 4px solid white;
        }
        
        .step-card:hover .step-number {
            background: var(--gradient-accent);
            transform: scale(1.1);
            transition: all 0.3s ease;
        }
        
        .step-title {
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.8rem;
            font-size: 1.3rem;
        }
        
        .step-desc {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .step-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-weight: 600;
            text-decoration: none;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .step-action:hover {
            color: var(--primary);
            gap: 12px;
        }
        
        /* CTA SECTION */
        .cta-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, #5dade2 0%, #85c1e9 100%);
            color: #0a2a4a;
            text-align: center;
        }
        
        .cta-title {
            font-weight: 900;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0a2a4a;
        }
        
        .cta-subtitle {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            color: #1a4d7a;
        }
        
        .btn-cta {
            background: var(--gradient-accent);
            color: white;
            font-weight: 700;
            padding: 1rem 2.5rem;
            border-radius: 40px;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-cta:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            box-shadow: 0 10px 25px rgba(34, 197, 94, 0.4);
        }
        
        /* FOOTER */
        .footer {
            padding: 3rem 0 2rem;
            background: transparent;
        }
        
        .footer-card {
            background: linear-gradient(135deg, #5dade2 0%, #85c1e9 40%, #ebf5fb 100%);
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }
        
        .footer-brand .logo-img-footer {
            height: 50px;
            width: auto;
            border-radius: 12px;
        }
        
        .footer-brand .brand-text-footer {
            display: flex;
            flex-direction: column;
        }
        
        .footer-brand .brand-text-footer .univ-name-footer {
            font-weight: 800;
            font-size: 1.1rem;
            color: #0a2a4a;
        }
        
        .footer-brand .brand-text-footer .univ-tagline-footer {
            font-size: 0.65rem;
            color: #1a4d7a;
        }
        
        .footer-description {
            font-size: 0.85rem;
            color: #1a4d7a;
            margin: 1rem 0;
            line-height: 1.5;
        }
        
        .social-icons {
            display: flex;
            gap: 12px;
            margin: 1.5rem 0;
        }
        
        .social-icon {
            width: 38px;
            height: 38px;
            background: rgba(10, 42, 74, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0a2a4a;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-3px);
        }
        
        .footer-title {
            font-weight: 800;
            margin-bottom: 1.2rem;
            font-size: 1.1rem;
            position: relative;
            display: inline-block;
            color: #0a2a4a;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--accent);
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.7rem;
        }
        
        .footer-links a {
            color: #1a4d7a;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
        
        .footer-links a:hover {
            color: #0a2a4a;
            padding-left: 5px;
        }
        
        .footer-contact {
            list-style: none;
            padding: 0;
        }
        
        .footer-contact li {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #1a4d7a;
        }
        
        .footer-contact li i {
            width: 25px;
            font-size: 1rem;
            color: #0a2a4a;
        }
        
        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(10, 42, 74, 0.2);
            font-size: 0.75rem;
            color: #1a4d7a;
        }
        
        /* ANIMATIONS */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s ease;
        }
        
        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .slide-in-right {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s ease;
        }
        
        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .zoom-in {
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.8s ease;
        }
        
        .zoom-in.visible {
            opacity: 1;
            transform: scale(1);
        }
        
        .card-delay-1 { transition-delay: 0.1s; }
        .card-delay-2 { transition-delay: 0.2s; }
        .card-delay-3 { transition-delay: 0.3s; }
        .card-delay-4 { transition-delay: 0.4s; }
        .card-delay-5 { transition-delay: 0.5s; }
        .card-delay-6 { transition-delay: 0.6s; }
        .card-delay-7 { transition-delay: 0.7s; }
        .card-delay-8 { transition-delay: 0.8s; }
        
        /* Custom column untuk 5 card dalam satu baris di desktop */
        .col-xl-2-4 {
            flex: 0 0 auto;
            width: 20%;
        }
        
        @media (max-width: 1199px) {
            .navbar-collapse {
                background: linear-gradient(135deg, #5dade2 0%, #85c1e9 100%);
                padding: 1.5rem;
                border-radius: 20px;
                margin-top: 1rem;
            }
            .dropdown-menu-login {
                position: static;
                background: rgba(255,255,255,0.9);
            }
            .visi-misi-grid {
                grid-template-columns: 1fr;
            }
            .about-title {
                font-size: 2rem;
            }
            .hero-video-container {
                height: 50vh;
                min-height: 350px;
            }
            .logo-img {
                height: 55px;
            }
            .brand-text .univ-name {
                font-size: 1rem;
            }
            .program-sidebar {
                margin-bottom: 1.5rem;
            }
            .col-xl-2-4 {
                width: 33.333%;
            }
        }
        
        @media (max-width: 768px) {
            .about-title {
                font-size: 1.8rem;
            }
            .about-content {
                padding: 1.5rem;
            }
            .stats-grid {
                gap: 1rem;
            }
            .stat-about-number {
                font-size: 1.8rem;
            }
            .section-title {
                font-size: 1.8rem;
            }
            .cta-title {
                font-size: 1.8rem;
            }
            .hero-video-container {
                height: 40vh;
                min-height: 300px;
            }
            .footer-card {
                padding: 1.5rem;
            }
            .logo-img {
                height: 45px;
            }
            .col-xl-2-4 {
                width: 50%;
            }
        }
        
        @media (max-width: 480px) {
            .col-xl-2-4 {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR TETAP -->
    <nav class="navbar navbar-expand-lg" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <img src="assets/logokampus1.png" alt="Logo UCN" class="logo-img" onerror="this.src='https://via.placeholder.com/70x70/3498db/ffffff?text=UCN'">
                <div class="brand-text">
                    <span class="univ-name">UNIVERSITAS CENDEKIA NUSANTARA</span>
                    <span class="univ-tagline">Mencerdaskan Bangsa, Membangun Peradaban</span>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list" style="font-size: 1.5rem; color: #0a2a4a;"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">
                            <i class="bi bi-house-door-fill"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">
                            <i class="bi bi-building"></i> Tentang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">
                            <i class="bi bi-book-fill"></i> Program Studi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#steps">
                            <i class="bi bi-list-check"></i> Cara Daftar
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a class="btn btn-register" href="./auth/pendaftaran-user.php">
                            <i class="bi bi-pencil-square"></i> Daftar Sekarang
                        </a>
                    </li>
                    <li class="nav-item dropdown-login ms-lg-3 mt-3 mt-lg-0">
                        <button class="dropdown-toggle-login" id="loginDropdownBtn">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                        <div class="dropdown-menu-login" id="loginDropdownMenu">
                            <a href="./auth/login_maba.php" class="dropdown-item-login">
                                <i class="bi bi-person-fill"></i>
                                <div>
                                    <div class="fw-bold">Login Mahasiswa</div>
                                    <small>Calon Mahasiswa Baru</small>
                                </div>
                                <span class="login-badge">USER</span>
                            </a>
                            <a href="./auth/login_admin.php" class="dropdown-item-login">
                                <i class="bi bi-shield-lock-fill"></i>
                                <div>
                                    <div class="fw-bold">Login Admin</div>
                                    <small>Administrator Sistem</small>
                                </div>
                                <span class="login-badge">ADMIN</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- HERO VIDEO -->
    <div class="hero-video-container" id="home">
        <video id="heroVideo" autoplay muted loop playsinline>
            <source src="assets/VIDIO KAMPUS 1.mp4" type="video/mp4">
            Browser Anda tidak mendukung video tag.
        </video>
    </div>

    <!-- TENTANG UNIVERSITAS -->
    <section class="about-section" id="tentang">
        <div class="container">
            <div class="text-center">
                <h2 class="about-title fade-in">🏛️ Universitas Cendekia Nusantara</h2>
                <p class="about-subtitle fade-in">📚 Mencetak Generasi Unggul, Berkarakter, dan Berdaya Saing Global</p>
            </div>
            
            <div class="about-content fade-in">
                <div class="about-text">
                    <p><strong>🌟 Universitas Cendekia Nusantara (UCN)</strong> adalah perguruan tinggi swasta terkemuka yang berlokasi di kota Bandung. Didirikan pada tahun 2015, UCN telah tumbuh menjadi salah satu institusi pendidikan favorit di Indonesia dengan komitmen kuat terhadap <strong>inovasi, riset, dan pengembangan karakter</strong>.</p>
                    
                    <p>📖 Dengan motto <strong>"Mencetak Intelektual Membangun Peradaban"</strong>, UCN bertekad untuk melahirkan lulusan yang tidak hanya cerdas secara akademis, tetapi juga memiliki integritas, kepemimpinan, dan kepedulian sosial yang tinggi.</p>
                    
                    <div class="about-highlight">
                        <i class="bi bi-quote" style="font-size: 1.5rem; color: var(--accent);"></i>
                        <p class="mb-0 mt-2"><em>"Kami percaya bahwa pendidikan adalah kunci untuk membuka pintu masa depan. Di UCN, setiap mahasiswa didorong untuk menemukan potensi terbaik mereka dan berkontribusi bagi masyarakat."</em></p>
                        <p class="mt-2 mb-0 fw-bold">— Rektor Universitas Cendekia Nusantara</p>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-about zoom-in card-delay-1">
                            <div class="stat-about-number">10+</div>
                            <div class="stat-about-label">📅 Tahun Berdiri</div>
                        </div>
                        <div class="stat-about zoom-in card-delay-2">
                            <div class="stat-about-number">5.000+</div>
                            <div class="stat-about-label">👨‍🎓 Mahasiswa Aktif</div>
                        </div>
                        <div class="stat-about zoom-in card-delay-3">
                            <div class="stat-about-number">92%</div>
                            <div class="stat-about-label">📊 Tingkat Kelulusan</div>
                        </div>
                        <div class="stat-about zoom-in card-delay-4">
                            <div class="stat-about-number">200+</div>
                            <div class="stat-about-label">🤝 Mitra Industri</div>
                        </div>
                    </div>
                    
                    <div class="visi-misi-grid">
                        <div class="visi-card slide-in-left card-delay-1">
                            <h3><i class="bi bi-eye-fill"></i> Visi</h3>
                            <p>Menjadi universitas unggul berkelas dunia yang menghasilkan lulusan inovatif, berkarakter, dan berkontribusi bagi peradaban global pada tahun 2035.</p>
                        </div>
                        <div class="misi-card slide-in-right card-delay-2">
                            <h3><i class="bi bi-flag-fill"></i> Misi</h3>
                            <ul class="misi-list">
                                <li><i class="bi bi-check-circle-fill"></i> Menyelenggarakan pendidikan berkualitas berbasis riset dan teknologi</li>
                                <li><i class="bi bi-check-circle-fill"></i> Mengembangkan karakter kepemimpinan dan kewirausahaan</li>
                                <li><i class="bi bi-check-circle-fill"></i> Membangun kemitraan global untuk pengembangan karir mahasiswa</li>
                                <li><i class="bi bi-check-circle-fill"></i> Mengabdi kepada masyarakat melalui program pemberdayaan</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center gap-2 fade-in card-delay-1">
                                <i class="bi bi-award-fill" style="color: var(--accent); font-size: 1.5rem;"></i>
                                <span><strong>Akreditasi Unggul</strong> dari BAN-PT</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center gap-2 fade-in card-delay-2">
                                <i class="bi bi-globe2" style="color: var(--accent); font-size: 1.5rem;"></i>
                                <span><strong>Kurikulum Internasional</strong> MBKM</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center gap-2 fade-in card-delay-3">
                                <i class="bi bi-people-fill" style="color: var(--accent); font-size: 1.5rem;"></i>
                                <span><strong>Dosen Profesional</strong> berpengalaman</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROGRAM STUDI (5 CARD BERJEJER + SIDEBAR KIRI) -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title fade-in">📚 Program Studi Unggulan</h2>
                <p class="section-subtitle fade-in">Pilih program studi yang sesuai dengan passion dan bakat Anda</p>
            </div>
            
            <!-- BIDANG TEKNIK: sidebar kiri + 5 card berjejer -->
            <div class="row g-4 mb-5 align-items-stretch">
                <div class="col-lg-3">
                    <div class="program-sidebar" id="sidebarTeknik">
                        <i class="bi bi-gear-wide-connected"></i>
                        <h3>🔧 Bidang Teknik</h3>
                        <div class="divider-custom"></div>
                        <p>Rekayasa & Inovasi Teknologi Masa Depan</p>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card1">
                                <div class="program-icon"><i class="bi bi-laptop"></i></div>
                                <h4 class="program-title">💻 Teknik Informatika</h4>
                                <p class="program-desc">AI, Cyber Security, Software Modern</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card2">
                                <div class="program-icon"><i class="bi bi-cpu"></i></div>
                                <h4 class="program-title">⚡ Teknik Elektro</h4>
                                <p class="program-desc">Energi Terbarukan, Otomasi, IoT</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card3">
                                <div class="program-icon"><i class="bi bi-building"></i></div>
                                <h4 class="program-title">🏗️ Teknik Sipil</h4>
                                <p class="program-desc">Konstruksi Modern, Infrastruktur</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card4">
                                <div class="program-icon"><i class="bi bi-gear"></i></div>
                                <h4 class="program-title">🔧 Teknik Mesin</h4>
                                <p class="program-desc">Manufaktur, Robotika, Otomotif</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card5">
                                <div class="program-icon"><i class="bi bi-water"></i></div>
                                <h4 class="program-title">🌊 Teknik Lingkungan</h4>
                                <p class="program-desc">Pengolahan Limbah, Konservasi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- BIDANG NON-TEKNIK: sidebar kiri + 5 card berjejer -->
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-3">
                    <div class="program-sidebar" id="sidebarNonTeknik">
                        <i class="bi bi-book-half"></i>
                        <h3>📖 Bidang Non-Teknik</h3>
                        <div class="divider-custom"></div>
                        <p>Sosial, Bisnis & Kesehatan Berkelas Dunia</p>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card6">
                                <div class="program-icon"><i class="bi bi-briefcase"></i></div>
                                <h4 class="program-title">📊 Manajemen Bisnis</h4>
                                <p class="program-desc">Leadership, Kewirausahaan</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card7">
                                <div class="program-icon"><i class="bi bi-heart-pulse"></i></div>
                                <h4 class="program-title">🏥 Keperawatan</h4>
                                <p class="program-desc">Lab Modern, Magang RS</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card8">
                                <div class="program-icon"><i class="bi bi-calculator"></i></div>
                                <h4 class="program-title">📈 Akuntansi</h4>
                                <p class="program-desc">Akuntansi Digital, Perpajakan</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card9">
                                <div class="program-icon"><i class="bi bi-translate"></i></div>
                                <h4 class="program-title">🌏 Sastra Inggris</h4>
                                <p class="program-desc">Karir Internasional</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-2-4">
                            <div class="program-card" id="card10">
                                <div class="program-icon"><i class="bi bi-shop"></i></div>
                                <h4 class="program-title">🛒 Manajemen Pemasaran</h4>
                                <p class="program-desc">Digital Marketing, Branding</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CARA PENDAFTARAN -->
    <section class="steps-section" id="steps">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title fade-in">📝 Cara Pendaftaran</h2>
                <p class="section-subtitle fade-in">Ikuti langkah mudah berikut untuk menjadi mahasiswa UCN</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 fade-in card-delay-1">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4 class="step-title">📝 Registrasi Akun</h4>
                        <p class="step-desc">Buat akun dengan mengisi formulir pendaftaran online.</p>
                        <a href="./user/pendaftaran-user.php" class="step-action">Daftar Sekarang →</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fade-in card-delay-2">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4 class="step-title">📋 Lengkapi Data</h4>
                        <p class="step-desc">Isi data diri dan upload dokumen yang diperlukan.</p>
                        <a href="#" class="step-action">Lihat Persyaratan →</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fade-in card-delay-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4 class="step-title">🎯 Pilih Program</h4>
                        <p class="step-desc">Pilih program studi yang sesuai dengan minat Anda.</p>
                        <a href="#features" class="step-action">Lihat Program →</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fade-in card-delay-4">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4 class="step-title">✅ Tes & Daftar Ulang</h4>
                        <p class="step-desc">Ikuti tes seleksi online dan lakukan daftar ulang.</p>
                        <a href="#" class="step-action">Info Lengkap →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title fade-in">🎓 Siap Bergabung dengan UCN?</h2>
            <p class="cta-subtitle fade-in">Pendaftaran masih dibuka! Dapatkan potongan biaya pendaftaran untuk 100 pendaftar pertama.</p>
            <button class="btn btn-cta fade-in" onclick="window.location.href='./auth/pendaftaran-user.php'">
                ✏️ Daftar Sekarang Gratis!
            </button>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-card fade-in">
                <div class="row">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <div class="footer-brand">
                            <img src="assets/logokampus1.png" alt="Logo UCN" class="logo-img-footer" onerror="this.src='https://via.placeholder.com/50x50/3498db/ffffff?text=UCN'">
                            <div class="brand-text-footer">
                                <span class="univ-name-footer">UNIVERSITAS CENDEKIA NUSANTARA</span>
                                <span class="univ-tagline-footer">Mencerdaskan Bangsa, Membangun Peradaban</span>
                            </div>
                        </div>
                        <p class="footer-description">
                            Universitas Cendekia Nusantara - Menyediakan pendidikan berkualitas untuk menciptakan generasi unggul dan berdaya saing global.
                        </p>
                        <div class="social-icons">
                            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <h5 class="footer-title">🔗 Tautan</h5>
                        <ul class="footer-links">
                            <li><a href="#home"><i class="bi bi-house-door"></i> Beranda</a></li>
                            <li><a href="#tentang"><i class="bi bi-building"></i> Tentang</a></li>
                            <li><a href="#features"><i class="bi bi-book"></i> Program Studi</a></li>
                            <li><a href="#steps"><i class="bi bi-list-check"></i> Cara Daftar</a></li>
                        </ul>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <h5 class="footer-title">📞 Kontak Kami</h5>
                        <ul class="footer-contact">
                            <li><i class="bi bi-geo-alt"></i> Jl. Cendekia No. 45, Bandung, Jawa Barat</li>
                            <li><i class="bi bi-telephone"></i> (022) 1234-5678</li>
                            <li><i class="bi bi-envelope"></i> info@ucn.ac.id</li>
                            <li><i class="bi bi-clock"></i> Senin - Jumat: 08.00 - 17.00</li>
                        </ul>
                    </div>
                </div>
                <div class="copyright">
                    © 2024 Universitas Cendekia Nusantara. All Rights Reserved. 🎓
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dropdown login
        document.getElementById('loginDropdownBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('loginDropdownMenu').classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('loginDropdownMenu');
            const btn = document.getElementById('loginDropdownBtn');
            if (!btn.contains(e.target) && !menu.contains(e.target)) menu.classList.remove('show');
        });

        // Animations on scroll untuk semua elemen
        const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .zoom-in');
        const programCards = document.querySelectorAll('.program-card');
        const sidebars = document.querySelectorAll('.program-sidebar');
        
        function checkVisibility() {
            // Animasi umum
            animatedElements.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    el.classList.add('visible');
                }
            });
            
            // Animasi khusus untuk program cards (fade-in up)
            programCards.forEach((card, index) => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    card.style.transitionDelay = (index * 0.05) + 's';
                    card.classList.add('visible');
                }
            });
            
            // Animasi untuk sidebars
            sidebars.forEach((sidebar, index) => {
                const rect = sidebar.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    sidebar.style.transitionDelay = (index * 0.1) + 's';
                    sidebar.classList.add('visible');
                }
            });
        }
        
        window.addEventListener('scroll', checkVisibility);
        window.addEventListener('load', checkVisibility);
        checkVisibility();

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = this.getAttribute('href') === '#home' ? 0 : 75;
                    window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
                    const mobileMenu = document.querySelector('.navbar-collapse.show');
                    if (mobileMenu) mobileMenu.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>