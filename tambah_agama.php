<?php
include "koneksi.php";
$db = new database();
if(isset($_POST['simpan'])){
    $db->tambah_agama(
        $_POST['nama_agama']);
        header("location:data_agama.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Agama</title>
</head>
<body>
    <h2>Form Tambah Agama</h2>
    <form action="" method="post">
        
        <label for="nama_agama">Nama Agama:</label><br>
        <input type="text" id="nama_agama" name="nama_agama" required><br><br> 
        
        <input type="submit" name="simpan" value="Tambah Agama">
    </form>
</body>
</html>