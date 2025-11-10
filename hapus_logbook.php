<?php
// hapus_logbook.php - Menangani penghapusan logbook bimbingan

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan yang mengakses adalah dosen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

// Gunakan config.php untuk koneksi database
require_once 'config.php';

// Ambil parameter
$id_logbook = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nim_mahasiswa = isset($_GET['nim']) ? $_GET['nim'] : '';
$id_dosen_login = $_SESSION['user_id'];

// Validasi parameter
if ($id_logbook <= 0 || empty($nim_mahasiswa)) {
    $_SESSION['pesan_error'] = "Parameter tidak valid.";
    header("Location: dashboard_dosen.php");
    exit();
}

// Verifikasi bahwa logbook ini milik dosen yang login dan mahasiswa bimbingannya
$stmt_verify = $conn->prepare("SELECT l.id_log FROM logbook l 
                                JOIN mahasiswa m ON l.nim_mahasiswa = m.nim 
                                WHERE l.id_log = ? 
                                AND l.nim_mahasiswa = ? 
                                AND m.id_dosen_pa = ?");
$stmt_verify->bind_param("isi", $id_logbook, $nim_mahasiswa, $id_dosen_login);
$stmt_verify->execute();
$result_verify = $stmt_verify->get_result();

if ($result_verify->num_rows === 0) {
    $_SESSION['pesan_error'] = "Anda tidak memiliki hak akses untuk menghapus logbook ini.";
    $stmt_verify->close();
    $conn->close();
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa));
    exit();
}
$stmt_verify->close();

// Hapus logbook
$stmt_delete = $conn->prepare("DELETE FROM logbook WHERE id_log = ?");
$stmt_delete->bind_param("i", $id_logbook);

if ($stmt_delete->execute()) {
    $_SESSION['pesan_sukses'] = "Riwayat bimbingan berhasil dihapus!";
} else {
    $_SESSION['pesan_error'] = "Gagal menghapus riwayat bimbingan. Error: " . $conn->error;
}

$stmt_delete->close();
$conn->close();

// Redirect kembali ke detail mahasiswa
header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa));
exit();
?>