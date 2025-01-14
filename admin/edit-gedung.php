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

// Ambil data user dari session
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

// Ambil ID gedung dari URL (pastikan valid)
$id_gedung = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data gedung dari database
$stmt_gedung = $conn->prepare("SELECT * FROM gedung WHERE id_gedung = ?");
$stmt_gedung->bind_param("i", $id_gedung);
$stmt_gedung->execute();
$result_gedung = $stmt_gedung->get_result();

if ($result_gedung->num_rows === 0) {
    die("Gedung tidak ditemukan.");
}

$gedung_data = $result_gedung->fetch_assoc();
$nama_gedung = htmlspecialchars($gedung_data['nama_gedung']);
$deskripsi = htmlspecialchars($gedung_data['deskripsi']);
$foto_gedung = "../images/gedung/" . htmlspecialchars($gedung_data['foto_gedung']);

// Variabel untuk menyimpan pesan error atau sukses
$error_message = "";
$success_message = "";

// Cek token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token tidak valid.");
    }
}

// Proses form update gedung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_gedung'], $_POST['deskripsi'])) {
    $nama_gedung = htmlspecialchars($_POST['nama_gedung']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);

    // Validasi upload file
    $upload_dir = "../images/gedung/";
    $foto_gedung_baru = $_FILES['foto_gedung'] ?? null; // Cek apakah file ada

    if ($foto_gedung_baru && $foto_gedung_baru['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($foto_gedung_baru['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi jenis file
        if (in_array($image_file_type, ['jpg', 'png', 'jpeg', 'gif'])) {
            // Validasi ukuran file (misal max 5MB)
            if ($foto_gedung_baru['size'] > 5 * 1024 * 1024) {
                $error_message = "Ukuran file terlalu besar. Maksimal 5MB.";
            } else {
                // Cek apakah file adalah gambar yang valid
                if (getimagesize($foto_gedung_baru['tmp_name']) === false) {
                    $error_message = "File yang diunggah bukan gambar.";
                } else {
                    // Pindahkan file ke folder tujuan
                    if (move_uploaded_file($foto_gedung_baru['tmp_name'], $target_file)) {
                        // Update data ke database
                        $stmt = $conn->prepare("UPDATE gedung SET nama_gedung = ?, deskripsi = ?, foto_gedung = ? WHERE id_gedung = ?");
                        $stmt->bind_param("sssi", $nama_gedung, $deskripsi, $file_name, $id_gedung);

                        if ($stmt->execute()) {
                            $success_message = "Data gedung berhasil diperbarui.";
                        } else {
                            $error_message = "Gagal memperbarui data: " . $conn->error;
                        }
                    } else {
                        $error_message = "Gagal mengunggah file.";
                    }
                }
            }
        } else {
            $error_message = "Format file tidak valid. Hanya diperbolehkan: jpg, jpeg, png, gif.";
        }
    } else {
        // Update tanpa mengganti foto jika tidak ada file baru
        $stmt = $conn->prepare("UPDATE gedung SET nama_gedung = ?, deskripsi = ? WHERE id_gedung = ?");
        $stmt->bind_param("ssi", $nama_gedung, $deskripsi, $id_gedung);

        if ($stmt->execute()) {
            $success_message = "Data gedung berhasil diperbarui.";
        } else {
            $error_message = "Gagal memperbarui data: " . $conn->error;
        }
    }
}

// Proses penghapusan gedung
if (isset($_POST['hapus_gedung'])) {
    // Menghapus gambar gedung dari folder
    if (file_exists($foto_gedung)) {
        unlink($foto_gedung); // Menghapus file foto gedung
    }

    // Menghapus data gedung dari database
    $stmt_delete = $conn->prepare("DELETE FROM gedung WHERE id_gedung = ?");
    $stmt_delete->bind_param("i", $id_gedung);

    if ($stmt_delete->execute()) {
        $success_message = "Gedung berhasil dihapus.";
        // Redirect ke halaman halaman-utama setelah penghapusan
        header("Location: ../admin/halaman-utama.php");
        exit();
    } else {
        $error_message = "Gagal menghapus gedung: " . $conn->error;
    }
}

// Generate CSRF Token untuk form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Edit Gedung</title>
    <link rel="stylesheet" href="../back-end/admin/edit-gedung.css">
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
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

        <!-- Navigation -->
        <div class="navigation">
            <ul class="nav-list">
                <li><a href="../tampilan/beranda.php">Beranda</a></li>
                <li><a href="../tampilan/tentang.php">Tentang Kami</a></li>
                <li><a href="../tampilan/fitur.php">Fitur</a></li>
                <li><a href="../tampilan/kontak.php">Kontak</a></li>
            </ul>
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
                </ul>
            </div>
            <div class="content">
                <h2>Edit Gedung</h2>
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

                <form action="../admin/edit-gedung.php?id=<?= $id_gedung ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="form-group">
                        <label for="nama_gedung">Nama Gedung</label>
                        <input type="text" id="nama_gedung" name="nama_gedung" value="<?= $nama_gedung ?>" required>

                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" required><?= $deskripsi ?></textarea>
                    
                        <label for="foto_gedung">Foto Gedung</label>
                        <input type="file" id="foto_gedung" name="foto_gedung" accept="image/*">
                        <p>Foto saat ini:</p>
                        <img src="<?= $foto_gedung ?>" alt="Foto Gedung" width="100%">
                    </div>
                    <button type="submit" class="submit-btn">Simpan</button>
                    <button type="submit" name="hapus_gedung" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus gedung ini?');">Hapus Gedung</button>
                </form>
            </div>
        </div>

        <!-- Footer -->
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
</body>
</html>
