<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pastikan hanya admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil filter pencarian (jika ingin sama dengan mahasiswa.php)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = '';
$params = [];
$types  = '';

if ($search !== '') {
    $search_like = "%{$search}%";
    $where       = "WHERE m.nim LIKE ? 
                    OR m.nama_mahasiswa LIKE ? 
                    OR p.nama_prodi LIKE ?";
    $params      = [$search_like, $search_like, $search_like];
    $types       = "sss";
}

// Query ambil semua data (tanpa pagination)
$sql = "
    SELECT 
        m.nim,
        m.nama_mahasiswa,
        m.email,
        m.angkatan,
        p.nama_prodi,
        d.nama_dosen AS nama_dosen_pa
    FROM mahasiswa m
    LEFT JOIN program_studi p ON m.id_prodi = p.id_prodi
    LEFT JOIN dosen d ON m.id_dosen_pa = d.id_dosen
    $where
    ORDER BY m.nama_mahasiswa ASC
";

if ($where) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Header untuk file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=mahasiswa_smartba_' . date('Ymd_His') . '.csv');

// Buka output
$output = fopen('php://output', 'w');

// Tulis header kolom
fputcsv($output, [
    'NIM',
    'Nama Mahasiswa',
    'Email',
    'Angkatan',
    'Program Studi',
    'Dosen PA'
]);

// Tulis baris data
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['nim'],
            $row['nama_mahasiswa'],
            $row['email'],
            $row['angkatan'],
            $row['nama_prodi'],
            $row['nama_dosen_pa']
        ]);
    }
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
exit();
