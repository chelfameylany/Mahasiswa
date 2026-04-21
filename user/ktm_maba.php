<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id_maba'])) {
    header("Location: login_maba.php");
    exit();
}

$id_maba = mysqli_real_escape_string($koneksi, $_SESSION['id_maba']);
$query = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba='$id_maba'");
$data = mysqli_fetch_assoc($query);

$tahun = date('y');
$nim_10_digit = $tahun . str_pad($id_maba, 4, '0', STR_PAD_LEFT) . rand(1000, 9999);

$prodi_nama = $data['jurusan'] ?? 'TEKNIK NUKLIR';
$tanggal_lahir_formatted = !empty($data['tanggal_lahir']) ? date('d-m-Y', strtotime($data['tanggal_lahir'])) : '12-08-2009';
$tanggal_dikeluarkan = date('d-m-Y');
$tanggal_berlaku = date('d-m-Y', strtotime('+4 years'));

// WARNA UNTUK KTM
$jurusan_lower = strtolower($prodi_nama);
if(strpos($jurusan_lower, 'teknik') !== false) {
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_header_start = "#1B5E20";
    $warna_header_end = "#43A047";
    $warna_bg_body = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan = "fa-microchip";
} else {
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_header_start = "#1E40AF";
    $warna_header_end = "#3B82F6";
    $warna_bg_body = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan = "fa-graduation-cap";
}

