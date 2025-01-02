<?php
    session_start();

    // Periksa apakah session user sudah ada
    if (!isset($_SESSION['nik'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    require_once('../koneksi/koneksi.php');
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

    if ($role_user !== 'Mahasiswa') {
        die("Akses hanya untuk Mahasiswa.");
    }

    $query = "SELECT g.nama_gedung, p.kode_ruangan, p.tanggal_pemakaian, p.waktu_mulai, p.waktu_selesai, 
                     p.tanggal_peminjaman, p.keperluan, p.status_peminjaman
              FROM peminjaman p
              JOIN ruangan r ON p.kode_ruangan = r.kode_ruangan
              JOIN gedung g ON r.id_gedung = g.id_gedung
             WHERE p.nik = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result_riwayat = $stmt->get_result();
    
    if (!empty($_SESSION['notification'])) {
        unset($_SESSION['notification']);
    }

    // Fungsi untuk format tanggal
    function formatTanggalIndonesia($tanggal, $formatWaktu = false) {
        $bulanIndonesia = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $hariIndonesia = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        $timestamp = strtotime($tanggal);
        if (!$timestamp) {
            return '-'; // Jika tanggal tidak valid
        }

        $hari = date('d', $timestamp);
        $bulan = $bulanIndonesia[date('n', $timestamp)];
        $tahun = date('Y', $timestamp);

        if ($formatWaktu) {
            $waktu = date('H:i', $timestamp) . ' WIB';
            $hariNama = $hariIndonesia[date('l', $timestamp)];
            return "{$waktu}, {$hariNama} {$hari}/" . date('m/Y', $timestamp);
        }

        return "{$hari} {$bulan} {$tahun}";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Notifikasi</title>
    <link rel="stylesheet" href="../back-end/mahasiswa/notifikasi.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .content .display {
            width: 100%;
        }
    </style>
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
                        <img src="<?= $foto_user ?>" alt="user Profile" class="profile-img">
                        <span class="username"><?= htmlspecialchars($nama_lengkap) ?></span>
                    </div>
                    <div class="logout" >
                        <a href="../auth/logout.php" style="font-weight: bold;">Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Navigation -->
            <div class="navigation">
                <ul class="nav-list">
                    <li><a href="../tampilan/roleda.php">Beranda</a></li>
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
                    <li><a href="../mahasiswa/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../mahasiswa/data-diri.php">Data Diri</a></li>
                    <li><a href="../mahasiswa/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../mahasiswa/riwayat.php">Riwayat</a></li>
                </ul>
            </div>
            <div class="content">
                <h1 style="margin-bottom: 20px;">Notifikasi Peminjaman Ruangan</h1>
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nama Gedung</th>
                            <th>Kode Ruangan</th>
                            <th>Tanggal Pemakaian</th>
                            <th>Waktu Pemakaian</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Keperluan</th>
                            <th>Status Peminjaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_riwayat->num_rows > 0) {
                            while ($row = $result_riwayat->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['nama_gedung']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['kode_ruangan']) . '</td>';
                                echo '<td>' . formatTanggalIndonesia($row['tanggal_pemakaian']) . '</td>';
                                echo '<td>' . htmlspecialchars(substr($row['waktu_mulai'], 0, 5) . " - " . substr($row['waktu_selesai'], 0, 5)) . '</td>';
                                echo '<td>' . formatTanggalIndonesia($row['tanggal_peminjaman'], true) . '</td>';
                                echo '<td>' . htmlspecialchars($row['keperluan']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['status_peminjaman']) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">Tidak ada riwayat peminjaman ditemukan.</td></tr>';
                        }
                        ?>
                    </tbody> 
                </table>
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
        $(document).ready(function () {
        // Inisialisasi DataTable tanpa paginasi
        $('#example').DataTable({
            paging: false, // Nonaktifkan paginasi
            searching: true, // Tetap aktifkan fitur pencarian
            info: false, // Nonaktifkan informasi jumlah data (opsional)
            language: {
                search: "Cari:"
            }
        });
        }
    )
    </script>
</body>
</html>
