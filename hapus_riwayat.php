<?php
session_start();

// Keamanan: Pastikan yang mengakses adalah mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

// Gunakan config.php untuk koneksi database
require_once 'config.php';

// Ambil parameter
$idlogbook = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nim_mahasiswa_login = $_SESSION['user_id'];

// Validasi parameter
if ($idlogbook == 0) {
    $_SESSION['pesan_error'] = '❌ Parameter tidak valid.';
    header("Location: dashboard_mahasiswa.php");
    exit();
}

// Verifikasi bahwa logbook ini milik mahasiswa yang login
// GANTI idlog → id (atau nama kolom yang benar)
$stmt_verify = $conn->prepare("
    SELECT id_log, topik_bimbingan 
    FROM logbook 
    WHERE id_log = ? AND nim_mahasiswa = ? AND pengisi = 'Mahasiswa'
");
$stmt_verify->bind_param('is', $idlogbook, $nim_mahasiswa_login);
$stmt_verify->execute();
$result_verify = $stmt_verify->get_result();

if ($result_verify->num_rows == 0) {
    $_SESSION['pesan_error'] = '❌ Anda tidak memiliki hak akses untuk menghapus catatan ini.';
    $stmt_verify->close();
    $conn->close();
    header("Location: dashboard_mahasiswa.php");
    exit();
}

$data = $result_verify->fetch_assoc();
$topik = $data['topik_bimbingan'];
$stmt_verify->close();

// Hapus logbook (GANTI idlog → id)
$stmt_delete = $conn->prepare("DELETE FROM logbook WHERE id_log = ?");
$stmt_delete->bind_param('i', $idlogbook);

if ($stmt_delete->execute()) {
    if ($stmt_delete->affected_rows > 0) {
        $_SESSION['pesan_sukses_logbook'] = '✅ Catatan bimbingan "' . htmlspecialchars($topik) . '" berhasil dihapus!';
    } else {
        $_SESSION['pesan_error'] = '⚠️ Data tidak ditemukan atau sudah dihapus.';
    }
} else {
    $_SESSION['pesan_error'] = '❌ Gagal menghapus catatan bimbingan. Error: ' . $conn->error;
}

$stmt_delete->close();
$conn->close();

// Redirect kembali ke dashboard
header("Location: dashboard_mahasiswa.php");
exit();
?>
