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
    <title>AdminLTE 4 | Data Agama</title>
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
     <?php include "sidebar.php";?>
      
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Data Agama</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Agama</li>
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
                <!-- /.card -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">DATA AGAMA</h3>
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn btn-success btn-tambah" id="btnTambahAgama">
                            <i class="bi bi-plus-circle"></i> Tambah Agama
                        </button>
                    </div>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body p-0">
                    <?php
                      // Menampilkan pesan sukses atau error jika ada
                      if (isset($_SESSION['success_message'])) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        unset($_SESSION['success_message']);
                      }
                      if (isset($_SESSION['error_message'])) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['error_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        unset($_SESSION['error_message']);
                      }
                    ?>
                    <table class="table table-striped" id="example">
                      <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Agama</th>
                            <th>Option</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $no = 1;
                        foreach($db->tampil_data_agama() as $x){
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $x['nama_agama']; ?></td>
                            <td>
                              <button class="btn btn-sm btn-primary btn-edit"
                                  data-idagama="<?= $x['idagama']; ?>"
                                  data-nama_agama="<?= htmlspecialchars($x['nama_agama'], ENT_QUOTES); ?>">
                                  Edit
                              </button>
                              <button class="btn btn-sm btn-danger btn-delete"
                                  data-idagama="<?= $x['idagama']; ?>"
                                  data-nama_agama="<?= htmlspecialchars($x['nama_agama'], ENT_QUOTES); ?>">
                                  Hapus
                              </button>
                            </td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
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
    </div>

    <!-- Modal Tambah Agama -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="proses.php" method="post" id="formTambah">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white py-2">
                        <h6 class="modal-title mb-0"><i class="bi bi-person-plus"></i> Tambah Data Agama</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="tambahAgama" class="form-label mb-1 small">Nama Agama</label>
                                <input type="text" name="nama_agama" class="form-control form-control-sm" id="tambahAgama" required>
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

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="proses.php" method="post" id="formEdit">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalEditLabel"><i class="bi bi-pencil-square"></i> Edit Data Agama</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2">
                        <div class="mb-3">
                            <label for="displayEditidagama" class="form-label">Id Agama</label>
                            <input type="text" class="form-control" id="displayEditidagama" readonly>
                        </div>
                        <input type="hidden" name="idagama" id="editidagama">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editnama_agama" class="form-label">Nama Agama</label>
                                    <input type="text" name="nama_agama" class="form-control" id="editnama_agama" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <input type="hidden" name="aksi" value="update">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="proses.php" method="post" id="formHapus">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalHapusLabel"><i class="bi bi-trash"></i> Konfirmasi Hapus Data Agama</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus data agama <span id="hapusNamaAgamaSpan" class="fw-bold"></span> (<span id="displayHapusidagamaSpan"></span>) ini?</p>
                        
                        <div class="row g-2">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <small class="text-muted">Id Agama</small>
                                                <input type="text" class="form-control form-control-sm" id="displayHapusIdAgamaInput" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Nama Agama</small>
                                                <input type="text" class="form-control form-control-sm" id="hapusNamaAgamaInput" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden inputs untuk data yang akan dikirim -->
                        <input type="hidden" name="idagama" id="hapusidagama">
                        <input type="hidden" name="nama_agama" id="hapusnama_agama">
                        <input type="hidden" name="aksi" value="hapus">
                    </div>
                    <div class="modal-footer py-2">
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

    <!-- Scripts -->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <script src="dist/js/adminlte.js"></script>
    
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize OverlayScrollbars
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

            // Initialize all modals
            const modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
            const modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
            const modalHapus = new bootstrap.Modal(document.getElementById('modalHapus'));

            // Event listener for Tambah button
            document.getElementById('btnTambahAgama').addEventListener('click', () => {
                document.getElementById('formTambah').reset();
                modalTambah.show();
            });

            // Event listener for Edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('editidagama').value = btn.dataset.idagama;
                    document.getElementById('displayEditidagama').value = btn.dataset.idagama;
                    document.getElementById('editnama_agama').value = btn.dataset.nama_agama;
                    modalEdit.show();
                });
            });

            // Event listener for Delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idagama = btn.dataset.idagama;
                    const nama_agama = btn.dataset.nama_agama;
                    
                    // Isi hidden inputs untuk data yang akan dikirim ke server
                    document.getElementById('hapusidagama').value = idagama;
                    document.getElementById('hapusnama_agama').value = nama_agama;
                    
                    // Isi display inputs untuk ditampilkan ke user
                    document.getElementById('displayHapusIdAgamaInput').value = idagama;
                    document.getElementById('hapusNamaAgamaInput').value = nama_agama;
                    
                    // Isi span untuk konfirmasi text
                    document.getElementById('displayHapusidagamaSpan').textContent = idagama;
                    document.getElementById('hapusNamaAgamaSpan').textContent = nama_agama;
                    
                    // Show modal
                    modalHapus.show();
                });
            });
            
            // Initialize DataTable
            $('#example').DataTable();
        });
    </script>
    </body>
</html>