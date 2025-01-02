<?php
session_start();

if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../koneksi/koneksi.php');

setlocale(LC_TIME, 'Indonesian_indonesia.1252');

$nik = $_SESSION['nik'];

function getuserData($conn, $nik) {
    $stmt = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getGedungData($conn, $id_gedung) {
    $stmt = $conn->prepare("SELECT nama_gedung FROM gedung WHERE id_gedung = ?");
    $stmt->bind_param("s", $id_gedung);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function getRoomCapacity($conn, $kode_ruangan) {
    $stmt = $conn->prepare("SELECT kapasitas FROM ruangan WHERE kode_ruangan = ?");
    $stmt->bind_param("s", $kode_ruangan);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

$user_data = getuserData($conn, $nik);
if (!$user_data) {
    die("Data pengguna tidak ditemukan.");
}

$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
$role_user = $user_data['peran'];

if ($role_user !== 'Admin') {
    die("Akses hanya untuk Admin.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_gedung = htmlspecialchars($_POST['id_gedung'] ?? '');
    $kode_ruang = htmlspecialchars($_POST['kode_ruang'] ?? '');
    $tanggal_pemakaian = htmlspecialchars($_POST['tanggal_pemakaian'] ?? '');
    $waktu_mulai = htmlspecialchars($_POST['waktu_mulai'] ?? '');
    $waktu_selesai = htmlspecialchars($_POST['waktu_selesai'] ?? '');
    $keperluan = htmlspecialchars($_POST['keperluan'] ?? '');
    $jumlah_orang = htmlspecialchars($_POST['kapasitas'] ?? '');

    if (empty($id_gedung) || empty($kode_ruang) || empty($tanggal_pemakaian) || empty($waktu_mulai) || empty($waktu_selesai) || empty($keperluan)) {
        die("Semua data harus diisi.");
    }
} else {
    $id_gedung = htmlspecialchars($_GET['id_gedung'] ?? '');

    if (empty($id_gedung)) {
        die("ID gedung tidak diberikan.");
    }
}

$gedung_data = getGedungData($conn, $id_gedung);
$nama_gedung = $gedung_data ? htmlspecialchars($gedung_data['nama_gedung']) : "Nama gedung tidak ditemukan.";

// Ambil kapasitas ruang
$room_data = getRoomCapacity($conn, $kode_ruang);
$kapasitas = $room_data ? $room_data['kapasitas'] : 0;

// Ambil data peminjaman yang sudah ada untuk tanggal yang dipilih
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_pemakaian = $_POST['tanggal_pemakaian'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $keperluan = trim($_POST['keperluan']);
    $jumlah_orang = $_POST['jumlah_orang'];

    // Validasi tanggal tidak berada di masa lampau
    $today = new DateTime();
    $selected_date = new DateTime($tanggal_pemakaian);

    // Cek apakah hari adalah Sabtu atau Minggu
    $day_of_week = $selected_date->format('N'); // 1 (Senin) - 7 (Minggu)
    if ($day_of_week == 6 || $day_of_week == 7) {
        $error_message = "Peminjaman tidak diperbolehkan pada hari Sabtu atau Minggu.";
    } elseif ($selected_date < $today) {
        $error_message = "Tanggal pemakaian tidak boleh berada di masa lalu atau hari ini.";
    } else {
        // Validasi waktu selesai lebih besar dari waktu mulai
        $datetime_mulai = new DateTime($waktu_mulai);
        $datetime_selesai = new DateTime($waktu_selesai);

        if ($datetime_mulai >= $datetime_selesai) {
            $error_message = "Waktu selesai harus lebih dari waktu mulai.";
        } else {
            // Cek konflik peminjaman
            $stmt_check = $conn->prepare("
                SELECT waktu_mulai, waktu_selesai 
                FROM peminjaman
                WHERE kode_ruangan = ? 
                AND tanggal_pemakaian = ?
            ");
            $stmt_check->bind_param("ss", $kode_ruang, $tanggal_pemakaian);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            $reserved_times = [];
            while ($row = $result_check->fetch_assoc()) {
                $reserved_times[] = [
                    'waktu_mulai' => $row['waktu_mulai'],
                    'waktu_selesai' => $row['waktu_selesai']
                ];
            }

            // Cek konflik dengan waktu yang sudah dipesan
            foreach ($reserved_times as $time) {
                $reserved_start = new DateTime($time['waktu_mulai']);
                $reserved_end = new DateTime($time['waktu_selesai']);

                if ($datetime_mulai < $reserved_end && $datetime_selesai > $reserved_start) {
                    $error_message = "Waktu yang dipilih sudah dipinjam. Silakan pilih waktu lain.";
                    break;
                }
            }

            // Validasi jumlah orang
            if (empty($jumlah_orang)) {
                $error_message = "Jumlah orang yang memakai ruangan harus diisi.";
            } elseif ($jumlah_orang < 2) {
                $error_message = "Jumlah orang harus lebih dari 1.";
            } elseif ($jumlah_orang > $kapasitas) {
                $error_message = "Jumlah orang tidak boleh melebihi kapasitas ruangan yang tersedia (maksimal: " . $kapasitas . " orang).";
            }

            if (empty($error_message)) {
                // Simpan data peminjaman
                $stmt_insert = $conn->prepare("
                    INSERT INTO peminjaman (kode_ruangan, tanggal_pemakaian, waktu_mulai, waktu_selesai, keperluan, nik)    
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt_insert->bind_param("ssssss", $kode_ruang, $tanggal_pemakaian, $waktu_mulai, $waktu_selesai, $keperluan, $nik);
                if ($stmt_insert->execute()) {
                    // Set notifikasi berhasil
                    $success_message = "Peminjaman berhasil dilakukan.";
                } else {
                    // Set notifikasi gagal
                    $error_message = "Terjadi kesalahan saat menyimpan data: " . $stmt_insert->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Rincian Pinjaman</title>
    <link rel="stylesheet" href="../back-end/admin/pinjam-ruang.css">
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
                        <img src="<?= $foto_user ?>" alt="user Profile" class="profile-img">
                        <span class="username"><?= $nama_lengkap ?></span>
                    </div>
                    <div class="logout">
                        <a href="../auth/logout.php">Keluar</a>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="row">
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
                    <li><a href="../admin/data-diri.php">Data Pengguna</a></li>
                </ul>
            </div>
            <div class="content">
                <h1>Detail Pinjaman</h1>
                <a href="../admin/pinjam-ruang.php?id_gedung=<?= $id_gedung ?>&kode_ruangan=<?= $kode_ruang ?>" class="back-btn">&lt; Kembali ke Formulir</a>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 20px;">
                        <?= $error_message ?>
                    </div>
                <?php elseif (!empty($success_message)): ?>
                    <div class="success-message" style="color: green; margin-bottom: 20px;">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
                <form action="../admin/validasi-pinjam.php?id_gedung=<?= $id_gedung ?>&kode_ruangan=<?= $kode_ruang ?>" method="POST">
                    <div class="form-group">
                        <label for="nama_gedung">Gedung</label>
                        <input type="text" name="nama_gedung" id="nama_gedung" value="<?= $nama_gedung ?>" readonly required>
                        <input type="hidden" name="id_gedung" id="id_gedung" value="<?= $id_gedung ?>" required>

                        <label for="kode_ruang">Kode Ruangan</label>
                        <input type="text" name="kode_ruang" id="kode_ruang" value="<?= $kode_ruang ?>" readonly required>

                        <label for="tanggal_pemakaian">Tanggal Pemakaian</label>
                        <input type="text" name="tanggal_pemakaian" id="tanggal_pemakaian" 
                            value="<?= strftime('%d %B %Y', strtotime($tanggal_pemakaian)) ?>" 
                            readonly required>

                        <label for="waktu_mulai">Waktu Mulai</label>
                        <input type="text" id="waktu_mulai" value="<?= $waktu_mulai ?>" readonly required>

                        <label for="waktu_selesai">Waktu Selesai</label>
                        <input type="text" id="waktu_selesai" value="<?= $waktu_selesai ?>" readonly required>

                        <label for="keperluan">Keperluan</label>
                        <input type="text" id="keperluan" value="<?= $keperluan ?>" readonly required>

                        <label for="jumlah_orang">Jumlah orang yang memakai ruangan</label>
                        <input type="number" name="jumlah_orang" id="jumlah_orang" min="2" max="<?= $kapasitas ?>" value="<?=$jumlah_orang?>" readonly required>
                    </div>
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
