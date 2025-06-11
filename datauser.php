<?php

session_start();
include "koneksi.php";
$db = new database();


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
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Data Users</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE 4 | Simple Tables" />
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
    
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>

    <style>
        /* Custom styles for better table appearance */
        .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 0;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
            border-bottom: none;
        }
        
        .card-header h3 {
            margin-bottom: 0;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .card-body {
            padding: 0;
            border-radius: 0 0 15px 15px;
        }
        
        /* Table Container */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 0 0 15px 15px;
        }
        
        /* Table Styles */
        .table {
            margin-bottom: 0;
            width: 100%;
            border-collapse: collapse;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            padding: 18px 12px;
            border: none;
            font-size: 14px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 15px 12px;
            font-size: 14px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
            word-wrap: break-word;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9ff;
            transform: scale(1.001);
            transition: all 0.2s ease;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Column widths untuk memastikan alignment */
        .table th:nth-child(1), .table td:nth-child(1) { width: 8%; }
        .table th:nth-child(2), .table td:nth-child(2) { width: 18%; }
        .table th:nth-child(3), .table td:nth-child(3) { width: 22%; }
        .table th:nth-child(4), .table td:nth-child(4) { width: 12%; }
        .table th:nth-child(5), .table td:nth-child(5) { width: 20%; }
        .table th:nth-child(6), .table td:nth-child(6) { width: 20%; }
        
        /* Button styles */
        .btn-action {
            margin: 2px;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 5px;
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
        
        /* Modal styles */
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
        
        /* Responsive improvements */
        @media (max-width: 992px) {
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .card-header {
                padding: 15px 20px;
            }
            
            .table thead th, .table tbody td {
                padding: 12px 8px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 768px) {
            .card-header h3 {
                font-size: 1.1rem;
            }
            
            .table thead th, .table tbody td {
                padding: 10px 6px;
                font-size: 12px;
            }
            
            .btn-action {
                padding: 3px 6px;
                font-size: 11px;
            }
            
            /* Adjust column widths for mobile */
            .table th, .table td {
                width: auto !important;
                min-width: 80px;
            }
        }
        
        /* DataTable overrides */
        .dataTables_wrapper {
            padding: 20px;
        }
        
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            margin: 10px 0;
        }
        
        .dataTables_filter input {
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            padding: 8px 12px;
        }
        
        .page-link {
            border-radius: 8px;
            margin: 0 2px;
        }
    </style>

  </head>
  <!--end::Head-->
  <!--begin::Body-->
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
              <div class="col-sm-6"><h3 class="mb-0">Data Users</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Users</li>
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
              <div class="col-12">
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

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">DATA USERS</h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table table-hover" id="example">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Role</th>
                                        <th>Password</th>
                                        <th>Foto Profil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach($db->tampil_data_user() as $x):
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($x['username']); ?></td>
                                        <td><?= htmlspecialchars($x['fullname']); ?></td>
                                        <td>
                                            <span class="badge bg-<?= $x['roll'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= ucfirst(htmlspecialchars($x['roll'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted font-monospace" style="font-size: 12px;">
                                                <?= substr(htmlspecialchars($x['password']), 0, 20) . '...'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($x['profileImage']) && $x['profileImage'] !== 'dist/assets/img/user.png'): ?>
                                                <img src="<?= htmlspecialchars($x['profileImage']); ?>" 
                                                     alt="Profile" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">Default</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End Main Card -->

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

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="proses.php" method="post" id="formEditUser" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Data User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <input type="hidden" name="username" id="editUsername" readonly>
                    <!-- TAMBAHKAN INI - Username lama untuk referensi update -->
                    <input type="hidden" name="username_old" id="editUsernameOld">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="editFullname" class="form-label">Nama Lengkap</label>
                            <input type="text" name="fullname" class="form-control form-control-sm" id="editFullname" required>
                        </div>
                        <div class="col-6">
                            <label for="editRoll" class="form-label">Role</label>
                            <select name="roll" class="form-select form-select-sm" id="editRoll" required>
                                <option value="">-- Pilih --</option>
                                <option value="admin">Admin</option>
                                <option value="guru">Guru</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control form-control-sm" id="editPassword" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="col-6">
                            <label for="editProfileImage" class="form-label">Foto Profil</label>
                            <input type="file" name="profileImage" class="form-control form-control-sm" id="editProfileImage" accept="image/*">
                            <small class="form-text text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" name="aksi" value="update_user">
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

<!-- Modal Hapus User -->
<div class="modal fade" id="modalHapusUser" tabindex="-1">
    <div class="modal-dialog">
        <form action="proses.php" method="post" id="formHapusUser">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash"></i> Konfirmasi Hapus User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Peringatan!</strong> Data user yang sudah dihapus tidak dapat dikembalikan.
                    </div>
                    <p class="mb-3">Apakah Anda yakin ingin menghapus user berikut?</p>
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">Username:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusUsername" name="username" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Nama Lengkap:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusFullname" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Role:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusRoll" readonly>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Foto Profil:</small>
                                    <input type="text" class="form-control form-control-sm" id="hapusProfileImage" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" name="aksi" value="hapus_user">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Hapus User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEditUser = new bootstrap.Modal(document.getElementById('modalEditUser'));
    const modalHapusUser = new bootstrap.Modal(document.getElementById('modalHapusUser'));

    // Event listener for Edit User buttons
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editUsername').value = btn.dataset.username;
            // TAMBAHKAN INI - Set username lama
            document.getElementById('editUsernameOld').value = btn.dataset.username;
            document.getElementById('editFullname').value = btn.dataset.fullname;
            document.getElementById('editRoll').value = btn.dataset.roll;
            document.getElementById('editPassword').value = '';
            // Clear file input
            document.getElementById('editProfileImage').value = '';
            modalEditUser.show();
        });
    });

    // Event listener for Delete User buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('hapusUsername').value = btn.dataset.username;
            document.getElementById('hapusFullname').value = btn.dataset.fullname;
            document.getElementById('hapusRoll').value = btn.dataset.roll;
            document.getElementById('hapusProfileImage').value = btn.dataset.profileimage;
            modalHapusUser.show();
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
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
            
            console.log(`Menampilkan ${visibleRows} dari ${tableRows.length} data`);
        });
    }

    // Initialize DataTable
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#example').DataTable();
    }
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