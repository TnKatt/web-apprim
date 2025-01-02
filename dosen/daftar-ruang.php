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

// Ambil data user dari database
$nik = $_SESSION['nik']; // Ambil NIK dari session

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

// Periksa role user
$role_user = $user_data['role'];
if ($role_user !== 'Dosen') {
    die("Akses hanya untuk Dosen.");
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
        user.nama_lengkap AS pic_nama 
    FROM 
        ruangan 
    LEFT JOIN 
        user 
    ON 
        ruangan.nik = user.nik 
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
            header("Location: ../dosen/daftar-ruang.php?id_gedung=" . $id_gedung . "&status_ruangan_update=success");
            exit();
        } else {
            // Redirect dengan pesan error
            header("Location: ../dosen/daftar-ruang.php?id_gedung=" . $id_gedung . "&status_ruangan_update=failure");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Daftar Ruang</title>
    <link rel="stylesheet" href="../back-end/dosen/daftar-ruang.css">
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
                        <a href="../auth/logout.php">Logout</a>
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
                    <li><a href="../dosen/dashboard.php">Dashboard</a></li>
                    <li><a href="../dosen/profile.php">Profile</a></li>
                    <li><a href="../dosen/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../dosen/riwayat.php">Riwayat</a></li>

                    <li class="menu-item">
                        <a href="javascript:void(0)">Data User</a>
                        <ul>
                            <a href="../dosen/mahasiswa.php">Mahasiswa</a>
                            <a href="../dosen/dosen.php">Dosen</a>
                            <a href="../dosen/pic.php">PIC</a>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="content">
                <h1>Daftar Ruang - <?= htmlspecialchars($gedung_data['nama_gedung']) ?></h1>

                

                <!-- Popup notifikasi -->
                <div class="popup-notification" id="popupNotification">
                    <p id="popupMessage"></p>
                </div>

                <?php if ($result_ruang->num_rows > 0): ?>
                    <?php while ($ruang = $result_ruang->fetch_assoc()): ?>
                        <div class="room-box">
                            <div class="room-left">
                                <img src="../images/ruang/<?= htmlspecialchars($ruang['foto_ruang'] ?? 'default.jpg') ?>" alt="room" class="room-img">
                            </div>
                            <div class="room-right">
                                <h2 class="room-name">
                                    <?= htmlspecialchars($ruang['jenis_ruang'] ?? 'Ruangan tidak tersedia') ?>
                                </h2>
                                <p>PIC Ruangan: <?= htmlspecialchars($ruang['pic_nama'] ?? 'Belum ditentukan') ?></p>
                                <p>Nama Ruang: <?= htmlspecialchars($ruang['kode_ruangan']) ?></p>
                                <p>Lokasi: <?= htmlspecialchars($ruang['lokasi']) ?></p>
                                <p>Kapasitas: <?= htmlspecialchars($ruang['kapasitas']) ?> orang</p>
                                <p>Rating Ruangan: <?= htmlspecialchars($ruang['rating']) ?> / 10</p>
                                <p>Fasilitas: <?= htmlspecialchars($ruang['fasilitas']) ?></p>
                                <div class="button-container">
                                    <!-- Tombol Edit Ruangan -->
    

                                    <!-- Tombol Status Ruangan -->
                                    <?php if (isset($ruang['status_ruangan']) && strtolower(trim($ruang['status_ruangan'])) === 'terbuka'): ?>
                                        <a href="pesan-ruang.php?id_gedung=<?= htmlspecialchars($id_gedung); ?>&kode_ruangan=<?= htmlspecialchars($ruang['kode_ruangan']); ?>" class="edit-room-btn">Pesan Ruang</a>
                                        <a
                                        class="status-room-btn" 
                                        style="background-color: green;">Terbuka</a>
                                    <?php else: ?>
                                     <!-- Tombol Pesan Ruangan -->
                                        <a
                                        class="status-room-btn" 
                                        style="background-color: red;">
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
