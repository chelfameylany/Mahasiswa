<?php
include "koneksi.php";
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========== FUNGSI RESPONSE ==========
function jsonResponse($status, $message, $data = []) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ========== CEK SESSION ==========
$id_maba = $_SESSION['id_maba'] ?? null;
if (!$id_maba) {
    jsonResponse('error', 'Akses ditolak. Silakan login kembali.');
}

// ========== CEK FIELD ==========
$field = $_POST['field'] ?? '';
$allowed_fields = ['foto', 'ijazah_pdf', 'kartu_keluarga', 'akte_kelahiran', 'kartu_pelajar_ktp'];

if (!in_array($field, $allowed_fields)) {
    jsonResponse('error', 'Field tidak valid.');
}

// ========== AMBIL DATA MAHASISWA ==========
$res = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba='$id_maba'");
if (!$res || mysqli_num_rows($res) === 0) {
    jsonResponse('error', 'Data mahasiswa tidak ditemukan.');
}

$data = mysqli_fetch_assoc($res);
$filename = $data[$field] ?? '';

// ========== HAPUS FILE DARI SERVER ==========
$folder_map = [
    'foto' => 'uploads/foto',
    'ijazah_pdf' => 'uploads/ijazah',
    'kartu_keluarga' => 'uploads/kk',
    'akte_kelahiran' => 'uploads/akte',
    'kartu_pelajar_ktp' => 'uploads/ktp'
];

if (!empty($filename) && isset($folder_map[$field])) {
    $file_path = $folder_map[$field] . '/' . $filename;
    
    // Coba hapus file jika ada
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            // Coba ubah permission dulu
            chmod($file_path, 0644);
            if (!unlink($file_path)) {
                jsonResponse('warning', 'File gagal dihapus dari server, tetapi database akan diupdate.');
            }
        }
    }
}

// ========== UPDATE DATABASE ==========
$update_sql = "UPDATE calon_maba SET $field = NULL WHERE id_maba = '$id_maba'";
if (!mysqli_query($koneksi, $update_sql)) {
    jsonResponse('error', 'Gagal update database: ' . mysqli_error($koneksi));
}

// ========== CEK KELENGKAPAN BARU ==========
$res_check = mysqli_query($koneksi, "SELECT foto, ijazah_pdf, kartu_keluarga, akte_kelahiran, kartu_pelajar_ktp FROM calon_maba WHERE id_maba = '$id_maba'");
$data_check = mysqli_fetch_assoc($res_check);

$wajib_fields = ['foto', 'ijazah_pdf', 'kartu_keluarga', 'akte_kelahiran', 'kartu_pelajar_ktp'];
$is_lengkap = true;
foreach ($wajib_fields as $wajib) {
    if (empty($data_check[$wajib])) {
        $is_lengkap = false;
        break;
    }
}

// ========== RESPONSE SUKSES ==========
$field_names = [
    'foto' => 'Foto Profil',
    'ijazah_pdf' => 'Ijazah',
    'kartu_keluarga' => 'Kartu Keluarga',
    'akte_kelahiran' => 'Akte Kelahiran',
    'kartu_pelajar_ktp' => 'Kartu Pelajar/KTP'
];

$field_name = $field_names[$field] ?? $field;

$message = "✅ File <strong>{$field_name}</strong> berhasil dihapus.";
if ($is_lengkap) {
    $message .= " Semua data sudah lengkap!";
}

jsonResponse('success', $message, [
    'field' => $field,
    'lengkap' => $is_lengkap
]);
?>