<?php
session_start(); // Pastikan session_start() ada di bagian paling atas

include "koneksi.php";
$db = new database();

$listJurusan = $db->getAllJurusan();
$listAgama   = $db->getAllAgama();

// Cek apakah user sudah login dan ambil data user dari session
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    // Jika belum login, redirect ke halaman login
    header("Location: index.php");
    exit;
}

// Ambil data user dari session dengan prioritas pada format baru
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $userId = $user['id'];
    $username = $user['username'];
    $fullname = $user['fullname'];
    $userRoll = $user['roll'];
    $profileImage = $user['profileImage'];
} else {
    // Fallback ke format lama jika diperlukan
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $fullname = $_SESSION['fullname'];
    $userRoll = $_SESSION['user_roll'];
    $profileImage = $_SESSION['profileImage'] ?? '';
}

// Inisialisasi variabel tambahan jika belum ada
$email = '';
$phone = '';
$passwordHash = '';

// Cek apakah user klik tombol logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handler untuk update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'])) {
    $conn = new mysqli("localhost", "root", "", "sekolah");
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $newFullname = trim($_POST['fullname']);
    $uploadedImage = '';
    $updateSuccess = false;
    $errorMessage = '';
    
    // Validasi input
    if (empty($newFullname)) {
        $errorMessage = "Nama lengkap tidak boleh kosong";
    } else {
        // Handle upload foto profil
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/profile/';
            
            // Buat direktori jika belum ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['profile_picture']['name']);
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = strtolower($fileInfo['extension']);
            
            // Validasi tipe file
            if (!in_array($fileExtension, $allowedTypes)) {
                $errorMessage = "Tipe file tidak diizinkan. Gunakan JPG, PNG, atau GIF";
            }
            // Validasi ukuran file (2MB)
            elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
                $errorMessage = "Ukuran file terlalu besar. Maksimal 2MB";
            } else {
                // Generate nama file unik
                $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;
                
                // Upload file
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                    $uploadedImage = $uploadPath;
                    
                    // Hapus foto lama jika ada dan bukan default
                    if (!empty($profileImage) && $profileImage !== 'dist/assets/img/user.png' && file_exists($profileImage)) {
                        unlink($profileImage);
                    }
                } else {
                    $errorMessage = "Gagal mengupload foto profil";
                }
            }
        }
        
        // Update database jika tidak ada error
        if (empty($errorMessage)) {
            $sql = "UPDATE users SET fullname = ?";
            $params = [$newFullname];
            $types = "s";
            
            // Jika ada foto baru, update juga kolom profileImage
            if (!empty($uploadedImage)) {
                $sql .= ", profileImage = ?";
                $params[] = $uploadedImage;
                $types .= "s";
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $userId;
            $types .= "i";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                // Update session data
                if (isset($_SESSION['user'])) {
                    $_SESSION['user']['fullname'] = $newFullname;
                    if (!empty($uploadedImage)) {
                        $_SESSION['user']['profileImage'] = $uploadedImage;
                    }
                } else {
                    $_SESSION['fullname'] = $newFullname;
                    if (!empty($uploadedImage)) {
                        $_SESSION['profileImage'] = $uploadedImage;
                    }
                }
                
                // Update variabel lokal
                $fullname = $newFullname;
                if (!empty($uploadedImage)) {
                    $profileImage = $uploadedImage;
                }
                
                $updateSuccess = true;
            } else {
                $errorMessage = "Gagal memperbarui data: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
    
    $conn->close();
    
    // Set pesan untuk ditampilkan
    if ($updateSuccess) {
        $successMessage = "Profile berhasil diperbarui!";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Data Siswa</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE 4 | Data Siswa" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" />
    
    <style>
        /* Custom styles for better table appearance */
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            padding: 15px 8px;
            border: none;
            font-size: 14px;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 12px 8px;
            font-size: 13px;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9ff;
            transform: scale(1.001);
            transition: all 0.2s ease;
        }
        
        .btn-action {
            margin: 2px;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 5px;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .btn-tambah {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.3);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
            border-bottom: none;
            padding: 25px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge-status {
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 20px;
        }
        
        .text-truncate-custom {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        @media (max-width: 768px) {
            .table thead th, .table tbody td {
                padding: 8px 4px;
                font-size: 12px;
            }
            
            .btn-action {
                padding: 3px 6px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->
          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->
            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="<?= !empty($profileImage) ? htmlspecialchars($profileImage) : 'dist/assets/img/user.png' ?>"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline"><?= htmlspecialchars($fullname) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                    src="<?= !empty($profileImage) ? htmlspecialchars($profileImage) : 'dist/assets/img/user.png' ?>"
                    class="rounded-circle shadow"
                    alt="User Image"
                  />
                  <p>
                    <?= htmlspecialchars($fullname) ?> - <?= ucfirst(htmlspecialchars($userRoll)) ?>
                    <small>Username: <?= htmlspecialchars($username) ?></small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="#" class="btn btn-default btn-flat" data-bs-toggle="modal" data-bs-target="#profileModal">Profile</a>
                  <a href="?action=logout" class="btn btn-default btn-flat float-end">Sign out</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      
      <!-- Sidebar include -->
      <?php include "sidebar.php";?>
      
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Data Siswa</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Siswa</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-md-12">
                <!-- Alert Messages -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($successMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($errorMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                  <div class="card-header">
                      <h3 class="card-title">DATA SISWA</h3>
                      <div class="d-flex justify-content-end mb-3">
                          <button type="button" class="btn btn-success btn-tambah" id="btnTambahSiswa">
                              <i class="bi bi-plus-circle"></i> Tambah Siswa
                          </button>
                      </div>
                  </div>
                  
                  <div class="card-body p-0">
                      <!-- DataTables Controls -->
                      <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                          <div class="d-flex align-items-center">
                              <select class="form-select form-select-sm" style="width: auto;" id="entriesPerPage">
                                  <option value="10">10</option>
                                  <option value="25">25</option>
                                  <option value="50">50</option>
                                  <option value="100">100</option>
                              </select>
                              <span class="ms-2">entries per page</span>
                          </div>
                          <div class="d-flex align-items-center">
                              <label class="me-2">Search:</label>
                              <input type="search" class="form-control form-control-sm" placeholder="" id="searchInput" style="width: 200px;">
                          </div>
                      </div>
                      
                      <table class="table table-striped mb-0" id="example">
                        <thead>
                          <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Jurusan</th>
                            <th>Kelas</th>
                            <th>Alamat</th>
                            <th>Agama</th>
                            <th>No HP</th>
                            <th>Option</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; foreach($db->tampil_kode_jurusan_lengkap() as $x): ?>
                          <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($x['nisn']); ?></td>
                            <td><?= htmlspecialchars($x['nama']); ?></td>
                            <td><?= htmlspecialchars($x['jenis kelamin']); ?></td>
                            <td><?= htmlspecialchars($x['jurusan']); ?></td>
                            <td><?= htmlspecialchars($x['kelas']); ?></td>
                            <td class="text-truncate-custom"><?= htmlspecialchars($x['alamat']); ?></td>
                            <td><?= htmlspecialchars($x['agama']); ?></td>
                            <td><?= htmlspecialchars($x['nohp']); ?></td>
                            <td>
                              <button class="btn btn-sm btn-primary btn-edit btn-action"
                                data-nisn="<?= htmlspecialchars($x['nisn']); ?>"
                                data-nama="<?= htmlspecialchars($x['nama'], ENT_QUOTES); ?>"
                                data-jenis_kelamin="<?= htmlspecialchars($x['jenis kelamin']); ?>"
                                data-jurusan="<?= htmlspecialchars($x['jurusan']); ?>"
                                data-kelas="<?= htmlspecialchars($x['kelas']); ?>"
                                data-alamat="<?= htmlspecialchars($x['alamat'], ENT_QUOTES); ?>"
                                data-agama="<?= htmlspecialchars($x['agama']); ?>"
                                data-nohp="<?= htmlspecialchars($x['nohp']); ?>">
                                <i class="bi bi-pencil"></i> Edit
                              </button>
                              <button
                                class="btn btn-sm btn-danger btn-delete btn-action"
                                data-nisn="<?= htmlspecialchars($x['nisn']); ?>"
                                data-nama="<?= htmlspecialchars($x['nama'], ENT_QUOTES); ?>"
                                data-jenis_kelamin="<?= htmlspecialchars($x['jenis kelamin']); ?>"
                                data-jurusan="<?= htmlspecialchars($x['jurusan']); ?>"
                                data-kelas="<?= htmlspecialchars($x['kelas']); ?>"
                                data-alamat="<?= htmlspecialchars($x['alamat'], ENT_QUOTES); ?>"
                                data-agama="<?= htmlspecialchars($x['agama']); ?>"
                                data-nohp="<?= htmlspecialchars($x['nohp']); ?>"
                              >
                                <i class="bi bi-trash"></i> Hapus
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                        </tbody>
                      </table>
                      
                      <!-- Pagination Info -->
                      <div class="d-flex justify-content-between align-items-center p-3 border-top">
                          <div id="tableInfo">
                              Showing 1 to 10 of 57 entries
                          </div>
                          <nav aria-label="Table pagination">
                              <ul class="pagination pagination-sm mb-0" id="tablePagination">
                                  <li class="page-item disabled">
                                      <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                  </li>
                                  <li class="page-item active" aria-current="page">
                                      <a class="page-link" href="#">1</a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">2</a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">3</a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">Next</a>
                                  </li>
                              </ul>
                          </nav>
                      </div>
                  </div>
                </div>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
    </div>
    <!--end::App Wrapper-->

<!-- Modal Profile -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="profileModalLabel">
                    <i class="bi bi-person-circle"></i> Profile Pengguna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <!-- Alert Messages -->
                <?php if (isset($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form id="profileForm" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Profile Picture Section -->
                        <div class="col-md-4 text-center">
                            <div class="profile-image-container mb-3">
                                <img 
                                    id="profilePreview" 
                                    src="<?= !empty($profileImage) ? htmlspecialchars($profileImage) : 'dist/assets/img/user.png' ?>" 
                                    class="rounded-circle shadow" 
                                    alt="Profile Picture" 
                                    style="width: 150px; height: 150px; object-fit: cover;"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="profilePicture" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="profilePicture" name="profile_picture" accept="image/*">
                                <small class="form-text text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                            </div>
                        </div>
                        
                        <!-- User Information Section -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            id="username" 
                                            name="username" 
                                            value="<?= htmlspecialchars($username) ?>" 
                                            readonly
                                        />
                                        <small class="form-text text-muted">Username tidak dapat diubah</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="roll" class="form-label">Role</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            id="roll" 
                                            name="roll" 
                                            value="<?= ucfirst(htmlspecialchars($userRoll)) ?>" 
                                            readonly
                                        />
                                        <small class="form-text text-muted">Role tidak dapat diubah</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Nama Lengkap *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="fullname" 
                                    name="fullname" 
                                    value="<?= htmlspecialchars($fullname) ?>" 
                                    required
                                />
                                <small class="form-text text-muted">Field ini wajib diisi</small>
                            </div>
                            
                            <!-- Password Hash Display (for admin/debugging) -->
                            <?php if ($userRoll === 'admin'): ?>
                            <div class="mt-3">
                                <label class="form-label">Password Hash (Admin View)</label>
                                <input 
                                    type="text" 
                                    class="form-control font-monospace" 
                                    value="<?= htmlspecialchars($passwordHash ?? 'N/A') ?>" 
                                    readonly
                                    style="font-size: 12px;"
                                />
                                <small class="form-text text-muted">Field ini hanya terlihat oleh administrator</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="submit" form="profileForm" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Preview profile picture before upload
document.getElementById('profilePicture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file harus kurang dari 2MB');
            this.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Hanya file JPG, PNG, dan GIF yang diizinkan');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const fullname = document.getElementById('fullname').value.trim();
    
    if (!fullname) {
        e.preventDefault();
        alert('Nama lengkap wajib diisi');
        document.getElementById('fullname').focus();
        return false;
    }
    
    if (fullname.length < 2) {
        e.preventDefault();
        alert('Nama lengkap harus minimal 2 karakter');
        document.getElementById('fullname').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"][form="profileForm"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    // Optional: You can add a timeout to re-enable the button if needed
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 10000); // 10 seconds timeout
});

// Auto-show modal if there are messages (success or error)
<?php if (isset($successMessage) || (isset($errorMessage) && !empty($errorMessage))): ?>
document.addEventListener('DOMContentLoaded', function() {
    const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
    profileModal.show();
});
<?php endif; ?>

</script>

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="proses.php" method="post" id="formTambah">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-person-plus"></i> Tambah Data Siswa</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="tambahNisn" class="form-label mb-1 small">NISN</label>
                            <input type="text" name="nisn" class="form-control form-control-sm" id="tambahNisn" required>
                        </div>
                        <div class="col-6">
                            <label for="tambahNama" class="form-label mb-1 small">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control form-control-sm" id="tambahNama" required>
                        </div>
                        
                        <div class="col-6">
                            <label for="tambahJenisKelamin" class="form-label mb-1 small">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select form-select-sm" id="tambahJenisKelamin" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="tambahJurusan" class="form-label mb-1 small">Jurusan</label>
                            <select name="jurusan" class="form-select form-select-sm" id="tambahJurusan" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($listJurusan as $j): ?>
                                    <option value="<?= htmlspecialchars($j['nama_jurusan']); ?>"><?= htmlspecialchars($j['nama_jurusan']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="tambahKelas" class="form-label mb-1 small">Kelas</label>
                            <select name="kelas" class="form-select form-select-sm" id="tambahKelas" required>
                                <option value="">-- Pilih --</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label for="tambahAlamat" class="form-label mb-1 small">Alamat</label>
                            <textarea name="alamat" class="form-control form-control-sm" id="tambahAlamat" rows="2" required placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        <div class="col-6">
                            <label for="tambahAgama" class="form-label mb-1 small">Agama</label>
                            <select name="agama" class="form-select form-select-sm" id="tambahAgama" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($listAgama as $a): ?>
                                    <option value="<?= htmlspecialchars($a['nama_agama']); ?>"><?= htmlspecialchars($a['nama_agama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="tambahNoHp" class="form-label mb-1 small">No HP</label>
                            <input type="text" name="nohp" class="form-control form-control-sm" id="tambahNoHp" required placeholder="08xxxxxxxxx">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 border-top-0">
                    <input type="hidden" name="aksi" value="tambah">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="proses.php" method="post" id="formEdit">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Data Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <input type="hidden" name="nisn" id="editNisn" readonly>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="editNama" class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control form-control-sm" id="editNama" required>
                        </div>
                        <div class="col-6">
                            <label for="editJenisKelamin" class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select form-select-sm" id="editJenisKelamin" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editJurusan" class="form-label">Jurusan</label>
                            <select name="jurusan" class="form-select form-select-sm" id="editJurusan" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($listJurusan as $j): ?>
                                    <option value="<?= $j['nama_jurusan']; ?>"><?= $j['nama_jurusan']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editKelas" class="form-label">Kelas</label>
                            <select name="kelas" class="form-select form-select-sm" id="editKelas" required>
                                <option value="">-- Pilih --</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editAgama" class="form-label">Agama</label>
                            <select name="agama" class="form-select form-select-sm" id="editAgama" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($listAgama as $a): ?>
                                    <option value="<?= $a['nama_agama']; ?>"><?= $a['nama_agama']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editAlamat" class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control form-control-sm" id="editAlamat" rows="2" required></textarea>
                        </div>
                        <div class="col-6">
                            <label for="editNoHp" class="form-label">No HP</label>
                            <input type="text" name="nohp" class="form-control form-control-sm" id="editNoHp" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" name="aksi" value="update">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog">
        <form action="proses.php" method="get" id="formHapus">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash"></i> Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Peringatan!</strong> Data yang sudah dihapus tidak dapat dikembalikan.
                    </div>
                    <p class="mb-3">Apakah Anda yakin ingin menghapus data siswa berikut?</p>
                    
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">NISN:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusNisn" name="nisn" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Nama:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusNama" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Jenis Kelamin:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusJenisKelamin" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Kelas:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusKelas" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Jurusan:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusJurusan" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Agama:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusAgama" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">No HP:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusNoHp" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Alamat:</small>
                                    <textarea class="form-control form-control-sm" id="hapusAlamat" rows="1" readonly></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" name="aksi" value="hapus">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Hapus Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('example');
    const rows = table.querySelectorAll('tbody tr');
    const entriesPerPageSelect = document.getElementById('entriesPerPage');
    const tableInfo = document.getElementById('tableInfo');
    const tablePagination = document.getElementById('tablePagination');

    let currentPage = 1;
    let entriesToShow = parseInt(entriesPerPageSelect.value);
    let filteredRows = Array.from(rows); // Initialize with all rows

    function displayTable() {
        const startIndex = (currentPage - 1) * entriesToShow;
        const endIndex = startIndex + entriesToShow;

        rows.forEach(row => row.style.display = 'none'); // Hide all rows initially

        const currentDisplayedRows = filteredRows.slice(startIndex, endIndex);
        currentDisplayedRows.forEach(row => row.style.display = ''); // Show only relevant rows

        updatePagination();
        updateTableInfo();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / entriesToShow);
        tablePagination.innerHTML = ''; // Clear existing pagination

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.classList.add('page-item');
        if (currentPage === 1) prevLi.classList.add('disabled');
        prevLi.innerHTML = `<a class="page-link" href="#" tabindex="-1" aria-disabled="${currentPage === 1}">Previous</a>`;
        prevLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                displayTable();
            }
        });
        tablePagination.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.classList.add('page-item');
            if (i === currentPage) pageLi.classList.add('active');
            pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            pageLi.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = i;
                displayTable();
            });
            tablePagination.appendChild(pageLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.classList.add('page-item');
        if (currentPage === totalPages || totalPages === 0) nextLi.classList.add('disabled');
        nextLi.innerHTML = `<a class="page-link" href="#" aria-disabled="${currentPage === totalPages || totalPages === 0}">Next</a>`;
        nextLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                displayTable();
            }
        });
        tablePagination.appendChild(nextLi);
    }

    function updateTableInfo() {
        const totalEntries = filteredRows.length;
        const startEntry = (currentPage - 1) * entriesToShow + 1;
        const endEntry = Math.min(currentPage * entriesToShow, totalEntries);
        
        if (totalEntries === 0) {
            tableInfo.textContent = 'Showing 0 of 0 entries';
        } else {
            tableInfo.textContent = `Showing ${startEntry} to ${endEntry} of ${totalEntries} entries`;
        }
    }

    // Search functionality
    searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();
        filteredRows = Array.from(rows).filter(row => {
            const rowText = row.textContent.toLowerCase();
            return rowText.includes(searchTerm);
        });
        currentPage = 1; // Reset to first page after search
        displayTable();
    });

    // Entries per page change
    entriesPerPageSelect.addEventListener('change', function() {
        entriesToShow = parseInt(this.value);
        currentPage = 1; // Reset to first page
        displayTable();
    });

    // Initial display
    displayTable();
});
</script>

