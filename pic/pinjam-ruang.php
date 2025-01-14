<?php
session_start();
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../koneksi/koneksi.php');
$nik = $_SESSION['nik'];

// Ambil data user
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

// Batasi akses hanya untuk pic
if ($role_user !== 'PIC Ruangan') {
    die("Akses hanya untuk pic.");
}

// Ambil data gedung
$id_gedung = isset($_GET['id_gedung']) ? intval($_GET['id_gedung']) : null;
if (!$id_gedung) {
    die("Gedung tidak valid.");
}

$stmt_gedung = $conn->prepare("SELECT * FROM gedung WHERE id_gedung = ?");
$stmt_gedung->bind_param("i", $id_gedung);
$stmt_gedung->execute();
$result_gedung = $stmt_gedung->get_result();
if ($result_gedung->num_rows === 0) {
    die("Gedung tidak ditemukan.");
}
$gedung = $result_gedung->fetch_assoc();

// Ambil kode ruangan
$kode_ruang = isset($_GET['kode_ruangan']) ? htmlspecialchars($_GET['kode_ruangan']) : null;
if (!$kode_ruang) {
    die("Kode ruang tidak valid.");
}

$stmt_ruang = $conn->prepare("SELECT * FROM ruangan WHERE kode_ruangan = ?");
$stmt_ruang->bind_param("s", $kode_ruang);
$stmt_ruang->execute();
$result_ruang = $stmt_ruang->get_result();
if ($result_ruang->num_rows === 0) {
    die("Ruang tidak ditemukan.");
}
$ruang = $result_ruang->fetch_assoc();
$kapasitas_ruang = (int)$ruang['kapasitas'];

