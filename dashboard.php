<?php
session_start();

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
    <title>AdminLTE v4 | Dashboard - <?= htmlspecialchars($fullname) ?></title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE v4 | Dashboard" />
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
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />
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
              <a class="nav-link" data-lte-toggle="sidebar" href="#" roll="button">
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
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">Dashboard</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        
        <?php
          // Koneksi ke database
          $conn = new mysqli("localhost", "root", "", "sekolah");

          // Cek koneksi
          if ($conn->connect_error) {
              die("Koneksi gagal: " . $conn->connect_error);
          }

          // Query menghitung jumlah siswa
          $result = $conn->query("SELECT COUNT(*) AS jumlah FROM siswa");
          $row = $result->fetch_assoc();
          $jumlah_siswa = $row['jumlah'];

          $result = $conn->query("SELECT COUNT(*) AS jumlah FROM jurusan");
          $row = $result->fetch_assoc();
          $jumlah_jurusan = $row['jumlah'];

          $result = $conn->query("SELECT COUNT(*) AS jumlah FROM agama");
          $row = $result->fetch_assoc();
          $jumlah_agama = $row['jumlah'];

          $result = $conn->query("SELECT COUNT(*) AS total FROM users");
          $row = $result->fetch_assoc();
          $total_users = $row['total'];

          $conn->close();
        ?>

        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            
            <?php if ($userRoll === 'admin'): ?>
              <!-- Tampilan khusus Admin -->
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Anda login sebagai Administrator.
              </div>
            <?php elseif ($userRoll === 'guru'): ?>
              <!-- Tampilan khusus Guru -->
              <div class="alert alert-warning">
                <i class="bi bi-person-badge"></i> Anda login sebagai Guru.
              </div>
            <?php elseif ($userRoll === 'siswa'): ?>
              <!-- Tampilan khusus Siswa -->
              <div class="alert alert-success">
                <i class="bi bi-mortarboard"></i> Anda login sebagai Siswa.
              </div>
            <?php endif; ?>
            
            <!--begin::Row-->
            <div class="row">
              <!--begin::Col-->
              <?php if ($userRoll === 'admin' || $userRoll === 'guru'): ?>
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3><?= $jumlah_siswa ?></h3>
                    <p>Data Siswa</p>
                  </div>
                  <i class="bi bi-person-fill" style ="font-size:70px; position: absolute; top: 5px; right: 10px; opacity: 0.3;"></i>
                  <a
                    href="datasiswa.php"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    View Details <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 2-->
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3><?= $jumlah_jurusan ?></h3>
                    <p>Data Jurusan</p>
                  </div>
                  <i class="bi bi-pencil-square" style ="font-size:70px; position: absolute; top: 5px; right: 10px; opacity: 0.3;"></i>
                  <a
                    href="datajurusan.php"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    View Details <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 2-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3><?= $jumlah_agama ?></h3>
                    <p>Data Agama</p>
                  </div>
                  <div class="small-box-icon d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="80" height="80" aria-hidden="true">
                      <path d="M9.5 2a9.5 9.5 0 1 0 7.9 15.27A7.5 7.5 0 1 1 9.5 2zm6.5 5.5l.87 1.76 1.93.28-1.4 1.36.33 1.91L16 11.6l-1.73.91.33-1.91-1.4-1.36 1.93-.28.87-1.76z"/>
                    </svg>
                  </div>

                  <a
                    href="dataagama.php"
                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    View Details <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 3-->
              </div>
              <!--end::Col-->
              <?php endif; ?>

              <?php if ($userRoll === 'admin'): ?>
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 4-->
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3><?= $total_users ?></h3>
                    <p>Users</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path
                      clip-rule="evenodd"
                      fill-rule="evenodd"
                      d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"
                    ></path>
                    <path
                      clip-rule="evenodd"
                      fill-rule="evenodd"
                      d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"
                    ></path>
                  </svg>
                  <a
                    href="datauser.php"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    Manage Users <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 4-->
              </div>
              <!--end::Col-->
              <?php endif; ?>
              
              <?php if ($userRoll === 'siswa'): ?>
              <!-- Tampilan khusus untuk siswa -->
              <div class="col-lg-6 col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Informasi Pribadi</h3>
                  </div>
                  <div class="card-body">
                    <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($fullname) ?></p>
                    <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
                    <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($userRoll)) ?></p>
                    <p><strong>User ID:</strong> <?= htmlspecialchars($userId) ?></p>
                  </div>
                </div>
              </div>
              
              <?php endif; ?>
              
            </div>
            <!--end::Row-->
            <!-- /.row (main row) -->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">
          Logged in as: <strong><?= htmlspecialchars($fullname) ?></strong> (<?= ucfirst(htmlspecialchars($userRoll)) ?>)
        </div>
        <strong>Copyright &copy; 2024 Sistem Sekolah.</strong> All rights reserved.
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">User Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
              <div class="mb-3">
                <img 
                  id="profilePreview" 
                  src="<?= !empty($profileImage) ? htmlspecialchars($profileImage) : 'dist/assets/img/user.png' ?>" 
                  class="rounded-circle shadow" 
                  alt="Profile Picture" 
                  style="width: 150px; height: 150px; object-fit: cover;"
                />
              </div>
              <div class="mb-3">
                <label for="profilePicture" class="form-label">Change Profile Picture</label>
                <input type="file" class="form-control" id="profilePicture" name="profile_picture" accept="image/*">
                <small class="form-text text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
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
                    <small class="form-text text-muted">Username cannot be changed</small>
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
                    <small class="form-text text-muted">Role cannot be changed</small>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="fullname" class="form-label">Full Name *</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="fullname" 
                  name="fullname" 
                  value="<?= htmlspecialchars($fullname) ?>" 
                  required
                />
                <small class="form-text text-muted">This field is required</small>
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
                <small class="form-text text-muted">This field is only visible to administrators</small>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="profileForm" class="btn btn-primary">
          <i class="bi bi-save"></i> Save Changes
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
            alert('File size must be less than 2MB');
            this.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG, PNG, and GIF files are allowed');
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
        alert('Full name is required');
        document.getElementById('fullname').focus();
        return false;
    }
    
    if (fullname.length < 2) {
        e.preventDefault();
        alert('Full name must be at least 2 characters long');
        document.getElementById('fullname').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"][form="profileForm"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
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
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ="
      crossorigin="anonymous"
    ></script>
    <!-- sortablejs -->
    <script>
      const connectedSortables = document.querySelectorAll('.connectedSortable');
      connectedSortables.forEach((connectedSortable) => {
        let sortable = new Sortable(connectedSortable, {
          group: 'shared',
          handle: '.card-header',
        });
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    <!-- ChartJS -->
    <script>
      // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
      // IT'S ALL JUST JUNK FOR DEMO
      // ++++++++++++++++++++++++++++++++++++++++++

      const sales_chart_options = {
        series: [
          {
            name: 'Digital Goods',
            data: [28, 48, 40, 19, 86, 27, 90],
          },
          {
            name: 'Electronics',
            data: [65, 59, 80, 81, 56, 55, 40],
          },
        ],
        chart: {
          height: 300,
          type: 'area',
          toolbar: {
            show: false,
          },
        },
        legend: {
          show: false,
        },
        colors: ['#0d6efd', '#20c997'],
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: 'smooth',
        },
        xaxis: {
          type: 'datetime',
          categories: [
            '2023-01-01',
            '2023-02-01',
            '2023-03-01',
            '2023-04-01',
            '2023-05-01',
            '2023-06-01',
            '2023-07-01',
          ],
        },
        tooltip: {
          x: {
            format: 'MMMM yyyy',
          },
        },
      };

      const sales_chart = new ApexCharts(
        document.querySelector('#revenue-chart'),
        sales_chart_options,
      );
      // Uncomment jika ada element #revenue-chart
      // sales_chart.render();
    </script>
    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>