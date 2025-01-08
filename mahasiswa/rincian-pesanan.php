<?php
session_start();

if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../koneksi/koneksi.php');

$nik = $_SESSION['nik'];
$stmt_user = $conn->prepare("SELECT * FROM user WHERE nik = ?");
$stmt_user->bind_param("s", $nik);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Data user tidak ditemukan.");
}

$user_data = $result_user->fetch_assoc();
$foto_user = !empty($user_data['foto_user']) ? "../images/user/" . htmlspecialchars($user_data['foto_user']) : "../images/user/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
$role_user = $user_data['role'];

if ($role_user !== 'Mahasiswa') {
    die("Akses hanya untuk Mahasiswa.");
}

// Ambil data dari POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_ruang = htmlspecialchars($_POST['kode_ruang']);
    $tanggal_pemakaian = htmlspecialchars($_POST['tanggal_pemakaian']);
    $waktu_mulai = htmlspecialchars($_POST['waktu_mulai']);
    $waktu_selesai = htmlspecialchars($_POST['waktu_selesai']);
    $keperluan = htmlspecialchars($_POST['keperluan']);
} else {
    die("Tidak ada data yang dikirim.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Rincian Pesanan</title>
    <link rel="stylesheet" href="../back-end/admin/pesan-ruang.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="header">
                <div class="logo">
                    <img src="../images/aplikasi/logo.jpg" alt="Logo" class="logo-img">
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
                    <li><a href="../tampilan/home.php">Home</a></li>
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
                    <li><a href="../mahasiswa/dashboard.php">Dashboard</a></li>
                    <li><a href="../mahasiswa/profile.php">Profile</a></li>
                    <li><a href="../mahasiswa/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../mahasiswa/riwayat.php">Riwayat</a></li>
                </ul>
            </div>
            <div class="content">
                <h1>Rincian Pesanan</h1>
                <a href="" class="back-btn">< Kembali ke Formulir</a>
                <!-- Notifikasi -->
                <?php if (!empty($error_message)): ?>
                        <div class="error-message" style="color: red; margin-bottom: 20px;">
                            <?= $error_message ?>
                        </div>
                    <?php elseif (!empty($success_message)): ?>
                        <div class="success-message" style="color: green; margin-bottom: 20px;">
                            <?= $success_message ?>
                        </div>
                    <?php endif; ?>
                <form action="../mahasiswa/pesan-ruang.php?id_gedung=2&kode_ruangan=<?=$kode_ruang?>" method="POST">
                    <div class="form-group">
                        <!-- <label for="id_gedung">Gedung</label>
                        <input type="text" name="id_gedung" id="id_gedung" value="<?= $id_gedung?>"> -->

                        <label for="kode_ruangan">Kode Ruangan</label>
                        <input type="text" name="kode_ruangan" id="kode_ruangan" VALUE="<?= $kode_ruang?>">

                        <label for="tanggal_pemmakaian">Tanggal Pemakaian</label>
                        <input type="" name="tanggal_pemakaian" id="tanggal_pemakaian" value="<?= $tanggal_pemakaian?>">

                        <label for="waktu_mulai">Waktu Mulai</label>
                        <input type="text" id="waktu_mulai" value="<?=$waktu_mulai?>">

                        <label for="waktu_selesai">Waktu Selesai</label>
                        <input type="text" id="waktu_selesai"VALUE="<?=$waktu_selesai?>">

                        <label for="keperluan">Keperluan</label>
                        <input type="text" id="keperluan" value="<?=$keperluan?>">
                    </div>
                    <button type="submit" class="submit-btn">Konfirmasi Pesanan</button>
                </form>
            </div>
        </div>
        

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
                    <li>Suci Aqila Nst</li>
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
</body>
</html>