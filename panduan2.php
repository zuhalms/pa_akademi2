<?php
// Tidak perlu session karena halaman ini bisa diakses publik
$page_title = 'Panduan Penggunaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - SMART-BA UIN Palopo</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --green-primary: #00A86B;
            --text-dark: #343a40;
        }
        
        body {
            font-family: 'Lato', sans-serif;
            color: var(--text-dark);
        }
        
        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Montserrat', sans-serif;
        }
        
        .navbar {
            background-color: #212529;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--green-primary) 0%, #008F5A 100%);
            padding: 6rem 0 3rem 0;
            color: white;
            text-align: center;
        }
        
        .page-header h1 {
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        section {
            padding: 4rem 0;
        }
        
        .panduan-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 3rem;
        }
        
        .panduan-container h2 {
            color: var(--green-primary);
            font-weight: 800;
            margin-top: 2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--green-primary);
        }
        
        .panduan-container h3 {
            color: #555;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .panduan-container ul {
            list-style: none;
            padding-left: 0;
        }
        
        .panduan-container ul li {
            padding: 0.5rem 0;
            padding-left: 2rem;
            position: relative;
        }
        
        .panduan-container ul li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--green-primary);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .panduan-container ol {
            padding-left: 1.5rem;
        }
        
        .panduan-container ol li {
            padding: 0.5rem 0;
            line-height: 1.8;
        }
        
        .panduan-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 1.5rem 0;
        }
        
        .alert-custom {
            background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
            border-left: 4px solid #ffc107;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .btn-back {
            background: var(--green-primary);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-back:hover {
            background: #008F5A;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,168,107,0.3);
        }
        
        .footer {
            background-color: #212529;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">SMART-BA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="profil_umum.php">Profil</a></li>
                <li class="nav-item"><a class="nav-link" href="fasilitas.php">Fasilitas</a></li>
                <li class="nav-item"><a class="nav-link active" href="panduan.php">Panduan</a></li>
                <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- HEADER -->
<header class="page-header">
    <div class="container" data-aos="fade-up">
        <h1><i class="bi bi-book-fill me-3"></i>Panduan Penggunaan SMART-BA</h1>
        <p class="lead">Petunjuk lengkap untuk mahasiswa dan dosen dalam menggunakan sistem</p>
    </div>
</header>

<!-- CONTENT -->
<section class="bg-light">
    <div class="container">
        <div class="panduan-container" data-aos="fade-up">
            
            <div class="alert-custom">
                <strong><i class="bi bi-info-circle me-2"></i>Catatan:</strong> 
                Panduan ini membantu Anda memahami fitur-fitur SMART-BA. Jika ada kendala, silakan hubungi admin melalui halaman <a href="kontak.php">Kontak</a>.
            </div>

            <h2><i class="bi bi-person-badge me-2"></i>Panduan untuk Mahasiswa</h2>

            <h3>1. Login ke Sistem</h3>
            <ol>
                <li>Buka halaman <a href="login.php"><strong>Login</strong></a></li>
                <li>Masukkan <strong>NIM</strong> sebagai username</li>
                <li>Masukkan <strong>password</strong> yang telah diberikan admin</li>
                <li>Klik tombol <strong>"Masuk"</strong></li>
            </ol>

            <h3>2. Mengisi Riwayat Akademik</h3>
            <ol>
                <li>Setelah login, pilih menu <strong>"Input Riwayat"</strong> di navbar</li>
                <li>Isi <strong>IP Semester</strong> dan <strong>SKS</strong> untuk setiap semester yang telah Anda tempuh</li>
                <li>Sistem akan otomatis menghitung <strong>IPK</strong> dan <strong>Total SKS</strong></li>
                <li>Klik <strong>"Simpan Perubahan"</strong></li>
            </ol>

            <h3>3. Menambah Catatan Logbook Bimbingan</h3>
            <ol>
                <li>Di <strong>Dashboard Mahasiswa</strong>, scroll ke bagian <strong>"Riwayat Bimbingan"</strong></li>
                <li>Klik tombol <strong>"+ Tambah Catatan Bimbingan"</strong></li>
                <li>Isi form:
                    <ul>
                        <li><strong>Tanggal Bimbingan</strong></li>
                        <li><strong>Topik Bimbingan</strong> (contoh: "Konsultasi BAB 1")</li>
                        <li><strong>Isi Bimbingan</strong> (penjelasan detail)</li>
                    </ul>
                </li>
                <li>Klik <strong>"Simpan"</strong></li>
            </ol>

            <h3>4. Menghapus Catatan Logbook</h3>
            <ol>
                <li>Cari catatan yang ingin dihapus di tabel <strong>"Riwayat Bimbingan"</strong></li>
                <li>Klik tombol <strong>"Hapus"</strong> (ikon tempat sampah merah)</li>
                <li>Konfirmasi penghapusan</li>
                <li><strong>Catatan:</strong> Anda hanya bisa menghapus catatan yang Anda input sendiri</li>
            </ol>

            <h3>5. Upload Dokumen</h3>
            <ol>
                <li>Pilih menu <strong>"Upload Dokumen"</strong></li>
                <li>Klik <strong>"Pilih File"</strong> (format: PDF, DOC, DOCX, max 5MB)</li>
                <li>Isi <strong>Keterangan Dokumen</strong></li>
                <li>Klik <strong>"Upload"</strong></li>
            </ol>

            <h3>6. Melihat Evaluasi dari Dosen PA</h3>
            <ol>
                <li>Di <strong>Dashboard</strong>, lihat kartu <strong>"Evaluasi Terbaru"</strong></li>
                <li>Klik kartu tersebut untuk melihat detail penilaian soft skill</li>
            </ol>

            <hr class="my-5">

            <h2><i class="bi bi-person-video3 me-2"></i>Panduan untuk Dosen PA</h2>

            <h3>1. Login ke Sistem</h3>
            <ol>
                <li>Buka halaman <a href="login.php"><strong>Login</strong></a></li>
                <li>Masukkan <strong>NIP/Email</strong> sebagai username</li>
                <li>Masukkan <strong>password</strong></li>
                <li>Klik <strong>"Masuk"</strong></li>
            </ol>

            <h3>2. Melihat Daftar Mahasiswa Bimbingan</h3>
            <ol>
                <li>Setelah login, Anda akan masuk ke <strong>Dashboard Dosen</strong></li>
                <li>Tabel <strong>"Mahasiswa Bimbingan Anda"</strong> menampilkan semua mahasiswa PA</li>
                <li>Klik <strong>"Detail"</strong> untuk melihat profil lengkap mahasiswa</li>
            </ol>

            <h3>3. Menambah Catatan Logbook Bimbingan</h3>
            <ol>
                <li>Klik tombol <strong>"Detail"</strong> pada mahasiswa yang ingin dibimbing</li>
                <li>Di halaman detail, scroll ke <strong>"Logbook Bimbingan"</strong></li>
                <li>Klik <strong>"+ Tambah Catatan"</strong></li>
                <li>Isi form dan klik <strong>"Simpan"</strong></li>
            </ol>

            <h3>4. Edit Riwayat Akademik Mahasiswa</h3>
            <ol>
                <li>Di <strong>Dashboard Dosen</strong>, pilih menu <strong>"Edit Riwayat"</strong></li>
                <li>Pilih mahasiswa dari dropdown</li>
                <li>Edit IP dan SKS per semester</li>
                <li>Sistem akan otomatis update IPK</li>
                <li>Klik <strong>"Simpan Riwayat"</strong></li>
            </ol>

            <h3>5. Memberikan Evaluasi Soft Skill</h3>
            <ol>
                <li>Buka halaman <strong>"Detail Mahasiswa"</strong></li>
                <li>Scroll ke bagian <strong>"Evaluasi Soft Skill"</strong></li>
                <li>Beri nilai 1-5 untuk setiap aspek:
                    <ul>
                        <li>Kedisiplinan</li>
                        <li>Komunikasi</li>
                        <li>Tanggung Jawab</li>
                        <li>Kemampuan Problem Solving</li>
                    </ul>
                </li>
                <li>Isi <strong>Catatan</strong> (opsional)</li>
                <li>Klik <strong>"Simpan Evaluasi"</strong></li>
            </ol>

            <h3>6. Menghapus Data</h3>
            <ol>
                <li><strong>Logbook:</strong> Klik tombol "Hapus" di samping catatan yang ingin dihapus</li>
                <li><strong>Dokumen:</strong> Klik tombol "Hapus" di tabel dokumen mahasiswa</li>
                <li><strong>Riwayat Akademik:</strong> Di halaman "Edit Riwayat", kosongkan field IP & SKS, lalu klik "Simpan"</li>
            </ol>

            <hr class="my-5">

            <h2><i class="bi bi-shield-check me-2"></i>Tips Keamanan</h2>
            <ul>
                <li>Jangan bagikan password Anda kepada siapapun</li>
                <li>Selalu <strong>Logout</strong> setelah selesai menggunakan sistem</li>
                <li>Gunakan komputer pribadi atau perangkat yang aman</li>
                <li>Jika lupa password, hubungi admin melalui halaman <a href="kontak.php">Kontak</a></li>
            </ul>

            <hr class="my-5">

            <h2><i class="bi bi-question-circle me-2"></i>FAQ (Frequently Asked Questions)</h2>

            <h3>Q: Saya lupa password, bagaimana?</h3>
            <p><strong>A:</strong> Hubungi admin kampus atau kirim email ke <a href="mailto:kontak@uinpalopo.ac.id">kontak@uinpalopo.ac.id</a></p>

            <h3>Q: IPK saya tidak sesuai, bagaimana cara memperbaiki?</h3>
            <p><strong>A:</strong> Mahasiswa dapat memperbaiki sendiri melalui menu "Input Riwayat". Jika masih error, minta bantuan dosen PA untuk edit melalui menu "Edit Riwayat".</p>

            <h3>Q: Ukuran file maksimal untuk upload dokumen?</h3>
            <p><strong>A:</strong> Maksimal <strong>5 MB</strong> per file. Format yang didukung: PDF, DOC, DOCX.</p>

            <h3>Q: Apakah dosen PA bisa melihat semua dokumen saya?</h3>
            <p><strong>A:</strong> Ya, dosen PA dapat melihat semua dokumen yang Anda upload untuk keperluan bimbingan.</p>

            <div class="text-center mt-5">
                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer text-white text-center py-3">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> SMART-BA - Inisiatif Smart Green Campus UIN Palopo.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

</body>
</html>
