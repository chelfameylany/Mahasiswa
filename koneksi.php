<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "mahasiswa";
$port = 8111;

// koneksi database
$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

// cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
