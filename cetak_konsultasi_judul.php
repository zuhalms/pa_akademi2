<?php
session_start();
require_once 'config.php';

// Hanya mahasiswa yang boleh mencetak suratnya sendiri
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'mahasiswa') {
    header("Location: login.php");
    exit();
}

$nim = $_SESSION['user_id'];

// Pastikan ada id konsultasi
if (!isset($_GET['id'])) {
    echo "Parameter tidak lengkap.";
    exit();
}

$id_konsultasi = (int)$_GET['id'];

// Ambil data konsultasi + mahasiswa + prodi + dosen
$stmt = $conn->prepare("
    SELECT 
        k.id_konsultasi,
        k.nim,
        k.judul_usulan,
        k.deskripsi,
        k.status,
        k.tanggal_pengajuan,
        k.tanggal_respon,
        k.catatan_dosen,
        m.nama_mahasiswa,
        p.nama_prodi,
        d.nama_dosen
    FROM konsultasi_judul k
    JOIN mahasiswa m ON k.nim = m.nim
    LEFT JOIN program_studi p ON m.id_prodi = p.id_prodi
    LEFT JOIN dosen d ON k.id_dosen = d.id_dosen
    WHERE k.id_konsultasi = ? AND k.nim = ?
    LIMIT 1
");
$stmt->bind_param("is", $id_konsultasi, $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Data konsultasi tidak ditemukan atau Anda tidak berhak mengakses data ini.";
    exit();
}

$data = $result->fetch_assoc();
$stmt->close();

// Hanya boleh cetak jika sudah Disetujui
if (strtoupper(trim($data['status'])) !== 'DISETUJUI') {
    echo "Judul ini belum berstatus Disetujui, sehingga belum dapat dicetak surat persetujuannya.";
    exit();
}

// Siapkan tanggal tampil
$tgl_pengajuan = date('d F Y', strtotime($data['tanggal_pengajuan']));
$tgl_respon    = !empty($data['tanggal_respon'])
    ? date('d F Y', strtotime($data['tanggal_respon']))
    : '-';

// Nama prodi dan dosen fallback
$nama_prodi  = $data['nama_prodi']  ?: '-';
$nama_dosen  = $data['nama_dosen']  ?: 'Dosen PA';
$catatan_dsn = $data['catatan_dosen'] ?: '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Persetujuan Konsultasi Judul</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }
        
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }
        
        /* KOP SURAT */
        .kop-surat {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #000;
        }
        
        .kop-logo-left {
            flex-shrink: 0;
            width: 70px;
            text-align: center;
            margin-right: 10px;
        }
        
        .kop-logo-left img {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }
        
        .kop-text {
            flex-grow: 1;
            text-align: center;
            padding: 0 10px;
        }
        
        .kop-text h3 {
            margin: 0 0 0.2rem 0;
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.3;
        }
        
        .kop-text h4 {
            margin: 0 0 0.2rem 0;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.3;
        }
        
        .kop-text h5 {
            margin: 0 0 0.2rem 0;
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.3;
        }
        
        .kop-text p {
            margin: 0;
            font-size: 9pt;
            line-height: 1.3;
        }
        
        .kop-logo-right {
            flex-shrink: 0;
            width: 70px;
            text-align: center;
            margin-left: 10px;
        }
        
        .kop-logo-right img {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }
        
        /* JUDUL SURAT */
        .judul-surat {
            text-align: center;
            margin: 1rem 0 0.5rem 0;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: underline;
            font-size: 12pt;
        }
        
        .nomor-surat {
            text-align: center;
            margin-bottom: 1rem;
            font-size: 10pt;
        }
        
        /* CONTENT */
        .content {
            text-align: justify;
            line-height: 1.6;
        }
        
        .content p {
            margin-bottom: 0.8rem;
        }
        
        .content table {
            width: 100%;
            margin-bottom: 0.8rem;
            border-collapse: collapse;
        }
        
        .content td {
            vertical-align: top;
            padding: 0.15rem 0;
        }
        
        .content td.label {
            width: 28%;
        }
        
        .content td.sep {
            width: 3%;
        }
        
        .judul-skripsi {
            margin: 0.5rem 0 0.8rem 1.5cm;
            font-weight: bold;
            font-style: italic;
        }
        
        .deskripsi-box {
            margin: 0.5rem 0 0.8rem 1.5cm;
            text-align: justify;
        }
        
        /* TANDA TANGAN */
        .ttd-container {
            width: 100%;
            margin-top: 1.5rem;
            page-break-inside: avoid;
        }
        
        .ttd-right {
            width: 45%;
            float: right;
            text-align: left;
        }
        
        .ttd-right p {
            margin: 0.2rem 0;
        }
        
        .ttd-space {
            margin-top: 2.5rem;
        }
        
        .ttd-nama {
            border-top: 1px solid #000;
            padding-top: 0.3rem;
            font-weight: bold;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        /* BUTTON CETAK (HANYA DI LAYAR) */
        .btn-print {
            display: none;
        }
        
        @media screen {
            body {
                background: #e5e7eb;
                padding: 20px;
            }
            
            .container {
                background: #fff;
                padding: 20mm;
                box-shadow: 0 0 15px rgba(0,0,0,0.15);
                margin: 20px auto;
            }
            
            .btn-print {
                display: block;
                margin: 20px auto;
                padding: 10px 24px;
                background: #049D6F;
                color: #fff;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
            }
            
            .btn-print:hover {
                background: #037a59;
            }
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .btn-print {
                display: none !important;
            }
            
            .container {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen</button>

<div class="container">
    <!-- KOP SURAT -->
    <div class="kop-surat">
        <div class="kop-logo-left">
            <img src="assets/logo_uin.png" alt="Logo UIN">
        </div>
        <div class="kop-text">
            <h3>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
            <h4>UNIVERSITAS ISLAM NEGERI KOTA PALOPO</h4>
            <h5>FAKULTAS SYARIAH</h5>
            <p>Jl. Agatis, Balandai, Kota Palopo, Sulawesi Selatan 91922</p>
            <p>Telp: (0471) 22978 | Website: www.uinpalopo.ac.id</p>
        </div>
        <div class="kop-logo-right">
            <img src="assets/kemenag.png" alt="Logo Kemenag">
        </div>
    </div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        Surat Persetujuan Konsultasi Judul Skripsi
    </div>
    <div class="nomor-surat">
        Nomor: &mdash;/F-SY/UIN-PAL/<?= date('Y'); ?>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <p>Yang bertanda tangan di bawah ini menyatakan bahwa judul skripsi mahasiswa berikut telah disetujui pada tahap konsultasi judul:</p>

        <table>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="sep">:</td>
                <td><?= htmlspecialchars($data['nama_mahasiswa']); ?></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="sep">:</td>
                <td><?= htmlspecialchars($data['nim']); ?></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="sep">:</td>
                <td><?= htmlspecialchars($nama_prodi); ?></td>
            </tr>
        </table>

        <p>Adapun judul skripsi yang disetujui adalah:</p>

        <div class="judul-skripsi">
            "<?= htmlspecialchars($data['judul_usulan']); ?>"
        </div>

        <?php if (!empty($data['deskripsi'])): ?>
        <p>Dengan ringkasan/deskripsi sebagai berikut:</p>
        <div class="deskripsi-box">
            <?= nl2br(htmlspecialchars($data['deskripsi'])); ?>
        </div>
        <?php endif; ?>

        <p>Catatan/tanggapan dosen pembimbing akademik (PA):</p>
        <div class="deskripsi-box">
            <?= nl2br(htmlspecialchars($catatan_dsn)); ?>
        </div>

        <table>
            <tr>
                <td class="label">Tanggal Pengajuan</td>
                <td class="sep">:</td>
                <td><?= $tgl_pengajuan; ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal Persetujuan</td>
                <td class="sep">:</td>
                <td><?= $tgl_respon; ?></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="sep">:</td>
                <td><?= htmlspecialchars($data['status']); ?></td>
            </tr>
        </table>

        <p>Demikian surat persetujuan konsultasi judul skripsi ini dibuat untuk dapat dipergunakan sebagaimana mestinya serta sebagai dasar penetapan dosen pembimbing skripsi.</p>

        <!-- TANDA TANGAN -->
        <div class="ttd-container clearfix">
            <div class="ttd-right">
                <p>Palopo, <?= $tgl_respon !== '-' ? $tgl_respon : $tgl_pengajuan; ?></p>
                <p>Dosen Pembimbing Akademik (PA)</p>
                <div class="ttd-space"></div>
                <p class="ttd-nama"><?= htmlspecialchars($nama_dosen); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        // Auto print saat halaman dimuat (opsional, bisa dinonaktifkan)
        // window.print();
    });
</script>
</body>
</html>
<?php
$conn->close();
?>
