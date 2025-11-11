<?php
session_start();
require_once 'config.php';

// Set header JSON
header('Content-Type: application/json');

// Validasi session mahasiswa
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Session invalid']);
    exit();
}

// Validasi POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['semester'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$nim_mahasiswa_login = $_SESSION['user_id'];
$semester = intval($_POST['semester']);

// Validate semester
if ($semester < 1 || $semester > 14) {
    echo json_encode(['success' => false, 'message' => 'Invalid semester number']);
    exit();
}

// DELETE query
$stmt_delete = $conn->prepare("DELETE FROM riwayat_akademik WHERE nim_mahasiswa = ? AND semester = ?");
$stmt_delete->bind_param("si", $nim_mahasiswa_login, $semester);

if ($stmt_delete->execute()) {
    if ($stmt_delete->affected_rows > 0) {
        // Recalculate IPK
        $stmt_calc = $conn->prepare("
            SELECT ip_semester, sks_semester 
            FROM riwayat_akademik 
            WHERE nim_mahasiswa = ? AND ip_semester > 0 AND sks_semester > 0
        ");
        $stmt_calc->bind_param("s", $nim_mahasiswa_login);
        $stmt_calc->execute();
        $result_calc = $stmt_calc->get_result();
        
        $total_sks = 0;
        $total_bobot = 0;
        
        while ($row = $result_calc->fetch_assoc()) {
            $total_sks += $row['sks_semester'];
            $total_bobot += ($row['ip_semester'] * $row['sks_semester']);
        }
        $stmt_calc->close();
        
        $ipk_final = ($total_sks > 0) ? round($total_bobot / $total_sks, 2) : 0;
        
        // Update mahasiswa table
        $stmt_update = $conn->prepare("UPDATE mahasiswa SET ipk = ?, total_sks = ? WHERE nim = ?");
        $stmt_update->bind_param("dis", $ipk_final, $total_sks, $nim_mahasiswa_login);
        $stmt_update->execute();
        $stmt_update->close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Data semester ' . $semester . ' berhasil dihapus!',
            'ipk' => $ipk_final,
            'total_sks' => $total_sks
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data semester ' . $semester . ' tidak ditemukan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt_delete->error]);
}

$stmt_delete->close();
$conn->close();
?>
