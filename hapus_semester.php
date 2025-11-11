<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    exit('Unauthorized');
}

$nim = $_SESSION['user_id'];
$semester = intval($_GET['semester']);

$stmt = $conn->prepare("DELETE FROM riwayat_akademik WHERE nim_mahasiswa = ? AND semester = ?");
$stmt->bind_param("si", $nim, $semester);
$stmt->execute();
$stmt->close();

header("Location: input_riwayat.php");
exit();
?>
