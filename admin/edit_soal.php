<?php
include "auth_admin.php";
include '../koneksi.php';

// Ambil parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : 'umum';

// Tentukan tabel berdasarkan jurusan
$table = ($jurusan == 'teknik') ? 'soal_tes_teknik' : 'soal_tes';

// Ambil data soal
$query = mysqli_query($koneksi, "SELECT * FROM $table WHERE id_soal = '$id'");
$row = mysqli_fetch_assoc($query);

if(!$row) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: "Soal Tidak Ditemukan! 😔",
                text: "Soal yang ingin Anda edit tidak ada dalam database.",
                icon: "error",
                confirmButtonText: "Kembali",
                background: "linear-gradient(135deg, #0d3b66, #0d6efd)",
                color: "white",
                confirmButtonColor: "#dc3545"
            }).then(() => {
                window.location.href = "soal_tes.php?jurusan=' . $jurusan . '&show_soal=show";
            });
        </script>
    </body>
    </html>';
    exit;
}

// Proses update soal
if(isset($_POST['edit'])) {
    $pertanyaan = mysqli_real_escape_string($koneksi, $_POST['pertanyaan']);
    $a = mysqli_real_escape_string($koneksi, $_POST['a']);
    $b = mysqli_real_escape_string($koneksi, $_POST['b']);
    $c = mysqli_real_escape_string($koneksi, $_POST['c']);
    $d = mysqli_real_escape_string($koneksi, $_POST['d']);
    $e = isset($_POST['e']) ? mysqli_real_escape_string($koneksi, $_POST['e']) : '';
    $jawaban = mysqli_real_escape_string($koneksi, $_POST['jawaban']);
    
    // Proses upload gambar baru
    $gambar = $row['gambar'];
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($ekstensi, $allowed)) {
            if(!empty($row['gambar']) && file_exists($target_dir . $row['gambar'])) {
                unlink($target_dir . $row['gambar']);
            }
            
            $nama_file = time() . "_" . uniqid() . "." . $ekstensi;
            $target_file = $target_dir . $nama_file;
            
            if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $nama_file;
            }
        }
    }
    
    $update = "UPDATE $table SET 
                pertanyaan = '$pertanyaan',
                opsi_a = '$a',
                opsi_b = '$b',
                opsi_c = '$c',
                opsi_d = '$d',
                opsi_e = '$e',
                jawaban_benar = '$jawaban',
                gambar = '$gambar'
               WHERE id_soal = '$id'";
    
    if(mysqli_query($koneksi, $update)) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: "Berhasil Diupdate! ✨",
                    text: "Soal berhasil diperbarui dengan baik.",
                    icon: "success",
                    confirmButtonText: "Lihat Soal",
                    background: "linear-gradient(135deg, #0d3b66, #0d6efd)",
                    color: "white",
                    confirmButtonColor: "#198754"
                }).then(() => {
                    window.location.href = "soal_tes.php?jurusan=' . $jurusan . '&show_soal=show";
                });
            </script>
        </body>
        </html>';
        exit;
    } else {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: "Gagal Diupdate! 😔",
                    html: "' . mysqli_error($koneksi) . '",
                    icon: "error",
                    confirmButtonText: "Coba Lagi",
                    background: "linear-gradient(135deg, #0d3b66, #0d6efd)",
                    color: "white",
                    confirmButtonColor: "#dc3545"
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Soal Tes - <?= ($jurusan == 'teknik') ? 'Teknik' : 'Umum' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(180deg, #0d3b66 0%, #0d6efd 45%, #ffffff 100%);
    color: #0f172a;
}

body.dark {
    background: #020617;
    color: #e5e7eb;
}

.content {
    margin-left: 250px;
    padding: 28px 30px 40px;
}

.box {
    background: #ffffff;
    border-radius: 32px;
    padding: 34px 36px;
    box-shadow: 0 18px 46px rgba(13, 59, 102, .25);
    max-width: 1050px;
}

body.dark .box {
    background: #020617;
    box-shadow: 0 18px 46px rgba(0, 0, 0, .6);
}

.page-title {
    font-weight: 900;
    font-size: 23px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-title i {
    font-size: 28px;
    color: #0d6efd;
}

.page-sub {
    font-size: 13px;
    opacity: .65;
    margin-bottom: 20px;
}

.form-control, .form-select, textarea {
    border-radius: 14px;
    padding: 12px 14px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    transition: 0.2s;
}

.form-control:focus, .form-select:focus, textarea:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    outline: none;
}

body.dark .form-control,
body.dark .form-select,
body.dark textarea {
    background: #1e293b;
    border-color: #334155;
    color: white;
}

textarea {
    min-height: 120px;
    resize: vertical;
}

.btn-main {
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    color: white;
    font-weight: 800;
    border-radius: 14px;
    padding: 11px 20px;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.2s;
}

.btn-main:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.3);
}

.btn-back {
    background: #e5e7eb;
    color: #0f172a;
    font-weight: 800;
    border-radius: 14px;
    padding: 11px 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: 0.2s;
}

.btn-back:hover {
    background: #d1d5db;
    transform: translateY(-2px);
}

body.dark .btn-back {
    background: #334155;
    color: #e5e7eb;
}

