<?php
    // Mulai session untuk menggunakan session variables
    session_start();

    // Periksa apakah session user sudah ada
    if (!isset($_SESSION['nik'])) {
        // Jika tidak ada, alihkan user ke halaman login
        header("Location: ../auth/login.php");
        exit();
    }

    // Masukkan file koneksi
    require_once('../koneksi/koneksi.php');

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
    if ($role_user !== 'Mahasiswa') {
        die("Akses hanya untuk Mahasiswa.");
    }
?>

<html>
    <head>
        <title>Pesanan Berhasil</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
        <link rel="stylesheet" href="../back-end/mahasiswa/detail-riwayat.css">
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
                    <img src="<?= $foto_user ?>" alt="user Profile" class="profile-img">
                    <span class="username"><?= htmlspecialchars($nama_lengkap) ?></span>
                </div>
                <div class="logout">
                    <a href="/logout">Keluar</a>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="navigation">
            <ul class="nav-list">
                <li><a href="/beranda">Beranda</a></li>
                <li><a href="/about">Tentang Kami</a></li>
                <li><a href="/features">Fitur</a></li>
                <li><a href="/contact">Kontak</a></li>
            </ul>
        </div>

        <!-- Sidebar dan Content -->
        <div class="row">
            <div class="sidebar">
                <h2>Menu</h2>
                <ul class="sidebar-list">
                    <li><a href="../mahasiswa/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../mahasiswa/data-diri.php">Data Diri</a></li>
                    <li><a href="/notifications">Notifikasi</a></li>
                    <li><a href="../mahasiswa/riwayat.php">Riwayat</a></li>
                </ul>
            </div>

        <div class="content">
            <h1>Detail Pesanan</h1>
                <div class="card">
                <div style="display: flex; align-items: center;">
                <div style="text-align: center;">
                <img alt="ruang" height="100" src="../images/22A.jpg" width="100"/>
                <div class="room-code">TA.81</div>
            </div>
            <div class="details">
                <h2>Ruang Rapat</h2>
                <p>Nama Ruangan : TA.81</p>
                <p>Lokasi : Lantai 2</p>
                <p>PIC Ruangan : -</p>
                <p>Kapasitas : -</p>
                <p>Rating : 8.3 / 10</p>
                <p>Fasilitas : Whiteboard, AC, TV, Injector, Mic & amp, Sound System</p>
            </div>
        </div>

        <div class="info">
            <p>NIK Pemesan :</p>
            <p>Nama Pemesan :</p>
            <p>Tanggal Memesan : 16:00 WIB, 29 Oktober 2024</p>
            <p>Tanggal Pemakaian : 01 November 2024</p>
            <p>Jam Pemakaian : 08:00 - 10:00 WIB</p>
            <p>Keperluan : Bimbingan Mahasiswa</p>  
        </div>
        
        <div class="buttons">
            <a href="../mahasiswa/riwayat.php"><button>Kembali</button></a>
            <button>Email PIC</button>
        </div>
        
    </body>
</html>