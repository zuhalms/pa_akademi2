<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pastikan hanya admin yang bisa
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Pastikan id dosen ada
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID dosen tidak ditemukan.";
    header("Location: dosen.php");
    exit();
}

$id_dosen = (int)$_GET['id'];

// Cek dosen ada atau tidak
$stmt = $conn->prepare("SELECT id_dosen, nama_dosen, nidn_dosen FROM dosen WHERE id_dosen = ?");
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

// Password default = NIDN tanpa spasi (sesuai pola login)
$nidn_clean    = str_replace(' ', '', $dosen['nidn_dosen']);
$password_hash = password_hash($nidn_clean, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE dosen SET password = ? WHERE id_dosen = ?");
$update->bind_param("si", $password_hash, $id_dosen);

if ($update->execute()) {
    $_SESSION['success'] = "Password untuk dosen {$dosen['nama_dosen']} (NIDN: {$dosen['nidn_dosen']}) berhasil direset ke NIDN.";
} else {
    $_SESSION['error'] = "Gagal mereset password dosen: " . $update->error;
}
$update->close();

header("Location: dosen.php");
exit();
