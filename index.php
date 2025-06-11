<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sekolah");

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi untuk menentukan redirect berdasarkan role
function getRedirectByRoll($roll) {
    switch ($roll) {
        case 'admin':
            return 'dashboard.php';
        case 'guru':
            return 'dashboard.php';
        case 'siswa':
            return 'dashboard.php';
        default:
            return 'dashboard.php';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['nama'];
    $password = $_POST['password'];

    // Prepare query untuk ambil data user berdasarkan username
    $stmt = $conn->prepare("SELECT id, username, fullname, roll, profileImage, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah user ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed = $row['password'];

        // Verifikasi password
        $password_valid = false;
        
        if (password_verify($password, $hashed)) {
            $password_valid = true;
        }
        elseif ($password === $hashed) {
            $password_valid = true;
        }

        if ($password_valid) {
            // Simpan data user ke session
            $_SESSION['user'] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'fullname' => $row['fullname'],
                'roll' => $row['roll'],
                'profileImage' => $row['profileImage']
            ];
            
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['user_roll'] = $row['roll'];
            $_SESSION['profile_image'] = $row['profileImage'];
            $_SESSION['logged_in'] = true;
            
            $redirectUrl = getRedirectByRoll($row['roll']);
            header("Location: " . $redirectUrl);
            exit();
        } else {
            $error_message = "Password salah";
        }
    } else {
        $error_message = "Username tidak ditemukan";
    }

    $stmt->close();
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sistem Sekolah | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Sistem Sekolah | Login" />
    <meta name="description" content="Sistem Manajemen Sekolah - Portal Login" />
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Third Party Plugins -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="dist/css/adminlte.css" />
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 12px 48px rgba(0, 0, 0, 0.15);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            position: relative;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
            overflow: hidden;
            max-width: 380px;
            width: 100%;
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: slideInUp 0.8s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 123, 255, 0.4);
        }
        
        .login-header {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
            padding: 30px 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .school-icon {
            font-size: 3rem;
            color: white;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .login-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .login-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-top: 8px;
            position: relative;
            z-index: 2;
        }
        
        .login-body {
            padding: 30px 25px;
            background: white;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 12px 20px 12px 50px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
            background: white;
            transform: translateY(-1px);
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
            font-size: 1.1rem;
            z-index: 3;
            transition: all 0.3s ease;
        }
        
        .form-control:focus + .input-icon {
            color: #0056b3;
            transform: translateY(-50%) scale(1.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 25px;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .features {
            display: flex;
            justify-content: space-around;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .feature {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }
        
        .feature-icon {
            font-size: 1.3rem;
            color: #007bff;
            margin-bottom: 6px;
            display: block;
        }
        
        .feature-text {
            font-size: 0.8rem;
            color: #888;
            font-weight: 500;
        }
        
        /* Mobile Responsive */
        @media (max-width: 480px) {
            .login-card {
                margin: 10px;
                border-radius: 16px;
                max-width: 340px;
            }
            
            .login-header {
                padding: 25px 20px;
            }
            
            .login-body {
                padding: 25px 20px;
            }
            
            .school-icon {
                font-size: 2.5rem;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
        }
        
        /* Loading Animation */
        .btn-login.loading {
            pointer-events: none;
        }
        
        .btn-login.loading::after {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            margin-left: 10px;
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <i class="bi bi-mortarboard-fill school-icon"></i>
                <h1 class="login-title">Sistem Sekolah</h1>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <div class="welcome-message">
                    Selamat datang! Silakan masuk ke akun Anda untuk mengakses sistem.
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="index.php" method="post" id="loginForm">
                    <div class="form-group">
                        <label for="nama" class="form-label">Username</label>
                        <div class="input-group">
                            <input type="text" 
                                   id="nama" 
                                   name="nama" 
                                   class="form-control" 
                                   placeholder="Masukkan username Anda" 
                                   required 
                                   autocomplete="username" />
                            <i class="bi bi-person-fill input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Masukkan password Anda" 
                                   required 
                                   autocomplete="current-password" />
                            <i class="bi bi-lock-fill input-icon"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Masuk ke Sistem
                    </button>
                </form>
                
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="dist/js/adminlte.js"></script>
    
    <script>
        // Form handling dengan animasi
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Memproses...';
        });
        
        // Input focus effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
        
        // Auto focus pada username saat halaman dimuat
        window.addEventListener('load', function() {
            document.getElementById('nama').focus();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>