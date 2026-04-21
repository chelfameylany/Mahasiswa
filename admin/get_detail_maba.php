<?php
header('Content-Type: application/json');
error_reporting(0);
include "../koneksi.php";
session_start();

// Cek login
if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Login dulu']);
    exit();
}

// Ambil ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit();
}

// Ambil data
$result = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo json_encode(['error' => 'Data tidak ditemukan']);
    exit();
}

// ================= PATH FIX =================
// SEMUA FILE DI SINI
$base_path = "../uploads/foto/";

// Cek file ada atau tidak
$data['foto_exists'] = (!empty($data['foto']) && file_exists($base_path . $data['foto']));
$data['ijazah_exists'] = (!empty($data['ijazah_pdf']) && file_exists($base_path . $data['ijazah_pdf']));
$data['kk_exists'] = (!empty($data['kartu_keluarga']) && file_exists($base_path . $data['kartu_keluarga']));
$data['akte_exists'] = (!empty($data['akte_kelahiran']) && file_exists($base_path . $data['akte_kelahiran']));
$data['ktp_exists'] = (!empty($data['kartu_pelajar_ktp']) && file_exists($base_path . $data['kartu_pelajar_ktp']));

// Kirim JSON
echo json_encode($data);
?>