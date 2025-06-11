<?php
include "koneksi.php";
$db = new database();
if(isset($_POST['simpan'])){
    $db->tambah_jurusan(
        $_POST['nama_jurusan']);
        header("location:data_jurusan.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Jurusan</title>
</head>
<body>
    <h2>Form Tambah Jurusan</h2>
    <form action="" method="post">
        
        <label for="nama_jurusan">Nama Jurusan:</label><br>
        <input type="text" id="nama_jurusan" name="nama_jurusan" required><br><br> 
        
        <input type="submit" name="simpan" value="Tambah Jurusan">
    </form>
</body>
</html>