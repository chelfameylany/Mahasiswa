<?php
session_start();
include "../koneksi.php";

// CEK LOGIN
if (!isset($_SESSION['maba'])) {
    header("Location: login_maba.php");
    exit();
}

// Ambil data user
$username = $_SESSION['maba'];
$stmt = mysqli_prepare($koneksi, "SELECT * FROM calon_maba WHERE username=?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$query_user = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    header("Location: logout_maba.php");
    exit();
}

$id_maba = $user['id_maba'];
$jurusan_asli = $user['jurusan'] ?? 'umum';
$id_maba_formatted = "PMB" . str_pad($id_maba, 5, '0', STR_PAD_LEFT);

// Ambil data pembayaran
$stmt = mysqli_prepare($koneksi, "SELECT * FROM pembayaran_gedung WHERE id_maba=? ORDER BY id_pembayaran DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_maba);
mysqli_stmt_execute($stmt);
$query_cek = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($query_cek);

$kode_unik = $data['kode_unik'] ?? rand(100, 999);
$total_bayar = $data['total_bayar'] ?? (10000000 + $kode_unik);
$batas_waktu = $data['batas_waktu'] ?? date('Y-m-d H:i:s', strtotime('+5 days'));

// ===== DETEKSI WARNA BERDASARKAN JURUSAN =====
$jurusan_lower = strtolower(trim($jurusan_asli));

if(strpos($jurusan_lower, 'teknik') !== false) {
    $warna_primary = "#2E7D32";
    $warna_primary_dark = "#1B5E20";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #E8F5E9 0%, #FFFFFF 100%)";
    $warna_soft = "#E8F5E9";
    $teks_jurusan = "TEKNIK";
    $icon_jurusan = "fa-gear";
} else {
    $warna_primary = "#3B82F6";
    $warna_primary_dark = "#2563EB";
    $warna_accent = "#FFB300";
    $warna_gradient_bg = "linear-gradient(180deg, #DBEAFE 0%, #FFFFFF 100%)";
    $warna_soft = "#DBEAFE";
    $teks_jurusan = "UMUM";
    $icon_jurusan = "fa-users";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pembayaran - Universitas Cendekia Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: <?= $warna_gradient_bg ?>;
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container-guide {
            max-width: 800px;
            margin: 0 auto;
        }

        .header-guide {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .logo-guide {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: <?= $warna_primary ?>;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .logo-text h2 {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
            margin: 0;
        }

        .logo-text p {
            font-size: 10px;
            color: #6B7280;
            margin: 0;
        }

        .user-guide {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 6px 14px 6px 8px;
            border-radius: 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: <?= $warna_primary ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
        }

        .user-id {
            font-size: 10px;
            color: #9CA3AF;
        }

        .jurusan-badge {
            background: <?= $warna_soft ?>;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 600;
            color: <?= $warna_primary ?>;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 8px;
        }

        .guide-card {
            background: white;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .guide-title {
            text-align: center;
            margin-bottom: 28px;
        }

        .guide-icon {
            width: 56px;
            height: 56px;
            background: <?= $warna_primary ?>;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 26px;
            color: white;
        }

        .guide-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .guide-title p {
            font-size: 12px;
            color: #6B7280;
        }

        .info-important {
            background: #FFF9E6;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #FEF3C7;
        }

        .info-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .info-header i {
            font-size: 22px;
            color: <?= $warna_accent ?>;
        }

        .info-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #92400E;
            margin: 0;
        }

        .detail-transfer {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #F0F0F0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 12px;
            color: #6B7280;
        }

        .detail-value {
            font-weight: 700;
            color: #1F2937;
            font-size: 13px;
        }

        .unique-code {
            background: #FEF3C7;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            margin-top: 12px;
        }

        .unique-code strong {
            font-size: 32px;
            font-weight: 800;
            color: <?= $warna_accent ?>;
            font-family: monospace;
            letter-spacing: 3px;
            display: block;
            margin-bottom: 6px;
        }

        .unique-code span {
            font-size: 11px;
            color: #92400E;
            display: block;
        }

        .countdown-premium {
            background: linear-gradient(135deg, <?= $warna_accent ?>, #FF8C00);
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
        }

        .countdown-premium i {
            color: white;
            font-size: 16px;
        }

        .countdown-premium span {
            font-size: 11px;
            color: white;
            font-weight: 500;
        }

        .countdown-premium strong {
            font-size: 16px;
            font-family: monospace;
            color: white;
            font-weight: 700;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .section-title i {
            color: <?= $warna_primary ?>;
            font-size: 18px;
        }

        /* Bank Cards */
        .bank-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .bank-card {
            background: #FFFFFF;
            border-radius: 18px;
            padding: 16px;
            transition: all 0.3s ease;
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .bank-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: <?= $warna_primary ?>;
        }

        .bank-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .bank-logo {
            width: 36px;
            height: 36px;
            background: #F3F4F6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .bank-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .bank-title {
            flex: 1;
        }

        .bank-name {
            font-size: 15px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 2px;
        }

        .bank-badge {
            font-size: 9px;
            background: <?= $warna_soft ?>;
            color: <?= $warna_primary ?>;
            padding: 2px 6px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }

        .bank-details {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .bank-details .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }

        .bank-details .detail-row:first-child {
            border-bottom: 1px solid #E5E7EB;
            margin-bottom: 5px;
            padding-bottom: 5px;
        }

        .bank-details .detail-label {
            font-size: 10px;
            color: #6B7280;
        }

        .bank-account-number {
            font-size: 12px;
            font-weight: 700;
            font-family: monospace;
            color: <?= $warna_primary ?>;
        }

        .swift {
            font-size: 11px;
            font-weight: 600;
            font-family: monospace;
            color: #1F2937;
        }

        .for-info {
            background: #EFF6FF;
            border-radius: 8px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 9px;
            color: <?= $warna_primary ?>;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .for-info i {
            font-size: 10px;
        }

        .copy-btn {
            width: 100%;
            background: <?= $warna_primary ?>;
            border: none;
            padding: 8px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .copy-btn:hover {
            background: <?= $warna_primary_dark ?>;
            transform: scale(1.01);
        }

        .steps-section {
            margin-bottom: 24px;
        }

        .step-item {
            display: flex;
            gap: 12px;
            padding: 10px 12px;
            background: #F9FAFB;
            border-radius: 14px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .step-item:hover {
            background: white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }

        .step-number {
            width: 28px;
            height: 28px;
            background: <?= $warna_primary ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: white;
            flex-shrink: 0;
        }

        .step-content h4 {
            font-size: 13px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 2px;
        }

        .step-content p {
            font-size: 10px;
            color: #6B7280;
            margin: 0;
        }

        .warning-box {
            background: #FEF2F2;
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-left: 3px solid #EF4444;
        }

        .warning-box i {
            font-size: 16px;
            color: #EF4444;
        }

        .warning-box p {
            font-size: 10px;
            color: #991B1B;
            margin: 0;
            line-height: 1.4;
        }

        .btn-action {
            width: 100%;
            background: <?= $warna_primary ?>;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: <?= $warna_primary_dark ?>;
            transform: translateY(-1px);
            color: white;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #9CA3AF;
            text-decoration: none;
            font-size: 12px;
        }

        .back-link:hover {
            color: <?= $warna_primary ?>;
        }

        @media (max-width: 650px) {
            .bank-grid {
                grid-template-columns: 1fr;
            }
            .guide-card {
                padding: 20px;
            }
            .unique-code strong {
                font-size: 28px;
            }
            .guide-title h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container-guide">
    <!-- Header -->
    <div class="header-guide">
        <div class="logo-guide">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="logo-text">
                <h2>Universitas Cendekia Nusantara</h2>
                <p>Panduan Pembayaran</p>
            </div>
        </div>
        <div class="user-guide">
            <div class="user-avatar">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
                <div class="user-id"><?= $id_maba_formatted ?></div>
            </div>
            <div class="jurusan-badge">
                <i class="fas <?= $icon_jurusan ?>"></i>
                <span><?= $teks_jurusan ?></span>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="guide-card">
        <div class="guide-title">
            <div class="guide-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h1>Panduan Pembayaran</h1>
            <p>Ikuti langkah-langkah berikut</p>
        </div>

        <!-- Info Penting -->
        <div class="info-important">
            <div class="info-header">
                <i class="fas fa-info-circle"></i>
                <h3>Informasi Penting</h3>
            </div>
            <div class="detail-transfer">
                <div class="detail-row">
                    <span class="detail-label">Kode Pembayaran</span>
                    <span class="detail-value">INV/<?= date('Ymd') ?>/<?= str_pad($id_maba, 4, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Uang Gedung</span>
                    <span class="detail-value">Rp 10.000.000</span>
                </div>
                <div class="unique-code">
                    <strong><?= $kode_unik ?></strong>
                    <span>✦ KODE UNIK (wajib ditambahkan) ✦</span>
                </div>
                <div class="detail-row" style="margin-top: 10px;">
                    <span class="detail-label">Total Dibayar</span>
                    <span class="detail-value" style="color: <?= $warna_primary ?>; font-size: 18px; font-weight: 800;">Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="countdown-premium">
                <div>
                    <i class="fas fa-hourglass-half"></i>
                    <span> Batas waktu</span>
                </div>
                <strong id="countdown">--:--:--</strong>
            </div>
        </div>

        <!-- Bank Section -->
        <div class="section-title">
            <i class="fas fa-university"></i>
            <span>Rekening Tujuan</span>
        </div>
        <div class="bank-grid">
            <!-- Bank BCA -->
            <div class="bank-card">
                <div class="bank-header">
                    <div class="bank-logo">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/1200px-Bank_Central_Asia.svg.png" alt="BCA">
                    </div>
                    <div class="bank-title">
                        <div class="bank-name">Bank BCA</div>
                        <span class="bank-badge">Lokal & Internasional</span>
                    </div>
                </div>
                <div class="bank-details">
                    <div class="detail-row">
                        <span class="detail-label">Account Number</span>
                        <span class="detail-value bank-account-number">0810 1305 1607 26</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SWIFT Code</span>
                        <span class="detail-value swift">CENAIDJA</span>
                    </div>
                </div>
                <div class="for-info">
                    <i class="fas fa-globe"></i>
                    <span>Transfer lokal & internasional (WNA)</span>
                </div>
                <button class="copy-btn" onclick="copyToClipboard('08101305160726', 'BCA')">
                    <i class="fas fa-copy"></i> Salin Rekening
                </button>
            </div>
            
            <!-- Bank Mandiri - PASTIKAN FILE livin mandiri.jpg ADA DI FOLDER YANG SAMA -->
            <div class="bank-card">
                <div class="bank-header">
                    <div class="bank-logo">
                        <img src="123 living.png" alt="Bank Mandiri" style="width: 100px; height: 100px; object-fit: contain;">
                    </div>
                    <div class="bank-title">
                        <div class="bank-name">Bank Mandiri</div>
                        <span class="bank-badge">Lokal & Internasional</span>
                    </div>
                </div>
                <div class="bank-details">
                    <div class="detail-row">
                        <span class="detail-label">Account Number</span>
                        <span class="detail-value bank-account-number">1412 2405 1978 11</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SWIFT Code</span>
                        <span class="detail-value swift">BMRIIDJA</span>
                    </div>
                </div>
                <div class="for-info">
                    <i class="fas fa-globe"></i>
                    <span>Transfer lokal & internasional (WNA)</span>
                </div>
                <button class="copy-btn" onclick="copyToClipboard('14122405197811', 'Mandiri')">
                    <i class="fas fa-copy"></i> Salin Rekening
                </button>
            </div>
        </div>

        <!-- Langkah Pembayaran -->
        <div class="section-title">
            <i class="fas fa-list-ol"></i>
            <span>Langkah Pembayaran</span>
        </div>
        <div class="steps-section">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Catat Kode Unik</h4>
                    <p>Kode unik <strong style="color: <?= $warna_primary ?>;"><?= $kode_unik ?></strong> wajib ditambahkan ke Rp 10.000.000</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Transfer ke Rekening Tujuan</h4>
                    <p>Lakukan transfer ke BCA atau Mandiri</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Transfer dengan Nominal TEPAT</h4>
                    <p>Nominal transfer: <strong>Rp <?= number_format($total_bayar, 0, ',', '.') ?></strong></p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Simpan Bukti Transfer</h4>
                    <p>Screenshot atau foto bukti transfer Anda</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h4>Upload Bukti Pembayaran</h4>
                    <p>Klik tombol di bawah untuk upload bukti</p>
                </div>
            </div>
        </div>

        <!-- Peringatan -->
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <p><strong>Perhatian!</strong> Transfer maksimal 5 hari. Nominal harus SESUAI (termasuk kode unik). Transfer tidak sesuai akan ditolak.</p>
        </div>

        <!-- Tombol Upload -->
        <a href="pembayaran.php" class="btn-action">
            <i class="fas fa-upload"></i>
            Upload Bukti Pembayaran
            <i class="fas fa-arrow-right"></i>
        </a>

        <a href="dashboard_maba.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: #1F2937; color: white; padding: 10px 20px; border-radius: 40px; font-size: 12px; z-index: 1000; opacity: 0; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
    <i class="fas fa-check-circle" style="color: <?= $warna_accent ?>;"></i>
    <span id="toastMsg"></span>
</div>

<script>
    const batasWaktu = "<?= $batas_waktu ?>";
    
    function updateCountdown() {
        const now = new Date().getTime();
        const deadline = new Date(batasWaktu).getTime();
        const distance = deadline - now;
        
        if (distance < 0) {
            document.getElementById('countdown').innerHTML = "Waktu Habis";
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        let text = "";
        if (days > 0) text += days + "d ";
        text += String(hours).padStart(2, '0') + ":" + 
                String(minutes).padStart(2, '0') + ":" + 
                String(seconds).padStart(2, '0');
        
        document.getElementById('countdown').innerHTML = text;
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
    
    function copyToClipboard(text, bankName) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Nomor rekening ' + bankName + ' disalin!');
        }).catch(function() {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast('Nomor rekening ' + bankName + ' disalin!');
        });
    }
    
    function showToast(msg) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        toastMsg.textContent = msg;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(100px)';
        }, 2000);
    }
</script>

</body>
</html>