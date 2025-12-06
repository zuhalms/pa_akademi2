<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pastikan hanya admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// (Opsional) ambil parameter search jika ingin nanti disaring
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = '';
$params = [];
$types  = '';

if ($search !== '') {
    $search_like = "%{$search}%";
    $where       = "WHERE d.nidn_dosen LIKE ? 
                    OR d.nip LIKE ? 
                    OR d.nama_dosen LIKE ?";
    $params      = [$search_like, $search_like, $search_like];
    $types       = "sss";
}

$sql = "
    SELECT 
        d.nidn_dosen,
        d.nip,
        d.nama_dosen,
        d.email_dosen,
        d.telp_dosen,
        d.jabatan_akademik,
        d.status_dosen
    FROM dosen d
    $where
    ORDER BY d.nama_dosen ASC
";

if ($where) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Header file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=dosen_smartba_' . date('Ymd_His') . '.csv');

// Buka output
$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, [
    'NIDN',
    'NIP',
    'Nama Dosen',
    'Email',
    'Telepon',
    'Jabatan Akademik',
    'Status'
]);

// Data baris
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['nidn_dosen'],
            $row['nip'],
            $row['nama_dosen'],
            $row['email_dosen'],
            $row['telp_dosen'],
            $row['jabatan_akademik'],
            $row['status_dosen']
        ]);
    }
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
exit();
