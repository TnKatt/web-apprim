<?php
session_start();
require_once('../koneksi/koneksi.php');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nik = $_SESSION['nik'];
$stmt_user = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
$stmt_user->bind_param("s", $nik);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Data pengguna tidak ditemukan.");
}

$user_data = $result_user->fetch_assoc();
$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
$role_user = $user_data['peran'];

if ($role_user !== 'Admin') {
    die("Akses hanya untuk Admin.");
}

$id_peminjaman = isset($_GET['id_peminjaman']) ? $_GET['id_peminjaman'] : null;
if (!$id_peminjaman) {
    die("ID peminjaman tidak valid.");
}

// Ambil data peminjaman untuk ditampilkan
$stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id_peminjaman = ?");
$stmt->bind_param("i", $id_peminjaman);
$stmt->execute();
$hasil_peminjaman = $stmt->get_result();

if ($hasil_peminjaman->num_rows === 0) {
    die("Data peminjaman tidak ditemukan.");
}

$data_peminjaman = $hasil_peminjaman->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Isi Penilaian</title>
    <link rel="stylesheet" href="../back-end/admin/isi-penilaian.css">
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
</head>
<body>
        <div class="container">
            <div class="row">
                <!-- Header -->
                <div class="header">
                    <div class="logo">
                        <img src="../images/aplikasi/logo.jpg" alt="Logo" class="logo-img">
                        <span class="title">APPRIM</span>
                    </div>
                    <div class="profile-logout">
                        <div class="profile">
                            <img src="<?= $foto_user ?>" alt="User Profile" class="profile-img">
                            <span class="username"><?= htmlspecialchars($nama_lengkap) ?></span>
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

            <!-- Sidebar dan Content -->
            <div class="row">
                <div class="sidebar">
                    <h2>Menu</h2>
                    <ul class="sidebar-list">
                        <li><a href="../admin/halaman-utama.php">Halaman Utama</a></li>
                        <li><a href="../admin/data-diri.php">Data Diri</a></li>
                        <li><a href="../admin/notifikasi.php">Notifikasi</a></li>
                        <li><a href="../admin/riwayat.php">Riwayat</a></li>
                    <li><a href="../admin/data-pengguna.php">Data Pengguna</a></li>
                </div>
                <div class="content">
                    <h1>Isi Penilaian untuk Peminjaman</h1>
                    <form action="../admin/update-nilai.php?id_peminjaman=<?= $data_peminjaman['id_peminjaman'] ?>" method="POST">
                            <input type="hidden" name="id_peminjaman" value="<?= $data_peminjaman['id_peminjaman'] ?>">
                            <div class="form-group">
                                <label for="penilaian">Penilaian (1 - 10):</label>
                                <input type="number" id="penilaian" name="penilaian" min="1" max="10" required>

                                <button type="submit" class="edit-button">Kirim Penilaian</button>
                            </div>
                    </form>
                </div>
            </div>
            
            <div class="row">
                <div class="footer">
                    <div class="footer-left">
                        <img src="../images/aplikasi/polibatam.jpg" alt="Logo" class="footer-logo">
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
