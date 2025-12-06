<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID dosen tidak ditemukan.";
    header("Location: dosen.php");
    exit();
}

$id = (int)$_GET['id'];

// Cek apakah dosen ada
$stmt = $conn->prepare("SELECT nama_dosen FROM dosen WHERE id_dosen = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    $_SESSION['error'] = "Data dosen tidak ditemukan.";
    $stmt->close();
    header("Location: dosen.php");
    exit();
}

$dosen = $res->fetch_assoc();
$stmt->close();

// Cek relasi dengan mahasiswa (dosen PA)
$cek_rel = $conn->prepare("SELECT COUNT(*) as jml FROM mahasiswa WHERE id_dosen_pa = ?");
$cek_rel->bind_param("i", $id);
$cek_rel->execute();
$rel_res = $cek_rel->get_result();
$jml_rel  = $rel_res ? ($rel_res->fetch_assoc()['jml'] ?? 0) : 0;
$cek_rel->close();

if ($jml_rel > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus dosen karena masih menjadi dosen PA untuk $jml_rel mahasiswa.";
    header("Location: dosen.php");
    exit();
}

// Jika aman, hapus dosen
$del = $conn->prepare("DELETE FROM dosen WHERE id_dosen = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    $_SESSION['success'] = "Dosen \"" . $dosen['nama_dosen'] . "\" berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus dosen: " . $del->error;
}
$del->close();

header("Location: dosen.php");
exit();
