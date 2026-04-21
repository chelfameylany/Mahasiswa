

<?php
include "auth_admin.php";
include "koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=hasil_tes_maba.xls");

echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Nama Maba</th>
        <th>Benar</th>
        <th>Salah</th>
        <th>Nilai</th>
        <th>Status</th>
      </tr>";

$data = mysqli_query($koneksi, "
    SELECT 
        cm.nama,
        ht.jumlah_benar,
        ht.jumlah_salah,
        ht.nilai,
        IFNULL(ht.status_lulus,'pending') AS status_lulus
    FROM calon_maba cm
    LEFT JOIN hasil_tes ht ON cm.id_maba = ht.id_maba
    ORDER BY ht.nilai DESC
");

$no = 1;
while($row = mysqli_fetch_assoc($data)){
    echo "<tr>
            <td>{$no}</td>
            <td>{$row['nama']}</td>
            <td>{$row['jumlah_benar']}</td>
            <td>{$row['jumlah_salah']}</td>
            <td>{$row['nilai']}</td>
            <td>{$row['status_lulus']}</td>
          </tr>";
    $no++;
}

echo "</table>";