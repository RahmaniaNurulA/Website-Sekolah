<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$koneksi = new mysqli("localhost", "root", "", "sekolah");

if ($koneksi->connect_error) {
    $_SESSION['error_message'] = "Koneksi database gagal: " . $koneksi->connect_error;
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];

    // ----------- HANDLE UPDATE -----------
    if ($aksi === 'update') {
        // Update Jurusan
        if (isset($_POST['kode_jurusan']) && isset($_POST['nama_jurusan'])) {
            $kode_jurusan = $_POST['kode_jurusan'];
            $nama_jurusan = $_POST['nama_jurusan'];

            if (empty($kode_jurusan) || empty($nama_jurusan)) {
                $_SESSION['error_message'] = "Kode jurusan dan nama jurusan harus diisi.";
                header("Location: datajurusan.php");
                exit;
            }

            $stmt = $koneksi->prepare("UPDATE jurusan SET nama_jurusan = ? WHERE kode_jurusan = ?");
            if (!$stmt) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement UPDATE jurusan: " . $koneksi->error;
                header("Location: datajurusan.php");
                exit;
            }
            $stmt->bind_param("ss", $nama_jurusan, $kode_jurusan);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data jurusan berhasil diupdate.";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate data jurusan: " . $stmt->error;
            }
            $stmt->close();
            header("Location: datajurusan.php");
            exit;
        }
        // Update Agama
        elseif (isset($_POST['idagama']) && isset($_POST['nama_agama'])) {
            $idagama = $_POST['idagama'];
            $nama_agama = $_POST['nama_agama'];

            if (empty($idagama) || empty($nama_agama)) {
                $_SESSION['error_message'] = "Id Agama dan nama agama harus diisi.";
                header("Location: dataagama.php");
                exit;
            }

            $stmt = $koneksi->prepare("UPDATE agama SET nama_agama = ? WHERE idagama = ?");
            if (!$stmt) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement UPDATE agama: " . $koneksi->error;
                header("Location: dataagama.php");
                exit;
            }
            $stmt->bind_param("ss", $nama_agama, $idagama);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data agama berhasil diupdate.";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate data agama: " . $stmt->error;
            }
            $stmt->close();
            header("Location: dataagama.php");
            exit;
        }
        // Update User