<script>
// Fungsi untuk validasi input hanya angka
function validateNumberInput(input, maxLength) {
    // Hapus semua karakter non-digit
    input.value = input.value.replace(/\D/g, '');
    
    // Batasi panjang sesuai maxLength
    if (input.value.length > maxLength) {
        input.value = input.value.slice(0, maxLength);
    }
}

// Fungsi untuk validasi real-time saat mengetik
function setupNumberValidation(inputId, maxLength, fieldName) {
    const input = document.getElementById(inputId);
    
    if (input) {
        // Event listener untuk input real-time
        input.addEventListener('input', function(e) {
            validateNumberInput(this, maxLength);
            updateCharacterCounter(inputId, maxLength);
        });
        
        // Event listener untuk paste
        input.addEventListener('paste', function(e) {
            setTimeout(() => {
                validateNumberInput(this, maxLength);
                updateCharacterCounter(inputId, maxLength);
            }, 10);
        });
        
        // Event listener untuk keypress (mencegah input non-digit)
        input.addEventListener('keypress', function(e) {
            // Izinkan: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Izinkan: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            // Pastikan hanya angka (0-9)
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        
        // Tambahkan atribut untuk validasi HTML5
        input.setAttribute('pattern', '[0-9]*');
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('maxlength', maxLength);
        
        // Tambahkan placeholder yang informatif
        if (fieldName === 'NISN') {
            input.setAttribute('placeholder', 'Masukkan maks 12 digit NISN');
        } else if (fieldName === 'No HP') {
            input.setAttribute('placeholder', '08xxxxxxxxx (max 14 digit)');
        }
        
        // Tambahkan counter karakter
        addCharacterCounter(inputId, maxLength);
    }
}

// Fungsi untuk menampilkan counter karakter
function addCharacterCounter(inputId, maxLength) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const label = input.previousElementSibling;
    if (label && label.tagName === 'LABEL') {
        const counter = document.createElement('small');
        counter.id = inputId + '_counter';
        counter.className = 'text-muted ms-2';
        counter.style.fontSize = '11px';
        label.appendChild(counter);
        
        updateCharacterCounter(inputId, maxLength);
    }
}

// Fungsi untuk update counter karakter
function updateCharacterCounter(inputId, maxLength) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(inputId + '_counter');
    
    if (input && counter) {
        const currentLength = input.value.length;
        counter.textContent = `(${currentLength}/${maxLength})`;
        
        if (currentLength > maxLength * 0.8) {
            counter.style.color = '#dc3545';
        } else if (currentLength >= maxLength * 0.6) {
            counter.style.color = '#ffc107';
        } else {
            counter.style.color = '#6c757d';
        }
    }
}

