<?php
// Mulai session
session_start();

// Periksa apakah session pengguna sudah ada
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Masukkan file koneksi
require_once('../koneksi/koneksi.php');

// Ambil Data Pengguna dari database
$nik = $_SESSION['nik']; // Ambil NIK dari session

$stmt_user = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
$stmt_user->bind_param("s", $nik);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Data Pengguna tidak ditemukan.");
}

$user_data = $result_user->fetch_assoc();
$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);


// Periksa role user
$role_user = $user_data['peran'];
if ($role_user !== 'Admin') {
    die("Akses hanya untuk Admin.");
}

// Ambil id_gedung dari URL
$id_gedung = isset($_GET['id_gedung']) ? intval($_GET['id_gedung']) : null;

// Validasi apakah id_gedung valid
$stmt_gedung = $conn->prepare("SELECT * FROM gedung WHERE id_gedung = ?");
$stmt_gedung->bind_param("i", $id_gedung);
$stmt_gedung->execute();
$result_gedung = $stmt_gedung->get_result();

if ($result_gedung->num_rows === 0) {
    die("Gedung tidak ditemukan.");
}

$gedung_data = $result_gedung->fetch_assoc();

// Ambil daftar ruang beserta informasi PIC dari database
$query_ruang = "
    SELECT 
        ruangan.*, 
        pengguna.nama_lengkap AS pic_nama 
    FROM 
        ruangan 
    LEFT JOIN 
        pengguna 
    ON 
        ruangan.nik_pic = pengguna.nik 
    WHERE 
        ruangan.id_gedung = ?
";

$stmt_ruang = $conn->prepare($query_ruang);
$stmt_ruang->bind_param("i", $id_gedung);
$stmt_ruang->execute();
$result_ruang = $stmt_ruang->get_result();

// Menangani pembaruan status ruangan
if (isset($_GET['kode_ruangan']) && isset($_GET['status_ruangan'])) {
    $id_ruang = intval($_GET['kode_ruangan']); 
    $status = $_GET['status_ruangan'];

    // Validasi status
    if ($status !== 'terbuka' && $status !== 'tertutup') {
        die("Status tidak valid.");
    }

    // Menangani pembaruan status ruangan
    if (isset($_GET['kode_ruangan']) && isset($_GET['status_ruangan'])) {
        $id_ruang = $_GET['kode_ruangan']; // Mengambil kode ruangan dari URL
        $status = $_GET['status_ruangan'];

        // Validasi status
        if ($status !== 'terbuka' && $status !== 'tertutup') {
            die("Status tidak valid.");
        }

        // Perbarui status ruangan di database
        $stmt_update = $conn->prepare("UPDATE ruangan SET status_ruangan = ? WHERE kode_ruangan = ?");
        $stmt_update->bind_param("ss", $status, $id_ruang); // Menggunakan 'ss' karena kode_ruangan bisa berupa string

        if ($stmt_update->execute()) {
            // Redirect kembali ke halaman daftar ruangan dengan pesan sukses
            header("Location: ../admin/daftar-ruang.php?id_gedung=" . $id_gedung . "&status_ruangan_update=success");
            exit();
        } else {
            // Redirect dengan pesan error
            header("Location: ../admin/daftar-ruang.php?id_gedung=" . $id_gedung . "&status_ruangan_update=failure");
            exit();
        }
    }
}

