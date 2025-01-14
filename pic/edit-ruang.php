<?php
// Mulai session
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
$nik = $_SESSION['nik'];

// Gunakan prepared statement untuk keamanan
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

// Validasi role untuk akses halaman
if ($role_user !== 'PIC Ruangan') {
    die("Akses hanya untuk pic.");
}

// Ambil id_gedung dan kode_ruangan dari URL
$id_gedung = isset($_GET['id_gedung']) ? intval($_GET['id_gedung']) : 0;
$kode_ruangan = isset($_GET['kode_ruangan']) ? $_GET['kode_ruangan'] : '';

if (!$id_gedung || !$kode_ruangan) {
    header("Location: ../pic/daftar-ruang.php?error=ID Gedung atau Kode Ruangan tidak valid.");
    exit();
}

// Ambil data gedung
$stmt_gedung = $conn->prepare("SELECT * FROM gedung WHERE id_gedung = ?");
$stmt_gedung->bind_param("i", $id_gedung);
$stmt_gedung->execute();
$result_gedung = $stmt_gedung->get_result();

if ($result_gedung->num_rows === 0) {
    header("Location: ../pic/daftar-ruang.php?error=Gedung tidak ditemukan.");
    exit();
}

$gedung_data = $result_gedung->fetch_assoc();

// Ambil data ruangan
$stmt_ruang = $conn->prepare("SELECT * FROM ruangan WHERE kode_ruangan = ?");
$stmt_ruang->bind_param("s", $kode_ruangan);
$stmt_ruang->execute();
$result_ruang = $stmt_ruang->get_result();

if ($result_ruang->num_rows === 0) {
    die("Data ruangan tidak ditemukan.");
}