// Fungsi validasi form sebelum submit
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const nisnInput = form.querySelector('input[name="nisn"]');
    const nohpInput = form.querySelector('input[name="nohp"]');
    
    let isValid = true;
    let errorMessages = [];
    
    // Validasi NISN
    if (nisnInput && nisnInput.value) {
        const nisnValue = nisnInput.value.trim();
        if (nisnValue.length < 5 || nisnValue.length > 12) {
            errorMessages.push('NISN harus 10-12 digit');
            nisnInput.classList.add('is-invalid');
            isValid = false;
        } else {
            nisnInput.classList.remove('is-invalid');
            nisnInput.classList.add('is-valid');
        }
    }
    
    // Validasi No HP
    if (nohpInput && nohpInput.value) {
        const nohpValue = nohpInput.value.trim();
        if (nohpValue.length < 10 || nohpValue.length > 14) {
            errorMessages.push('No HP harus 10-14 digit');
            nohpInput.classList.add('is-invalid');
            isValid = false;
        } else if (!nohpValue.startsWith('08')) {
            errorMessages.push('No HP harus diawali dengan 08');
            nohpInput.classList.add('is-invalid');
            isValid = false;
        } else {
            nohpInput.classList.remove('is-invalid');
            nohpInput.classList.add('is-valid');
        }
    }
    
    // Tampilkan pesan error jika ada
    if (!isValid) {
        // Buat modal error yang lebih menarik
        showValidationError(errorMessages);
    }
    
    return isValid;
}