// Ambil pesan dari session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Hapus pesan dari session setelah diambil
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Daftar Ruang</title>
    <link rel="stylesheet" href="../back-end/admin/daftar-ruang.css">
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
                    <li><a href="../admin/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../admin/data-diri.php">Data Diri</a></li>
                    <li><a href="../admin/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../admin/riwayat.php">Riwayat</a></li>
                    <li><a href="../admin/data-pengguna.php">Data Pengguna</a></li>
                </ul>
            </div>

            <div class="content">
                <h1>Daftar Ruang - <?= htmlspecialchars($gedung_data['nama_gedung']) ?></h1>
                <a href="../admin/tambah-ruang.php?id_gedung=<?= $id_gedung ?>" class="add-room-btn">Tambah Ruang</a>

                <!-- Notifikasi -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message" style="
                        background-color: #f8d7da;
                        color: #721c24;
                        border: 1px solid #f5c6cb;
                        padding: 10px;
                        border-radius: 5px;
                        font-size: 16px;
                        margin-bottom: 20px;
                        font-weight:bold;
                    ">
                        <?= $error_message ?>
                    </div>
                <?php elseif (!empty($success_message)): ?>
                    <div class="success-message" style="
                        background-color: #d4edda;
                        color: #155724;
                        border: 1px solid #c3e6cb;
                        padding: 10px;
                        border-radius: 5px;
                        font-size: 16px;
                        margin-bottom: 20px;
                        font-weight:bold;
                    ">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if ($result_ruang->num_rows > 0): ?>
                    <?php while ($ruang = $result_ruang->fetch_assoc()): ?>
                        <div class="room-box">
                            <div class="room-left">
                                <img src="../images/ruang/<?= htmlspecialchars($ruang['foto_ruang']) ?>" alt="room" class="room-img">
                            </div>
                            <div class="room-right">
                                <h2 class="room-name">
                                    <?= htmlspecialchars($ruang['jenis_ruang'] ?? 'Ruangan tidak tersedia') ?>
                                </h2>
                                <p>PIC Ruangan: <?= htmlspecialchars($ruang['pic_nama'] ?? 'Belum ditentukan') ?></p>
                                <p>Nama Ruang: <?= htmlspecialchars($ruang['kode_ruangan']) ?></p>
                                <p>Lokasi: <?= htmlspecialchars($ruang['lokasi']) ?></p>
                                <p>Kapasitas: <?= htmlspecialchars($ruang['kapasitas']) ?> orang</p>
                                <p>Penilaian: <?= htmlspecialchars($ruang['penilaian']) ?> / 10</p>
                                <p>Fasilitas: <?= htmlspecialchars($ruang['fasilitas']) ?></p>
                                <div class="button-container">
                                    <!-- tombol ketersediaan -->
                                    <a href="../admin/ketersediaan.php?kode_ruangan=<?= isset($ruang['kode_ruangan']) ? htmlspecialchars($ruang['kode_ruangan']) : '' ?>" class="edit-room-btn">Ketersediaan</a>
                                    <!-- Tombol Edit Ruangan -->
                                    <a href="../admin/edit-ruang.php?id_gedung=<?= isset($gedung_data['id_gedung']) ? htmlspecialchars($gedung_data['id_gedung']) : '' ?>&kode_ruangan=<?= isset($ruang['kode_ruangan']) ? htmlspecialchars($ruang['kode_ruangan']) : '' ?>" 
                                    class="edit-room-btn">Edit Ruangan</a>

                                    <!-- Tombol Status Ruangan -->
                                    <?php if (isset($ruang['status_ruangan']) && strtolower(trim($ruang['status_ruangan'])) === 'terbuka'): ?>
                                        <a href="pinjam-ruang.php?id_gedung=<?= htmlspecialchars($id_gedung); ?>&kode_ruangan=<?= htmlspecialchars($ruang['kode_ruangan']); ?>" class="edit-room-btn">Pinjam Ruang</a>
                                        <a href="../admin/daftar-ruang.php?id_gedung=<?= $id_gedung ?>&kode_ruangan=<?= urlencode($ruang['kode_ruangan']) ?>&status_ruangan=tertutup"
                                        class="status-room-btn" 
                                        style="background-color: green;">Terbuka</a>
                                    <?php else: ?>
                                     <!-- Tombol Pesan Ruangan -->
                                        <a href="../admin/daftar-ruang.php?id_gedung=<?= $id_gedung ?>&kode_ruangan=<?= urlencode($ruang['kode_ruangan']) ?>&status_ruangan=terbuka"
                                        class="status-room-btn" 
                                        style="background-color: #800e13;">
                                            Tertutup
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
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
    </div>

    <script>
        // Sidebar menu toggle
        const menuItems = document.querySelectorAll('.sidebar-list li.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('active');
            });
        });  

        // JavaScript untuk menampilkan popup notifikasi
        <?php if (isset($_GET['status_update'])): ?>
            var statusUpdate = "<?php echo $_GET['status_update']; ?>";
            var message = statusUpdate === 'success' ? "Status ruangan berhasil diperbarui." : "Gagal memperbarui status ruangan.";
            var popup = document.getElementById('popupNotification');
            var popupMessage = document.getElementById('popupMessage');
            
            popupMessage.textContent = message;
            popup.classList.add(statusUpdate === 'success' ? '' : 'failure');
            popup.style.display = 'block';
            
            // Menghilangkan popup setelah 3 detik
            setTimeout(function() {
                popup.style.display = 'none';
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