body.dark .btn-back:hover {
    background: #475569;
}

.mode-toggle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: fixed;
    top: 28px;
    right: 40px;
    z-index: 100;
}

.mode-toggle:hover {
    transform: scale(1.05);
}

body.dark .mode-toggle {
    background: #1e293b;
}

.badge-jurusan {
    background: linear-gradient(135deg, #0d3b66, #0d6efd);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 10px;
}

.badge-teknik {
    background: linear-gradient(135deg, #198754, #0d3b66);
}

.preview-img {
    max-width: 200px;
    border-radius: 16px;
    margin-top: 10px;
    box-shadow: 0 6px 16px rgba(0,0,0,.15);
    border: 2px solid #e2e8f0;
}

body.dark .preview-img {
    border-color: #334155;
}

hr {
    margin: 20px 0;
    border-color: #e2e8f0;
}

body.dark hr {
    border-color: #334155;
}

@media (max-width: 1000px) {
    .content {
        margin-left: 0;
        padding: 20px;
    }
    .box {
        padding: 20px;
    }
    .mode-toggle {
        top: 20px;
        right: 20px;
    }
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="mode-toggle" onclick="toggleMode()">
        <i class="bi bi-moon-stars-fill" id="modeIcon"></i>
    </div>
    
    <div class="box">
        <div class="mb-3">
            <div class="page-title">
                <i class="bi bi-pencil-square"></i> Edit Soal Tes
                <span class="badge-jurusan <?= ($jurusan == 'teknik') ? 'badge-teknik' : '' ?>">
                    <?= ($jurusan == 'teknik') ? '⚙️ Jurusan Teknik' : '👥 Jurusan Umum' ?>
                </span>
            </div>
            <div class="page-sub">Perbarui soal, gambar, dan jawaban dengan mudah</div>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="fw-semibold mb-1">Pertanyaan <span class="text-danger">*</span></label>
                <textarea name="pertanyaan" class="form-control" required><?= htmlspecialchars($row['pertanyaan']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="fw-semibold mb-1">Gambar Soal</label>
                <input type="file" name="gambar" class="form-control" accept="image/*" id="gambarInput">
                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengganti. Gambar akan disimpan di folder <strong>uploads/</strong></small>

                <?php if(!empty($row['gambar']) && file_exists("uploads/" . $row['gambar'])): ?>
                    <div class="mt-2">
                        <small class="text-muted">Gambar saat ini:</small><br>
                        <img src="uploads/<?= $row['gambar'] ?>" class="preview-img">
                    </div>
                <?php elseif(!empty($row['gambar'])): ?>
                    <div class="mt-2">
                        <small class="text-danger">⚠️ Gambar tidak ditemukan di folder uploads/ (file: <?= $row['gambar'] ?>)</small>
                    </div>
                <?php endif; ?>
                
                <div id="previewNew" class="mt-2"></div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold mb-1">Opsi A <span class="text-danger">*</span></label>
                    <input type="text" name="a" class="form-control" value="<?= htmlspecialchars($row['opsi_a']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold mb-1">Opsi B <span class="text-danger">*</span></label>
                    <input type="text" name="b" class="form-control" value="<?= htmlspecialchars($row['opsi_b']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold mb-1">Opsi C <span class="text-danger">*</span></label>
                    <input type="text" name="c" class="form-control" value="<?= htmlspecialchars($row['opsi_c']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold mb-1">Opsi D <span class="text-danger">*</span></label>
                    <input type="text" name="d" class="form-control" value="<?= htmlspecialchars($row['opsi_d']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold mb-1">Opsi E (Opsional)</label>
                    <input type="text" name="e" class="form-control" value="<?= htmlspecialchars($row['opsi_e']) ?>" placeholder="Kosongkan jika tidak ada">
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold mb-1">Jawaban Benar <span class="text-danger">*</span></label>
                    <select name="jawaban" class="form-select" required>
                        <option value="">-- Pilih Jawaban --</option>
                        <?php foreach(['A','B','C','D','E'] as $j): ?>
                            <option value="<?= $j ?>" <?= ($row['jawaban_benar'] == $j) ? 'selected' : '' ?>>
                                Opsi <?= $j ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <hr>

            <div class="d-flex gap-3 mt-4">
                <button type="submit" name="edit" class="btn-main">
                    <i class="bi bi-check-circle-fill"></i> Update Soal
                </button>
                <a href="soal_tes.php?jurusan=<?= $jurusan ?>&show_soal=show" class="btn-back">
                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMode(){
    const body = document.body;
    const icon = document.getElementById('modeIcon');
    body.classList.toggle('dark');
    icon.className = body.classList.contains('dark')
        ? "bi bi-sun-fill"
        : "bi bi-moon-stars-fill";
    localStorage.setItem('darkMode', body.classList.contains('dark'));
}

if(localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark');
    document.getElementById('modeIcon').className = 'bi bi-sun-fill';
}

// Preview gambar baru
document.getElementById('gambarInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('previewNew');
    
    if(file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            previewDiv.innerHTML = `
                <small class="text-muted">Preview gambar baru:</small><br>
                <img src="${event.target.result}" class="preview-img" style="max-width:200px">
            `;
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.innerHTML = '';
    }
});
</script>

</body>
</html>