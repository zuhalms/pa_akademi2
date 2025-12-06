<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

if (!isset($_GET['nim'])) {
    $_SESSION['error'] = "NIM tidak ditemukan.";
    header("Location: mahasiswa.php");
    exit();
}

$nim = $_GET['nim'];

// Optional: cek dulu datanya ada atau tidak
$stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Data mahasiswa tidak ditemukan.";
    $stmt->close();
    header("Location: mahasiswa.php");
    exit();
}
$stmt->close();

// Hapus data
$delete = $conn->prepare("DELETE FROM mahasiswa WHERE nim = ?");
$delete->bind_param("s", $nim);

if ($delete->execute()) {
    $_SESSION['success'] = "Mahasiswa dengan NIM $nim berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus mahasiswa: " . $delete->error;
}
$delete->close();

header("Location: mahasiswa.php");
exit();
