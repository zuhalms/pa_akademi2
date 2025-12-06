<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';

$error_message = '';

// Jika sudah login, langsung arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'dosen') {
        header("Location: dashboard_dosen.php");
        exit();
    } elseif ($_SESSION['user_role'] == 'mahasiswa') {
        header("Location: dashboard_mahasiswa.php");
        exit();
    } elseif ($_SESSION['user_role'] == 'admin') {
        header("Location: admin/dashboard_admin.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role     = $_POST['role'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Bersihkan spasi dari input pengguna
    $username_clean = str_replace(' ', '', $username);

    if ($role == 'dosen') {
        $stmt = $conn->prepare("SELECT id_dosen, nama_dosen, password FROM dosen WHERE REPLACE(nidn_dosen, ' ', '') = ?");
        $stmt->bind_param("s", $username_clean);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $dosen = $result->fetch_assoc();
            if (password_verify($password, $dosen['password'])) {
                $_SESSION['user_id']   = $dosen['id_dosen'];
                $_SESSION['user_name'] = $dosen['nama_dosen'];
                $_SESSION['user_role'] = 'dosen';
                header("Location: dashboard_dosen.php");
                exit();
            }
        }
        $stmt->close();

    } elseif ($role == 'mahasiswa') {
        $stmt = $conn->prepare("SELECT nim, nama_mahasiswa, password FROM mahasiswa WHERE REPLACE(nim, ' ', '') = ?");
        $stmt->bind_param("s", $username_clean);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $mahasiswa = $result->fetch_assoc();
            if (password_verify($password, $mahasiswa['password'])) {
                $_SESSION['user_id']   = $mahasiswa['nim'];
                $_SESSION['user_name'] = $mahasiswa['nama_mahasiswa'];
                $_SESSION['user_role'] = 'mahasiswa';
                header("Location: dashboard_mahasiswa.php");
                exit();
            }
        }
        $stmt->close();

    } elseif ($role == 'admin') {
        $stmt = $conn->prepare("SELECT id_admin, username_admin, nama_admin, password FROM admin WHERE username_admin = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['user_id']   = $admin['id_admin'];
                $_SESSION['user_name'] = $admin['nama_admin'];
                $_SESSION['user_role'] = 'admin';
                header("Location: admin/dashboard_admin.php");
                exit();
            }
        }
        $stmt->close();
    }

    // Jika semua gagal
    $error_message = "Kredensial yang Anda masukkan salah.";
}

if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMART-BA</title>

    <link rel="icon" href="assets/logo_uin.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e6f6f1;
        }
        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-wrapper {
            width: 100%;
            max-width: 900px;
            display: flex;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-art {
            flex-basis: 45%;
            background: linear-gradient(135deg, #00A86B, #008F5A);
            color: white;
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-art-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .login-art-header img {
            height: 40px;
            width: auto;
        }
        .login-art .brand-title {
            font-weight: 700;
            font-size: 1.4rem;
        }
        .login-art .brand-subtitle {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .login-art p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 1.75rem;
        }
        .feature-list li {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        .feature-list i {
            margin-right: 0.75rem;
        }
        
        .login-form-container {
            flex-basis: 55%;
            padding: 3rem;
        }
        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .role-selector .role-btn {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
        }
        .role-selector .role-btn.active {
            background-color: #00A86B;
            color: white;
            border-color: #00A86B;
            font-weight: 600;
        }
        .role-selector .role-btn:hover:not(.active) {
            background-color: #f8f9fa;
        }

        .btn-brand {
            background-color: #00A86B;
            border-color: #00A86B;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
        }
        .btn-brand:hover {
            background-color: #008F5A;
            border-color: #008F5A;
            color: white;
        }
        
        @media (max-width: 768px) {
            .login-art {
                display: none;
            }
            .login-form-container {
                flex-basis: 100%;
                padding: 2.25rem 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-wrapper">
            <div class="login-art">
                <div class="login-art-header">
                    <img src="assets/logo_uin.png" alt="Logo UIN">
                    <div>
                        <div class="brand-title">SMART-BA</div>
                        <div class="brand-subtitle">Fakultas Syariah</div>
                    </div>
                </div>
                <p>Sistem Manajemen Akademik dan Bimbingan Terpadu berbasis kampus hijau dan cerdas.</p>
                <p class="mt-2" style="font-size: 0.8rem;">
                    Universitas Islam Negeri Kota Palopo
                </p>
                <ul class="feature-list">
                    <li><i class="bi bi-check-circle-fill"></i> Multi-Role Access (Admin, Dosen, Mahasiswa)</li>
                    <li><i class="bi bi-check-circle-fill"></i> Digital Logbook & Monitoring Bimbingan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Integrasi data prodi & dosen PA</li>
                    <li><i class="bi bi-check-circle-fill"></i> Aman dengan enkripsi password</li>
                </ul>
            </div>
            <div class="login-form-container">
                <div>
                    <h3 class="fw-bold mb-1">Selamat Datang</h3>
                    <p class="text-muted mb-4">Silakan login sesuai peran Anda di SMART-BA.</p>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Login Sebagai:</label>
                            <div class="role-selector">
                                <a href="#" class="role-btn active"
                                   data-role="mahasiswa"
                                   data-label="NIM"
                                   data-placeholder="Masukkan NIM tanpa spasi">
                                    <i class="bi bi-person-fill me-2"></i>Mahasiswa
                                </a>
                                <a href="#" class="role-btn"
                                   data-role="dosen"
                                   data-label="ID Dosen"
                                   data-placeholder="Masukkan ID Dosen PA tanpa spasi">
                                    <i class="bi bi-person-workspace me-2"></i>Dosen PA
                                </a>
                                <a href="#" class="role-btn"
                                   data-role="admin"
                                   data-label="Username Admin"
                                   data-placeholder="Masukkan Username Admin">
                                    <i class="bi bi-shield-lock-fill me-2"></i>Admin
                                </a>
                            </div>
                            <input type="hidden" id="role" name="role" value="mahasiswa">
                        </div>

                        <div class="mb-3">
                            <label for="username" id="username-label" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Masukkan NIM tanpa spasi" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Masukkan Password Anda" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-brand">Masuk</button>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none text-muted small">Kembali ke Beranda</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleButtons   = document.querySelectorAll('.role-btn');
            const roleInput     = document.getElementById('role');
            const usernameLabel = document.getElementById('username-label');
            const usernameInput = document.getElementById('username');

            roleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    roleButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const role = this.dataset.role;
                    roleInput.value = role;
                    usernameLabel.textContent = this.dataset.label;
                    usernameInput.placeholder = this.dataset.placeholder;
                });
            });

            // Toggle show/hide password
            const passwordInput = document.getElementById('password');
            const toggleBtn     = document.getElementById('togglePassword');
            const toggleIcon    = document.getElementById('togglePasswordIcon');

            toggleBtn.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                if (type === 'text') {
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
