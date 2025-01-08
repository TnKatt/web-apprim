<?php
// Mulai session untuk menggunakan session variables
session_start();

// Periksa apakah session pengguna sudah ada
if (!isset($_SESSION['nik'])) {
    // Jika tidak ada, alihkan pengguna ke halaman login
    header("Location: ../auth/login.php");
    exit();
}

// Masukkan file koneksi
require_once('../koneksi/koneksi.php');

// Pastikan token CSRF valid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }
}

// Ambil data user dari database
$nik = $_SESSION['nik']; // Ambil NIK dari session

// Gunakan prepared statement untuk keamanan
$stmt_user = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
$stmt_user->bind_param("s", $nik);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// Periksa apakah data user ditemukan
if ($result_user->num_rows === 0) {
    die("Data pengguna tidak ditemukan.");
}

$user_data = $result_user->fetch_assoc();
$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);

// Ambil data role user
$role_user = $user_data['peran'];

// Validasi role untuk akses halaman
if ($role_user !== 'Admin') {
    die("Akses hanya untuk Admin.");
}

// Variabel untuk menyimpan pesan error atau sukses
$error_message = "";
$success_message = "";

// Proses form tambah gedung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_gedung = htmlspecialchars($_POST['nama_gedung']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);

    // Validasi upload file
    $upload_dir = "../images/gedung/";
    $foto_gedung = $_FILES['foto_gedung'] ?? null; // Cek apakah file ada

    // Pastikan file diunggah
    if ($foto_gedung && $foto_gedung['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($foto_gedung['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Membuat nama file baru dengan menambahkan timestamp
        $unique_file_name = uniqid("gedung_", true) . "." . $file_ext;
        $target_file = $upload_dir . $unique_file_name;

        // Validasi jenis file
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Pindahkan file ke folder tujuan
            if (move_uploaded_file($foto_gedung['tmp_name'], $target_file)) {
                // Simpan data ke database dengan nama file yang baru
                $stmt = $conn->prepare("INSERT INTO gedung (nama_gedung, deskripsi, foto_gedung) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nama_gedung, $deskripsi, $unique_file_name);

                if ($stmt->execute()) {
                    $success_message = "Data gedung berhasil ditambahkan.";
                } else {
                    $error_message = "Gagal menambahkan data: " . $conn->error;
                }
            } else {
                $error_message = "Gagal mengunggah file.";
            }
        } else {
            $error_message = "Format file tidak valid. Hanya diperbolehkan: jpg, jpeg, png, gif.";
        }
    } else {
        $error_message = "File tidak diunggah atau terjadi kesalahan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Tambah Gedung</title>
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
    <link rel="stylesheet" href="../back-end/admin/tambah-gedung.css">
</head>
<body>
    <div class="container">
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
                    <li><a href="../admin/data-pengguna">Data Pengguna</a></li>
                </ul>
            </div>
            <div class="content">
                <h2>Tambah Gedung</h2>
                <a href="../admin/halaman-utama.php" class="back-btn">< Kembali ke Daftar Gedung</a>

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

                <form action="../admin/tambah-gedung.php" method="POST" enctype="multipart/form-data">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="form-group">
                        <label for="nama_gedung">Nama Gedung</label>
                        <input type="text" id="nama_gedung" name="nama_gedung" required>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="foto_gedung">Foto Gedung</label>
                        <input type="file" id="foto_gedung" name="foto_gedung" accept="image/*" required>
                    </div>
                    <button type="submit" class="submit-btn">Simpan</button>
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
