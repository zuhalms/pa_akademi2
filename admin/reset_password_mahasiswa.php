<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pastikan hanya admin yang bisa
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Pastikan NIM ada
if (!isset($_GET['nim'])) {
    $_SESSION['error'] = "NIM mahasiswa tidak ditemukan.";
    header("Location: mahasiswa.php");
    exit();
}

$nim = $_GET['nim'];

// Cek mahasiswa ada atau tidak
$stmt = $conn->prepare("SELECT nim, nama_mahasiswa FROM mahasiswa WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $_SESSION['error'] = "Data mahasiswa dengan NIM tersebut tidak ditemukan.";
    header("Location: mahasiswa.php");
    exit();
}

$mhs = $result->fetch_assoc();
$stmt->close();

// Reset password ke NIM (hash)
$password_hash = password_hash($mhs['nim'], PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE mahasiswa SET password = ? WHERE nim = ?");
$update->bind_param("ss", $password_hash, $mhs['nim']);

if ($update->execute()) {
    $_SESSION['success'] = "Password untuk mahasiswa {$mhs['nama_mahasiswa']} (NIM: {$mhs['nim']}) berhasil direset ke NIM.";
} else {
    $_SESSION['error'] = "Gagal mereset password mahasiswa: " . $update->error;
}
$update->close();

header("Location: mahasiswa.php");
exit();
