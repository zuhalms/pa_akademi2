<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

$page_title = 'Edit Riwayat Mahasiswa';

// Gunakan config.php
require_once 'config.php';
require 'templates/header.php';

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id_dosen = $_SESSION['user_id'];
$nim = $_GET['nim'] ?? '';
$message = '';
$message_type = 'success';

// Ambil daftar mahasiswa bimbingan
$stmt = $conn->prepare("SELECT nim, nama_mahasiswa FROM mahasiswa WHERE id_dosen_pa = ? ORDER BY nama_mahasiswa ASC");
$stmt->bind_param('i', $id_dosen);
$stmt->execute();
$list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// PROSES DELETE (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_semester') {
    $nim_delete = $_POST['nim'];
    $semester = intval($_POST['semester']);
    
    $stmt_delete = $conn->prepare("DELETE FROM riwayat_akademik WHERE nim_mahasiswa = ? AND semester = ?");
    $stmt_delete->bind_param("si", $nim_delete, $semester);
    
    if ($stmt_delete->execute()) {
        // Recalculate IPK
        $stmt_calc = $conn->prepare("
            SELECT ip_semester, sks_semester 
            FROM riwayat_akademik 
            WHERE nim_mahasiswa = ? AND ip_semester > 0 AND sks_semester > 0
        ");
        $stmt_calc->bind_param("s", $nim_delete);
        $stmt_calc->execute();
        $result_calc = $stmt_calc->get_result();
        
        $total_sks = 0;
        $total_bobot = 0;
        
        while ($row = $result_calc->fetch_assoc()) {
            $total_sks += $row['sks_semester'];
            $total_bobot += ($row['ip_semester'] * $row['sks_semester']);
        }
        $stmt_calc->close();
        
        $ipk_final = ($total_sks > 0) ? ($total_bobot / $total_sks) : 0;
        
        $stmt_update = $conn->prepare("UPDATE mahasiswa SET ipk = ?, total_sks = ? WHERE nim = ?");
        $ipk_formatted = round($ipk_final, 2);
        $stmt_update->bind_param("dis", $ipk_formatted, $total_sks, $nim_delete);
        $stmt_update->execute();
        $stmt_update->close();
        
        echo json_encode(['success' => true, 'message' => 'Data semester ' . $semester . ' berhasil dihapus!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
    }
    $stmt_delete->close();
    $conn->close();
    exit();
}

// PROSES FORM SUBMIT (SAVE/UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nim_selected']) && !isset($_POST['action'])) {
    $nim_post = $_POST['nim_selected'];
    $ips_values = $_POST['ips'];
    $sks_values = $_POST['sks'];
    
    $stmt_upsert = $conn->prepare("
        INSERT INTO riwayat_akademik (nim_mahasiswa, semester, ip_semester, sks_semester) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            ip_semester = VALUES(ip_semester), 
            sks_semester = VALUES(sks_semester)
    ");
    
    if (!$stmt_upsert) {
        $message = "Error: " . $conn->error;
        $message_type = 'danger';
    } else {
        $data_saved = 0;
        $data_deleted = 0;
        
        // CARA 1: Auto-delete jika dikosongkan
        for ($i = 1; $i <= 14; $i++) {
            $ip_value = trim($ips_values[$i] ?? '');
            $sks_value = trim($sks_values[$i] ?? '');
            
            // Jika KEDUA field kosong -> DELETE
            if (empty($ip_value) && empty($sks_value)) {
                $stmt_del = $conn->prepare("DELETE FROM riwayat_akademik WHERE nim_mahasiswa = ? AND semester = ?");
                $stmt_del->bind_param("si", $nim_post, $i);
                if ($stmt_del->execute() && $stmt_del->affected_rows > 0) {
                    $data_deleted++;
                }
                $stmt_del->close();
                continue;
            }
            
            // Jika ada nilai -> UPSERT
            if (is_numeric($ip_value) && is_numeric($sks_value)) {
                $ip = (float)$ip_value;
                $sks = (int)$sks_value;
                
                if ($ip > 0 && $ip <= 4.0 && $sks >= 0 && $sks <= 24) {
                    $stmt_upsert->bind_param('sidi', $nim_post, $i, $ip, $sks);
                    if ($stmt_upsert->execute()) {
                        $data_saved++;
                    }
                }
            }
        }
        
        $stmt_upsert->close();
        
        if ($data_saved > 0 || $data_deleted > 0) {
            // AUTO-CALCULATE IPK
            $stmt_calc = $conn->prepare("
                SELECT ip_semester, sks_semester 
                FROM riwayat_akademik 
                WHERE nim_mahasiswa = ? AND ip_semester > 0 AND sks_semester > 0
            ");
            
            if ($stmt_calc) {
                $stmt_calc->bind_param('s', $nim_post);
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
                
                // UPDATE MAHASISWA TABLE
                $stmt_update = $conn->prepare("UPDATE mahasiswa SET ipk = ?, total_sks = ? WHERE nim = ?");
                
                if ($stmt_update) {
                    $stmt_update->bind_param('dis', $ipk_final, $total_sks, $nim_post);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
                
                $message = "✅ ";
                if ($data_saved > 0) $message .= "$data_saved semester disimpan. ";
                if ($data_deleted > 0) $message .= "$data_deleted semester dihapus. ";
                $message .= "IPK: $ipk_final | Total SKS: $total_sks";
                $message_type = 'success';
            }
        } else {
            $message = "⚠️ Tidak ada perubahan data.";
            $message_type = 'warning';
        }
        
        $nim = $nim_post;
    }
}

// Ambil data riwayat jika ada NIM yang dipilih
$riwayat = [];
if (!empty($nim)) {
    $stmt2 = $conn->prepare("SELECT semester, ip_semester, sks_semester FROM riwayat_akademik WHERE nim_mahasiswa = ?");
    $stmt2->bind_param('s', $nim);
    $stmt2->execute();
    $res = $stmt2->get_result();
    while ($r = $res->fetch_assoc()) {
        $riwayat[$r['semester']] = $r;
    }
    $stmt2->close();
}

$conn->close();
?>

<style>
    .btn-delete-small {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 0.3rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-delete-small:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    
    .form-control:focus {
        border-color: #049D6F;
        box-shadow: 0 0 0 0.2rem rgba(4, 157, 111, 0.15);
    }
</style>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Riwayat Mahasiswa</h4>
        </div>
        <div class="card-body">
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="GET" action="edit_riwayat.php" class="mb-3 d-flex gap-2 align-items-center">
                <select name="nim" class="form-select" onchange="this.form.submit()">
                    <option value="">Pilih Mahasiswa...</option>
                    <?php foreach($list as $l): ?>
                        <option value="<?= htmlspecialchars($l['nim']); ?>" <?= ($nim == $l['nim']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($l['nama_mahasiswa']); ?> (<?= htmlspecialchars($l['nim']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="dashboard_dosen.php" class="btn btn-secondary">Kembali</a>
            </form>
            
            <?php if (!empty($nim)): ?>
            <form method="POST" action="edit_riwayat.php">
                <input type="hidden" name="nim_selected" value="<?= htmlspecialchars($nim); ?>">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Tip:</strong> Untuk menghapus data semester, kosongkan kedua field IP & SKS lalu klik Simpan, ATAU klik tombol "Hapus".
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">Semester</th>
                                <th style="width: 35%;">IP</th>
                                <th style="width: 35%;">SKS</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 14; $i++): ?>
                                <tr>
                                    <td><strong>Semester <?= $i; ?></strong></td>
                                    <td>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               max="4.00" 
                                               class="form-control" 
                                               name="ips[<?= $i; ?>]" 
                                               placeholder="0.00 - 4.00"
                                               value="<?= htmlspecialchars($riwayat[$i]['ip_semester'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               min="0" 
                                               max="24" 
                                               class="form-control" 
                                               name="sks[<?= $i; ?>]" 
                                               placeholder="0 - 24"
                                               value="<?= htmlspecialchars($riwayat[$i]['sks_semester'] ?? ''); ?>">
                                    </td>
                                    <td class="text-center">
                                        <?php if (isset($riwayat[$i])): ?>
                                            <button type="button" 
                                                    class="btn-delete-small" 
                                                    onclick="deleteSemester('<?= htmlspecialchars($nim) ?>', <?= $i ?>)">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Simpan Riwayat
                    </button>
                </div>
            </form>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<script>
// DELETE SEMESTER FUNCTION (AJAX) - UPDATED
function deleteSemester(nim, semester) {
    if (!confirm(`Yakin ingin menghapus data Semester ${semester}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('nim', nim);
    formData.append('semester', semester);
    
    // PANGGIL FILE TERPISAH
    fetch('delete_riwayat_dosen.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Debug: lihat response text dulu
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response bukan JSON:', text);
                throw new Error('Invalid JSON response');
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error: ' + error.message);
    });
}


<?php require 'templates/footer.php'; ?>
