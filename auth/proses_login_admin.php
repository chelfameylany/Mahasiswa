<?php
// File: proses_login_admin.php
// Letakkan di: C:\xampp\htdocs\Mahasiswa\auth\proses_login_admin.php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include koneksi database
include_once "../koneksi.php";

// Cek apakah koneksi berhasil
if (!isset($koneksi) || !$koneksi) {
    die("Error: Koneksi database tidak tersedia. Periksa file koneksi.php");
}

// Hanya menerima method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_admin.php");
    exit();
}

// Ambil dan bersihkan input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input tidak kosong
if (empty($username) || empty($password)) {
    header("Location: login_admin.php?error=empty");
    exit();
}

// Batasi panjang username (max 50 karakter)
if (strlen($username) > 50) {
    header("Location: login_admin.php?error=invalid");
    exit();
}

// Query menggunakan prepared statement untuk keamanan
$query = "SELECT id_admin, username, password_hash FROM admin WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    // Error pada prepare statement
    error_log("MySQL Prepare Error: " . mysqli_error($koneksi));
    header("Location: login_admin.php?error=system");
    exit();
}

// Bind parameter
mysqli_stmt_bind_param($stmt, "s", $username);

// Execute query
if (!mysqli_stmt_execute($stmt)) {
    // Error pada execute
    error_log("MySQL Execute Error: " . mysqli_stmt_error($stmt));
    header("Location: login_admin.php?error=system");
    exit();
}

// Ambil hasil
$result = mysqli_stmt_get_result($stmt);

// Cek apakah username ditemukan
if ($data = mysqli_fetch_assoc($result)) {
    // Verifikasi password menggunakan password_hash
    if (password_verify($password, $data['password_hash'])) {
        // Login berhasil - regenerate session ID untuk keamanan
        session_regenerate_id(true);
        
        // Set session
        $_SESSION['admin'] = $data['username'];
        $_SESSION['id_admin'] = $data['id_admin'];
        $_SESSION['login_time'] = time();
        
        // Hapus attempt login jika ada
        unset($_SESSION['login_attempts']);
        
        // Redirect ke dashboard
        header("Location: ../admin/dashboard_admin.php");
        exit();
    } else {
        // Password salah
        header("Location: login_admin.php?error=wrong");
        exit();
    }
} else {
    // Username tidak ditemukan
    header("Location: login_admin.php?error=wrong");
    exit();
}

// Tutup statement
mysqli_stmt_close($stmt);
?>