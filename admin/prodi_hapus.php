<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID program studi tidak ditemukan.";
    header("Location: prodi.php");
    exit();
}

$id = (int)$_GET['id'];

// Cek apakah prodi ada
$stmt = $conn->prepare("SELECT nama_prodi FROM program_studi WHERE id_prodi = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    $_SESSION['error'] = "Data program studi tidak ditemukan.";
    $stmt->close();
    header("Location: prodi.php");
    exit();
}

$prodi = $res->fetch_assoc();
$stmt->close();

// Cek relasi dengan mahasiswa
$cek_mhs = $conn->prepare("SELECT COUNT(*) AS jml FROM mahasiswa WHERE id_prodi = ?");
$cek_mhs->bind_param("i", $id);
$cek_mhs->execute();
$mhs_res = $cek_mhs->get_result();
$jml_mhs  = $mhs_res ? ($mhs_res->fetch_assoc()['jml'] ?? 0) : 0;
$cek_mhs->close();

// (Opsional) jika nanti dosen punya kolom id_prodi_homebase, bisa dicek juga di sini
// $cek_dsn = $conn->prepare("SELECT COUNT(*) AS jml FROM dosen WHERE id_prodi_homebase = ?");
// ...

if ($jml_mhs > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus prodi karena masih digunakan oleh $jml_mhs mahasiswa.";
    header("Location: prodi.php");
    exit();
}

// Jika aman, hapus prodi
$del = $conn->prepare("DELETE FROM program_studi WHERE id_prodi = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    $_SESSION['success'] = "Program studi \"" . $prodi['nama_prodi'] . "\" berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus program studi: " . $del->error;
}
$del->close();

header("Location: prodi.php");
exit();
