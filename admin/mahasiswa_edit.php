<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Ambil NIM dari URL
if (!isset($_GET['nim'])) {
    header("Location: mahasiswa.php");
    exit();
}

$nim = $_GET['nim'];

// Ambil data mahasiswa
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Data mahasiswa tidak ditemukan.";
    header("Location: mahasiswa.php");
    exit();
}

$mahasiswa = $result->fetch_assoc();
$stmt->close();

// Ambil daftar prodi
$prodi_result = $conn->query("SELECT id_prodi, nama_prodi FROM program_studi ORDER BY nama_prodi ASC");

// Ambil daftar dosen
$dosen_result = $conn->query("SELECT id_dosen, nama_dosen, nidn_dosen FROM dosen ORDER BY nama_dosen ASC");

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = trim($_POST['nama_mahasiswa']);
    $email       = trim($_POST['email_mahasiswa']);
    $angkatan    = trim($_POST['angkatan']);
    $id_prodi    = $_POST['id_prodi'] !== '' ? $_POST['id_prodi'] : null;
    $id_dosen_pa = $_POST['id_dosen_pa'] !== '' ? $_POST['id_dosen_pa'] : null;

    if ($nama === '' || $email === '' || $angkatan === '') {
        $_SESSION['error'] = "Nama, email, dan angkatan wajib diisi.";
    } else {
        $update = $conn->prepare("
            UPDATE mahasiswa 
            SET nama_mahasiswa = ?, email = ?, angkatan = ?, id_prodi = ?, id_dosen_pa = ?
            WHERE nim = ?
        ");
        $update->bind_param(
            "sssiss",
            $nama,
            $email,
            $angkatan,
            $id_prodi,
            $id_dosen_pa,
            $nim
        );

        if ($update->execute()) {
            $_SESSION['success'] = "Data mahasiswa berhasil diperbarui.";
            $update->close();
            header("Location: mahasiswa.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal memperbarui data: " . $update->error;
            $update->close();
        }
    }

    // Refresh data mahasiswa dari POST agar form tetap menampilkan input terakhir
    $mahasiswa['nama_mahasiswa']  = $nama;
    $mahasiswa['email']           = $email;
    $mahasiswa['angkatan']        = $angkatan;
    $mahasiswa['id_prodi']        = $id_prodi;
    $mahasiswa['id_dosen_pa']     = $id_dosen_pa;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="../assets/logo_uin.png">
    <meta charset="UTF-8">
    <title>Edit Mahasiswa - Admin SMART-BA</title>
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
                    <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['dosen.php','dosen_tambah.php','dosen_edit.php','dosen_detail.php']) ? 'active' : ''; ?>" href="dosen.php">
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
                    <h2 class="page-title mb-1">Edit Mahasiswa</h2>
                    <p class="breadcrumb-text text-muted mb-0">
                        Perbarui data mahasiswa untuk menjaga informasi akademik tetap akurat.
                    </p>
                </div>
                <div>
                    <a href="mahasiswa.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Data Mahasiswa
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
                        <i class="bi bi-pencil-square me-2 text-success"></i>Form Edit Mahasiswa
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIM</label>
                            <input type="text" class="form-control"
                                   value="<?= htmlspecialchars($mahasiswa['nim']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Mahasiswa</label>
                            <input type="text" name="nama_mahasiswa" class="form-control"
                                   value="<?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_mahasiswa" class="form-control"
                                   value="<?= htmlspecialchars($mahasiswa['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Angkatan</label>
                            <input type="text" name="angkatan" class="form-control"
                                   value="<?= htmlspecialchars($mahasiswa['angkatan']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Program Studi</label>
                            <select name="id_prodi" class="form-select">
                                <option value="">-- Pilih Prodi --</option>
                                <?php if ($prodi_result && $prodi_result->num_rows > 0): ?>
                                    <?php while ($prodi = $prodi_result->fetch_assoc()): ?>
                                        <option value="<?= $prodi['id_prodi']; ?>"
                                            <?= ($mahasiswa['id_prodi'] == $prodi['id_prodi']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($prodi['nama_prodi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Dosen PA</label>
                            <select name="id_dosen_pa" class="form-select">
                                <option value="">-- Pilih Dosen PA --</option>
                                <?php if ($dosen_result && $dosen_result->num_rows > 0): ?>
                                    <?php while ($dosen = $dosen_result->fetch_assoc()): ?>
                                        <option value="<?= $dosen['id_dosen']; ?>"
                                            <?= ($mahasiswa['id_dosen_pa'] == $dosen['id_dosen']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($dosen['nama_dosen']); ?>
                                            (<?= htmlspecialchars($dosen['nidn_dosen']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="mahasiswa.php" class="btn btn-secondary ms-2">Batal</a>
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
