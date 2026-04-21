<?php
include "auth_admin.php";
$jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : 'umum';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Soal Tes - Umum</title>
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
    background: linear-gradient(180deg, #020617 0%, #020617 45%, #020617 100%);
    color: #e5e7eb;
}

.content {
    margin-left: 250px;
    padding: 28px 30px 40px;
}

.box {
    background: #ffffff;
    border-radius: 24px;
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

.badge-umum {
    background: linear-gradient(135deg, #0d6efd, #0d3b66);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 10px;
}

.info-box {
    background: #f8fafc;
    border-radius: 16px;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-left: 4px solid #0d6efd;
}

body.dark .info-box {
    background: #0f172a;
    border-left-color: #0d6efd;
}

.info-box i {
    color: #0d6efd;
    font-size: 20px;
    margin-right: 10px;
}

.info-box small {
    color: #64748b;
}

body.dark .info-box small {
    color: #94a3b8;
}

.csv-section {
    background: linear-gradient(135deg, #f8fafc, #ffffff);
    border-radius: 20px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #e2e8f0;
}

body.dark .csv-section {
    background: #0f172a;
    border-color: #334155;
}

.csv-section .page-title {
    font-size: 18px;
    margin-bottom: 5px;
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
                <i class="bi bi-people-fill"></i> Tambah Soal Tes Umum
                <span class="badge-umum">Jurusan Umum</span>
            </div>
            <div class="page-sub">Masukkan soal untuk jurusan umum secara manual atau upload via CSV</div>
        </div>

        <div class="info-box">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Petunjuk:</strong> Isi form di bawah untuk menambahkan soal satu per satu ATAU upload file CSV untuk menambah banyak soal sekaligus.
            <small class="d-block mt-1">Pastikan jawaban benar sesuai dengan opsi yang tersedia (A/B/C/D/E). Gambar akan disimpan di folder <strong>uploads/</strong></small>
        </div>

        <form action="proses_soal.php" method="POST" enctype="multipart/form-data" id="soalForm">
            <input type="hidden" name="jurusan" value="umum">
            <input type="hidden" name="submission_type" id="submissionType" value="manual">
            
            <div id="manualSection">
                <div class="mb-3">
                    <label class="fw-semibold mb-1">Pertanyaan <span class="text-danger">*</span></label>
                    <textarea name="pertanyaan" class="form-control" 
                        placeholder="Masukkan pertanyaan soal umum..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="fw-semibold mb-1">Gambar Soal (Opsional)</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB. Disimpan di folder uploads/</small>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-semibold mb-1">Opsi A <span class="text-danger">*</span></label>
                        <input type="text" name="a" class="form-control" placeholder="Pilihan jawaban A">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-1">Opsi B <span class="text-danger">*</span></label>
                        <input type="text" name="b" class="form-control" placeholder="Pilihan jawaban B">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-1">Opsi C <span class="text-danger">*</span></label>
                        <input type="text" name="c" class="form-control" placeholder="Pilihan jawaban C">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-1">Opsi D <span class="text-danger">*</span></label>
                        <input type="text" name="d" class="form-control" placeholder="Pilihan jawaban D">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-1">Opsi E (Opsional)</label>
                        <input type="text" name="e" class="form-control" placeholder="Pilihan jawaban E (kosongkan jika tidak ada)">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-1">Jawaban Benar <span class="text-danger">*</span></label>
                        <select name="jawaban" class="form-select">
                            <option value="">-- Pilih Jawaban --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="csv-section">
                <div class="page-title" style="font-size: 18px;">
                    <i class="bi bi-filetype-csv"></i> Upload Soal CSV
                </div>
                <div class="page-sub" style="margin-bottom: 15px;">
                    Upload file CSV untuk menambahkan banyak soal sekaligus
                </div>
                
                <div class="mb-3">
                    <input type="file" name="csv_file" id="csvFile" class="form-control" accept=".csv">
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> Format CSV: pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,opsi_e,jawaban_benar<br>
                        <strong>Contoh:</strong> "Apa ibu kota Indonesia?",Jakarta,Surabaya,Bandung,Medan,Semarang,A
                    </small>
                </div>
                
                <div class="mt-2">
                    <a href="template_soal_umum.csv" class="btn-back" style="padding: 6px 15px; font-size: 12px;">
                        <i class="bi bi-download"></i> Download Template CSV
                    </a>
                </div>
            </div>

            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn-main" id="submitBtn">
                    <i class="bi bi-check-circle-fill"></i> Simpan Soal Umum
                </button>
                <a href="soal_tes.php?jurusan=umum&show_soal=show" class="btn-back">
                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

document.getElementById('soalForm').addEventListener('submit', function(e) {
    const csvFile = document.getElementById('csvFile').files[0];
    const manualFields = {
        pertanyaan: document.querySelector('textarea[name="pertanyaan"]').value.trim(),
        a: document.querySelector('input[name="a"]').value.trim(),
        b: document.querySelector('input[name="b"]').value.trim(),
        c: document.querySelector('input[name="c"]').value.trim(),
        d: document.querySelector('input[name="d"]').value.trim(),
        jawaban: document.querySelector('select[name="jawaban"]').value
    };
    
    if(csvFile) {
        if(csvFile.size > 5 * 1024 * 1024) {
            e.preventDefault();
            Swal.fire('Error!', 'Ukuran file CSV terlalu besar! Maksimal 5MB.', 'error');
            return false;
        }
        
        const extension = csvFile.name.split('.').pop().toLowerCase();
        if(extension !== 'csv') {
            e.preventDefault();
            Swal.fire('Error!', 'File harus berformat .csv!', 'error');
            return false;
        }
        
        document.getElementById('submissionType').value = 'csv';
        return true;
    }
    
    if(!manualFields.pertanyaan) {
        e.preventDefault();
        Swal.fire('Oops!', 'Pertanyaan harus diisi!', 'warning');
        document.querySelector('textarea[name="pertanyaan"]').focus();
        return false;
    }
    
    if(!manualFields.a || !manualFields.b || !manualFields.c || !manualFields.d) {
        e.preventDefault();
        Swal.fire('Oops!', 'Opsi A, B, C, dan D harus diisi semua!', 'warning');
        return false;
    }
    
    if(!manualFields.jawaban) {
        e.preventDefault();
        Swal.fire('Oops!', 'Jawaban benar harus dipilih!', 'warning');
        return false;
    }
    
    document.getElementById('submissionType').value = 'manual';
    return true;
});

document.getElementById('csvFile').addEventListener('change', function() {
    const submitBtn = document.getElementById('submitBtn');
    const manualSection = document.getElementById('manualSection');
    
    if(this.files.length > 0) {
        manualSection.style.opacity = '0.5';
        manualSection.style.pointerEvents = 'none';
        submitBtn.innerHTML = '<i class="bi bi-filetype-csv"></i> Upload CSV Saja';
        submitBtn.style.background = "linear-gradient(135deg, #0d6efd, #0d3b66)";
    } else {
        manualSection.style.opacity = '1';
        manualSection.style.pointerEvents = 'auto';
        submitBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Simpan Soal Umum';
        submitBtn.style.background = "linear-gradient(135deg, #0d3b66, #0d6efd)";
    }
});
</script>

</body>
</html>