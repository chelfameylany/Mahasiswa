<?php
header('Content-Type: application/json');
error_reporting(0);

// Karena koneksi.php ada di folder atas (Mahasiswa)
include "../koneksi.php";

$response = ['success' => false, 'message' => ''];

if (!$koneksi) {
    $response['message'] = 'Koneksi database gagal';
    echo json_encode($response);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($id <= 0) {
    $response['message'] = 'ID tidak valid';
    echo json_encode($response);
    exit;
}

if (!in_array($status, ['diterima', 'ditolak'])) {
    $response['message'] = 'Status tidak valid';
    echo json_encode($response);
    exit;
}

$query = "UPDATE calon_maba SET status = '$status' WHERE id_maba = $id";
$result = mysqli_query($koneksi, $query);

if ($result) {
    $response['success'] = true;
    $response['message'] = 'Status berhasil diupdate';
} else {
    $response['message'] = 'Gagal update: ' . mysqli_error($koneksi);
}

echo json_encode($response);
?>