<?php
session_start();
include "../koneksi.php";

// Cek apakah ada data pendaftaran di session
if (!isset($_SESSION['pendaftaran_data'])) {
    header("Location: ../auth/pendaftaran-user.php");
    exit;
}

// Data program studi lengkap dengan gambar yang sesuai jurusan
$program_studi = [
    'teknik' => [
        'nama' => 'FAKULTAS TEKNIK',
        'icon' => 'bi-cpu',
        'warna' => '#059669',
        'bg' => '#f0fdf4',
        'prodi' => [
            ['nama' => 'Teknik Informatika', 'gelar' => 'S.Kom', 'deskripsi' => 'Mempelajari pengembangan perangkat lunak, kecerdasan buatan, dan sistem informasi.', 'prospek' => 'Software Engineer, Data Scientist, AI Specialist', 'img' => 'https://images.pexels.com/photos/1181263/pexels-photo-1181263.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Sipil', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari perencanaan, pembangunan, dan pemeliharaan infrastruktur.', 'prospek' => 'Kontraktor, Konsultan, PNS', 'img' => 'https://images.pexels.com/photos/209251/pexels-photo-209251.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Mesin', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari desain, manufaktur, dan sistem energi.', 'prospek' => 'Engineer Manufaktur, Energy Specialist', 'img' => 'https://images.pexels.com/photos/162553/keys-workshop-mechanic-tools-162553.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Elektro', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari sistem tenaga listrik, elektronika, dan telekomunikasi.', 'prospek' => 'Power Engineer, Telecommunication Specialist', 'img' => 'https://images.pexels.com/photos/257904/pexels-photo-257904.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Industri', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari optimasi sistem produksi dan manajemen industri.', 'prospek' => 'Industrial Engineer, Operation Manager', 'img' => 'https://images.pexels.com/photos/327540/pexels-photo-327540.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Kimia', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari proses industri berbasis kimia dan bioteknologi.', 'prospek' => 'Process Engineer, Quality Control', 'img' => 'https://images.pexels.com/photos/2280549/pexels-photo-2280549.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Arsitektur', 'gelar' => 'S.Ars', 'deskripsi' => 'Mempelajari perancangan bangunan dan tata ruang.', 'prospek' => 'Arsitek, Urban Planner', 'img' => 'https://images.pexels.com/photos/1732414/pexels-photo-1732414.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Lingkungan', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari pengelolaan lingkungan dan sumber daya alam.', 'prospek' => 'Environmental Consultant', 'img' => 'https://images.pexels.com/photos/459728/pexels-photo-459728.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Pertambangan', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari eksplorasi dan pengolahan bahan galian.', 'prospek' => 'Mining Engineer', 'img' => 'https://images.pexels.com/photos/1422213/pexels-photo-1422213.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Perkapalan', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari desain dan konstruksi kapal.', 'prospek' => 'Naval Architect', 'img' => 'https://images.pexels.com/photos/1724660/pexels-photo-1724660.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Geodesi', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari pemetaan, survei, dan sistem informasi geografis.', 'prospek' => 'Surveyor, GIS Analyst, Kartografer', 'img' => 'https://images.pexels.com/photos/210793/pexels-photo-210793.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Teknik Nuklir', 'gelar' => 'S.T.', 'deskripsi' => 'Mempelajari teknologi nuklir, radiologi, dan keselamatan radiasi.', 'prospek' => 'Nuclear Engineer, Radiologist, Safety Officer', 'img' => 'https://images.pexels.com/photos/256381/pexels-photo-256381.jpeg?w=400&h=250&fit=crop'],
        ]
    ],
    'non_teknik' => [
        'nama' => 'FAKULTAS NON-TEKNIK',
        'icon' => 'bi-globe2',
        'warna' => '#0d3b66',
        'bg' => '#eff6ff',
        'prodi' => [
            ['nama' => 'Manajemen', 'gelar' => 'S.M.', 'deskripsi' => 'Mempelajari strategi bisnis, pemasaran, dan manajemen organisasi.', 'prospek' => 'Marketing Manager, Business Consultant', 'img' => 'https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Akuntansi', 'gelar' => 'S.Ak.', 'deskripsi' => 'Mempelajari sistem akuntansi, audit, dan perpajakan.', 'prospek' => 'Akuntan, Auditor, Tax Consultant', 'img' => 'https://images.pexels.com/photos/53621/calculator-calculation-insurance-finance-53621.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Ilmu Hukum', 'gelar' => 'S.H.', 'deskripsi' => 'Mempelajari sistem hukum, peradilan, dan pembuatan kebijakan.', 'prospek' => 'Pengacara, Hakim, Legal Officer', 'img' => 'https://images.pexels.com/photos/6077326/pexels-photo-6077326.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Ilmu Komunikasi', 'gelar' => 'S.I.Kom.', 'deskripsi' => 'Mempelajari media, public relations, dan komunikasi massa.', 'prospek' => 'Public Relations, Jurnalis, Marketing Communication', 'img' => 'https://images.pexels.com/photos/1674918/pexels-photo-1674918.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Psikologi', 'gelar' => 'S.Psi.', 'deskripsi' => 'Mempelajari perilaku manusia, konseling, dan psikometri.', 'prospek' => 'Psikolog, HR, Counselor', 'img' => 'https://images.pexels.com/photos/4101143/pexels-photo-4101143.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Administrasi Publik', 'gelar' => 'S.AP', 'deskripsi' => 'Mempelajari manajemen pemerintahan dan kebijakan publik.', 'prospek' => 'ASN, Policy Analyst', 'img' => 'https://images.pexels.com/photos/7083920/pexels-photo-7083920.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Administrasi Bisnis', 'gelar' => 'S.A.B.', 'deskripsi' => 'Mempelajari manajemen bisnis dan kewirausahaan.', 'prospek' => 'Business Analyst, Entrepreneur', 'img' => 'https://images.pexels.com/photos/3183153/pexels-photo-3183153.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'PGSD', 'gelar' => 'S.Pd.', 'deskripsi' => 'Mempelajari pendidikan dan pengajaran untuk jenjang SD.', 'prospek' => 'Guru SD, Dosen PGSD', 'img' => 'https://images.pexels.com/photos/5212345/pexels-photo-5212345.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Pendidikan Bahasa Inggris', 'gelar' => 'S.Pd.', 'deskripsi' => 'Mempelajari metodologi pengajaran bahasa Inggris.', 'prospek' => 'Guru Bahasa Inggris, Translator', 'img' => 'https://images.pexels.com/photos/4145190/pexels-photo-4145190.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Hubungan Internasional', 'gelar' => 'S.Hub.Int.', 'deskripsi' => 'Mempelajari diplomasi, politik global, dan kerjasama internasional.', 'prospek' => 'Diplomat, International Relations Analyst', 'img' => 'https://images.pexels.com/photos/45111/pexels-photo-45111.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Kedokteran', 'gelar' => 'S.Ked.', 'deskripsi' => 'Mempelajari ilmu kedokteran dan pelayanan kesehatan.', 'prospek' => 'Dokter Umum, Spesialis', 'img' => 'https://images.pexels.com/photos/4386467/pexels-photo-4386467.jpeg?w=400&h=250&fit=crop'],
            ['nama' => 'Ilmu Gizi', 'gelar' => 'S.Gz.', 'deskripsi' => 'Mempelajari ilmu gizi dan manajemen makanan.', 'prospek' => 'Nutritionist, Dietitian', 'img' => 'https://images.pexels.com/photos/1640774/pexels-photo-1640774.jpeg?w=400&h=250&fit=crop'],
        ]
    ]
];

// Hitung jumlah prodi
$jumlah_teknik = count($program_studi['teknik']['prodi']);
$jumlah_non_teknik = count($program_studi['non_teknik']['prodi']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Program Studi - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
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
        
        .container-custom {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-card {
            background: linear-gradient(135deg, #0d3b66, #0d6efd);
            border-radius: 24px;
            padding: 25px;
            text-align: center;
            margin-bottom: 35px;
            box-shadow: 0 10px 30px rgba(13, 59, 102, 0.3);
        }
        
        .header-card h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
        }
        
        .header-card p {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }
        
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            color: #0d3b66;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .filter-btn i {
            margin-right: 8px;
        }
        
        .filter-btn.teknik.active {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
        }
        
        .filter-btn.nonteknik.active {
            background: linear-gradient(135deg, #0d3b66, #0d6efd);
            color: white;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
        }
        
        .prodi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .prodi-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
        }
        
        .prodi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(13, 59, 102, 0.15);
        }
        
        .prodi-card.selected {
            border-color: #0d6efd;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.2);
        }
        
        .prodi-card.selected::after {
            content: '\F26A';
            font-family: bootstrap-icons;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 22px;
            color: #0d6efd;
            background: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .card-img-container {
            width: 100%;
            height: 140px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .prodi-card:hover .card-img {
            transform: scale(1.05);
        }
        
        .card-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 700;
            color: white;
            z-index: 5;
        }
        
        .card-badge.teknik {
            background: linear-gradient(135deg, #059669, #10b981);
        }
        
        .card-badge.nonteknik {
            background: linear-gradient(135deg, #0d3b66, #0d6efd);
        }
        
        .card-content {
            padding: 12px;
        }
        
        .card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0d3b66;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .card-gelar {
            font-size: 0.65rem;
            color: #64748b;
            background: #f1f5f9;
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        
        .card-description {
            font-size: 0.7rem;
            color: #475569;
            line-height: 1.4;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-prospek {
            background: #f8fafc;
            padding: 8px;
            border-radius: 10px;
            margin-top: 8px;
        }
        
        .prospek-label {
            font-size: 0.55rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 3px;
        }
        
        .prospek-value {
            font-size: 0.65rem;
            color: #1e293b;
            font-weight: 500;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            padding: 15px 25px;
            background: white;
            border-radius: 60px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 450px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-confirm {
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            border: none;
            background: linear-gradient(135deg, #0d3b66, #0d6efd);
            color: white;
            transition: all 0.3s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .btn-confirm:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #94a3b8;
        }
        
        .btn-confirm:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 59, 102, 0.3);
        }
        
        .btn-back {
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            background: #f1f5f9;
            color: #0d3b66;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .btn-back:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
            color: #0d3b66;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .prodi-card {
            animation: fadeIn 0.4s ease forwards;
        }
        
        @media (max-width: 1200px) {
            .prodi-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
            }
        }
        
        @media (max-width: 900px) {
            .prodi-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
        
        @media (max-width: 600px) {
            .prodi-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-tabs {
                gap: 15px;
            }
            
            .filter-btn {
                padding: 10px 30px;
                font-size: 0.9rem;
            }
            
            .header-card h1 {
                font-size: 1.4rem;
            }
            
            .action-buttons {
                flex-direction: column;
                border-radius: 20px;
                gap: 8px;
                max-width: 100%;
            }
            
            .btn-confirm, .btn-back {
                text-align: center;
            }
            
            .card-img-container {
                height: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="container-custom">
        <div class="header-card">
            <h1><i class="bi bi-mortarboard-fill me-2"></i>Pilih Program Studi</h1>
            <p>Pilih program studi yang sesuai dengan minat dan bakat Anda</p>
        </div>
        
        <div class="filter-tabs">
            <button class="filter-btn teknik active" onclick="filterProdi('teknik')">
                <i class="bi bi-cpu"></i>TEKNIK <span style="font-size:0.7rem;">(<?= $jumlah_teknik ?> Prodi)</span>
            </button>
            <button class="filter-btn nonteknik" onclick="filterProdi('non_teknik')">
                <i class="bi bi-globe2"></i>NON-TEKNIK <span style="font-size:0.7rem;">(<?= $jumlah_non_teknik ?> Prodi)</span>
            </button>
        </div>
        
        <div class="prodi-grid" id="prodiGrid">
            <?php foreach ($program_studi as $kategori => $data): ?>
                <?php foreach ($data['prodi'] as $index => $prodi): ?>
                    <div class="prodi-card" 
                         data-kategori="<?= $kategori ?>"
                         data-nama="<?= htmlspecialchars($prodi['nama']) ?>"
                         onclick="selectProdi(this, '<?= htmlspecialchars($prodi['nama']) ?>')">
                        <div class="card-img-container">
                            <img src="<?= $prodi['img'] ?>" alt="<?= htmlspecialchars($prodi['nama']) ?>" class="card-img" loading="lazy" onerror="this.src='https://images.pexels.com/photos/267885/pexels-photo-267885.jpeg?w=400&h=250&fit=crop'">
                            <div class="card-badge <?= $kategori == 'teknik' ? 'teknik' : 'nonteknik' ?>">
                                <i class="bi <?= $data['icon'] ?> me-1"></i><?= $kategori == 'teknik' ? 'TEKNIK' : 'NON-TEKNIK' ?>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-title"><?= htmlspecialchars($prodi['nama']) ?></div>
                            <div class="card-gelar">
                                <i class="bi bi-award me-1"></i><?= $prodi['gelar'] ?>
                            </div>
                            <div class="card-description">
                                <?= htmlspecialchars($prodi['deskripsi']) ?>
                            </div>
                            <div class="card-prospek">
                                <div class="prospek-label">
                                    <i class="bi bi-briefcase-fill me-1"></i>PROSPEK KERJA
                                </div>
                                <div class="prospek-value">
                                    <?= htmlspecialchars($prodi['prospek']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="action-buttons">
            <a href="../auth/pendaftaran-user.php" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <button type="button" class="btn-confirm" id="confirmBtn" disabled onclick="confirmProdi()">
                <i class="bi bi-check-circle me-2"></i>Pilih Prodi Ini
            </button>
        </div>
    </div>
    
    <script>
        let selectedProdi = null;
        let selectedCard = null;
        
        function filterProdi(kategori) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            if (kategori === 'teknik') {
                document.querySelector('.filter-btn.teknik').classList.add('active');
            } else {
                document.querySelector('.filter-btn.nonteknik').classList.add('active');
            }
            
            const cards = document.querySelectorAll('.prodi-card');
            cards.forEach(card => {
                if (card.dataset.kategori === kategori) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (selectedCard && selectedCard.dataset.kategori !== kategori) {
                selectedCard.classList.remove('selected');
                selectedProdi = null;
                selectedCard = null;
                document.getElementById('confirmBtn').disabled = true;
            }
        }
        
        function selectProdi(card, nama) {
            document.querySelectorAll('.prodi-card').forEach(c => {
                c.classList.remove('selected');
            });
            
            card.classList.add('selected');
            selectedProdi = nama;
            selectedCard = card;
            document.getElementById('confirmBtn').disabled = false;
        }
        
        function confirmProdi() {
            if (selectedProdi) {
                // Redirect ke konfirmasi_pendaftaran.php di folder yang sama (user/)
                window.location.href = 'konfirmasi_pendaftaran.php?prodi=' + encodeURIComponent(selectedProdi);
            } else {
                alert('Silakan pilih program studi terlebih dahulu!');
            }
        }
        
        filterProdi('teknik');
    </script>
</body>
</html>