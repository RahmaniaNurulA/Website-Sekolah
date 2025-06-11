<?php
// koneksi.php
class database{
    var $host = "localhost";
    var $username = "root";
    var $password = "";
    var $database = "sekolah";
    public $koneksi; // Make it public to be accessible outside the class

    function __construct(){
        $this->koneksi = mysqli_connect($this->host, $this->username, $this->password);
        if (!$this->koneksi) {
            // Handle connection error
            die("Connection failed: " . mysqli_connect_error());
        }
        $cekdb = mysqli_select_db($this->koneksi, $this->database);
        if (!$cekdb) {
            // Handle database selection error
            die("Database selection failed: " . mysqli_error($this->koneksi));
        }
    }

    // --- SISWA FUNCTIONS ---
    function tampil_data_siswa(){
        $data = mysqli_query($this->koneksi,"select * from siswa");
        $hasil = [];
        if ($data) {
            while($row = mysqli_fetch_array($data)){
                $hasil[] = $row;
            }
        }
        return $hasil;
    }

    function tambah_siswa($nisn,$nama,$jenis_kelamin,$kode_jurusan,$kelas,$alamat,$agama,$no_hp){
        $query = "INSERT INTO siswa(nisn, nama, jenis_kelamin, kode_jurusan, kelas, alamat, agama, nohp)
                  VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssssssis", $nisn, $nama, $jenis_kelamin, $kode_jurusan, $kelas, $alamat, $agama, $no_hp);
        return mysqli_stmt_execute($stmt); // Return true on success, false on failure
    }

    // --- AGAMA FUNCTIONS ---
    function tampil_data_agama(){
        $data = mysqli_query($this->koneksi,"select * from agama");
        $hasil = [];
        if ($data) { // Check if query was successful
            while($row = mysqli_fetch_array($data)){
                $hasil[] = $row;
            }
        }
        return $hasil;
    }

    function tambah_agama($nama_agama){
        $query = "INSERT INTO agama(nama_agama) VALUES(?)";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $nama_agama);
        return mysqli_stmt_execute($stmt);
    }

    // --- USER FUNCTIONS ---
    function tampil_data_user(){
        $data = mysqli_query($this->koneksi,"select * from users");
        $hasil = [];
        if ($data) {
            while($row = mysqli_fetch_array($data)){
                $hasil[] = $row;
            }
        }
        return $hasil;
    }

    // --- JURUSAN FUNCTIONS ---
    function tampil_data_jurusan(){
        $data = mysqli_query($this->koneksi,"select * from jurusan");
        $hasil = [];
        if ($data) {
            while($row = mysqli_fetch_array($data)){
                $hasil[] = $row;
            }
        }
        return $hasil;
    }

    function tambah_jurusan($nama_jurusan){
        $query = "INSERT INTO jurusan(nama_jurusan) VALUES(?)";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $nama_jurusan);
        return mysqli_stmt_execute($stmt);
    }

    public function getAllJurusan() {
        $query = mysqli_query($this->koneksi, "SELECT kode_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
        return mysqli_fetch_all($query, MYSQLI_ASSOC);
    }

    public function getAllAgama() {
        $query = mysqli_query($this->koneksi, "SELECT idagama, nama_agama FROM agama ORDER BY nama_agama");
        return mysqli_fetch_all($query, MYSQLI_ASSOC);
    }

    function tampil_kode_jurusan_lengkap(){
        $data = mysqli_query($this->koneksi,
            "SELECT s.nisn AS nisn,
                    s.nama AS nama,
                    s.jenis_kelamin AS `jenis kelamin`,
                    j.nama_jurusan AS jurusan,
                    s.kelas AS kelas,
                    s.alamat AS alamat,
                    a.nama_agama AS agama,
                    s.nohp AS nohp
             FROM siswa s
             JOIN jurusan j ON s.kode_jurusan = j.kode_jurusan
             JOIN agama a ON s.agama = a.idagama"
        );
        $hasil = [];
        if ($data) {
            while($row = mysqli_fetch_array($data)){
                $hasil[] = $row;
            }
        }
        return $hasil;
    }
}
?>