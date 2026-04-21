<?php
include "koneksi.php";
session_start();

if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$set = $_GET['set'] ?? '';
$id = $_GET['id'] ?? 0;

if ($set == 'lulus') {
    $status = 'lulus';
} elseif ($set == 'tidak') {
    $status = 'tidak_lulus';
} else {
    http_response_code(400);
    echo "Invalid parameter";
    exit;
}

$query = "UPDATE hasil_tes SET status_lulus='$status' WHERE id_maba='$id'";
if (mysqli_query($koneksi, $query)) {
    echo "success";
} else {
    http_response_code(500);
    echo "error";
}
?>