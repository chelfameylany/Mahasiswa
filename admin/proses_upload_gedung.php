<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id_maba'])) {
    header("Location: login_maba.php");
    exit();
}

$id_maba = $_SESSION['id_maba'];
$id_pembayaran = $_POST['id_pembayaran'];

if ($_FILES['bukti']['error'] == 0) {
    $target_dir = "uploads/bukti_gedung/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['bukti']['name']);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $allowed = array('jpg', 'jpeg', 'png', 'pdf');
    if (in_array($file_type, $allowed)) {
        if ($_FILES['bukti']['size'] <= 2000000) {
            
            if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
                
                mysqli_query($koneksi, "UPDATE pembayaran_gedung SET 
                    bukti_bayar='$file_name',
                    status='menunggu',
                    tanggal_bayar=NOW()
                    WHERE id_pembayaran='$id_pembayaran' AND id_maba='$id_maba'");
                
                header("Location: pembayaran.php?success=upload");
                exit();
                
            } else {
                header("Location: pembayaran.php?error=gagal_upload");
                exit();
            }
        } else {
            header("Location: pembayaran.php?error=file_besar");
            exit();
        }
    } else {
        header("Location: pembayaran.php?error=format_salah");
        exit();
    }
} else {
    header("Location: pembayaran.php?error=pilih_file");
    exit();
}
?>