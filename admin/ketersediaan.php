<?php
    // Memulai sesi untuk menggunakan variabel sesi
    session_start();

    // Mengecek apakah pengguna sudah login
    if (!isset($_SESSION['nik'])) {
        // Jika belum login, arahkan ke halaman login
        header("Location: ../auth/login.php");
        exit();
    }

    // Menyertakan file koneksi database
    require_once('../koneksi/koneksi.php');

    // Mendapatkan NIK pengguna dari sesi
    $nik = $_SESSION['nik'];

    // Menggunakan prepared statement untuk mengambil data pengguna berdasarkan NIK
    $stmt_user = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
    $stmt_user->bind_param("s", $nik);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    // Memeriksa apakah data pengguna ditemukan
    if ($result_user->num_rows === 0) {
        die("Data pengguna tidak ditemukan.");
    }

    // Mengambil data pengguna
    $user_data = $result_user->fetch_assoc();
    $foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
    $nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
    $role_user = $user_data['peran'];

    // Mengecek apakah peran pengguna adalah 'Admin'
    if ($role_user !== 'Admin') {
        die("Akses hanya untuk Admin.");
    }

    // Mendapatkan kode ruangan dari URL
    $kode_ruangan = isset($_GET['kode_ruangan']) ? htmlspecialchars($_GET['kode_ruangan']) : null;

    // Jika kode ruangan tidak ditemukan, hentikan eksekusi
    if (!$kode_ruangan) {
        die("Kode ruangan tidak ditemukan.");
    }

    // Mengambil data ruangan berdasarkan kode ruangan
    $stmt_ruangan = $conn->prepare("SELECT * FROM ruangan WHERE kode_ruangan = ?");
    $stmt_ruangan->bind_param("s", $kode_ruangan);
    $stmt_ruangan->execute();
    $result_ruangan = $stmt_ruangan->get_result();

    // Memeriksa apakah data ruangan ditemukan
    if ($result_ruangan->num_rows === 0) {
        die("Data ruangan tidak ditemukan.");
    }

    // Mengambil data ruangan
    $ruangan_data = $result_ruangan->fetch_assoc();

    // Mengambil data peminjaman yang belum selesai untuk ruangan tertentu
    $stmt_peminjaman = $conn->prepare("
        SELECT 
            peminjaman.id_peminjaman,
            peminjaman.waktu_mulai,
            peminjaman.waktu_selesai,
            peminjaman.tanggal_pemakaian,
            pengguna.nama_lengkap AS peminjam
        FROM 
            peminjaman
        LEFT JOIN 
            pengguna ON peminjaman.nik = pengguna.nik
        WHERE 
            peminjaman.kode_ruangan = ? 
            AND CONCAT(peminjaman.tanggal_pemakaian, ' ', peminjaman.waktu_selesai) >= NOW()
        ORDER BY 
            peminjaman.waktu_mulai ASC
    ");
    $stmt_peminjaman->bind_param("s", $kode_ruangan);
    $stmt_peminjaman->execute();
    $result_peminjaman = $stmt_peminjaman->get_result();

    // Fungsi untuk memformat tanggal dalam bahasa Indonesia
    function formatTanggalIndonesia($tanggal, $tampilkanWaktu = false) {
        // Daftar nama bulan dalam bahasa Indonesia
        $bulanIndonesia = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        // Daftar nama hari dalam bahasa Indonesia
        $hariIndonesia = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 
            'Saturday' => 'Sabtu'
        ];

        // Mengonversi tanggal menjadi timestamp
        $timestamp = strtotime($tanggal);
        if (!$timestamp) {
            return '-'; // Jika tanggal tidak valid
        }

        // Memformat hari, bulan, dan tahun
        $hari = date('j', $timestamp);
        $bulan = $bulanIndonesia[date('n', $timestamp)];
        $tahun = date('Y', $timestamp);

        // Jika waktu juga ingin ditampilkan
        if ($tampilkanWaktu) {
            $waktu = date('H:i', $timestamp); // Format jam dan menit
            $hariNama = $hariIndonesia[date('l', $timestamp)]; // Nama hari
            return "{$hariNama}, {$hari} {$bulan} {$tahun} {$waktu} WIB";
        }

        // Hanya tanggal
        return "{$hari} {$bulan} {$tahun}";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Ketersediaan</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../back-end/admin/data-pengguna.css">
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">

    <style>
        #peminjamanTable_wrapper.dataTables_wrapper {
            width: 100%;
        }

        #peminjamanTable_filter.dataTables_filter {
            margin-bottom: 10px;
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
                <h1>Ketersediaan Ruangan <?= htmlspecialchars($ruangan_data['kode_ruangan']) ?></h1>
                <a href="../admin/daftar-ruang.php?id_gedung=<?= htmlspecialchars($ruangan_data['id_gedung']) ?>" class="tambah-pengguna">Kembali ke Daftar Ruang</a>
                <?php if ($result_peminjaman->num_rows > 0): ?>
                    <!-- Tabel untuk menampilkan data peminjaman -->
                    <table style="width: 100%" id="peminjamanTable" class="display">
                        <thead>
                            <tr>
                                <th>Nama Peminjam</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($peminjaman = $result_peminjaman->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($peminjaman['peminjam']) ?></td>
                                    <td><?= formatTanggalIndonesia($peminjaman['tanggal_pemakaian']); ?></td>
                                    <td>
                                        <?php
                                        $waktu_mulai = $peminjaman['waktu_mulai'];
                                        $waktu_selesai = $peminjaman['waktu_selesai'];
                                        echo date("H:i", strtotime($waktu_mulai)) . " - " . date("H:i", strtotime($waktu_selesai)) . " WIB";
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada peminjaman untuk ruangan ini.</p>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function () {
            var table = $('#peminjamanTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.5/i18n/id.json"
                },
                "dom": 'lfrtip',
                "paging": false,
                "info": false,
                "searching": true,
            });

            // Menambahkan fitur pencarian manual
            $('#searchInput').on('keyup', function() {
                table.search(this.value).draw();
            });
        });
    </script>
</body>
</html>
