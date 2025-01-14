<?php
session_start();

// Periksa session pengguna
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Masukkan file koneksi
require_once('../koneksi/koneksi.php');

// Ambil data user
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

$error_message = "";
$success_message = "";

$id_gedung = isset($_GET['id_gedung']) ? intval($_GET['id_gedung']) : 0;
$stmt_gedung = $conn->prepare("SELECT * FROM gedung WHERE id_gedung = ?");
$stmt_gedung->bind_param("i", $id_gedung);
$stmt_gedung->execute();
$result_gedung = $stmt_gedung->get_result();

if ($result_gedung->num_rows === 0) {
    die("Gedung tidak ditemukan.");
}

$gedung_data = $result_gedung->fetch_assoc();

$stmt_pic = $conn->prepare("SELECT nik, nama_lengkap FROM pengguna WHERE peran = 'PIC Ruangan'");
$stmt_pic->execute();
$result_pic = $stmt_pic->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_ruang = isset($_POST['jenis_ruang']) ? htmlspecialchars(trim($_POST['jenis_ruang'])) : ''; 
    $kode_ruangan = isset($_POST['kode_ruangan']) ? htmlspecialchars(trim($_POST['kode_ruangan'])) : ''; 
    $lokasi = isset($_POST['lokasi']) ? htmlspecialchars(trim($_POST['lokasi'])) : ''; 
    $kapasitas = isset($_POST['kapasitas']) ? intval($_POST['kapasitas']) : 0;
    $fasilitas = isset($_POST['fasilitas']) ? htmlspecialchars(trim($_POST['fasilitas'])) : ''; 
    $status = isset($_POST['status']) ? htmlspecialchars(trim($_POST['status'])) : '';
    $nik_pic = isset($_POST['nik']) && !empty($_POST['nik']) ? htmlspecialchars(trim($_POST['nik'])) : null;

    $upload_dir = "../images/ruang/";
    $foto_ruang = $_FILES['foto_ruang'] ?? null;

    if (empty($jenis_ruang) || empty($kode_ruangan) || empty($lokasi) || $kapasitas < 3 || empty($fasilitas) || empty($status)) {
        $error_message = "Semua kolom wajib diisi dengan benar.";
    } elseif ($nik_pic) {
        $stmt_check_nik = $conn->prepare("SELECT * FROM pengguna WHERE nik = ? AND peran = 'PIC Ruangan'");
        $stmt_check_nik->bind_param("s", $nik_pic);
        $stmt_check_nik->execute();
        $result_check_nik = $stmt_check_nik->get_result();

        if ($result_check_nik->num_rows === 0) {
            $error_message = "NIK PIC harus valid dan berperan sebagai PIC Ruangan.";
        } else {
            $pic_valid = true;
        }
    }

    if (empty($error_message) && ($foto_ruang && $foto_ruang['error'] === UPLOAD_ERR_OK)) {
        $file_name = basename($foto_ruang['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $unique_file_name = uniqid("ruang_", true) . "." . $file_ext;
        $target_file = $upload_dir . $unique_file_name;

        if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Format file tidak valid. Gunakan jpg, jpeg, png, atau gif.";
        } elseif (!move_uploaded_file($foto_ruang['tmp_name'], $target_file)) {
            $error_message = "Gagal mengunggah file.";
        } else {
            $stmt = $conn->prepare("INSERT INTO ruangan (id_gedung, jenis_ruang, kode_ruangan, lokasi, kapasitas, fasilitas, status_ruangan, foto_ruang, nik_pic) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssissss", $id_gedung, $jenis_ruang, $kode_ruangan, $lokasi, $kapasitas, $fasilitas, $status, $unique_file_name, $nik_pic);

            if ($stmt->execute()) {
                $success_message = "Data ruang berhasil ditambahkan.";
            } else {
                $error_message = "Gagal menambahkan data: " . $conn->error;
            }
        }
    } elseif (empty($error_message)) {
        $error_message = "File tidak diunggah atau terjadi kesalahan.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Tambah Ruang</title>
    <link rel="stylesheet" href="../back-end/admin/tambah-ruang.css">
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
                </ul>
            </div>
            <div class="content">
                <h2>Tambah Ruang - Gedung <?= htmlspecialchars($gedung_data['nama_gedung']) ?></h2>
                <a href="../admin/daftar-ruang.php?id_gedung=<?= $id_gedung ?>" class="back-btn">< Kembali ke Ruangan</a>
                
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

                <form action="../admin/tambah-ruang.php?id_gedung=<?= $id_gedung ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="jenis_ruang">Jenis Ruang</label>
                        <select id="jenis_ruang" name="jenis_ruang" required>
                            <option value="Ruang Rapat">Ruang Rapat</option>
                            <option value="Ruang Diskusi">Ruang Diskusi</option>
                        </select><br>
                       
                        <label for="nik">NIK PIC Ruangan</label>
                        <input type="text" id="nik" name="nik" placeholder="Kosongkan jika belum ada PIC">

                        <label for="kode_ruangan">Nama Ruangan</label>
                        <input type="text" id="kode_ruangan" name="kode_ruangan" required>

                        <label for="lokasi">Lokasi</label>
                        <input type="text" id="lokasi" name="lokasi" placeholder="Lantai" required>

                        <label for="kapasitas">Kapasitas</label>
                        <input type="number" id="kapasitas" name="kapasitas" min="3" required>

                        <label for="fasilitas">Fasilitas</label>
                        <input type="text" id="fasilitas" name="fasilitas" required>

                        <select id="status" name="status" required>
                            <option value="terbuka" <?= isset($_POST['status']) && $_POST['status'] == 'terbuka' ? 'selected' : '' ?>>Terbuka</option>
                            <option value="tertutup" <?= isset($_POST['status']) && $_POST['status'] == 'tertutup' ? 'selected' : '' ?>>Tertutup</option>
                        </select>

                        <label for="foto_ruang">Foto Ruang (Disarankan ukuran foto 9:16)</label>
                        <input type="file" name="foto_ruang" id="foto_ruang" placeholder="Disarankan ukuran foto 9:16" required>
                    </div>

                    <button type="submit" class="submit-btn">Tambah Ruang</button>
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
    <script src="../admin/tambah-ruang.js"></script>
</body>
</html>