// Notifikasi
$error_message = '';
$success_message = '';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_pemakaian = $_POST['tanggal_pemakaian'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $keperluan = trim($_POST['keperluan']);
    $jumlah_orang = (int)$_POST['jumlah_orang'];

    if (empty($tanggal_pemakaian) || $tanggal_pemakaian < $current_date) {
        $error_message = "Tanggal pemakaian tidak valid atau sudah berlalu.";
    } else {
        // Periksa apakah tanggal adalah tanggal merah
        $stmt_libur = $conn->prepare("SELECT * FROM libur_nasional WHERE tanggal = ?");
        $stmt_libur->bind_param("s", $tanggal_pemakaian);
        $stmt_libur->execute();
        $result_libur = $stmt_libur->get_result();

        if ($result_libur->num_rows > 0) {
            // Mengambil deskripsi libur
            $libur = $result_libur->fetch_assoc();  // Ambil data libur
            $deskripsi_libur = htmlspecialchars($libur['deskripsi']);  // Ambil dan sanitasi deskripsi libur
            $error_message = "Tidak dapat meminjam ruangan pada tanggal merah atau hari libur nasional.<br>{$deskripsi_libur}";

            // Validasi input
            $current_date = date('Y-m-d');
            $current_time = date('H:i');


        } elseif (empty($waktu_mulai) || empty($waktu_selesai)) {
            $error_message = "Waktu mulai dan waktu selesai harus diisi.";
        } elseif ($waktu_mulai >= $waktu_selesai) {
            $error_message = "Waktu mulai harus lebih awal dari waktu selesai.";
        } elseif (strlen($keperluan) < 10) {
            $error_message = "Keperluan harus diisi dan lebih dari 2 karakter.";
        } elseif ($jumlah_orang < 2) {
            $error_message = "Jumlah orang harus minimal 2.";
        } elseif ($jumlah_orang > $kapasitas_ruang) {
            $error_message = "Jumlah orang tidak boleh melebihi kapasitas ruangan ({$kapasitas_ruang} orang).";
        } else {
            // Periksa bentrok waktu
            $stmt_check = $conn->prepare("
                SELECT * FROM peminjaman 
                WHERE kode_ruangan = ? AND tanggal_pemakaian = ? 
                AND (
                    (waktu_mulai < ? AND waktu_selesai > ?) OR
                    (waktu_mulai < ? AND waktu_selesai > ?)
                )
            ");
            $stmt_check->bind_param("ssssss", $kode_ruang, $tanggal_pemakaian, $waktu_selesai, $waktu_mulai, $waktu_mulai, $waktu_selesai);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $error_message = "Waktu yang Anda pilih bentrok dengan pemesanan lain.";
            } else {
                // Simpan data peminjaman
                $stmt_insert = $conn->prepare("
                    INSERT INTO peminjaman (nik, kode_ruangan, tanggal_pemakaian, waktu_mulai, waktu_selesai, keperluan, jumlah_orang)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_insert->bind_param("ssssssi", $nik, $kode_ruang, $tanggal_pemakaian, $waktu_mulai, $waktu_selesai, $keperluan, $jumlah_orang);
                if ($stmt_insert->execute()) {
                    $status_peminjaman = 'berhasil';
                    $id_peminjaman = $stmt_insert->insert_id; // Dapatkan ID peminjaman terakhir
                
                    // Tambahkan data ke tabel riwayat
                    $query_riwayat = "INSERT INTO riwayat (nik, id_peminjaman) VALUES ('$nik', '$id_peminjaman')";
                    mysqli_query($conn, $query_riwayat);
                
                    $success_message = "Peminjaman ruangan berhasil.";
                } else {
                    $error_message = "Terjadi kesalahan saat menyimpan data.";
                }                
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Pinjam Ruang</title>
    <link rel="stylesheet" href="../back-end/pic/pinjam-ruang.css">
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
                    <li><a href="../pic/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../pic/data-diri.php">Data Diri</a></li>
                    <li><a href="../pic/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../pic/riwayat.php">Riwayat</a></li>
                </ul>
            </div>
            <div class="content">
                <h1>Pinjam Ruang - <?=$kode_ruang?></h1>
                <a href="../pic/daftar-ruang.php?id_gedung=<?=$id_gedung ?>" class="back-btn">< Kembali ke Ruangan</a>
                
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

                <form action="../pic/validasi-pinjam.php?id_gedung=<?= $id_gedung ?>&kode_ruangan=<?= $kode_ruang ?>" method="POST">
                    <div class="form-group">
                        <!-- Input Tersembunyi -->
                        <input type="hidden" name="nik" id="nik" value="<?= htmlspecialchars($nik); ?>">
                        <input type="hidden" name="kode_ruang" id="kode_ruang" value="<?= htmlspecialchars($kode_ruang); ?>">
                        <input type="hidden" name="id_gedung" id="id_gedung" value="<?=htmlspecialchars($id_gedung); ?>">

                        <!-- Input Tanggal -->
                        <label for="tanggal_pemakaian">Tanggal:</label>
                        <input type="date" name="tanggal_pemakaian" id="tanggal_pemakaian" required>

                        <!-- Input Waktu Mulai -->
                        <label for="waktu_mulai">Dari Jam:</label>
                        <select name="waktu_mulai" id="waktu_mulai" required>
                            <?php
                            $jam_mulai = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00",  "19:00", "20:00", "21:00" ];
                            $jam_selesai = [ "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00",  "19:00", "20:00", "21:00", "22:00" ];
                            $reserved_times = []; // Simpan waktu yang sudah dipesan

                            // Ambil waktu yang sudah dipesan untuk tanggal yang dipilih
                            $stmt_check = $conn->prepare("
                                SELECT waktu_mulai, waktu_selesai 
                                FROM peminjaman
                                WHERE kode_ruangan = ? 
                                AND tanggal_pemakaian = ?
                            ");
                            $stmt_check->bind_param("ss", $kode_ruang, $tanggal_pemakaian);
                            $stmt_check->execute();
                            $result_check = $stmt_check->get_result();

                            while ($row = $result_check->fetch_assoc()) {
                                $reserved_times[] = [
                                    'waktu_mulai' => $row['waktu_mulai'],
                                    'waktu_selesai' => $row['waktu_selesai']
                                ];
                            }

                            foreach ($jam_mulai as $waktu) {
                                $is_available = true;
                                $datetime_waktu = new DateTime($waktu);

                                foreach ($reserved_times as $time) {
                                    $reserved_start = new DateTime($time['waktu_mulai']);
                                    $reserved_end = new DateTime($time['waktu_selesai']);

                                    if ($datetime_waktu >= $reserved_start && $datetime_waktu < $reserved_end) {
                                        $is_available = false;
                                        break;
                                    }
                                }

                                if ($is_available) {
                                    echo "<option value=\"$waktu\">$waktu</option>";
                                }
                            }
                            ?>
                        </select>

                        <!-- Input Waktu Selesai -->
                        <label for="waktu_selesai">Sampai Jam:</label>
                        <select name="waktu_selesai" id="waktu_selesai" required>
                            <?php
                            foreach ($jam_selesai as $waktu) {
                                $is_available = true;
                                $datetime_waktu = new DateTime($waktu);

                                foreach ($reserved_times as $time) {
                                    $reserved_start = new DateTime($time['waktu_mulai']);
                                    $reserved_end = new DateTime($time['waktu_selesai']);

                                    if ($datetime_waktu <= $reserved_end && $datetime_waktu > $reserved_start) {
                                        $is_available = false;
                                        break;
                                    }
                                }

                                if ($is_available) {
                                    echo "<option value=\"$waktu\">$waktu</option>";
                                }
                            }
                            ?>
                        </select>

                        <!-- Input Keperluan -->
                        <label for="keperluan">Keperluan:</label>
                        <textarea name="keperluan" id="keperluan" required></textarea>

                        <!-- Input Jumlah Orang Peminjam -->
                        <label for="jumlah_orang">Jumlah Orang:</label>
                        <input type="number" name="jumlah_orang" id="jumlah_orang" min="2" required>

                        <!-- Tombol -->
                        <button type="submit" class="submit-btn">Pinjam</button>
                        <button type="reset" class="cancel-btn">Kosongkan Formulir</button>
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
<script>
    // Sidebar menu toggle
    const menuItems = document.querySelectorAll('.sidebar-list li.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });     
</script>
</html>