// Fungsi untuk menampilkan error dengan styling yang lebih baik
function showValidationError(messages) {
    let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    errorHtml += '<h6><i class="bi bi-exclamation-triangle"></i> Validasi Gagal</h6>';
    errorHtml += '<ul class="mb-0">';
    messages.forEach(msg => {
        errorHtml += `<li>${msg}</li>`;
    });
    errorHtml += '</ul>';
    errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    errorHtml += '</div>';
    
    // Cari modal yang sedang terbuka
    const openModal = document.querySelector('.modal.show');
    if (openModal) {
        const modalBody = openModal.querySelector('.modal-body');
        if (modalBody) {
            // Hapus alert sebelumnya jika ada
            const existingAlert = modalBody.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            // Tambahkan alert baru di awal modal body
            modalBody.insertAdjacentHTML('afterbegin', errorHtml);
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all modals
    const modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
    const modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
    const modalHapus = new bootstrap.Modal(document.getElementById('modalHapus'));

    // Setup validasi untuk form tambah
    setupNumberValidation('tambahNisn', 12, 'NISN');
    setupNumberValidation('tambahNoHp', 14, 'No HP');
    
    // Setup validasi untuk form edit (NISN tidak bisa diedit, jadi tidak perlu validasi)
    setupNumberValidation('editNoHp', 14, 'No HP');

    // Event listener for Add button
    document.getElementById('btnTambahSiswa').addEventListener('click', () => {
        // Clear form fields
        document.getElementById('formTambah').reset();
        // Hapus semua class validasi
        document.querySelectorAll('#formTambah .is-invalid, #formTambah .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
        });
        // Update counter
        updateCharacterCounter('tambahNisn', 12);
        updateCharacterCounter('tambahNoHp', 14);
        // Show the add modal
        modalTambah.show();
    });

    // Event listener for Edit buttons
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            // Populate the edit modal fields
            document.getElementById('editNisn').value = btn.dataset.nisn;
            document.getElementById('editNama').value = btn.dataset.nama;
            document.getElementById('editJenisKelamin').value = btn.dataset.jenis_kelamin;
            document.getElementById('editJurusan').value = btn.dataset.jurusan;
            document.getElementById('editKelas').value = btn.dataset.kelas;
            document.getElementById('editAlamat').value = btn.dataset.alamat;
            document.getElementById('editAgama').value = btn.dataset.agama;
            document.getElementById('editNoHp').value = btn.dataset.nohp;

            // Hapus semua class validasi
            document.querySelectorAll('#formEdit .is-invalid, #formEdit .is-valid').forEach(el => {
                el.classList.remove('is-invalid', 'is-valid');
            });
            
            // Update counter untuk No HP
            updateCharacterCounter('editNoHp', 14);
            
            // Show the edit modal
            modalEdit.show();
        });
    });

    // Event listener for Delete buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            // Populate the delete modal fields
            document.getElementById('hapusNisn').value = btn.dataset.nisn;
            document.getElementById('hapusNama').value = btn.dataset.nama;
            document.getElementById('hapusJenisKelamin').value = btn.dataset.jenis_kelamin;
            document.getElementById('hapusJurusan').value = btn.dataset.jurusan;
            document.getElementById('hapusKelas').value = btn.dataset.kelas;
            document.getElementById('hapusAlamat').value = btn.dataset.alamat;
            document.getElementById('hapusAgama').value = btn.dataset.agama;
            document.getElementById('hapusNoHp').value = btn.dataset.nohp;

            // Show the delete modal
            modalHapus.show();
        });
    });

    // Form validation pada submit
    const formTambah = document.getElementById('formTambah');
    if (formTambah) {
        formTambah.addEventListener('submit', function(e) {
            // Hapus alert sebelumnya
            const existingAlert = this.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            if (!validateForm('formTambah')) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    const formEdit = document.getElementById('formEdit');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            // Hapus alert sebelumnya
            const existingAlert = this.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            if (!validateForm('formEdit')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Initialize DataTable
    $('#example').DataTable();
    
    // Tambahkan CSS untuk validasi
    const style = document.createElement('style');
    style.textContent = `
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .is-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        }
        
        .character-counter {
            font-size: 11px;
            margin-left: 8px;
        }
    `;
    document.head.appendChild(style);

    document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#example tbody tr');
    let visibleRows = 0;
    
    tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let rowText = '';
        
        // Gabungkan text dari semua kolom kecuali kolom terakhir (Option)
        for(let i = 0; i < cells.length - 1; i++) {
            rowText += cells[i].textContent.toLowerCase() + ' ';
        }
        
        if (rowText.includes(searchTerm)) {
            row.style.display = '';
            visibleRows++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update counter jika ada
    console.log(`Menampilkan ${visibleRows} dari ${tableRows.length} data`);
});
});

// Preview profile picture before upload
document.getElementById('profilePicture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file harus kurang dari 2MB');
            this.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Hanya file JPG, PNG, dan GIF yang diizinkan');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const fullname = document.getElementById('fullname').value.trim();
    
    if (!fullname) {
        e.preventDefault();
        alert('Nama lengkap wajib diisi');
        document.getElementById('fullname').focus();
        return false;
    }
    
    if (fullname.length < 2) {
        e.preventDefault();
        alert('Nama lengkap harus minimal 2 karakter');
        document.getElementById('fullname').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"][form="profileForm"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    // Optional: You can add a timeout to re-enable the button if needed
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 10000); // 10 seconds timeout
});

// Auto-show modal if there are messages (success or error)
<?php if (isset($successMessage) || (isset($errorMessage) && !empty($errorMessage))): ?>
document.addEventListener('DOMContentLoaded', function() {
    const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
    profileModal.show();
});
<?php endif; ?>
</script>

</div>
                  <!-- /.card-body -->
                </div>
                <!-- /.card -->
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });

      $(document).ready(function (){
        $('#example').DataTable();
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!--end::Script-->
</body>
</html>