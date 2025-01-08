<?php
session_start();

if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../koneksi/koneksi.php');

// Fungsi untuk format tanggal
function formatTanggalIndonesia($tanggal, $formatWaktu = false) {
    $bulanIndonesia = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $hariIndonesia = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];

    $timestamp = strtotime($tanggal);
    if (!$timestamp) {
        return '-'; // Jika tanggal tidak valid
    }

    $hari = date('d', $timestamp);
    $bulan = $bulanIndonesia[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);

    if ($formatWaktu) {
        $waktu = date('H:i', $timestamp) . ' WIB';
        $hariNama = $hariIndonesia[date('l', $timestamp)];
        return "{$waktu}, {$hariNama} {$hari} {$bulan} {$tahun}";
    }

    return "{$hari} {$bulan} {$tahun}";
}

$nik = $_SESSION['nik'];

function getUserData($conn, $nik) {
    $stmt = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$user_data = getUserData($conn, $nik);
$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
if (!$user_data) {
    die("Data pengguna tidak ditemukan.");
}

if ($user_data['peran'] !== 'Mahasiswa') {
    die("Akses hanya untuk mahasiswa.");
}

$id_peminjaman = htmlspecialchars($_GET['id_peminjaman'] ?? '');
if (empty($id_peminjaman)) {
    die("ID Peminjaman tidak valid.");
}

// Validasi: Pastikan id_peminjaman milik pengguna yang login
$query = "
    SELECT 
        g.nama_gedung, 
        r.kode_ruangan, 
        COALESCE(p1.nama_lengkap, 'PIC ruangan belum ditentukan') AS pic_ruangan, 
        p2.nama_lengkap AS peminjam, 
        pm.tanggal_pemakaian, 
        pm.waktu_mulai, 
        pm.waktu_selesai, 
        pm.keperluan, 
        pm.tanggal_peminjaman
    FROM peminjaman pm
    JOIN ruangan r ON pm.kode_ruangan = r.kode_ruangan
    JOIN gedung g ON r.id_gedung = g.id_gedung
    LEFT JOIN pengguna p1 ON r.nik_pic = p1.nik
    JOIN pengguna p2 ON pm.nik = p2.nik
    WHERE pm.id_peminjaman = ? AND pm.nik = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $id_peminjaman, $nik); // Validasi berdasarkan id_peminjaman dan nik
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    die("Ini adalah tampilan detail riwayat peminjaman orang lain.");
}

// Ambil data detail peminjaman
$nama_gedung = htmlspecialchars($result['nama_gedung']);
$kode_ruangan = htmlspecialchars($result['kode_ruangan']);
$pic_ruangan = htmlspecialchars($result['pic_ruangan']);
$peminjam = htmlspecialchars($result['peminjam']);
$tanggal_pemakaian = formatTanggalIndonesia($result['tanggal_pemakaian']);
$jam = htmlspecialchars(substr($result['waktu_mulai'], 0, 5) . " - " . substr($result['waktu_selesai'], 0, 5) . " WIB");
$keperluan = htmlspecialchars($result['keperluan']);
$tanggal_pengajuan = formatTanggalIndonesia($result['tanggal_peminjaman'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Detail Riwayat</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../back-end/admin/pinjam-ruang.css">
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
</head>
<body>
    <div class="container">
        <div class="row">
            <!-- Header -->
            <div class="header">
                <div class="logo">
                    <img src="../images/apmikasi/logo.jpg" alt="Logo" class="logo-img">
                    <span class="title">APPRIM</span>
                </div>
                <div class="profile-logout">
                    <div class="profile">
                        <img src="<?= $foto_user ?>" alt="User Profile" class="profile-img">
                        <span class="username"><?= $nama_lengkap ?></span>
                    </div>
                    <div class="logout">
                        <a href="../auth/logout.php">Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Navigation -->
            <div class="navigation">
                <ul class="nav-list">
                    <li><a href="../tampilan/beranda.php">Beranda</a></li>
                    <li><a href="../tampilan/tentang.php">Tentang Kami</a></li>
                    <li><a href="../tampilan/fitur.php">Fitur</a></li>
                    <li><a href="../tampilan/kontak.php">Kontak</a></li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="sidebar">
                <h2>Menu</h2>
                <ul class="sidebar-list">
                    <li><a href="../mahasiswa/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../mahasiswa/data-diri.php">Data Diri</a></li>
                    <li><a href="../mahasiswa/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../mahasiswa/riwayat.php">Riwayat</a></li>
                </ul>
            </div>

            <div class="content">
                <h1>Detail Riwayat <?= htmlspecialchars($ruangan_data['kode_ruangan']) ?></h1>
                <a href="../mahasiswa/riwayat.php" class="back-btn">Kembali ke riwayat</a>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="id_gedung">Gedung</label>
                        <input type="text" name="id_gedung" id="id_gedung" value="<?= $nama_gedung ?>" readonly>
                        
                        <label for="kode_ruangan">Ruang</label>
                        <input type="text" name="kode_ruangan" id="kode_ruangan" value="<?= $kode_ruangan ?>" readonly>

                        <label for="pic_ruangan">PIC Ruangan</label>
                        <input type="text" name="pic" id="pic" value="<?= $pic_ruangan ?>" readonly>
                        
                        <label for="peminjam">Peminjam</label>
                        <input type="text" name="peminjam" id="peminjam" value="<?= $peminjam ?>" readonly>

                        <label for="tanggal_pemakaian">Tanggal</label>
                        <input type="text" name="tanggal_pemakaian" id="tanggal_pemakaian" value="<?= $tanggal_pemakaian ?>" readonly>

                        <label for="jam">Jam</label>
                        <input type="text" name="jam" id="jam" value="<?= $jam ?>" readonly>

                        <label for="keperluan">Keperluan</label>
                        <input type="text" name="keperluan" id="keperluan" value="<?= $keperluan ?>" readonly>

                        <label for="tanggal_pengajuan">Tanggal Pengajuan</label>
                        <input type="text" name="tanggal_pengajuan" id="tanggal_pengajuan" value="<?= $tanggal_pengajuan ?>" readonly>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="footer">
                <div class="footer-left">
                    <img src="../images/apmikasi/polibatam.jpg" alt="Logo" class="footer-logo">
                </div>
                <div class="footer-center">
                    <h3>Anggota Kelompok</h3>
                    <ul>
                        <li>Adhcya Hafeez Wibowo</li>
                        <li>Nayla Nur Nabila</li>
                        <li>Hermansa</li>
                        <li>Berkat Tua Siallagan</li>
                        <li>Suci Aqilah Nst</li>
                        <li>Ray Refaldo</li>
                    </ul>
                </div>
                <div class="footer-right">
                    <h3>NIM Anggota</h3>
                    <ul>
                        <li>4342401080</li>
                        <li>4342401083</li>
                        <li>4342401084</li>
                        <li>4342401085</li>
                        <li>4342401087</li>
                        <li>4342401088</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
