<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | LUPA KATA SANDI</title>
    <link rel="icon" type="jpg" href="../images/aplikasi/logoo.jpg">
    <link rel="stylesheet" href="../back-end/auth/lupa.css">
</head>
<body>
    <div class="container">
        <img src="../images/aplikasi/logo.jpg" alt="logo" srcset="">

        <!-- Error message -->
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form action="validasi-lupa.php" method="POST">
            <div class="form-group">
                <label for="nik">Data Diri:</label>
                <input class="text" type="text" id="nik" name="nik" placeholder="Masukkan NIK / NIM / NIDN" required>

                <label for="nama">Nama Lengkap:</label>
                <input class="text" type="text" id="nama" name="nama" placeholder="Masukkan Nama Lengkap Anda" required>

                <label for="email">Email:</label>
                <input class="text" type="email" id="email" name="email" placeholder="Masukkan Email Anda" required>
            </div>
            <div class="button-group">
                <button type="submit">Kirim</button>
            </div>
        </form>

        <div class="back-to-login">
            <hr/>
            <span><a href="../auth/login.php">Kembali ke Halaman masuk</a></span>
            <hr/>
        </div>

        <div class="footer">
            APPRIM <br>
            Aplikasi Peminjaman Ruang Meeting
        </div>
    </div>
</body>
</html>
