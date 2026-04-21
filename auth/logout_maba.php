<?php
// logout_maba.php
session_start();

// Hapus semua data session mahasiswa
session_unset();
session_destroy();

// Redirect ke halaman LOGIN mahasiswa (bukan dashboard_utama)
header("Location: ../auth/login_maba.php");
exit();
?>