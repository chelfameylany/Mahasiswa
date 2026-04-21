<?php
include "koneksi.php";

if(isset($_GET['acc'])){
    mysqli_query($koneksi, "UPDATE calon_maba SET status='diterima' WHERE id_calon='$_GET[acc]'");
    header("Location: kelola_maba.php");
}

if(isset($_GET['tolak'])){
    mysqli_query($koneksi, "UPDATE calon_maba SET status='ditolak' WHERE id_calon='$_GET[tolak]'");
    header("Location: kelola_maba.php");
}
?>