$ruangan = $result_ruang->fetch_assoc();

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hapus'])) {
        // Hapus ruangan
        $stmt_delete = $conn->prepare("DELETE FROM ruangan WHERE kode_ruangan = ?");
        $stmt_delete->bind_param("s", $kode_ruangan);

        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = "Ruangan berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus ruangan.";
        }

        header("Location: daftar-ruang.php?id_gedung=" . $id_gedung);
        exit();
    } else {
        // Update data ruangan
        $lokasi = $_POST['lokasi'] ?? null;
        $nik_pic = $_POST['nik_pic'] ?? null;
        $jenis_ruang = $_POST['jenis_ruang'] ?? null;
        $kapasitas = $_POST['kapasitas'] ?? null;
        $fasilitas = $_POST['fasilitas'] ?? null;

        // Validasi input
        if (!$lokasi || !$jenis_ruang || !$kapasitas) {
            $_SESSION['error_message'] = "Pastikan semua kolom wajib diisi.";
        } else {
            // Proses foto jika ada
            $foto_ruang = $ruangan['foto_ruang'];
            if (!empty($_FILES['foto_ruang']['name'])) {
                // Hapus foto lama
                if ($foto_ruang && file_exists("../images/ruang/" . $foto_ruang)) {
                    unlink("../images/ruang/" . $foto_ruang);
                }

                // Validasi dan simpan foto baru
                $file_extension = strtolower(pathinfo($_FILES['foto_ruang']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                $max_file_size = 2 * 1024 * 1024; // 2MB

                if (!in_array($file_extension, $allowed_extensions)) {
                    $_SESSION['error_message'] = "Format file tidak valid.";
                } elseif ($_FILES['foto_ruang']['size'] > $max_file_size) {
                    $_SESSION['error_message'] = "Ukuran file terlalu besar.";
                } else {
                    $random_filename = 'ruang_' . uniqid() . '.' . $file_extension;
                    $target_file = "../images/ruang/" . $random_filename;

                    if (move_uploaded_file($_FILES['foto_ruang']['tmp_name'], $target_file)) {
                        $foto_ruang = $random_filename;
                    } else {
                        $_SESSION['error_message'] = "Gagal mengunggah foto.";
                    }
                }
            }

            // Update database
            if (!isset($_SESSION['error_message'])) {
                $stmt_update = $conn->prepare("
                    UPDATE ruangan 
                    SET lokasi = ?, nik_pic = ?, jenis_ruang = ?, kapasitas = ?, fasilitas = ?, foto_ruang = ? 
                    WHERE kode_ruangan = ?
                ");
                $stmt_update->bind_param("sssisss", $lokasi, $nik_pic, $jenis_ruang, $kapasitas, $fasilitas, $foto_ruang, $kode_ruangan);

                if ($stmt_update->execute()) {
                    $_SESSION['success_message'] = "Ruangan berhasil diperbarui.";
                } else {
                    $_SESSION['error_message'] = "Gagal memperbarui ruangan.";
                }
            }
        }

        header("Location: daftar-ruang.php?id_gedung=" . $id_gedung);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Edit Ruang</title>
    <link rel="stylesheet" href="../back-end/admin/edit-ruang.css">
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
                    <li><a href="../pic/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../pic/data-diri.php">Data Diri</a></li>
                    <li><a href="../pic/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../pic/riwayat.php">Riwayat</a></li>
                    <li><a href="../pic/data-pengguna.php">Data Pengguna</a></li>
                </ul>
            </div>

            <div class="content">
                <h1>Edit Ruangan - <?=$kode_ruangan?></h2>
                <a href="../pic/daftar-ruang.php?id_gedung=<?= $id_gedung ?>" class="back-btn">< Kembali ke Daftar Ruang</a>

                <!-- Notifikasi -->
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <!-- HTML untuk menampilkan pesan sukses jika ada -->
                <?php if (isset($success_message)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <form action="../pic/edit-ruang.php?id_gedung=<?= $id_gedung ?>&kode_ruangan=<?= htmlspecialchars($kode_ruangan) ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="kode_ruangan">Kode Ruangan</label>
                        <input type="text" name="kode_ruangan" id="kode_ruangan" value="<?= htmlspecialchars($ruangan['kode_ruangan']) ?>" readonly>

                        <label for="lokasi">Lokasi</label>
                        <input type="text" name="lokasi" id="lokasi" value="<?= htmlspecialchars($ruangan['lokasi']) ?>" required>

                        <label for="nik_pic">NIK PIC Ruangan</label>
                        <input type="text" name="nik_pic" id="nik_pic" value="<?= htmlspecialchars($ruangan['nik_pic'] ?? '') ?>" placeholder="Kosongkan jika belum ditentukan" oninput="updateNamaPIC()" readonly>

                        <label for="jenis_ruang">Jenis Ruangan</label>
                        <select name="jenis_ruang" id="jenis_ruang" required>
                            <option value="" hidden>Pilih jenis ruangan</option>
                            <option value="Ruang Rapat" <?= $ruangan['jenis_ruang'] === 'Ruang Rapat' ? 'selected' : '' ?>>Ruang Rapat</option>
                            <option value="Ruang Diskusi" <?= $ruangan['jenis_ruang'] === 'Ruang Diskusi' ? 'selected' : '' ?>>Ruang Diskusi</option>
                        </select>

                        <label for="kapasitas">Kapasitas</label>
                        <input type="number" name="kapasitas" id="kapasitas" min="2" value="<?= htmlspecialchars($ruangan['kapasitas']) ?>" required>

                        <label for="fasilitas">Fasilitas Ruangan</label>
                        <textarea name="fasilitas" id="fasilitas"><?= htmlspecialchars($ruangan['fasilitas'] ?? '') ?></textarea>

                        <label for="foto_ruang">Foto Ruangan</label>
                        <p>Gunakan Foto Landscape Atau Ukuran Rasio 9:16</p>
                        <input type="file" name="foto_ruang" id="foto_ruang">
                        <?php if (!empty($ruangan['foto_ruang'])): ?>
                            <img src="../images/ruang/<?= htmlspecialchars($ruangan['foto_ruang']) ?>" alt="Foto Ruang" class="ruang-img">
                        <?php else: ?>
                            <p>Foto tidak tersedia.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="submit-btn">Simpan</button>
                    <button type="submit" name="hapus" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">Hapus</button>
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