<?php
include "../koneksi.php";
session_start();

$id_maba = $_GET['id_maba'] ?? $_SESSION['id_maba'] ?? null;
if (!$id_maba) die(json_encode(['lengkap' => false]));

$res = mysqli_query($koneksi, "SELECT * FROM calon_maba WHERE id_maba='$id_maba'");
$data = mysqli_fetch_assoc($res);

$wajib = ['foto','ijazah_pdf','kartu_keluarga','akte_kelahiran','kartu_pelajar_ktp'];
$isLengkap = true;
foreach ($wajib as $r) {
    if (empty($data[$r])) { 
        $isLengkap = false; 
        break; 
    }
}

header('Content-Type: application/json');
echo json_encode(['lengkap' => $isLengkap]);
?>