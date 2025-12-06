<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pastikan ada id dosen
if (!isset($_GET['id'])) {
    header("Location: dosen.php");
    exit();
}

$id_dosen = (int)$_GET['id'];

// Ambil data dosen + statistik bimbingan
$stmt = $conn->prepare("
    SELECT 
        d.*,
        COUNT(m.nim) AS jumlah_bimbingan,
        GROUP_CONCAT(DISTINCT p.nama_prodi ORDER BY p.nama_prodi SEPARATOR ', ') AS prodi_bimbingan
    FROM dosen d
    LEFT JOIN mahasiswa m ON m.id_dosen_pa = d.id_dosen
    LEFT JOIN program_studi p ON m.id_prodi = p.id_prodi
    WHERE d.id_dosen = ?
    GROUP BY d.id_dosen
");
$stmt->bind_param("i", $id_dosen);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $_SESSION['error'] = "Data dosen tidak ditemukan.";
    header("Location: dosen.php");
    exit();
}

$dosen = $result->fetch_assoc();
$stmt->close();

// Ambil daftar mahasiswa bimbingan
$stmt_mhs = $conn->prepare("
    SELECT 
        m.nim,
        m.nama_mahasiswa,
        m.angkatan,
        p.nama_prodi
    FROM mahasiswa m
    JOIN program_studi p ON m.id_prodi = p.id_prodi
    WHERE m.id_dosen_pa = ?
    ORDER BY m.nama_mahasiswa ASC
");
$stmt_mhs->bind_param("i", $id_dosen);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();

$success_message = $_SESSION['success'] ?? '';
$error_message   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Dosen - Admin SMART-BA</title>
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
        .avatar-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #dcfce7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #166534;
            font-weight: 700;
        }
        .badge-soft {
            background-color: #ecfdf3;
            color: #166534;
            font-weight: 500;
            border-radius: 999px;
            padding: .25rem .75rem;
            font-size: .8rem;
        }
        .table thead {
            background-color: #ecfdf3;
        }
        .table thead th {
            border-bottom: 0;
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
                    <h2 class="page-title mb-1">Detail Dosen</h2>
                    <p class="breadcrumb-text text-muted mb-0">
                        Profil lengkap dan daftar mahasiswa bimbingan dosen.
                    </p>
                </div>
                <div>
                    <a href="dosen.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Data Dosen
                    </a>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Profil Dosen -->
                <div class="col-lg-4">
                    <div class="card section-card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle me-3">
                                    <?= strtoupper(substr($dosen['nama_dosen'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($dosen['nama_dosen']); ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($dosen['jabatan_akademik']); ?></small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="badge-soft">
                                    <i class="bi bi-people me-1"></i>
                                    <?= (int)$dosen['jumlah_bimbingan']; ?> Mahasiswa bimbingan
                                </span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Prodi bimbingan:</small>
                                <span>
                                    <?= htmlspecialchars($dosen['prodi_bimbingan'] ?? '-'); ?>
                                </span>
                            </div>

                            <hr>
                            <dl class="row mb-0">
                                <dt class="col-5">ID Dosen (NIDN)</dt>
                                <dd class="col-7"><?= htmlspecialchars($dosen['nidn_dosen']); ?></dd>

                                <dt class="col-5">NIP</dt>
                                <dd class="col-7"><?= htmlspecialchars($dosen['nip']); ?></dd>

                                <dt class="col-5">Email</dt>
                                <dd class="col-7"><?= htmlspecialchars($dosen['email_dosen']); ?></dd>

                                <dt class="col-5">Telepon</dt>
                                <dd class="col-7"><?= htmlspecialchars($dosen['telp_dosen']); ?></dd>

                                <dt class="col-5">Status</dt>
                                <dd class="col-7"><?= htmlspecialchars($dosen['status_dosen']); ?></dd>
                            </dl>

                            <div class="mt-3 d-flex gap-2">
                                <a href="dosen_edit.php?id=<?= urlencode($dosen['id_dosen']); ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil me-1"></i>Edit Profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Mahasiswa Bimbingan -->
                <div class="col-lg-8">
                    <div class="card section-card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-people me-2 text-success"></i>Daftar Mahasiswa Bimbingan
                            </h6>
                            <small class="text-muted">
                                Total: <?= (int)$dosen['jumlah_bimbingan']; ?> mahasiswa
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIM</th>
                                            <th>Nama</th>
                                            <th>Prodi</th>
                                            <th>Angkatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result_mhs && $result_mhs->num_rows > 0): ?>
                                            <?php $no = 1; ?>
                                            <?php while ($m = $result_mhs->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><?= htmlspecialchars($m['nim']); ?></td>
                                                    <td><?= htmlspecialchars($m['nama_mahasiswa']); ?></td>
                                                    <td><?= htmlspecialchars($m['nama_prodi']); ?></td>
                                                    <td><?= htmlspecialchars($m['angkatan']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    Belum ada mahasiswa bimbingan yang terdaftar.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt_mhs->close();
$conn->close();
?>
