<?php
include "koneksi.php";
$db = new database();
if(isset($_POST['simpan'])){
    $db->tambah_siswa(
        $_POST['nisn'],
        $_POST['nama'],
        $_POST['jenis_kelamin'],
        $_POST['kode_jurusan'],
        $_POST['kelas'],
        $_POST['alamat'],
        $_POST['agama'],
        $_POST['no_hp']);
        header("location:data_siswa.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Siswa</title>
</head>
<body>
    <h2>Form Tambah Siswa</h2>
    <form action="" method="post">
        <label for="nisn">NISN:</label><br>
        <input type="text" id="nisn" name="nisn" required><br><br>
        
        <label for="nama">Nama:</label><br>
        <input type="text" id="nama" name="nama" required><br><br> 
        
        <label for="jenis_kelamin">Jenis Kelamin:</label><br>
        <input type="radio" id="laki_laki" name="jenis_kelamin" value="Laki-laki" required>
        <label for="laki_laki">Laki-laki</label><br>
        <input type="radio" id="perempuan" name="jenis_kelamin" value="Perempuan" required>
        <label for="perempuan">Perempuan</label><br><br>
        
        <label for="kode_jurusan">Kode Jurusan:</label><br>
        <input type="text" id="kode_jurusan" name="kode_jurusan" required><br><br>
        
        <label for="kelas">Kelas:</label><br>
        <input type="text" id="kelas" name="kelas" required><br><br>
        
        <label for="alamat">Alamat:</label><br>
        <textarea id="alamat" name="alamat" required></textarea><br><br>
        
        <label for="agama">Agama:</label><br>
        <input type="text" id="agama" name="agama" required><br><br>
        
        <label for="no_hp">No HP:</label><br>
        <input type="text" id="no_hp" name="no_hp" required><br><br>
        
        <input type="submit" name="simpan" value="Tambah Siswa">
    </form>
</body>
</html>