$foto_path = "uploads/foto/" . ($data['foto'] ?? '');
$foto_exists = !empty($data['foto']) && file_exists($foto_path);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KTM - Universitas Cendekia Nusantara</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }

        .container {
            max-width: 750px;
            width: 100%;
        }

        .ktm-card {
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .campus-header {
            background: linear-gradient(135deg, <?php echo $warna_header_start; ?>, <?php echo $warna_header_end; ?>);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            color: white;
            position: relative;
        }

        .campus-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, <?php echo $warna_accent; ?>, transparent);
        }

        .logo-bulet {
            width: 55px;
            height: 55px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-bulet img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header-text {
            text-align: center;
        }

        .header-text h1 {
            font-size: 1.15rem;
            font-weight: 800;
        }

        .header-text .tagline {
            font-size: 0.6rem;
            opacity: 0.95;
            margin-top: 5px;
        }

        .ktm-body {
            background: <?php echo $warna_bg_body; ?>;
            padding: 20px;
            display: flex;
            gap: 25px;
        }

        .left-section {
            flex-shrink: 0;
            text-align: center;
            width: 155px;
        }

        .foto-box {
            width: 155px;
            height: 185px;
            background: transparent;
            border-radius: 16px;
            overflow: hidden;
        }

        .foto-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
        }

        .foto-box i {
            font-size: 4rem;
            color: #94a3b8;
            line-height: 185px;
            display: block;
            text-align: center;
            background: #f1f5f9;
            border-radius: 16px;
        }

        .foto-caption {
            font-size: 0.5rem;
            color: <?php echo $warna_primary; ?>;
            margin-top: 8px;
            font-weight: 600;
        }

        .nim-bawah {
            background: white;
            border-radius: 12px;
            padding: 10px 8px;
            text-align: center;
            margin-top: 12px;
            border: 1px solid <?php echo $warna_accent; ?>;
        }

        .nim-bawah-label {
            font-size: 0.45rem;
            font-weight: 700;
            color: <?php echo $warna_primary; ?>;
            text-transform: uppercase;
        }

        .nim-bawah-value {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.9rem;
            font-weight: 900;
            letter-spacing: 1.5px;
            color: <?php echo $warna_primary_dark; ?>;
            margin-top: 4px;
        }

        .right-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 20px;
            background: white;
            padding: 12px 15px;
            border-radius: 12px;
        }

        .data-item {
            display: flex;
            flex-direction: column;
        }

        .data-label {
            font-size: 0.5rem;
            font-weight: 700;
            color: <?php echo $warna_primary; ?>;
            text-transform: uppercase;
        }

        .data-value {
            font-size: 0.7rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: 3px;
        }

        .jurusan-area {
            background: white;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .jurusan-label {
            font-size: 0.6rem;
            font-weight: 700;
            color: <?php echo $warna_primary; ?>;
            text-transform: uppercase;
        }

        .jurusan-value {
            font-size: 0.85rem;
            font-weight: 800;
            color: <?php echo $warna_primary_dark; ?>;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .validity-area {
            display: flex;
            background: white;
            border-radius: 12px;
            padding: 12px 10px;
            text-align: center;
        }

        .validity-item {
            flex: 1;
            border-right: 1px solid #e2e8f0;
        }

        .validity-item:last-child {
            border-right: none;
        }

        .validity-label {
            font-size: 0.5rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        .validity-value {
            font-size: 0.65rem;
            font-weight: 700;
            color: <?php echo $warna_primary_dark; ?>;
            margin-top: 5px;
        }

        .status-active {
            color: <?php echo $warna_primary; ?>;
        }

        .footer-card {
            background: #f1f5f9;
            padding: 10px;
            text-align: center;
            font-size: 0.45rem;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .footer-card div {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* TOMBOL NAVIGASI */
        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-custom {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-dashboard {
            background: linear-gradient(135deg, <?php echo $warna_primary; ?>, <?php echo $warna_primary_dark; ?>);
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-dashboard:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            color: white;
        }

        .btn-print {
            background: <?php echo $warna_accent; ?>;
            color: <?php echo $warna_primary_dark; ?>;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            color: <?php echo $warna_primary_dark; ?>;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .ktm-card {
                box-shadow: none;
                border: 1px solid #ccc;
            }
            .campus-header, .ktm-body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .button-container {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .ktm-body {
                flex-direction: column;
            }
            .left-section {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="ktm-card">
            <!-- HEADER DENGAN LOGO GAMBAR -->
            <div class="campus-header">
                <div class="logo-bulet">
                    <img src="logokampus1.png" alt="Logo Universitas" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Ccircle cx=%2750%27 cy=%2750%27 r=%2745%27 fill=%27%23ffffff%27/%3E%3Ctext x=%2750%27 y=%2765%27 text-anchor=%27middle%27 font-size=%2740%27 fill=%27%231B5E20%27%3E🏫%3C/text%3E%3C/svg%3E';">
                </div>
                <div class="header-text">
                    <h1>UNIVERSITAS CENDEKIA NUSANTARA</h1>
                    <div class="tagline">✦ Terakreditasi Unggul ✦</div>
                </div>
            </div>

            <div class="ktm-body">
                <div class="left-section">
                    <div class="foto-box">
                        <?php if ($foto_exists): ?>
                            <img src="<?php echo $foto_path . '?t=' . time(); ?>" alt="Foto">
                        <?php else: ?>
                            <i class="fas fa-user-graduate"></i>
                        <?php endif; ?>
                    </div>
                    <div class="foto-caption">Pas Foto 3x4</div>
                    
                    <div class="nim-bawah">
                        <div class="nim-bawah-label">NIM</div>
                        <div class="nim-bawah-value"><?php echo $nim_10_digit; ?></div>
                    </div>
                </div>

                <div class="right-section">
                    <div class="data-grid">
                        <div class="data-item">
                            <div class="data-label">Nama</div>
                            <div class="data-value"><?php echo htmlspecialchars($data['nama'] ?? 'Alea Putri Zwijwa'); ?></div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">JK</div>
                            <div class="data-value"><?php echo ($data['jenis_kelamin'] ?? 'P') == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">TTL</div>
                            <div class="data-value"><?php echo htmlspecialchars($data['tempat_lahir'] ?? 'Banten'); ?>, <?php echo $tanggal_lahir_formatted; ?></div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Kewarganegaraan</div>
                            <div class="data-value"><?php echo htmlspecialchars($data['kewarganegaraan'] ?? 'WNI'); ?></div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Agama</div>
                            <div class="data-value"><?php echo htmlspecialchars($data['agama'] ?? 'Katolik'); ?></div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Alamat</div>
                            <div class="data-value"><?php echo htmlspecialchars(substr($data['alamat'] ?? 'Jl. Nusantara No. 88', 0, 30)); ?></div>
                        </div>
                    </div>

                    <div class="jurusan-area">
                        <span class="jurusan-label">PROGRAM STUDI</span>
                        <span class="jurusan-value">
                            <i class="fas <?php echo $icon_jurusan; ?>"></i>
                            <?php echo $teks_jurusan; ?> - <?php echo htmlspecialchars($prodi_nama); ?>
                        </span>
                    </div>

                    <div class="validity-area">
                        <div class="validity-item">
                            <div class="validity-label">DIKELUARKAN</div>
                            <div class="validity-value"><?php echo $tanggal_dikeluarkan; ?></div>
                        </div>
                        <div class="validity-item">
                            <div class="validity-label">BERLAKU S/D</div>
                            <div class="validity-value"><?php echo $tanggal_berlaku; ?></div>
                        </div>
                        <div class="validity-item">
                            <div class="validity-label">STATUS</div>
                            <div class="validity-value status-active">✔ AKTIF</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-card">
                <div><i class="fas fa-globe"></i> www.ucn.ac.id</div>
                <div><i class="fas fa-envelope"></i> info@ucn.ac.id</div>
                <div><i class="fas fa-phone"></i> (0711) 1234567</div>
                <div><i class="fas fa-qrcode"></i> <?php echo $nim_10_digit; ?></div>
            </div>
        </div>

        <!-- TOMBOL CETAK KTM DAN KEMBALI KE DASHBOARD -->
        <div class="button-container">
            <a href="dashboard_maba.php" class="btn-custom btn-dashboard">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <button onclick="window.print()" class="btn-custom btn-print">
                <i class="fas fa-print"></i> Cetak KTM
            </button>
        </div>
    </div>

    <script>
        // Optional: Tambahkan efek smooth saat cetak
        console.log("KTM siap dicetak");
    </script>
</body>
</html>