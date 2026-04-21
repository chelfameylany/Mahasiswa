<?php
include "../koneksi.php";

if (!isset($_GET['id'])) exit;

$id = intval($_GET['id']);
$q = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba = $id");
$maba = mysqli_fetch_assoc($q);

if (!$maba){
    echo "<div style='text-align:center; padding:40px; color:red;'>Data tidak ditemukan</div>";
    exit;
}

// Tentukan icon dan warna status
$status_icon = '';
$status_bg = '';
$status_color = '';
switch($maba['status']) {
    case 'pending':
        $status_icon = 'bi bi-clock-history';
        $status_bg = '#fef3c7';
        $status_color = '#d97706';
        break;
    case 'diterima':
        $status_icon = 'bi bi-check-circle-fill';
        $status_bg = '#d1fae5';
        $status_color = '#059669';
        break;
    case 'ditolak':
        $status_icon = 'bi bi-x-circle-fill';
        $status_bg = '#fee2e2';
        $status_color = '#dc2626';
        break;
    default:
        $status_icon = 'bi bi-question-circle';
        $status_bg = '#f1f5f9';
        $status_color = '#64748b';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Calon Mahasiswa</title>
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
            background: transparent;
            padding: 24px;
        }

        /* MAIN CONTAINER */
        .modal-container {
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        /* HEADER WITH GRADIENT */
        .modal-header {
            background: linear-gradient(135deg, #0c4a6e, #1e6f9f);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-title i {
            font-size: 2rem;
            color: white;
        }

        .header-title h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .status-header {
            background: <?= $status_bg ?>;
            padding: 8px 20px;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .status-header i {
            font-size: 1rem;
            color: <?= $status_color ?>;
        }

        .status-header span {
            color: <?= $status_color ?>;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        /* CONTENT */
        .modal-content {
            padding: 32px;
        }

        /* PROFILE SECTION */
        .profile-section {
            display: flex;
            gap: 32px;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #eef2f6;
            flex-wrap: wrap;
        }

        .profile-photo {
            text-align: center;
            flex-shrink: 0;
        }

        .profile-photo img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #3b82f6;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .profile-photo .no-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #e2e8f0;
        }

        .profile-photo .no-photo i {
            font-size: 4rem;
            color: #94a3b8;
        }

        .profile-photo p {
            margin-top: 12px;
            font-size: 0.8rem;
            color: #64748b;
        }

        .profile-info {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 32px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        /* DETAIL SECTION */
        .detail-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
            margin-bottom: 32px;
        }

        .detail-box {
            background: #f8fafc;
            border-radius: 24px;
            padding: 24px;
        }

        .detail-box-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .detail-box-title i {
            font-size: 1.3rem;
            color: #3b82f6;
        }

        .detail-box-title h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0c4a6e;
            margin: 0;
        }

        .detail-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .detail-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .detail-list-item i {
            font-size: 1rem;
            color: #3b82f6;
            width: 24px;
            margin-top: 2px;
        }

        .detail-list-item .item-label {
            width: 110px;
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
            flex-shrink: 0;
        }

        .detail-list-item .item-value {
            flex: 1;
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 500;
            word-break: break-word;
        }

        /* DOCUMENTS SECTION */
        .documents-section {
            background: #f8fafc;
            border-radius: 24px;
            padding: 24px;
            margin-top: 0;
        }

        .documents-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .documents-title i {
            font-size: 1.3rem;
            color: #3b82f6;
        }

        .documents-title h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0c4a6e;
            margin: 0;
        }

        .docs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .doc-card {
            background: white;
            border-radius: 20px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }

        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .doc-icon {
            width: 48px;
            height: 48px;
            background: #f1f5f9;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .doc-icon.ijazah { color: #3b82f6; }
        .doc-icon.kk { color: #10b981; }
        .doc-icon.akte { color: #f59e0b; }
        .doc-icon.ktp { color: #ef4444; }

        .doc-info {
            flex: 1;
        }

        .doc-name {
            font-weight: 700;
            font-size: 0.85rem;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .doc-status {
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }

        .doc-status.available {
            color: #10b981;
        }

        .doc-link {
            text-decoration: none;
            color: #3b82f6;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
        }

        .doc-link:hover {
            text-decoration: underline;
        }

        /* FOOTER - TOMBOL DI TENGAH DENGAN WARNA MERAH SOLID */
        .modal-footer {
            padding: 24px 32px 32px 32px;
            text-align: center;
            border-top: 1px solid #eef2f6;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-close {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 40px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(220,38,38,0.3);
            min-width: 140px;
        }

        .btn-close:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220,38,38,0.4);
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            body {
                padding: 16px;
            }
            
            .profile-info {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .detail-section {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .docs-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 24px;
            }
            
            .modal-header {
                padding: 20px 24px;
            }
            
            .header-title h2 {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 550px) {
            .profile-section {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .profile-info {
                width: 100%;
            }
            
            .detail-list-item {
                flex-direction: column;
                gap: 4px;
            }
            
            .detail-list-item .item-label {
                width: 100%;
            }
            
            .btn-close {
                padding: 10px 30px;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>

<div class="modal-container">
    <!-- HEADER -->
    <div class="modal-header">
        <div class="header-title">
            <i class="bi bi-person-badge-fill"></i>
            <h2>Detail Calon Mahasiswa</h2>
        </div>
        <div class="status-header">
            <i class="<?= $status_icon ?>"></i>
            <span><?= strtoupper($maba['status']) ?></span>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="modal-content">
        <!-- PROFILE SECTION -->
        <div class="profile-section">
            <div class="profile-photo">
                <?php if($maba['foto']): ?>
                    <img src="uploads/<?= $maba['foto'] ?>" alt="Foto Profil">
                    <p>Foto Profil</p>
                <?php else: ?>
                    <div class="no-photo">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <p>Foto belum diupload</p>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <div class="info-item">
                    <span class="info-label">Nama Lengkap</span>
                    <span class="info-value"><?= htmlspecialchars($maba['nama']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">NIK</span>
                    <span class="info-value"><?= htmlspecialchars($maba['nik']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tempat, Tanggal Lahir</span>
                    <span class="info-value"><?= htmlspecialchars($maba['tempat_lahir']) ?>, <?= htmlspecialchars($maba['tanggal_lahir']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jenis Kelamin</span>
                    <span class="info-value"><?= $maba['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Agama</span>
                    <span class="info-value"><?= $maba['agama'] ?: '-' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Kewarganegaraan</span>
                    <span class="info-value"><?= $maba['kewarganegaraan'] ?: '-' ?></span>
                </div>
            </div>
        </div>

        <!-- DETAIL SECTION (2 KOLOM) -->
        <div class="detail-section">
            <!-- ALAMAT & SEKOLAH -->
            <div class="detail-box">
                <div class="detail-box-title">
                    <i class="bi bi-geo-alt-fill"></i>
                    <h3>Alamat & Asal Sekolah</h3>
                </div>
                <div class="detail-list">
                    <div class="detail-list-item">
                        <i class="bi bi-house-door-fill"></i>
                        <span class="item-label">Alamat</span>
                        <span class="item-value"><?= nl2br(htmlspecialchars($maba['alamat'])) ?></span>
                    </div>
                    <div class="detail-list-item">
                        <i class="bi bi-backpack"></i>
                        <span class="item-label">Asal Sekolah</span>
                        <span class="item-value"><?= htmlspecialchars($maba['asal_sekolah']) ?></span>
                    </div>
                </div>
            </div>

            <!-- JURUSAN & NILAI -->
            <div class="detail-box">
                <div class="detail-box-title">
                    <i class="bi bi-mortarboard-fill"></i>
                    <h3>Akademik</h3>
                </div>
                <div class="detail-list">
                    <div class="detail-list-item">
                        <i class="bi bi-book-fill"></i>
                        <span class="item-label">Jurusan</span>
                        <span class="item-value"><?= htmlspecialchars($maba['jurusan']) ?></span>
                    </div>
                    <div class="detail-list-item">
                        <i class="bi bi-star-fill"></i>
                        <span class="item-label">Nilai Rata-rata</span>
                        <span class="item-value"><?= $maba['nilai'] ?: '-' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KONTAK & AKUN -->
        <div class="detail-section">
            <div class="detail-box">
                <div class="detail-box-title">
                    <i class="bi bi-envelope-fill"></i>
                    <h3>Kontak & Akun</h3>
                </div>
                <div class="detail-list">
                    <div class="detail-list-item">
                        <i class="bi bi-envelope"></i>
                        <span class="item-label">Email</span>
                        <span class="item-value"><?= htmlspecialchars($maba['email']) ?></span>
                    </div>
                    <div class="detail-list-item">
                        <i class="bi bi-person-badge"></i>
                        <span class="item-label">Username</span>
                        <span class="item-value"><?= htmlspecialchars($maba['username']) ?></span>
                    </div>
                    <div class="detail-list-item">
                        <i class="bi bi-calendar-plus"></i>
                        <span class="item-label">Tanggal Daftar</span>
                        <span class="item-value"><?= $maba['tanggal_daftar'] ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-box">
                <div class="detail-box-title">
                    <i class="bi bi-info-circle-fill"></i>
                    <h3>Informasi Lainnya</h3>
                </div>
                <div class="detail-list">
                    <div class="detail-list-item">
                        <i class="bi bi-check2-circle"></i>
                        <span class="item-label">Status</span>
                        <span class="item-value">
                            <span style="background: <?= $status_bg ?>; color: <?= $status_color ?>; padding: 4px 14px; border-radius: 30px; font-size: 0.75rem; font-weight: 600;">
                                <?= ucfirst($maba['status']) ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOKUMEN SECTION -->
        <div class="documents-section">
            <div class="documents-title">
                <i class="bi bi-folder2-open"></i>
                <h3>Dokumen Pendukung</h3>
            </div>
            <div class="docs-grid">
                <!-- Ijazah -->
                <div class="doc-card">
                    <div class="doc-icon ijazah">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">Ijazah / Surat Keterangan Lulus</div>
                        <?php if($maba['ijazah_pdf']): ?>
                            <div class="doc-status available">✓ Tersedia</div>
                            <a href="uploads/<?= $maba['ijazah_pdf'] ?>" target="_blank" class="doc-link">
                                <i class="bi bi-eye"></i> Lihat Dokumen
                            </a>
                        <?php else: ?>
                            <div class="doc-status">Ijazah belum diupload</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kartu Keluarga -->
                <div class="doc-card">
                    <div class="doc-icon kk">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">Kartu Keluarga</div>
                        <?php if($maba['kartu_keluarga']): ?>
                            <div class="doc-status available">✓ Tersedia</div>
                            <a href="uploads/<?= $maba['kartu_keluarga'] ?>" target="_blank" class="doc-link">
                                <i class="bi bi-eye"></i> Lihat Dokumen
                            </a>
                        <?php else: ?>
                            <div class="doc-status">Kartu Keluarga belum diupload</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Akte Kelahiran -->
                <div class="doc-card">
                    <div class="doc-icon akte">
                        <i class="bi bi-file-text-fill"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">Akte Kelahiran</div>
                        <?php if($maba['akte_kelahiran']): ?>
                            <div class="doc-status available">✓ Tersedia</div>
                            <a href="uploads/<?= $maba['akte_kelahiran'] ?>" target="_blank" class="doc-link">
                                <i class="bi bi-eye"></i> Lihat Dokumen
                            </a>
                        <?php else: ?>
                            <div class="doc-status">Akte Kelahiran belum diupload</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KTP / Kartu Pelajar -->
                <div class="doc-card">
                    <div class="doc-icon ktp">
                        <i class="bi bi-card-image"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">KTP / Kartu Pelajar</div>
                        <?php if($maba['kartu_pelajar_ktp']): ?>
                            <div class="doc-status available">✓ Tersedia</div>
                            <a href="uploads/<?= $maba['kartu_pelajar_ktp'] ?>" target="_blank" class="doc-link">
                                <i class="bi bi-eye"></i> Lihat Dokumen
                            </a>
                        <?php else: ?>
                            <div class="doc-status">KTP / Kartu Pelajar belum diupload</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER - TOMBOL DI TENGAH DENGAN WARNA MERAH SOLID -->
    <div class="modal-footer">
        <button class="btn-close" onclick="window.parent.closeModal()">
            <i class="bi bi-x-lg"></i> Tutup
        </button>
    </div>
</div>

</body>
</html>