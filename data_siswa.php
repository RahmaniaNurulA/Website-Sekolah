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
    <title>Data Siswa</title>
</head>
<body>
    <h2>Data Siswa</h2>
    <table border = "1">
        <tr>
            <th>No<th>
            <th>NISN<th>
            <th>Nama<th>
            <th>Jenis Kelamin<th>
            <th>Jurusan<th>
            <th>Kelas<th>
            <th>Alamat<th>
            <th>Agama<th>
            <th>No HP<th>
            <th>Option<th>
        </tr>
        <?php
        $no = 1;
        foreach($db->tampil_kode_jurusan_lengkap() as $x){
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo $x['nisn']; ?></td>
            <td><?php echo $x['nama']; ?></td>
            <td><?php echo $x['jenis kelamin']; ?></td>
            <td><?php echo $x['jurusan']; ?></td>
            <td><?php echo $x['kelas']; ?></td>
            <td><?php echo $x['alamat']; ?></td>
            <td><?php echo $x['agama']; ?></td>
            <td><?php echo $x['nohp']; ?></td>
            <td>
                <a href="edit_siswa.php?nisn=<?php echo $x['nisn']; ?>&aksi=edit">Edit</a>
                <a href="proses.php?nisn=<?php echo $x['nisn']; ?>&aksi=hapus">Hapus</a>
        </td>
        </tr>
        <?php } ?>
    </table>
    <a href="tambah_siswa.php">Tambah Data</a>
</body>
</html>