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

    // Ambil data pengguna dari database
    $nik = $_SESSION['nik']; // Ambil NIK dari session

    // Gunakan prepared statement untuk keamanan
    $stmt_pengguna = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
    $stmt_pengguna->bind_param("s", $nik);
    $stmt_pengguna->execute();
    $result_pengguna = $stmt_pengguna->get_result();

    // Periksa apakah data pengguna ditemukan
    if ($result_pengguna->num_rows === 0) {
        die("Data pengguna tidak ditemukan.");
    }

    $pengguna_data = $result_pengguna->fetch_assoc();
    $foto_pengguna = !empty($pengguna_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($pengguna_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
    $nama_lengkap = htmlspecialchars($pengguna_data['nama_lengkap']);
    
    // Ambil data peran pengguna
    $peran_pengguna = $pengguna_data['peran'];

    // Validasi peran untuk akses halaman
    if ($peran_pengguna !== 'PIC Ruangan') {
        die("Akses hanya untuk pic.");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Pembatalan</title>
    <link rel="stylesheet" href="../back-end/pic/pembatalan.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="../images/aplikasi/logo.jpg" alt="Logo" class="logo-img">
                <span class="title">APPRIM | PEMBATALAN</span>
            </div>
            <div class="profile-logout">
                <div class="profile">
                    <img src="<?= $foto_pengguna ?>" alt="pengguna Profile" class="profile-img">
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
                    <li><a href="../pic/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../pic/data-diri.php">Data Diri</a></li>
                    <li><a href="/notifications">Notifikasi</a></li>
                    <li><a href="../pic/riwayat.php">Riwayat</a></li>
                </ul>
            </div>
            <div class="content">
                <h1>Pembatalan</h1>
                <div class="history-item">
                    <div class="inner-border">
                        <div class="left">
                            <div class="code">
                                TA.81
                            </div>  
                        <div class="details">
                            <p>01 - 11 - 2024, 08:00 - 10:00 WIB</p>
                            <p>Bimbingan pic</p>
                            <div>
                                <span>Alasan: </span><input type="text" placeholder="Berikan alasan mu"></input> 
                            </div>
                        </div>
                    </div>

                    <div class="right">

                        <div class="buttons">
                            <a href="../pic/riwayat.php"><button class="detail">Kembali</button></a>
                            <a href="../pic/detail-history-pic.php"><button class="cancel">Batalkan</button></a>
                        </div>

                        </div>
                    </div>
                </div>
                </div> 
                </div>  
            </div>
        </div>

        <div class="row">
                <div class="footer">
                    <div class="footer-left">
                        <img src="../images/polibatam.jpg" alt="Logo" class="footer-logo">
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