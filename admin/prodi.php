<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pagination
$limit  = 10;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = "";
if ($search !== '') {
    $search_esc = $conn->real_escape_string($search);
    $where      = "WHERE kode_prodi LIKE '%$search_esc%' 
                   OR nama_prodi LIKE '%$search_esc%'";
}

// Get total records
$total_query  = "SELECT COUNT(*) as total FROM program_studi $where";
$total_result = $conn->query($total_query);
$total_rows   = $total_result ? ($total_result->fetch_assoc()['total'] ?? 0) : 0;
$total_pages  = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

// Get prodi data
$query = "SELECT * FROM program_studi
          $where
          ORDER BY nama_prodi ASC
          LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// Alert message from session
$success_message = $_SESSION['success'] ?? '';
$error_message   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="../assets/logo_uin.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Studi - Admin SMART-BA</title>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="page-title mb-1">Program Studi</h2>
                    <p class="breadcrumb-text text-muted mb-0">
                        Kelola data jurusan/program studi yang digunakan oleh mahasiswa dan dosen.
                    </p>
                </div>
                <div>
                    <a href="prodi_tambah.php" class="btn btn-brand">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Prodi
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
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

            <!-- Card utama -->
            <div class="card section-card">
                <div class="card-header bg-white">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0"><i class="bi bi-funnel me-2 text-success"></i>Filter & Pencarian</h6>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted">
                                Total data: <?= $total_rows; ?> prodi
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" class="row g-3 mb-3">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Cari berdasarkan kode atau nama prodi..."
                                   value="<?= htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-brand w-100">
                                <i class="bi bi-search me-2"></i>Cari
                            </button>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Prodi</th>
                                    <th>Nama Prodi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php $no = $offset + 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['kode_prodi'] ?? ''); ?></td>
                                            <td><?= htmlspecialchars($row['nama_prodi']); ?></td>
                                            <td>
                                                <a href="prodi_edit.php?id=<?= urlencode($row['id_prodi']); ?>"
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="prodi_hapus.php?id=<?= urlencode($row['id_prodi']); ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Yakin ingin menghapus program studi ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data program studi</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>">
                                            <?= $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <p class="text-muted text-center mb-0">
                        Menampilkan <?= $total_rows ? min($offset + 1, $total_rows) : 0; ?> - <?= min($offset + $limit, $total_rows); ?>
                        dari <?= $total_rows; ?> data
                    </p>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
