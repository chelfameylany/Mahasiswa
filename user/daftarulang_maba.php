<?php
session_start();
include "../koneksi.php";

// CEK LOGIN
if (!isset($_SESSION['id_maba'])) {
    header("Location: login_maba.php");
    exit();
}

$id_maba = mysqli_real_escape_string($koneksi, $_SESSION['id_maba']);
$query = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba='$id_maba'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit();
}

// GENERATE NIM 10 DIGIT
$tahun = date('y');
$nim_10_digit = $tahun . str_pad($id_maba, 4, '0', STR_PAD_LEFT) . rand(1000, 9999);

$tanggal_lahir_formatted = !empty($data['tanggal_lahir']) ? date('d/m/Y', strtotime($data['tanggal_lahir'])) : '-';
$tanggal_sekarang = date('d F Y');
$tanggal_dikeluarkan = date('d-m-Y');
$tanggal_berlaku = date('d-m-Y', strtotime('+4 years'));

$foto_path = "uploads/foto/" . ($data['foto'] ?? '');
$foto_exists = !empty($data['foto']) && file_exists($foto_path);

// PROGRAM STUDI
$jurusan = $data['jurusan'] ?? 'TEKNIK NUKLIR';
$kode_jurusan = $data['kode_jurusan'] ?? '271002';
$ptn_nama = "UNIVERSITAS CENDEKIA NUSANTARA";

function buatSingkatan($nama_jurusan) {
    $kata_kunci = [
        'TEKNIK INFORMATIKA' => 'S.Kom',
        'TEKNIK SIPIL' => 'S.T.',
        'TEKNIK MESIN' => 'S.T.',
        'TEKNIK ELEKTRO' => 'S.T.',
        'TEKNIK INDUSTRI' => 'S.T.',
        'TEKNIK KIMIA' => 'S.T.',
        'ARSITEKTUR' => 'S.Ars',
        'TEKNIK LINGKUNGAN' => 'S.T.',
        'TEKNIK PERTAMBANGAN' => 'S.T.',
        'TEKNIK PERKAPALAN' => 'S.T.',
        'TEKNIK GEODESI' => 'S.T.',
        'TEKNIK NUKLIR' => 'S.T.',
        'MANAJEMEN' => 'S.M.',
        'AKUNTANSI' => 'S.Ak.',
        'ILMU HUKUM' => 'S.H.',
        'HUKUM' => 'S.H.',
        'ILMU KOMUNIKASI' => 'S.I.Kom.',
        'PSIKOLOGI' => 'S.Psi.',
        'ADMINISTRASI PUBLIK' => 'S.AP',
        'ADMINISTRASI BISNIS' => 'S.A.B.',
        'PGSD' => 'S.Pd.',
        'PENDIDIKAN BAHASA INGGRIS' => 'S.Pd.',
        'HUBUNGAN INTERNASIONAL' => 'S.Hub.Int.',
        'KEDOKTERAN' => 'S.Ked.',
        'ILMU GIZI' => 'S.Gz.',
    ];
    
    $nama_upper = strtoupper(trim($nama_jurusan));
    if (isset($kata_kunci[$nama_upper])) {
        return $kata_kunci[$nama_upper];
    }
    foreach ($kata_kunci as $kunci => $singkatan) {
        if (strpos($nama_upper, $kunci) !== false) {
            return $singkatan;
        }
    }
    $kata = explode(' ', $nama_jurusan);
    $singkatan = '';
    foreach ($kata as $k) {
        if (!empty($k)) {
            $singkatan .= substr($k, 0, 1);
        }
    }
    return strtoupper($singkatan) . '.';
}

$singkatan_jurusan = buatSingkatan($jurusan);

// WARNA
$jurusan_lower = strtolower(trim($jurusan));
if(strpos($jurusan_lower, 'teknik') !== false) {
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #2E7D32, #1B5E20)";
    $warna_soft = "#E8F5E9";
    $icon_jurusan = "fa-gear";
    $warna_header_start = "#1B5E20";
    $warna_header_end = "#43A047";
    $warna_bg_body = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan_ktm = "fa-microchip";
} else {
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #DBEAFE 0%, #FFFFFF 100%)";
    $warna_gradient_card = "linear-gradient(135deg, #3B82F6, #2563EB)";
    $warna_soft = "#DBEAFE";
    $icon_jurusan = "fa-users";
    $warna_header_start = "#1E40AF";
    $warna_header_end = "#3B82F6";
    $warna_bg_body = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan_ktm = "fa-graduation-cap";
}

