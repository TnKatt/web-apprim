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

    // Ambil data user dari database
    $nik = $_SESSION['nik']; // Ambil NIK dari session

    // Gunakan prepared statement untuk keamanan
    $stmt_user = $conn->prepare("SELECT * FROM user WHERE nik = ?");
    $stmt_user->bind_param("s", $nik);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    // Periksa apakah data user ditemukan
    if ($result_user->num_rows === 0) {
        die("Data user tidak ditemukan.");
    }

    $user_data = $result_user->fetch_assoc();
    $foto_user = !empty($user_data['foto_user']) ? "../images/user/" . htmlspecialchars($user_data['foto_user']) : "../images/user/foto_default.jpg";
    $nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
    // Ambil data role user
    $role_user = $user_data['role'];
    $email_user = $user_data['email'];

    // Validasi role untuk akses halaman
    if ($role_user !== 'Dosen') {
        die("Akses hanya untuk Dosen.");
    }

    // Ambil data gedung dari database
    $sql_gedung = "SELECT * FROM gedung";
    $result_gedung = $conn->query($sql_gedung);

    if (!$result_gedung) {
        die("Query gagal: " . $conn->error);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Profile</title>
    <link rel="stylesheet" href="../back-end/Dosen/profile.css">
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
                        <a href="../auth/logout.php">Logout</a>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="navigation">
                <ul class="nav-list">
                    <li><a href=".../Dosen/home.php">Home</a></li>
                    <li><a href="../tampilan/tentang.php">Tentang Kami</a></li>
                    <li><a href="../tampilan/fitur.php">Fitur</a></li>
                    <li><a href="../tampilan/kontak.php">Kontak</a></li>
                </ul>
            </div>

            <!-- Sidebar dan Content -->
            <div class="row">
                <div class="sidebar">
                    <h2>Menu</h2>
                    <ul class="sidebar-list">
                        <li><a href="../Dosen/dashboard.php">Dashboard</a></li>
                        <li><a href="../Dosen/profile.php">Profile</a></li>
                        <li><a href="../Dosen/notifikasi.php">Notifikasi</a></li>
                        <li><a href="../Dosen/riwayat.php">Riwayat</a></li>
                        
                        <!-- Data User Dropdown -->
                        <li class="menu-item">
                            <a href="javascript:void(0)">Data User</a>
                            <ul>
                                <a href="../Dosen/mahasiswa.php">Mahasiswa</a>
                                <a href="../Dosen/dosen.php">Dosen</a>
                                <a href="../Dosen/pic.php">PIC</a>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="content">
                    <div class="building-box">
                        <div class="building-left">
                            <img src="../images/user/foto_default.jpg" alt="" class="building-img">
                        </div>
                        <div class="building-right">
                            <h2 class="building-name">Profile</h2>
                            <table class="profile-info">
                                <tr>
                                    <td>Peran</td>
                                    <td>:</td>
                                    <td><?=$role_user?></td>
                                </tr>
                                <tr>
                                    <td>NIM</td>
                                    <td>:</td>
                                    <td><?=$nik?></td>
                                </tr>
                                <tr>
                                    <td>Nama Lengkap</td>
                                    <td>:</td>
                                    <td><?=$nama_lengkap?></td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td><?= !empty($email_user) ? htmlspecialchars($email_user) : "Email belum dimasukkan" ?></td>
                                </tr>
                            </table>
 
                            <div class="button-container">
                                <a href="../Dosen/ganti-password.php" class="edit-building-btn">Ganti Password</a>
                                <a href="" class="choose-room-btn">Edit Profile</a>
                            </div>
                        </div>
                    </div>
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