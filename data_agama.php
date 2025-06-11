<?php
include "koneksi.php";
$db = new database();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Data Agama</title>
</head>
<body>
    <h2>Data Agama</h2>
    <table border = "1">
        <tr>
            <th>ID Agama<th>
            <th>Nama Agama<th>
            <th>Option<th>
        </tr>
        <?php
        $no = 1;
        foreach($db->tampil_data_agama() as $x){
        ?>
        <tr>
            <td><?php echo $x['idagama']; ?><td>
            <td><?php echo $x['nama_agama']; ?><td>
            <td>
                <a href="edit_agama.php?nama_agama=<?php echo $x['nama_agama']; ?>&aksi=edit">Edit</a>
                <a href="proses.php?nama_agama=<?php echo $x['nama_agama']; ?>&aksi=hapus">Hapus</a>
        </td>
        </tr>
        <?php } ?>
    </table>
    <a href="tambah_agama.php">Tambah Data</a>
</body>
</html>