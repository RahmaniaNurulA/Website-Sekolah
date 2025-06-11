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
    <title>Data Jurusan</title>
</head>
<body>
    <h2>Data Jurusan</h2>
    <table border = "1">
        <tr>
            <th>Kode Jurusan<th>
            <th>Nama Jurusan<th>
            <th>Option<th>
        </tr>
        <?php
        $no = 1;
        foreach($db->tampil_data_jurusan() as $x){
        ?>
        <tr>
            <td><?php echo $x['kode_jurusan']; ?><td>
            <td><?php echo $x['nama_jurusan']; ?><td>
            <td>
                <a href="edit_jurusan.php?nama_jurusan=<?php echo $x['nama_jurusan']; ?>&aksi=edit">Edit</a>
                <a href="proses.php?nama_jurusan=<?php echo $x['nama_jurusan']; ?>&aksi=hapus">Hapus</a>
        </td>
        </tr>
        <?php } ?>
    </table>
    <a href="tambah_jurusan.php">Tambah Data</a>
</body>
</html>