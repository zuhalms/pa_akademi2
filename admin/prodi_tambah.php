<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_prodi = trim($_POST['kode_prodi']);
    $nama_prodi = trim($_POST['nama_prodi']);

    if ($nama_prodi === '') {
        $_SESSION['error'] = "Nama program studi wajib diisi.";
    } else {
        // Cek duplikasi nama prodi
        $cek = $conn->prepare("SELECT id_prodi FROM program_studi WHERE nama_prodi = ?");
        $cek->bind_param("s", $nama_prodi);
        $cek->execute();
        $cek_res = $cek->get_result();
        if ($cek_res->num_rows > 0) {
            $_SESSION['error'] = "Nama program studi sudah terdaftar.";
        } else {
            $insert = $conn->prepare("INSERT INTO program_studi (kode_prodi, nama_prodi) VALUES (?, ?)");
            $insert->bind_param("ss", $kode_prodi, $nama_prodi);

            if ($insert->execute()) {
                $_SESSION['success'] = "Program studi baru berhasil ditambahkan.";
                $insert->close();
                $cek->close();
                header("Location: prodi.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal menambahkan prodi: " . $insert->error;
                $insert->close();
            }
        }
        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Program Studi - Admin SMART-BA</title>
    <link rel="icon" type="image/png" href="../assets/logo_uin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: radial-gradient(circle at top left, #e0f7ec 0, #f5fff9 40%, #f3f4ff 100%);
        }
        .sidebar-admin {
            background: linear-gradient(180deg, #064e3b 0%, #022c22 100%);
        }
        .sidebar-admin .brand-title {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: .03em;
        }
        .sidebar-admin .nav-link {
            border-radius: .6rem;
            margin-bottom: .25rem;
            font-size: .9rem;
        }
        .sidebar-admin .nav-link i {
            font-size: 1rem;
        }
        .sidebar-admin .nav-link.active {
            background: linear-gradient(90deg,#22c55e,#16a34a);
            font-weight: 600;
        }
        .sidebar-section-title {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .page-title {
            font-weight: 700;
        }
        .breadcrumb-text {
            font-size: .85rem;
        }
        .section-card {
            border-radius: 1rem;
            border: 0;
            box-shadow: 0 6px 18px rgba(15,23,42,.06);
        }
        .section-card .card-header {
            border-bottom: 0;
            background: #ffffff;
            border-radius: 1rem 1rem 0 0 !important;
        }
        .btn-brand {
            background: linear-gradient(135deg,#22c55e,#15803d);
            border: none;
            color: #fff;
        }
        .btn-brand:hover {
            background: linear-gradient(135deg,#16a34a,#166534);
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <aside class="col-md-2 col-lg-2 sidebar-admin text-white min-vh-100 p-3">
            <div class="d-flex align-items-center mb-4">
                <div class="me-2">
                    <i class="bi bi-mortarboard-fill fs-4 text-emerald"></i>
                </div>
                <div>
                    <div class="brand-title">SMART-BA</div>
                    <small class="text-white-50">Smart & Green Campus</small>
                </div>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'active' : ''; ?>" href="dashboard_admin.php">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item mt-3 mb-1">
                    <span class="sidebar-section-title text-white-50 ms-1">Manajemen Data</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['mahasiswa.php','mahasiswa_tambah.php','mahasiswa_edit.php']) ? 'active' : ''; ?>" href="mahasiswa.php">
                        <i class="bi bi-people me-2"></i>Mahasiswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['dosen.php','dosen_tambah.php','dosen_edit.php']) ? 'active' : ''; ?>" href="dosen.php">
                        <i class="bi bi-person-workspace me-2"></i>Dosen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['prodi.php','prodi_tambah.php','prodi_edit.php']) ? 'active' : ''; ?>" href="prodi.php">
                        <i class="bi bi-journal-bookmark me-2"></i>Program Studi
                    </a>
                </li>

                <li class="nav-item mt-3 mb-1">
                    <span class="sidebar-section-title text-white-50 ms-1">Akun</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="col-md-10 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="page-title mb-1">Tambah Program Studi</h2>
                    <p class="breadcrumb-text text-muted mb-0">
                        Tambahkan data jurusan/program studi baru yang akan digunakan oleh mahasiswa dan dosen.
                    </p>
                </div>
                <div>
                    <a href="prodi.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Program Studi
                    </a>
                </div>
            </div>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card section-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-journal-plus me-2 text-success"></i>Form Tambah Program Studi
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode Prodi (opsional)</label>
                            <input type="text" name="kode_prodi" class="form-control"
                                   value="<?= isset($_POST['kode_prodi']) ? htmlspecialchars($_POST['kode_prodi']) : ''; ?>"
                                   placeholder="Contoh: HES, HPI, dll">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama Program Studi</label>
                            <input type="text" name="nama_prodi" class="form-control"
                                   value="<?= isset($_POST['nama_prodi']) ? htmlspecialchars($_POST['nama_prodi']) : ''; ?>"
                                   placeholder="Contoh: Hukum Ekonomi Syariah" required>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-save me-2"></i>Simpan Prodi
                            </button>
                            <a href="prodi.php" class="btn btn-secondary ms-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
