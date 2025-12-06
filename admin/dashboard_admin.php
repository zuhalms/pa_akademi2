<?php
session_start();
require_once '../config.php';

// Proteksi admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil statistik dasar
$total_mahasiswa = $conn->query("SELECT COUNT(*) as total FROM mahasiswa")->fetch_assoc()['total'] ?? 0;
$total_dosen     = $conn->query("SELECT COUNT(*) as total FROM dosen")->fetch_assoc()['total'] ?? 0;

// Jika tabel krs memang ada:
$total_krs_pending = 0;
$result_krs = $conn->query("SELECT COUNT(*) as total FROM krs WHERE status = 'pending'");
if ($result_krs) {
    $row_krs = $result_krs->fetch_assoc();
    $total_krs_pending = $row_krs['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="../assets/logo_uin.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMART-BA</title>
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
        .card-stat {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(15,23,42,.08);
            transition: transform .15s ease, box-shadow .15s ease;
            position: relative;
            overflow: hidden;
        }
        .card-stat::after {
            content: "";
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 999px;
            background: rgba(255,255,255,.15);
            top: -40px;
            right: -40px;
        }
        .card-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(15,23,42,.12);
        }
        .card-stat .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            background: rgba(255,255,255,.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .page-title {
            font-weight: 700;
        }
        .breadcrumb-text {
            font-size: .85rem;
        }
        .badge-role {
            background-color: #ecfdf5;
            color: #15803d;
            border: 1px solid #bbf7d0;
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
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
<!-- Sidebar -->
<aside class="col-md-2 col-lg-2 sidebar-admin text-white min-vh-100 p-3">
    <div class="d-flex align-items-center mb-4">
        <div class="me-2">
            <img src="../assets/logo_uin.png" alt="Logo UIN" style="height:40px; width:auto;">
        </div>
        <div>
            <div class="brand-title">SMART-BA</div>
            <small class="brand-title">Fakultas Syariah</small>
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
            <span class="sidebar-section-title text-white-50 ms-1">Export Data</span>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="export_mahasiswa.php">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export Mahasiswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="export_dosen.php">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export Dosen
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
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2 class="page-title mb-1">Dashboard Admin</h2>
                    <p class="breadcrumb-text text-muted mb-0">
                        Selamat datang, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong> di panel SMART-BA 
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge badge-role">
                        <i class="bi bi-shield-check me-1"></i> Role: Admin
                    </span>
                </div>
            </div>

            <hr class="mb-4">

            <div class="row g-4">
                <!-- Total Mahasiswa -->
                <div class="col-md-4">
                    <div class="card card-stat text-white" style="background: linear-gradient(135deg,#22c55e,#15803d);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Mahasiswa</h6>
                                <h2 class="mb-0"><?= $total_mahasiswa; ?></h2>
                                <small class="text-white-50">Aktif dalam sistem pembinaan</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Dosen -->
                <div class="col-md-4">
                    <div class="card card-stat text-white" style="background: linear-gradient(135deg,#16a34a,#166534);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Dosen</h6>
                                <h2 class="mb-0"><?= $total_dosen; ?></h2>
                                <small class="text-white-50">Pembimbing akademik & PA</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-person-badge-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KRS Pending -->
                <div class="col-md-4">
                    <div class="card card-stat text-white" style="background: linear-gradient(135deg,#facc15,#eab308);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">KRS Menunggu Approval</h6>
                                <h2 class="mb-0"><?= $total_krs_pending; ?></h2>
                                <small class="text-white-50">Perlu tindak lanjut dosen PA</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <div class="card section-card">
                        <div class="card-header d-flex align-items-center bg-white">
                            <i class="bi bi-people text-success me-2"></i>
                            <h6 class="mb-0">Ringkasan Mahasiswa</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">
                                Di sini nantinya Anda bisa menampilkan daftar mahasiswa terbaru, distribusi mahasiswa per program studi, 
                                dan informasi lain yang mendukung monitoring kampus hijau dan cerdas.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card section-card">
                        <div class="card-header d-flex align-items-center bg-white">
                            <i class="bi bi-person-workspace text-success me-2"></i>
                            <h6 class="mb-0">Ringkasan Dosen</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">
                                Panel ini dapat diisi informasi dosen PA teraktif, rata-rata jumlah bimbingan, 
                                serta aktivitas akademik lain yang mendukung ekosistem kampus yang berkelanjutan.
                            </p>
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
