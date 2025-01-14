<?php
    // Masukkan file koneksi
    require_once('../koneksi/koneksi.php');

    // Pastikan session dimulai di awal file
    session_start();

    // Cek apakah CSRF token sudah ada, jika tidak buat baru
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Menghasilkan token yang aman
    }



    // Query untuk mengambil semua data user
    $sql = "SELECT * FROM pengguna";
    $result = $conn->query($sql);

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

    // Pastikan user sudah login
    if (!isset($_SESSION['nik'])) {
        header("Location: ../auth/login.php"); // Jika belum login, redirect ke login page
        exit();
    }

    // Validasi role untuk akses halaman
    if ($role_user !== 'Admin') {
        die("Akses hanya untuk Admin.");
    }
    // Inisialisasi variabel
    $uploadDir = '../images/pengguna/';  // Direktori upload gambar
    $defaultPhoto = $uploadDir . 'foto_default.jpg';  // Foto default
    $email = $user_data['email'];

    // Mengecek apakah form sudah disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Mengecek jika ada file yang diupload untuk foto profil
        if (isset($_FILES['foto_pengguna']) && $_FILES['foto_pengguna']['error'] == 0) {
            $fileName = basename($_FILES['foto_pengguna']['name']);
            $targetFile = $uploadDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Mengecek apakah tipe file yang diupload sesuai (hanya gambar yang diperbolehkan)
            $validImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $validImageTypes)) {
                // Jika file valid, pindahkan file ke folder tujuan
                if (move_uploaded_file($_FILES['foto_pengguna']['tmp_name'], $targetFile)) {
                    // Update foto profil di database
                    $stmt = $conn->prepare("UPDATE pengguna SET foto_pengguna = ? WHERE nik = ?");
                    $stmt->bind_param("ss", $fileName, $nik);
                    $stmt->execute();
                    $stmt->close();
                    $foto_user = $targetFile; // Perbarui foto yang ditampilkan
                } else {
                    echo "Maaf, terjadi kesalahan saat mengunggah file Anda.";
                }
            } else {
                echo "Maaf, hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
            }
        }

        // Mengecek apakah ada perubahan pada email
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stmt = $conn->prepare("UPDATE pengguna SET email = ? WHERE nik = ?");
                $stmt->bind_param("ss", $email, $nik);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Format email tidak valid.";
            }
        }

        // Redirect ke halaman data-diri.php setelah perubahan disimpan
        header("Location: ../admin/data-diri.php");
        exit();
    }

    $conn->close(); // Tutup koneksi
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>APPRIM | Edit Data Diri</title>
        <link rel="stylesheet" href="../back-end/admin/edit-data-diri.css">
        <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
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
                            <img src="<?= $foto_user ?>" alt="foto user" class="profile-img">
                            <span class="username"><?= htmlspecialchars($nama_lengkap) ?></span>
                        </div>
                        <div class="logout">
                            <a href="../auth/logout.php">Keluar</a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="navigation">
                    <ul class="nav-list">
                        <li><a href=".../tampilan/beranda.php">Beranda</a></li>
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
                            <li><a href="../admin/halaman-utama.php">Halaman Utama</a></li>
                            <li><a href="../admin/data-diri.php">Data Diri</a></li>
                            <li><a href="../admin/notifikasi.php">Notifikasi</a></li>
                            <li><a href="../admin/riwayat.php">Riwayat</a></li>
                            <li><a href="../admin/data-pengguna.php">Data Pengguna</a></li>
                        </ul>
                    </div>
                    <div class="content">
                        <div class="building-box">
                            <div class="building-left">
                                <img src="<?= $foto_user ?>" alt="Foto User" class="building-img" id="foto_preview">
                            </div>
                            <div class="building-right">
                                <h2 class="building-name">Edit Data Diri</h2>
                                <form action="../admin/update-data-diri.php" method="post" enctype="multipart/form-data">
                                    <table class="profile-info">
                                        <tr>
                                            <td>Foto Data Diri</td>
                                            <td>:  <input type="file" name="foto_pengguna" id="foto_pengguna" accept="image/*" onchange="previewFoto()"></td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td>: <input name="email" id="email" type="email" placeholder='Input Email' value="<?php echo $email; ?>"></td>
                                        </tr>
                                    </table>
                                    <br>
                                    <br>
                                    <div class="button-container">
                                        <!-- Tombol Hapus Foto -->
                                        <?php if ($foto_user != $defaultPhoto): ?>
                                            <a href="../admin/hapus-foto.php" class="delete-button">Hapus Foto</a>
                                        <?php endif; ?>
                                        <!-- Tombol Simpan: Menyimpan foto profil dan email -->
                                        <a href="../admin/data-diri.php" class="delete-button">Batal</a>
                                        <button type="submit" class="submit-btn" style="border: none;">Simpan</button>
                                    </div>
                                </form>
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
            </div>
            <script>
            // Fungsi untuk pratinjau foto profil
            function previewFoto() {
                const fileInput = document.getElementById('foto_pengguna');
                const previewImg = document.getElementById('foto_preview');

                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                    };
                    reader.onerror = function () {
                        console.error('Kesalahan saat membaca file.');
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                } else {
                    console.log('Tidak ada file yang dipilih.');
                }
            }
            </script>
        </body>
    </html> 