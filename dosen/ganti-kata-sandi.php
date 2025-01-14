<?php
session_start();
require '../koneksi/koneksi.php'; // Memuat koneksi ke database

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php?error=Harap login terlebih dahulu.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | GANTI KATA SANDI</title>
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
    <link rel="stylesheet" href="../back-end/dosen/ganti-kata-sandi.css">
</head>

<body>
    <div class="container">
        <img src="../images/aplikasi/logo.jpg" alt="logo" srcset="">
        <!-- Error message -->
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>


        <form action="update-kata-sandi.php" method="POST">
            <div class="form-group">
                <label for="username">Kata Sandi Lama:</label>
                <input class ="text" type="text" id="kata_sandi_lama" name="kata_sandi_lama" placeholder='Masukkan kata sandi lama' required >

                <label for="nama">Kata Sandi Baru:</label>
                <input class ="text" type="text" id="kata_sandi_baru" name="kata_sandi_baru" placeholder='Masukkan kata sandi baru' required >

                <label for="email">Ulang Kata Sandi Baru</label>
                <input class ="text" type="text" id="konfirmasi_kata_sandi_baru" name="konfirmasi_kata_sandi_baru" placeholder='Masukkan kata sandi baru' required >
                
            </div>

            <div class="button-group">
                <button type="submit">Ganti Kata Sandi</button>
            </div>
        </form>

        <div class="back-to-login">
            <hr/>
            <span><a href='../dosen/data-diri.php'>Kembali Ke Data Diri</a></span>
            <hr/>
        </div>

        <div class="footer">
            APPRIM <br>
            APlikasi Peminjaman Ruang Meeting
        </div>
    </div>
</body>
</html>