if ($aksi == 'update_user') {
    error_log("Masuk ke proses update user");
    
    $username = $_POST['username'] ?? '';
    $username_old = $_POST['username_old'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $roll = $_POST['roll'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Handle file upload untuk profile image
    $profileImage = '';
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        // Proses upload file
        $target_dir = "uploads/profile/";
        $file_extension = strtolower(pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION));
        $new_filename = $username . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Validasi file
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_extension, $allowed_types) && $_FILES['profileImage']['size'] <= 2097152) { // 2MB
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $target_file)) {
                $profileImage = $new_filename;
            }
        }
    } else {
        // Jika tidak ada file baru, gunakan yang lama
        $profileImage = $_POST['current_profileImage'] ?? '';
    }

    if (empty($username) || empty($fullname) || empty($roll)) {
        $_SESSION['error_message'] = "Username, fullname, dan roll harus diisi.";
        header("Location: datauser.php");
        exit;
    }

    // Jika username berubah, cek duplikat
    if ($username !== $username_old) {
        $stmt_check_username = $koneksi->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        if ($stmt_check_username) {
            $stmt_check_username->bind_param("s", $username);
            $stmt_check_username->execute();
            $stmt_check_username->bind_result($count_username);
            $stmt_check_username->fetch();
            $stmt_check_username->close();

            if ($count_username > 0) {
                $_SESSION['error_message'] = "Username '" . htmlspecialchars($username) . "' sudah digunakan.";
                header("Location: datauser.php");
                exit;
            }
        }
    }

    // Update dengan atau tanpa password
    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE users SET username = ?, fullname = ?, roll = ?, password = ?, profileImage = ?, updated_at = NOW() WHERE username = ?");
        if (!$stmt) {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement UPDATE: " . $koneksi->error;
            header("Location: datauser.php");
            exit;
        }
        $stmt->bind_param("ssssss", $username, $fullname, $roll, $password_hashed, $profileImage, $username_old);
    } else {
        $stmt = $koneksi->prepare("UPDATE users SET username = ?, fullname = ?, roll = ?, profileImage = ?, updated_at = NOW() WHERE username = ?");
        if (!$stmt) {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement UPDATE: " . $koneksi->error;
            header("Location: datauser.php");
            exit;
        }
        $stmt->bind_param("sssss", $username, $fullname, $roll, $profileImage, $username_old);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data user berhasil diupdate.";
        error_log("Update berhasil untuk user: " . $username);
    } else {
        $_SESSION['error_message'] = "Gagal mengupdate data user: " . $stmt->error;
        error_log("Update gagal: " . $stmt->error);
    }
    $stmt->close();
    
    header("Location: datauser.php");
    exit;
}
        // Update Siswa
        elseif (isset($_POST['nisn'])) {
            $nisn        = $_POST['nisn'] ?? '';
            $nama        = $_POST['nama'] ?? '';
            $jk          = $_POST['jenis_kelamin'] ?? '';
            $jurusan_nama_dari_form = $_POST['jurusan'] ?? '';
            $kelas       = $_POST['kelas'] ?? '';
            $alamat      = $_POST['alamat'] ?? '';
            $agama_nama_dari_form = $_POST['agama'] ?? '';
            $nohp        = $_POST['nohp'] ?? '';

            if (empty($nisn) || empty($nama) || empty($jk) || empty($jurusan_nama_dari_form) || empty($kelas) || empty($alamat) || empty($agama_nama_dari_form) || empty($nohp)) {
                $_SESSION['error_message'] = "Semua field siswa wajib diisi.";
                header("Location: datasiswa.php");
                exit;
            }

            // Lookup kode jurusan
            $kode_jurusan_untuk_update = null;
            $stmt_get_kode_jurusan = $koneksi->prepare("SELECT kode_jurusan FROM jurusan WHERE nama_jurusan = ?");
            if ($stmt_get_kode_jurusan) {
                $stmt_get_kode_jurusan->bind_param("s", $jurusan_nama_dari_form);
                $stmt_get_kode_jurusan->execute();
                $result_jurusan = $stmt_get_kode_jurusan->get_result();
                if ($row_jurusan = $result_jurusan->fetch_assoc()) {
                    $kode_jurusan_untuk_update = $row_jurusan['kode_jurusan'];
                }
                $stmt_get_kode_jurusan->close();
            }

            if ($kode_jurusan_untuk_update === null) {
                $_SESSION['error_message'] = "Jurusan '" . htmlspecialchars($jurusan_nama_dari_form) . "' tidak ditemukan.";
                header("Location: datasiswa.php");
                exit;
            }

            // Lookup kode agama
            $kode_agama_untuk_update = null;
            $stmt_get_idagama = $koneksi->prepare("SELECT idagama FROM agama WHERE nama_agama = ?");
            if ($stmt_get_idagama) {
                $stmt_get_idagama->bind_param("s", $agama_nama_dari_form);
                $stmt_get_idagama->execute();
                $result_agama = $stmt_get_idagama->get_result();
                if ($row_agama = $result_agama->fetch_assoc()) {
                    $kode_agama_untuk_update = $row_agama['idagama'];
                }
                $stmt_get_idagama->close();
            }

            if ($kode_agama_untuk_update === null) {
                $_SESSION['error_message'] = "Agama '" . htmlspecialchars($agama_nama_dari_form) . "' tidak ditemukan.";
                header("Location: datasiswa.php");
                exit;
            }

            $stmt = $koneksi->prepare("UPDATE siswa SET nama = ?, jenis_kelamin = ?, kode_jurusan = ?, kelas = ?, alamat = ?, agama = ?, nohp = ? WHERE nisn = ?");

            if ($stmt === false) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement UPDATE siswa: " . $koneksi->error;
                header("Location: datasiswa.php");
                exit;
            }

            $stmt->bind_param("ssissssi", $nama, $jk, $kode_jurusan_untuk_update, $kelas, $alamat, $kode_agama_untuk_update, $nohp, $nisn);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data siswa berhasil diupdate.";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate data siswa: " . $stmt->error;
            }
            $stmt->close();
            header("Location: datasiswa.php");
            exit;
        }
        else {
            $_SESSION['error_message'] = "Data untuk update tidak lengkap.";
            header("Location: index.php");
            exit;
        }
    }

    // ----------- HANDLE HAPUS -----------
    elseif ($aksi === 'hapus') {
        // Hapus jurusan
        if (isset($_POST['kode_jurusan'])) {
            $kode_jurusan = $_POST['kode_jurusan'];
            if (empty($kode_jurusan)) {
                $_SESSION['error_message'] = "Kode jurusan tidak diberikan untuk penghapusan.";
                header("Location: datajurusan.php");
                exit;
            }

            // Cek apakah ada siswa yang terkait dengan jurusan ini
            $stmt_check_siswa = $koneksi->prepare("SELECT COUNT(*) FROM siswa WHERE kode_jurusan = ?");
            if (!$stmt_check_siswa) {
                $_SESSION['error_message'] = "Gagal mempersiapkan cek siswa terkait: " . $koneksi->error;
                header("Location: datajurusan.php");
                exit;
            }
            $stmt_check_siswa->bind_param("s", $kode_jurusan);
            $stmt_check_siswa->execute();
            $stmt_check_siswa->bind_result($count_siswa);
            $stmt_check_siswa->fetch();
            $stmt_check_siswa->close();

            if ($count_siswa > 0) {
                $_SESSION['error_message'] = "Tidak dapat menghapus jurusan ini karena masih ada " . $count_siswa . " siswa yang terdaftar di jurusan ini. Hapus siswa-siswa tersebut terlebih dahulu.";
                header("Location: datajurusan.php");
                exit;
            }

            // Jika tidak ada siswa terkait, baru lanjutkan penghapusan jurusan
            $stmt_delete = $koneksi->prepare("DELETE FROM jurusan WHERE kode_jurusan = ?");
            if ($stmt_delete) {
                $stmt_delete->bind_param("s", $kode_jurusan);
                if ($stmt_delete->execute()) {
                    $_SESSION['success_message'] = "Data jurusan berhasil dihapus.";
                } else {
                    $_SESSION['error_message'] = "Gagal menghapus data jurusan: " . $stmt_delete->error;
                }
                $stmt_delete->close();
            } else {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement hapus jurusan: " . $koneksi->error;
            }
            header("Location: datajurusan.php");
            exit;
        }
        // Hapus agama
        elseif (isset($_POST['idagama'])) {
            $idagama = $_POST['idagama'];
            if (empty($idagama)) {
                $_SESSION['error_message'] = "Id Agama tidak diberikan untuk penghapusan.";
                header("Location: dataagama.php");
                exit;
            }

            // Cek apakah ada siswa yang terkait dengan agama ini
            $stmt_check_siswa = $koneksi->prepare("SELECT COUNT(*) FROM siswa WHERE agama = ?");
            if (!$stmt_check_siswa) {
                $_SESSION['error_message'] = "Gagal mempersiapkan cek siswa terkait: " . $koneksi->error;
                header("Location: dataagama.php");
                exit;
            }
            $stmt_check_siswa->bind_param("s", $idagama);
            $stmt_check_siswa->execute();
            $stmt_check_siswa->bind_result($count_siswa);
            $stmt_check_siswa->fetch();
            $stmt_check_siswa->close();

            if ($count_siswa > 0) {
                $_SESSION['error_message'] = "Tidak dapat menghapus agama ini karena masih ada " . $count_siswa . " siswa yang terdaftar di agama ini. Hapus siswa-siswa tersebut terlebih dahulu.";
                header("Location: dataagama.php");
                exit;
            }

            // Jika tidak ada siswa terkait, baru lanjutkan penghapusan agama
            $stmt_delete = $koneksi->prepare("DELETE FROM agama WHERE idagama = ?");
            if ($stmt_delete) {
                $stmt_delete->bind_param("s", $idagama);
                if ($stmt_delete->execute()) {
                    $_SESSION['success_message'] = "Data agama berhasil dihapus.";
                } else {
                    $_SESSION['error_message'] = "Gagal menghapus data agama: " . $stmt_delete->error;
                }
                $stmt_delete->close();
            } else {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement hapus agama: " . $koneksi->error;
            }
            header("Location: dataagama.php");
            exit;
        }
        // Hapus user
elseif ($aksi == 'hapus_user') {
    error_log("Masuk ke proses hapus user");
    
    $username = $_POST['username'] ?? '';
    
    if (empty($username)) {
        $_SESSION['error_message'] = "Username tidak diberikan untuk penghapusan.";
        header("Location: datauser.php");
        exit;
    }

    // Cek apakah user yang akan dihapus adalah user yang sedang login
    if (isset($_SESSION['username']) && $_SESSION['username'] == $username) {
        $_SESSION['error_message'] = "Tidak dapat menghapus akun Anda sendiri yang sedang aktif.";
        header("Location: datauser.php");
        exit;
    }

    $stmt_delete = $koneksi->prepare("DELETE FROM users WHERE username = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("s", $username);
        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = "Data user berhasil dihapus.";
            error_log("Hapus berhasil untuk user: " . $username);
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data user: " . $stmt_delete->error;
            error_log("Hapus gagal: " . $stmt_delete->error);
        }
        $stmt_delete->close();
    } else {
        $_SESSION['error_message'] = "Gagal mempersiapkan statement hapus: " . $koneksi->error;
    }
    
    header("Location: datauser.php");
    exit;
}
        // Hapus siswa
        elseif (isset($_POST['nisn'])) {
            $nisn_to_delete = $_POST['nisn'];
            if (empty($nisn_to_delete)) {
                $_SESSION['error_message'] = "NISN tidak diberikan untuk penghapusan siswa.";
                header("Location: datasiswa.php");
                exit;
            }

            $stmt_delete = $koneksi->prepare("DELETE FROM siswa WHERE nisn = ?");
            if ($stmt_delete) {
                $stmt_delete->bind_param("i", $nisn_to_delete);
                if ($stmt_delete->execute()) {
                    $_SESSION['success_message'] = "Data siswa berhasil dihapus.";
                } else {
                    $_SESSION['error_message'] = "Gagal menghapus data siswa: " . $stmt_delete->error;
                }
                $stmt_delete->close();
            } else {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement hapus siswa: " . $koneksi->error;
            }
            header("Location: datasiswa.php");
            exit;
        }
        else {
            $_SESSION['error_message'] = "Data untuk hapus tidak lengkap.";
            header("Location: index.php");
            exit;
        }
    }

    // ----------- HANDLE TAMBAH -----------
    elseif ($aksi === 'tambah') {
        // Tambah Jurusan
        if (isset($_POST['nama_jurusan']) && !isset($_POST['username']) && !isset($_POST['nisn']) && !isset($_POST['nama_agama'])) {
            $nama_jurusan = $_POST['nama_jurusan'] ?? '';

            if (empty($nama_jurusan)){
                $_SESSION['error_message'] = "Nama Jurusan wajib diisi.";
                header("Location: datajurusan.php");
                exit;
            }

            // Cek duplikat nama jurusan
            $stmt_check = $koneksi->prepare("SELECT COUNT(*) FROM jurusan WHERE nama_jurusan = ?");
            if ($stmt_check) {
                $stmt_check->bind_param("s", $nama_jurusan);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $_SESSION['error_message'] = "Nama jurusan '" . htmlspecialchars($nama_jurusan) . "' sudah ada. Gunakan nama yang berbeda.";
                    header("Location: datajurusan.php");
                    exit;
                }
            }

            $stmt = $koneksi->prepare("INSERT INTO jurusan (nama_jurusan) VALUES (?)");
            if ($stmt === false) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement INSERT jurusan: " . $koneksi->error;
                header("Location: datajurusan.php");
                exit;
            }

            $stmt->bind_param("s", $nama_jurusan);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data jurusan berhasil ditambahkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan data jurusan: " . $stmt->error;
            }
            $stmt->close();
            header("Location: datajurusan.php");
            exit;
        }
        // Tambah Agama
        elseif (isset($_POST['nama_agama']) && !isset($_POST['username']) && !isset($_POST['nisn']) && !isset($_POST['nama_jurusan'])) {
            $nama_agama = $_POST['nama_agama'] ?? '';

            if (empty($nama_agama)){
                $_SESSION['error_message'] = "Nama Agama wajib diisi.";
                header("Location: dataagama.php");
                exit;
            }

            // Cek duplikat nama agama
            $stmt_check = $koneksi->prepare("SELECT COUNT(*) FROM agama WHERE nama_agama = ?");
            if ($stmt_check) {
                $stmt_check->bind_param("s", $nama_agama);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $_SESSION['error_message'] = "Nama agama '" . htmlspecialchars($nama_agama) . "' sudah ada. Gunakan nama yang berbeda.";
                    header("Location: dataagama.php");
                    exit;
                }
            }

            $stmt = $koneksi->prepare("INSERT INTO agama (nama_agama) VALUES (?)");
            if ($stmt === false) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement INSERT agama: " . $koneksi->error;
                header("Location: dataagama.php");
                exit;
            }

            $stmt->bind_param("s", $nama_agama);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data agama berhasil ditambahkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan data agama: " . $stmt->error;
            }
            $stmt->close();
            header("Location: dataagama.php");
            exit;
        }
        // Tambah User - DIPERBAIKI field sesuai struktur tabel
        elseif (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['fullname']) && isset($_POST['roll'])) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $fullname = $_POST['fullname'] ?? '';
            $roll = $_POST['roll'] ?? '';
            $profileImage = $_POST['profileImage'] ?? '';

            if (empty($username) || empty($password) || empty($fullname) || empty($roll)) {
                $_SESSION['error_message'] = "Username, password, fullname, dan roll wajib diisi.";
                header("Location: datauser.php");
                exit;
            }

            if (strlen($password) < 6) {
                $_SESSION['error_message'] = "Password minimal 6 karakter.";
                header("Location: datauser.php");
                exit;
            }

            // Cek username duplikat
            $stmt_check_username = $koneksi->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            if ($stmt_check_username) {
                $stmt_check_username->bind_param("s", $username);
                $stmt_check_username->execute();
                $stmt_check_username->bind_result($count_username);
                $stmt_check_username->fetch();
                $stmt_check_username->close();

                if ($count_username > 0) {
                    $_SESSION['error_message'] = "Username '" . htmlspecialchars($username) . "' sudah digunakan. Gunakan username yang berbeda.";
                    header("Location: datauser.php");
                    exit;
                }
            }

            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO users (username, password, fullname, roll, profileImage, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

            if ($stmt === false) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement INSERT user: " . $koneksi->error;
                header("Location: datauser.php");
                exit;
            }

            $stmt->bind_param("sssss", $username, $password_hashed, $fullname, $roll, $profileImage);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data user berhasil ditambahkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan data user: " . $stmt->error;
            }
            $stmt->close();
            header("Location: datauser.php");
            exit;
        }
        // Tambah Siswa
        elseif (isset($_POST['nisn'])) {
            $nisn        = $_POST['nisn'] ?? '';
            $nama        = $_POST['nama'] ?? '';
            $jk          = $_POST['jenis_kelamin'] ?? '';
            $jurusan_nama_dari_form = $_POST['jurusan'] ?? '';
            $kelas       = $_POST['kelas'] ?? '';
            $alamat      = $_POST['alamat'] ?? '';
            $agama_nama_dari_form = $_POST['agama'] ?? '';
            $nohp        = $_POST['nohp'] ?? '';

            if (empty($nisn) || empty($nama) || empty($jk) || empty($jurusan_nama_dari_form) || empty($kelas) || empty($alamat) || empty($agama_nama_dari_form) || empty($nohp)) {
                $_SESSION['error_message'] = "Semua field siswa wajib diisi.";
                header("Location: datasiswa.php");
                exit;
            }

            // Cek apakah NISN sudah ada
            $stmt_check_nisn = $koneksi->prepare("SELECT COUNT(*) FROM siswa WHERE nisn = ?");
            if ($stmt_check_nisn) {
                $stmt_check_nisn->bind_param("i", $nisn);
                $stmt_check_nisn->execute();
                $stmt_check_nisn->bind_result($count_nisn);
                $stmt_check_nisn->fetch();
                $stmt_check_nisn->close();

                if ($count_nisn > 0) {
                    $_SESSION['error_message'] = "NISN " . htmlspecialchars($nisn) . " sudah terdaftar. Gunakan NISN yang berbeda.";
                    header("Location: datasiswa.php");
                    exit;
                }
            }

            // Lookup kode jurusan
            $kode_jurusan_untuk_insert = null;
            $stmt_get_kode_jurusan = $koneksi->prepare("SELECT kode_jurusan FROM jurusan WHERE nama_jurusan = ?");
            if ($stmt_get_kode_jurusan) {
                $stmt_get_kode_jurusan->bind_param("s", $jurusan_nama_dari_form);
                $stmt_get_kode_jurusan->execute();
                $result_jurusan = $stmt_get_kode_jurusan->get_result();
                if ($row_jurusan = $result_jurusan->fetch_assoc()) {
                    $kode_jurusan_untuk_insert = $row_jurusan['kode_jurusan'];
                }
                $stmt_get_kode_jurusan->close();
            }

            if ($kode_jurusan_untuk_insert === null) {
                $_SESSION['error_message'] = "Jurusan '" . htmlspecialchars($jurusan_nama_dari_form) . "' tidak ditemukan.";
                header("Location: datasiswa.php");
                exit;
            }

            // Lookup kode agama
            $kode_agama_untuk_insert = null;
            $stmt_get_idagama = $koneksi->prepare("SELECT idagama FROM agama WHERE nama_agama = ?");
            if ($stmt_get_idagama) {
                $stmt_get_idagama->bind_param("s", $agama_nama_dari_form);
                $stmt_get_idagama->execute();
                $result_agama = $stmt_get_idagama->get_result();
                if ($row_agama = $result_agama->fetch_assoc()) {
                    $kode_agama_untuk_insert = $row_agama['idagama'];
                }
                $stmt_get_idagama->close();
            }

            if ($kode_agama_untuk_insert === null) {
                $_SESSION['error_message'] = "Agama '" . htmlspecialchars($agama_nama_dari_form) . "' tidak ditemukan.";
                header("Location: datasiswa.php");
                exit;
            }

            // Insert data siswa baru
            $stmt = $koneksi->prepare("INSERT INTO siswa (nisn, nama, jenis_kelamin, kode_jurusan, kelas, alamat, agama, nohp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt === false) {
                $_SESSION['error_message'] = "Gagal mempersiapkan statement INSERT siswa: " . $koneksi->error;
                header("Location: datasiswa.php");
                exit;
            }

            $stmt->bind_param("ississis",
                $nisn,
                $nama,
                $jk,
                $kode_jurusan_untuk_insert,
                $kelas,
                $alamat,
                $kode_agama_untuk_insert,
                $nohp
            );

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data siswa berhasil ditambahkan.";
                header("Location: datasiswa.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan data siswa: " . $stmt->error;
                header("Location: datasiswa.php");
                exit;
            }
            $stmt->close();
        }

    // Tambah Jurusan
    elseif (isset($_POST['nama_jurusan'])) {
        $nama_jurusan = $_POST['nama_jurusan'] ?? '';

        // Validasi input
        if (empty($nama_jurusan)){
            $_SESSION['error_message'] = "Nama Jurusan wajib diisi.";
            header("Location: datajurusan.php");
            exit;
        }

        // Insert data jurusan baru
        $stmt = $koneksi->prepare("INSERT INTO jurusan (nama_jurusan) VALUES (?)");

        if ($stmt === false) {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement INSERT jurusan: " . $koneksi->error;
            header("Location: datajurusan.php");
            exit;
        }

        $stmt->bind_param("s",
            $nama_jurusan,
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data jurusan berhasil ditambahkan.";
            header("Location: datajurusan.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan data jurusan: " . $stmt->error;
            header("Location: datajurusan.php");
            exit;
        }
        $stmt->close();
    }
    // Tambah Agama
    elseif (isset($_POST['nama_agama'])) {
        $nama_agama = $_POST['nama_agama'] ?? '';

        // Validasi input
        if (empty($nama_agama)){
            $_SESSION['error_message'] = "Nama Agama wajib diisi.";
            header("Location: dataagama.php");
            exit;
        }

        // Insert data agama baru
        $stmt = $koneksi->prepare("INSERT INTO agama (nama_agama) VALUES (?)");

        if ($stmt === false) {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement INSERT agama: " . $koneksi->error;
            header("Location: dataagama.php");
            exit;
        }

        $stmt->bind_param("s",
            $nama_agama,
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data agama berhasil ditambahkan.";
            header("Location: dataagama.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan data agama: " . $stmt->error;
            header("Location: dataagama.php");
            exit;
        }
        $stmt->close();
    }
    else {
        $_SESSION['error_message'] = "Data untuk tambah agama tidak lengkap.";
        header("Location: dataagama.php");
        exit;
    }
}

    else {
        $_SESSION['error_message'] = "Aksi POST tidak dikenali.";
        header("Location: index.php"); // Sesuaikan ke halaman yang relevan
        exit;
    }
}
// Hapus jurusan via GET (ini biasanya tidak disarankan untuk hapus,
// karena bisa diakses langsung via URL. Lebih aman pakai POST.)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    if (isset($_GET['kode_jurusan'])) {
        $kode_jurusan = $_GET['kode_jurusan'];
        
        // --- Penanganan Foreign Key untuk GET (sama seperti POST) ---
        $stmt_check_siswa = $koneksi->prepare("SELECT COUNT(*) FROM siswa WHERE kode_jurusan = ?");
        if (!$stmt_check_siswa) {
            $_SESSION['error_message'] = "Gagal mempersiapkan cek siswa terkait (GET): " . $koneksi->error;
            header("Location: datajurusan.php");
            exit;
        }
        $stmt_check_siswa->bind_param("s", $kode_jurusan);
        $stmt_check_siswa->execute();
        $stmt_check_siswa->bind_result($count_siswa);
        $stmt_check_siswa->fetch();
        $stmt_check_siswa->close();

        if ($count_siswa > 0) {
            $_SESSION['error_message'] = "Tidak dapat menghapus jurusan ini karena masih ada " . $count_siswa . " siswa yang terdaftar di jurusan ini.";
            header("Location: datajurusan.php");
            exit;
        }

        $stmt_delete = $koneksi->prepare("DELETE FROM jurusan WHERE kode_jurusan = ?");
        if ($stmt_delete) {
            $stmt_delete->bind_param("s", $kode_jurusan);
            if ($stmt_delete->execute()) {
                $_SESSION['success_message'] = "Data jurusan berhasil dihapus.";
                header("Location: datajurusan.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data jurusan: " . $stmt_delete->error;
                header("Location: datajurusan.php");
                exit;
            }
            $stmt_delete->close();
        } else {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement hapus jurusan (GET): " . $koneksi->error;
            header("Location: datajurusan.php");
            exit;
        }
    }
    elseif (isset($_GET['nisn'])) {
        $nisn_to_delete = $_GET['nisn'];
        $stmt_delete = $koneksi->prepare("DELETE FROM siswa WHERE nisn = ?");
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $nisn_to_delete);
            if ($stmt_delete->execute()) {
                $_SESSION['success_message'] = "Data siswa berhasil dihapus.";
                header("Location: datasiswa.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data siswa (GET): " . $stmt_delete->error;
                header("Location: datasiswa.php");
                exit;
            }
            $stmt_delete->close();
        } else {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement hapus siswa (GET): " . $koneksi->error;
            header("Location: datasiswa.php");
            exit;
        }
    }
    else {
        $_SESSION['error_message'] = "Parameter penghapusan tidak lengkap (GET).";
        header("Location: index.php"); // Sesuaikan ke halaman yang relevan
        exit;
    }
}

else {
    // Ini akan terjadi jika proses.php diakses tanpa POST/GET aksi yang valid
    $_SESSION['error_message'] = "Aksi tidak dikenal atau tidak ada data yang dikirim.";
    header("Location: index.php"); // Sesuaikan ke halaman yang relevan
    exit;
}

$koneksi->close();
?>