$nik = $data['nik'] ?? ($data['nisn'] ?? '-');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Daftar Ulang - <?php echo htmlspecialchars($data['nama'] ?? 'Mahasiswa'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: <?php echo $warna_gradient_bg; ?>;
            min-height: 100vh;
            padding: 30px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 750px;
            width: 100%;
            margin: 0 auto;
        }

        .certificate-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: <?php echo $warna_gradient_card; ?>;
            padding: 20px 25px;
            color: white;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .logo-wrapper {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-wrapper i {
            font-size: 28px;
            color: <?php echo $warna_primary; ?>;
        }

        .univ-info {
            flex: 1;
        }

        .univ-name {
            font-size: 1.1rem;
            font-weight: 800;
        }

        .univ-akreditas span {
            background: rgba(255,255,255,0.2);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.6rem;
        }

        .header-title {
            text-align: center;
            margin: 8px 0;
        }

        .header-title h1 {
            font-size: 1.2rem;
            font-weight: 800;
        }

        .header-title h2 {
            font-size: 0.65rem;
            font-weight: 400;
        }

        .header-info {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 8px;
            font-size: 0.6rem;
        }

        .header-info span {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,0.15);
            padding: 4px 14px;
            border-radius: 40px;
        }

        .body {
            padding: 25px;
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .left-column {
            background: <?php echo $warna_soft; ?>;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }

        .foto-wrapper {
            width: 100%;
            max-width: 160px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #f0f0f0;
            aspect-ratio: 3 / 4;
        }

        .foto-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .foto-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, <?php echo $warna_primary; ?>, <?php echo $warna_primary_dark; ?>);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
        }

        .nama-mahasiswa {
            margin-top: 12px;
            font-size: 1rem;
            font-weight: 700;
            color: <?php echo $warna_primary_dark; ?>;
        }

        .nim-info {
            background: white;
            border-radius: 12px;
            padding: 12px;
            margin-top: 15px;
        }

        .nim-number {
            font-size: 1rem;
            font-weight: 800;
            font-family: monospace;
            color: <?php echo $warna_primary; ?>;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #d4edda;
            color: #155724;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.6rem;
        }

        .right-column {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        .info-header {
            background: <?php echo $warna_soft; ?>;
            padding: 12px 15px;
            border-bottom: 2px solid <?php echo $warna_primary; ?>;
        }

        .info-header h3 {
            font-size: 0.85rem;
            font-weight: 700;
            color: <?php echo $warna_primary_dark; ?>;
        }

        .info-content {
            padding: 15px;
        }

        .info-vertical {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            background: <?php echo $warna_soft; ?>;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: <?php echo $warna_primary; ?>;
        }

        .info-label {
            font-size: 0.55rem;
            color: #6c757d;
            font-weight: 600;
        }

        .info-value {
            font-weight: 600;
            color: #212529;
            font-size: 0.8rem;
        }

        .prodi-section {
            margin: 20px 0;
        }

        .prodi-card {
            background: linear-gradient(135deg, #fff, <?php echo $warna_soft; ?>);
            border-radius: 16px;
            border: 1px solid <?php echo $warna_primary; ?>;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .prodi-header {
            background: <?php echo $warna_primary; ?>;
            padding: 10px 15px;
            color: white;
        }

        .prodi-header h3 {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .prodi-content {
            padding: 18px;
        }

        .prodi-item {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .gelar-badge {
            background: <?php echo $warna_accent; ?>;
            color: <?php echo $warna_primary_dark; ?>;
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 800;
            font-size: 0.75rem;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            letter-spacing: 0.5px;
        }

        .prodi-detail {
            flex: 1;
        }

        .prodi-nama-lengkap {
            font-weight: 800;
            color: <?php echo $warna_primary_dark; ?>;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .prodi-kode {
            font-size: 0.65rem;
            color: #6c757d;
            margin-top: 4px;
        }

        .prodi-ptn {
            font-size: 0.7rem;
            color: <?php echo $warna_primary; ?>;
            margin-top: 6px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .pernyataan {
            background: #f8f9fa;
            border-left: 4px solid <?php echo $warna_accent; ?>;
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 10px;
            font-size: 0.7rem;
            font-style: italic;
            line-height: 1.5;
        }

        .signature-section {
            margin: 20px 0 15px;
            padding-top: 15px;
            border-top: 1px dashed #e9ecef;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            align-items: flex-start;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-date {
            font-size: 0.7rem;
            color: #6c757d;
            margin-bottom: 25px;
        }

        .mahasiswa-ttd-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .ttd-placeholder-text {
            font-size: 0.65rem;
            color: transparent;
            margin-bottom: 50px;
        }

        .ttd-line-mahasiswa {
            width: 180px;
            height: 1.5px;
            background: #000;
            margin: 0 auto 10px auto;
        }

        .rektor-ttd-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .ttd-rektor-container {
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }

        .stempel-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            width: 65px;
            height: 65px;
            border: 2.5px solid <?php echo $warna_accent; ?>;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: <?php echo $warna_accent; ?>;
            background: rgba(255,179,0,0.08);
            z-index: 1;
        }

        .stempel-bg i {
            font-size: 1.2rem;
        }

        .stempel-bg .stamp-text {
            font-size: 0.35rem;
            font-weight: 800;
        }

        .ttd-rektor-tulisan {
            position: relative;
            z-index: 2;
            font-family: 'Brush Script MT', cursive, 'Segoe UI', 'Poppins', sans-serif;
            font-size: 1.6rem;
            font-weight: 500;
            color: <?php echo $warna_primary_dark; ?>;
            line-height: 1;
            padding: 14px 0px;
            background: transparent;
        }

        .ttd-line-rektor {
            width: 180px;
            height: 1.5px;
            background: #000;
            margin: 0 auto 10px auto;
        }

        .signature-name {
            font-weight: 700;
            color: <?php echo $warna_primary; ?>;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .signature-role {
            font-size: 0.65rem;
            color: #6c757d;
        }

        .signature-nip {
            font-size: 0.6rem;
            color: <?php echo $warna_accent; ?>;
            margin-top: 4px;
        }

        .footer {
            background: #f8f9fa;
            padding: 12px 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-size: 0.55rem;
            color: #6c757d;
        }

        .footer div {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-nav {
            padding: 10px 22px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
        }

        .btn-back {
            background: rgba(0,0,0,0.1);
            color: <?php echo $warna_primary_dark; ?>;
            text-decoration: none;
        }

        .btn-print {
            background: <?php echo $warna_primary; ?>;
            color: white;
        }

        .btn-ktm {
            background: <?php echo $warna_accent; ?>;
            color: <?php echo $warna_primary_dark; ?>;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: relative;
            background: white;
            margin: 3% auto;
            padding: 20px;
            width: auto;
            max-width: 750px;
            border-radius: 20px;
            animation: modalPop 0.3s ease;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .close-modal {
            position: absolute;
            top: -12px;
            right: -12px;
            background: <?php echo $warna_primary_dark; ?>;
            color: white;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 1001;
            transition: 0.2s;
        }

        .close-modal:hover {
            background: <?php echo $warna_accent; ?>;
            color: <?php echo $warna_primary_dark; ?>;
            transform: scale(1.1);
        }

        /* TOMBOL CETAK DI BAWAH KTM */
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .btn-cetak-ktm {
            background: <?php echo $warna_primary; ?>;
            color: white;
            padding: 10px 24px;
            border-radius: 40px;
            border: none;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-cetak-ktm:hover {
            background: <?php echo $warna_primary_dark; ?>;
            transform: translateY(-2px);
        }

        .btn-tutup {
            background: #e2e8f0;
            color: #334155;
            padding: 10px 24px;
            border-radius: 40px;
            border: none;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-tutup:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }

        @media print {
            @page {
                size: A4;
                margin: 0.8cm;
            }
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .no-print, .nav-buttons, .modal {
                display: none;
            }
            .header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 600px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
            .signature-row {
                flex-direction: column;
                gap: 30px;
            }
            .modal-content {
                margin: 5% auto;
                width: 95%;
            }
            /* PERBAIKAN JARAK CARD DI MODAL */
.modal .jurusan-area {
    margin-bottom: 5px;
}

.modal .validity-area {
    margin-top: 0;
}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="certificate-card">
            <div class="header">
                <div class="logo-container">
                    <div class="logo-wrapper">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="univ-info">
                        <div class="univ-name">UNIVERSITAS CENDEKIA NUSANTARA</div>
                        <div class="univ-akreditas">
                            <span>AKREDITASI A</span>
                            <span>Perguruan Tinggi Terbaik</span>
                        </div>
                    </div>
                </div>
                <div class="header-title">
                    <h1>BUKTI DAFTAR ULANG</h1>
                    <h2>MAHASISWA BARU TAHUN AKADEMIK 2025/2026</h2>
                </div>
                <div class="header-info">
                    <span><i class="fas fa-calendar-alt"></i> Semester Ganjil</span>
                    <span><i class="fas fa-certificate"></i> No: <?php echo $nim_10_digit; ?>/UCN/2025</span>
                </div>
            </div>

            <div class="body">
                <div class="two-columns">
                    <div class="left-column">
                        <div class="foto-wrapper">
                            <?php if ($foto_exists): ?>
                                <img src="<?php echo $foto_path . '?t=' . time(); ?>" alt="Foto">
                            <?php else: ?>
                                <div class="foto-placeholder">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="nama-mahasiswa">
                            <?php echo htmlspecialchars($data['nama'] ?? '-'); ?>
                        </div>
                        <div class="nim-info">
                            <div class="nim-number"><?php echo $nim_10_digit; ?></div>
                            <div class="status-badge">
                                <i class="fas fa-check-circle"></i> TERVERIFIKASI
                            </div>
                        </div>
                    </div>

                    <div class="right-column">
                        <div class="info-header">
                            <h3><i class="fas fa-user-circle"></i> Data Pribadi Mahasiswa</h3>
                        </div>
                        <div class="info-content">
                            <div class="info-vertical">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-id-card"></i></div>
                                    <div class="info-text">
                                        <div class="info-label">NIK</div>
                                        <div class="info-value"><?php echo htmlspecialchars($nik); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="info-text">
                                        <div class="info-label">Tempat Lahir</div>
                                        <div class="info-value"><?php echo htmlspecialchars($data['tempat_lahir'] ?? '-'); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div class="info-text">
                                        <div class="info-label">Tanggal Lahir</div>
                                        <div class="info-value"><?php echo $tanggal_lahir_formatted; ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-flag"></i></div>
                                    <div class="info-text">
                                        <div class="info-label">Kewarganegaraan</div>
                                        <div class="info-value"><?php echo htmlspecialchars($data['kewarganegaraan'] ?? 'WNI'); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-school"></i></div>
                                    <div class="info-text">
                                        <div class="info-label">Asal Sekolah</div>
                                        <div class="info-value"><?php echo htmlspecialchars($data['asal_sekolah'] ?? '-'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="prodi-section">
                    <div class="prodi-card">
                        <div class="prodi-header">
                            <h3><i class="fas <?php echo $icon_jurusan; ?>"></i> PROGRAM STUDI YANG DIPILIH</h3>
                        </div>
                        <div class="prodi-content">
                            <div class="prodi-item">
                                <div class="gelar-badge"><?php echo $singkatan_jurusan; ?></div>
                                <div class="prodi-detail">
                                    <div class="prodi-nama-lengkap">
                                        <?php echo htmlspecialchars($jurusan); ?>
                                    </div>
                                    <div class="prodi-kode">Kode: <?php echo htmlspecialchars($kode_jurusan); ?></div>
                                    <div class="prodi-ptn"><i class="fas fa-university"></i> <?php echo $ptn_nama; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pernyataan">
                    <i class="fas fa-quote-left"></i> Saya menyatakan bahwa data yang saya masukkan dalam formulir pendaftaran adalah benar dan saya bersedia menerima ketentuan yang berlaku di Perguruan Tinggi dan Program Studi yang saya pilih.
                </div>

                <div class="signature-section">
                    <div class="signature-row">
                        <div class="signature-box">
                            <div class="signature-date">Bandung, <?php echo $tanggal_sekarang; ?></div>
                            <div class="mahasiswa-ttd-area">
                                <div class="ttd-placeholder-text">(tempat tanda tangan)</div>
                                <div class="ttd-line-mahasiswa"></div>
                                <div class="signature-name"><?php echo htmlspecialchars($data['nama'] ?? '_________________'); ?></div>
                                <div class="signature-role">Mahasiswa</div>
                                <div class="signature-nip">NIM: <?php echo $nim_10_digit; ?></div>
                            </div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-date">Bandung, <?php echo $tanggal_sekarang; ?></div>
                            <div class="rektor-ttd-area">
                                <div class="ttd-rektor-container">
                                    <div class="stempel-bg">
                                        <i class="fas fa-university"></i>
                                        <div class="stamp-text">UCN</div>
                                    </div>
                                    <div class="ttd-rektor-tulisan">Naura Oceanlynda</div>
                                </div>
                                <div class="ttd-line-rektor"></div>
                                <div class="signature-name">Prof. Dr. Naura Oceanlynda, M.B.A.</div>
                                <div class="signature-role">Rektor</div>
                                <div class="signature-nip">NIP. 199305172021032004</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div><i class="fas fa-globe"></i> www.ucn.ac.id</div>
                <div><i class="fas fa-envelope"></i> info@ucn.ac.id</div>
                <div><i class="fas fa-phone"></i> (0711) 1234567</div>
                <div><i class="fas fa-qrcode"></i> <?php echo $nim_10_digit; ?></div>
            </div>
        </div>

        <div class="nav-buttons no-print">
            <a href="dashboard_maba.php" class="btn-nav btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn-nav btn-print">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
            <button onclick="openKTM()" class="btn-nav btn-ktm">
                <i class="fas fa-id-card"></i> Kartu Mahasiswa
            </button>
        </div>
    </div>

    <!-- MODAL KTM - TAMPILAN SAMA PERSIS DENGAN ktm_maba.php -->
    <div id="ktmModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeKTM()">&times;</span>
            
            <!-- KTM - SAMA PERSIS DENGAN FILE ktm_maba.php -->
            <div class="ktm-card" style="width:100%; background:white; border-radius:20px; overflow:hidden;">
                <div class="campus-header" style="background:linear-gradient(135deg, <?php echo $warna_header_start; ?>, <?php echo $warna_header_end; ?>); padding:18px 20px; display:flex; align-items:center; justify-content:center; gap:20px; color:white; position:relative;">
                    <div class="logo-bulet" style="width:55px; height:55px; background:white; border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        <img src="logokampus1.png" alt="Logo Universitas" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Ccircle cx=%2750%27 cy=%2750%27 r=%2745%27 fill=%27%23ffffff%27/%3E%3Ctext x=%2750%27 y=%2765%27 text-anchor=%27middle%27 font-size=%2740%27 fill=%27%231B5E20%27%3E🏫%3C/text%3E%3C/svg%3E';">
                    </div>
                    <div class="header-text" style="text-align:center;">
                        <h1 style="font-size:1.15rem; font-weight:800;">UNIVERSITAS CENDEKIA NUSANTARA</h1>
                        <div class="tagline" style="font-size:0.6rem; opacity:0.95; margin-top:5px;">✦ Terakreditasi Unggul ✦</div>
                    </div>
                </div>

                <div class="ktm-body" style="background:<?php echo $warna_bg_body; ?>; padding:20px; display:flex; gap:25px;">
                    <div class="left-section" style="flex-shrink:0; text-align:center; width:155px;">
                        <div class="foto-box" style="width:155px; height:185px; border-radius:16px; overflow:hidden;">
                            <?php if ($foto_exists): ?>
                                <img src="<?php echo $foto_path . '?t=' . time(); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-user-graduate" style="font-size:4rem; color:#94a3b8; line-height:185px; display:block; text-align:center; background:#f1f5f9;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="foto-caption" style="font-size:0.5rem; color:<?php echo $warna_primary; ?>; margin-top:8px; font-weight:600;">Pas Foto 3x4</div>
                        <div class="nim-bawah" style="background:white; border-radius:12px; padding:10px 8px; text-align:center; margin-top:12px; border:1px solid <?php echo $warna_accent; ?>;">
                            <div class="nim-bawah-label" style="font-size:0.45rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">NIM</div>
                            <div class="nim-bawah-value" style="font-family:'Share Tech Mono', monospace; font-size:0.9rem; font-weight:900; letter-spacing:1.5px; color:<?php echo $warna_primary_dark; ?>; margin-top:4px;"><?php echo $nim_10_digit; ?></div>
                        </div>
                    </div>

                    <div class="right-section" style="flex:1; display:flex; flex-direction:column; gap:12px;">
                        <div class="data-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:10px 20px; background:white; padding:12px 15px; border-radius:12px;">
                            <div class="data-item" style="display:flex; flex-direction:column;">
                                <div class="data-label" style="font-size:0.5rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">Nama</div>
                                <div class="data-value" style="font-size:0.7rem; font-weight:600; color:#1e293b; margin-top:3px;"><?php echo htmlspecialchars($data['nama'] ?? 'Alea Putri Zwijwa'); ?></div>
                            </div>
                            <div class="data-item" style="display:flex; flex-direction:column;">
                                <div class="data-label" style="font-size:0.5rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">JK</div>
                                <div class="data-value" style="font-size:0.7rem; font-weight:600; color:#1e293b; margin-top:3px;"><?php echo ($data['jenis_kelamin'] ?? 'P') == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                            </div>
                            <div class="data-item" style="display:flex; flex-direction:column;">
                                <div class="data-label" style="font-size:0.5rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">TTL</div>
                                <div class="data-value" style="font-size:0.7rem; font-weight:600; color:#1e293b; margin-top:3px;"><?php echo htmlspecialchars($data['tempat_lahir'] ?? 'Banten'); ?>, <?php echo $tanggal_lahir_formatted; ?></div>
                            </div>
                            <div class="data-item" style="display:flex; flex-direction:column;">
                                <div class="data-label" style="font-size:0.5rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">Kewarganegaraan</div>
                                <div class="data-value" style="font-size:0.7rem; font-weight:600; color:#1e293b; margin-top:3px;"><?php echo htmlspecialchars($data['kewarganegaraan'] ?? 'WNI'); ?></div>
                            </div>
                            <div class="data-item" style="display:flex; flex-direction:column;">
                                <div class="data-label" style="font-size:0.5rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">Agama</div>
                                <div class="data-value" style="font-size:0.7rem; font-weight:600; color:#1e293b; margin-top:3px;"><?php echo htmlspecialchars($data['agama'] ?? 'Katolik'); ?></div>
                            </div>
                            <div class="data-item" style="display:flex; flex-direction:column;">
                                <div class="data-label" style="font-size:0.5rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">Alamat</div>
                                <div class="data-value" style="font-size:0.7rem; font-weight:600; color:#1e293b; margin-top:3px;"><?php echo htmlspecialchars(substr($data['alamat'] ?? 'Jl. Nusantara No. 88', 0, 30)); ?></div>
                            </div>
                        </div>

                        <div class="jurusan-area" style="background:white; border-radius:12px; padding:14px 16px; display:flex; align-items:center; justify-content:space-between;">
                            <span class="jurusan-label" style="font-size:0.6rem; font-weight:700; color:<?php echo $warna_primary; ?>; text-transform:uppercase;">PROGRAM STUDI</span>
                            <span class="jurusan-value" style="font-size:0.85rem; font-weight:800; color:<?php echo $warna_primary_dark; ?>; display:flex; align-items:center; gap:8px;">
                                <i class="fas <?php echo $icon_jurusan_ktm; ?>"></i>
                                <?php echo $teks_jurusan; ?> - <?php echo htmlspecialchars($jurusan); ?>
                            </span>
                        </div>

                        <div class="validity-area" style="display:flex; background:white; border-radius:12px; padding:12px 10px; text-align:center;">
                            <div class="validity-item" style="flex:1; border-right:1px solid #e2e8f0;">
                                <div class="validity-label" style="font-size:0.5rem; font-weight:600; color:#64748b; text-transform:uppercase;">DIKELUARKAN</div>
                                <div class="validity-value" style="font-size:0.65rem; font-weight:700; color:<?php echo $warna_primary_dark; ?>; margin-top:5px;"><?php echo $tanggal_dikeluarkan; ?></div>
                            </div>
                            <div class="validity-item" style="flex:1; border-right:1px solid #e2e8f0;">
                                <div class="validity-label" style="font-size:0.5rem; font-weight:600; color:#64748b; text-transform:uppercase;">BERLAKU S/D</div>
                                <div class="validity-value" style="font-size:0.65rem; font-weight:700; color:<?php echo $warna_primary_dark; ?>; margin-top:5px;"><?php echo $tanggal_berlaku; ?></div>
                            </div>
                            <div class="validity-item" style="flex:1;">
                                <div class="validity-label" style="font-size:0.5rem; font-weight:600; color:#64748b; text-transform:uppercase;">STATUS</div>
                                <div class="validity-value status-active" style="font-size:0.65rem; font-weight:700; color:<?php echo $warna_primary; ?>; margin-top:5px;">✔ AKTIF</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-card" style="background:#f1f5f9; padding:10px; text-align:center; font-size:0.45rem; color:#64748b; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; flex-wrap:wrap;">
                    <div style="display:inline-flex; align-items:center; gap:5px;"><i class="fas fa-globe"></i> www.ucn.ac.id</div>
                    <div style="display:inline-flex; align-items:center; gap:5px;"><i class="fas fa-envelope"></i> info@ucn.ac.id</div>
                    <div style="display:inline-flex; align-items:center; gap:5px;"><i class="fas fa-phone"></i> (0711) 1234567</div>
                    <div style="display:inline-flex; align-items:center; gap:5px;"><i class="fas fa-qrcode"></i> <?php echo $nim_10_digit; ?></div>
                </div>
            </div>

            <!-- TOMBOL CETAK DI BAWAH KTM -->
            <div class="modal-buttons">
                <button onclick="cetakKTM()" class="btn-cetak-ktm">
                    <i class="fas fa-print"></i> Cetak KTM
                </button>
                <button onclick="closeKTM()" class="btn-tutup">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openKTM() {
            document.getElementById('ktmModal').style.display = 'block';
        }

        function closeKTM() {
            document.getElementById('ktmModal').style.display = 'none';
        }

        function cetakKTM() {
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Kartu Tanda Mahasiswa - Universitas Cendekia Nusantara</title>');
            printWindow.document.write('<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">');
            printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">');
            printWindow.document.write('<style>');
            printWindow.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
            printWindow.document.write('body { font-family: "Poppins", sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: white; padding: 20px; }');
            printWindow.document.write('.ktm-card-print { width: 700px; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }');
            printWindow.document.write('.campus-header-print { background: linear-gradient(135deg, <?php echo $warna_header_start; ?>, <?php echo $warna_header_end; ?>); padding: 18px 20px; display: flex; align-items: center; justify-content: center; gap: 20px; color: white; }');
            printWindow.document.write('.logo-bulet-print { width: 55px; height: 55px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; }');
            printWindow.document.write('.logo-bulet-print img { width: 100%; height: 100%; object-fit: cover; }');
            printWindow.document.write('.header-text-print { text-align: center; }');
            printWindow.document.write('.header-text-print h1 { font-size: 1.15rem; font-weight: 800; }');
            printWindow.document.write('.ktm-body-print { background: <?php echo $warna_bg_body; ?>; padding: 20px; display: flex; gap: 25px; }');
            printWindow.document.write('.left-section-print { flex-shrink: 0; text-align: center; width: 155px; }');
            printWindow.document.write('.foto-box-print { width: 155px; height: 185px; border-radius: 16px; overflow: hidden; }');
            printWindow.document.write('.foto-box-print img { width: 100%; height: 100%; object-fit: cover; }');
            printWindow.document.write('.foto-caption-print { font-size: 0.5rem; color: <?php echo $warna_primary; ?>; margin-top: 8px; }');
            printWindow.document.write('.nim-bawah-print { background: white; border-radius: 12px; padding: 10px 8px; text-align: center; margin-top: 12px; border: 1px solid <?php echo $warna_accent; ?>; }');
            printWindow.document.write('.nim-bawah-label-print { font-size: 0.45rem; font-weight: 700; color: <?php echo $warna_primary; ?>; }');
            printWindow.document.write('.nim-bawah-value-print { font-family: "Share Tech Mono", monospace; font-size: 0.9rem; font-weight: 900; color: <?php echo $warna_primary_dark; ?>; }');
            printWindow.document.write('.data-grid-print { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; background: white; padding: 12px 15px; border-radius: 12px; }');
            printWindow.document.write('.data-label-print { font-size: 0.5rem; font-weight: 700; color: <?php echo $warna_primary; ?>; }');
            printWindow.document.write('.data-value-print { font-size: 0.7rem; font-weight: 600; color: #1e293b; }');
            printWindow.document.write('.jurusan-area-print { background: white; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; }');
            printWindow.document.write('.jurusan-label-print { font-size: 0.6rem; font-weight: 700; color: <?php echo $warna_primary; ?>; }');
            printWindow.document.write('.jurusan-value-print { font-size: 0.85rem; font-weight: 800; color: <?php echo $warna_primary_dark; ?>; display: flex; align-items: center; gap: 8px; }');
            printWindow.document.write('.validity-area-print { display: flex; background: white; border-radius: 12px; padding: 12px 10px; text-align: center; }');
            printWindow.document.write('.validity-item-print { flex: 1; border-right: 1px solid #e2e8f0; }');
            printWindow.document.write('.validity-item-print:last-child { border-right: none; }');
            printWindow.document.write('.validity-label-print { font-size: 0.5rem; font-weight: 600; color: #64748b; }');
            printWindow.document.write('.validity-value-print { font-size: 0.65rem; font-weight: 700; color: <?php echo $warna_primary_dark; ?>; }');
            printWindow.document.write('.status-active-print { color: <?php echo $warna_primary; ?>; }');
            printWindow.document.write('.footer-card-print { background: #f1f5f9; padding: 10px; text-align: center; font-size: 0.45rem; color: #64748b; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; flex-wrap: wrap; }');
            printWindow.document.write('.footer-card-print div { display: inline-flex; align-items: center; gap: 5px; }');
            printWindow.document.write('@media print { .campus-header-print, .ktm-body-print { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="ktm-card-print">');
            printWindow.document.write('<div class="campus-header-print"><div class="logo-bulet-print"><img src="logokampus1.png" alt="Logo" style="width:100%; height:100%; object-fit:cover;" onerror="this.src=\'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Ccircle cx=%2750%27 cy=%2750%27 r=%2745%27 fill=%27%23ffffff%27/%3E%3Ctext x=%2750%27 y=%2765%27 text-anchor=%27middle%27 font-size=%2740%27 fill=%27%231B5E20%27%3E🏫%3C/text%3E%3C/svg%3E\'"></div><div class="header-text-print"><h1>UNIVERSITAS CENDEKIA NUSANTARA</h1><div class="tagline">✦ Terakreditasi Unggul ✦</div></div></div>');
            printWindow.document.write('<div class="ktm-body-print"><div class="left-section-print"><div class="foto-box-print"><?php if ($foto_exists): ?><img src="<?php echo $foto_path . '?t=' . time(); ?>"><?php else: ?><i class="fas fa-user-graduate" style="font-size:4rem; color:#94a3b8; line-height:185px; display:block; text-align:center; background:#f1f5f9;"></i><?php endif; ?></div><div class="foto-caption-print">Pas Foto 3x4</div><div class="nim-bawah-print"><div class="nim-bawah-label-print">NIM</div><div class="nim-bawah-value-print"><?php echo $nim_10_digit; ?></div></div></div>');
            printWindow.document.write('<div class="right-section-print" style="flex:1"><div class="data-grid-print"><div class="data-item"><div class="data-label-print">Nama</div><div class="data-value-print"><?php echo htmlspecialchars($data['nama'] ?? 'Alea Putri Zwijwa'); ?></div></div><div class="data-item"><div class="data-label-print">JK</div><div class="data-value-print"><?php echo ($data['jenis_kelamin'] ?? 'P') == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div></div><div class="data-item"><div class="data-label-print">TTL</div><div class="data-value-print"><?php echo htmlspecialchars($data['tempat_lahir'] ?? 'Banten'); ?>, <?php echo $tanggal_lahir_formatted; ?></div></div><div class="data-item"><div class="data-label-print">Kewarganegaraan</div><div class="data-value-print"><?php echo htmlspecialchars($data['kewarganegaraan'] ?? 'WNI'); ?></div></div><div class="data-item"><div class="data-label-print">Agama</div><div class="data-value-print"><?php echo htmlspecialchars($data['agama'] ?? 'Katolik'); ?></div></div><div class="data-item"><div class="data-label-print">Alamat</div><div class="data-value-print"><?php echo htmlspecialchars(substr($data['alamat'] ?? 'Jl. Nusantara No. 88', 0, 30)); ?></div></div></div>');
            printWindow.document.write('<div class="jurusan-area-print"><span class="jurusan-label-print">PROGRAM STUDI</span><span class="jurusan-value-print"><i class="fas <?php echo $icon_jurusan_ktm; ?>"></i> <?php echo $teks_jurusan; ?> - <?php echo htmlspecialchars($jurusan); ?></span></div>');
            printWindow.document.write('<div class="validity-area-print"><div class="validity-item-print"><div class="validity-label-print">DIKELUARKAN</div><div class="validity-value-print"><?php echo $tanggal_dikeluarkan; ?></div></div><div class="validity-item-print"><div class="validity-label-print">BERLAKU S/D</div><div class="validity-value-print"><?php echo $tanggal_berlaku; ?></div></div><div class="validity-item-print"><div class="validity-label-print">STATUS</div><div class="validity-value-print status-active-print">✔ AKTIF</div></div></div></div></div>');
            printWindow.document.write('<div class="footer-card-print"><div><i class="fas fa-globe"></i> www.ucn.ac.id</div><div><i class="fas fa-envelope"></i> info@ucn.ac.id</div><div><i class="fas fa-phone"></i> (0711) 1234567</div><div><i class="fas fa-qrcode"></i> <?php echo $nim_10_digit; ?></div></div>');
            printWindow.document.write('</div></body></html>');
            printWindow.document.close();
            printWindow.print();
        }

        window.onclick = function(event) {
            var modal = document.getElementById('ktmModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>