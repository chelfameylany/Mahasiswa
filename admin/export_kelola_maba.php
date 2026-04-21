
<?php
include "koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=kelola_maba.xls");

$data = mysqli_query($koneksi, "SELECT * FROM calon_maba ORDER BY id_maba DESC");
?>

<table border="1">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Jurusan</th>
        <th>Status</th>
    </tr>

    <?php $no=1; while($row=mysqli_fetch_assoc($data)): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['jurusan'] ?></td>
        <td>
            <?= $row['status']=="pending"
                ? "Belum di-ACC"
                : ($row['status']=="diterima" ? "Diterima" : "Ditolak")
            ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>