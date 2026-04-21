<?php
include "auth_admin.php";
include "../koneksi.php";

$jurusan = isset($_POST['jurusan']) ? $_POST['jurusan'] : 'teknik';
$submission_type = isset($_POST['submission_type']) ? $_POST['submission_type'] : 'manual';

$table = ($jurusan == 'teknik') ? 'soal_tes_teknik' : 'soal_tes';

// Folder upload gambar
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// ========== PROSES MANUAL ==========
if ($submission_type == 'manual') {
    $pertanyaan = mysqli_real_escape_string($koneksi, $_POST['pertanyaan']);
    $opsi_a = mysqli_real_escape_string($koneksi, $_POST['a']);
    $opsi_b = mysqli_real_escape_string($koneksi, $_POST['b']);
    $opsi_c = mysqli_real_escape_string($koneksi, $_POST['c']);
    $opsi_d = mysqli_real_escape_string($koneksi, $_POST['d']);
    $opsi_e = isset($_POST['e']) ? mysqli_real_escape_string($koneksi, $_POST['e']) : '';
    $jawaban_benar = mysqli_real_escape_string($koneksi, $_POST['jawaban']);
    
    // Upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $nama_baru = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $target_file = $target_dir . $nama_baru;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar = $nama_baru;
        }
    }
    
    $query = "INSERT INTO $table (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban_benar, gambar) 
              VALUES ('$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$opsi_e', '$jawaban_benar', '$gambar')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: tambah_soal_teknik.php?jurusan=$jurusan&msg=success");
    } else {
        header("Location: tambah_soal_teknik.php?jurusan=$jurusan&msg=error");
    }
    exit();
}

// ========== PROSES CSV ==========
if ($submission_type == 'csv') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
        header("Location: tambah_soal_teknik.php?jurusan=$jurusan&msg=csv_error");
        exit();
    }
    
    $csv_file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($csv_file, 'r');
    $success_count = 0;
    $error_count = 0;
    
    // Lewati header jika ada (baris pertama)
    $is_header = true;
    
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        // Lewati baris header
        if ($is_header) {
            $is_header = false;
            continue;
        }
        
        // Format: pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban_benar
        if (count($data) >= 7) {
            $pertanyaan = mysqli_real_escape_string($koneksi, trim($data[0]));
            $opsi_a = mysqli_real_escape_string($koneksi, trim($data[1]));
            $opsi_b = mysqli_real_escape_string($koneksi, trim($data[2]));
            $opsi_c = mysqli_real_escape_string($koneksi, trim($data[3]));
            $opsi_d = mysqli_real_escape_string($koneksi, trim($data[4]));
            $opsi_e = isset($data[5]) ? mysqli_real_escape_string($koneksi, trim($data[5])) : '';
            $jawaban_benar = mysqli_real_escape_string($koneksi, trim($data[6]));
            
            if (!empty($pertanyaan) && !empty($opsi_a) && !empty($jawaban_benar)) {
                $query = "INSERT INTO $table (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban_benar) 
                          VALUES ('$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$opsi_e', '$jawaban_benar')";
                
                if (mysqli_query($koneksi, $query)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        } else {
            $error_count++;
        }
    }
    
    fclose($handle);
    
    if ($success_count > 0) {
        header("Location: tambah_soal_teknik.php?jurusan=$jurusan&msg=csv_success");
    } else {
        header("Location: tambah_soal_teknik.php?jurusan=$jurusan&msg=csv_error");
    }
    exit();
}

// Jika tidak ada submission type yang valid
header("Location: tambah_soal_teknik.php?jurusan=$jurusan&msg=error");
exit();
?>