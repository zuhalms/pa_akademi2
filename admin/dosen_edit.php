<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Ambil id_dosen dari URL
if (!isset($_GET['id'])) {
    header("Location: dosen.php");
    exit();
}

$id = (int)$_GET['id'];

// Ambil data dosen
$stmt = $conn->prepare("SELECT * FROM dosen WHERE id_dosen = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Data dosen tidak ditemukan.";
    header("Location: dosen.php");
    exit();
}

$dosen = $result->fetch_assoc();
$stmt->close();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nidn_dosen         = trim($_POST['nidn_dosen']);
    $nip                = trim($_POST['nip']);
    $nama_dosen         = trim($_POST['nama_dosen']);
    $email_dosen        = trim($_POST['email_dosen']);
    $telp_dosen         = trim($_POST['telp_dosen']);
    $tempat_lahir       = trim($_POST['tempat_lahir']);
    $tgl_lahir          = trim($_POST['tgl_lahir']);
    $jenis_kelamin      = trim($_POST['jenis_kelamin']);
    $alamat             = trim($_POST['alamat']);
    $pendidikan_terakhir= trim($_POST['pendidikan_terakhir']);
    $bidang_keahlian    = trim($_POST['bidang_keahlian']);
    $jabatan_akademik   = trim($_POST['jabatan_akademik']);
    $status_dosen       = trim($_POST['status_dosen']);

    // Validasi
    if ($nidn_dosen === '' || $nama_dosen === '' || $email_dosen === '') {
        $_SESSION['error'] = "NIDN, nama, dan email dosen wajib diisi.";
    } else {
        // Cek NIDN duplikat (kecuali dirinya sendiri)
        $cek = $conn->prepare("SELECT id_dosen FROM dosen WHERE nidn_dosen = ? AND id_dosen <> ?");
        $cek->bind_param("si", $nidn_dosen, $id);
        $cek->execute();
        $cek_res = $cek->get_result();
        if ($cek_res->num_rows > 0) {
            $_SESSION['error'] = "NIDN sudah digunakan oleh dosen lain.";
        } else {
            $update = $conn->prepare("UPDATE dosen SET
                nidn_dosen = ?, nip = ?, nama_dosen = ?, email_dosen = ?, telp_dosen = ?,
                tempat_lahir = ?, tgl_lahir = ?, jenis_kelamin = ?, alamat = ?,
                pendidikan_terakhir = ?, bidang_keahlian = ?, jabatan_akademik = ?,
                status_dosen = ?
                WHERE id_dosen = ?");
            $update->bind_param(
                "sssssssssssssi",
                $nidn_dosen,
                $nip,
                $nama_dosen,
                $email_dosen,
                $telp_dosen,
                $tempat_lahir,
                $tgl_lahir,
                $jenis_kelamin,
                $alamat,
                $pendidikan_terakhir,
                $bidang_keahlian,
                $jabatan_akademik,
                $status_dosen,
                $id
            );

            if ($update->execute()) {
                $_SESSION['success'] = "Data dosen berhasil diperbarui.";
                $update->close();
                header("Location: dosen.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal memperbarui data dosen: " . $update->error;
                $update->close();
            }
        }
        $cek->close();
    }

    // Jika gagal, isi array $dosen dengan nilai terbaru dari POST
    $dosen['nidn_dosen']          = $nidn_dosen;
    $dosen['nip']                 = $nip;
    $dosen['nama_dosen']          = $nama_dosen;
    $dosen['email_dosen']         = $email_dosen;
    $dosen['telp_dosen']          = $telp_dosen;
    $dosen['tempat_lahir']        = $tempat_lahir;
    $dosen['tgl_lahir']           = $tgl_lahir;
    $dosen['jenis_kelamin']       = $jenis_kelamin;
    $dosen['alamat']              = $alamat;
    $dosen['pendidikan_terakhir'] = $pendidikan_terakhir;
    $dosen['bidang_keahlian']     = $bidang_keahlian;
    $dosen['jabatan_akademik']    = $jabatan_akademik;
    $dosen['status_dosen']        = $status_dosen;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Dosen - Admin SMART-BA</title>
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
                    <h2 class="page-title mb-1">Edit Dosen</h2>
                    <p class="breadcrumb-text text-muted mb-0">
                        Perbarui data dosen untuk menjaga informasi pembimbingan tetap akurat.
                    </p>
                </div>
                <div>
                    <a href="dosen.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Data Dosen
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
                        <i class="bi bi-pencil-square me-2 text-success"></i>Form Edit Dosen
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">ID Dosen</label>
                            <input type="text" name="nidn_dosen" class="form-control"
                                   value="<?= htmlspecialchars($dosen['nidn_dosen']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control"
                                   value="<?= htmlspecialchars($dosen['nip']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Dosen</label>
                            <input type="text" name="nama_dosen" class="form-control"
                                   value="<?= htmlspecialchars($dosen['nama_dosen']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_dosen" class="form-control"
                                   value="<?= htmlspecialchars($dosen['email_dosen']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telp_dosen" class="form-control"
                                   value="<?= htmlspecialchars($dosen['telp_dosen']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Dosen</label>
                            <select name="status_dosen" class="form-select">
                                <?php
                                $statuses = ['Tetap','Kontrak','LB'];
                                foreach ($statuses as $st):
                                ?>
                                    <option value="<?= $st; ?>" <?= $dosen['status_dosen'] == $st ? 'selected' : ''; ?>>
                                        <?= $st; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control"
                                   value="<?= htmlspecialchars($dosen['tempat_lahir']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control"
                                   value="<?= htmlspecialchars($dosen['tgl_lahir']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">- Pilih -</option>
                                <option value="Laki-laki" <?= $dosen['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= $dosen['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" name="pendidikan_terakhir" class="form-control"
                                   value="<?= htmlspecialchars($dosen['pendidikan_terakhir']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bidang Keahlian</label>
                            <input type="text" name="bidang_keahlian" class="form-control"
                                   value="<?= htmlspecialchars($dosen['bidang_keahlian']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Jabatan Akademik</label>
                            <input type="text" name="jabatan_akademik" class="form-control"
                                   value="<?= htmlspecialchars($dosen['jabatan_akademik']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($dosen['alamat']); ?></textarea>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="dosen.php" class="btn btn-secondary ms-2">Batal</a>
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
