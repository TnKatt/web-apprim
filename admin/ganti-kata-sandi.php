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
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;    
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #566D7e;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            text-align: center;
            width: 350px;
        }

        .container img {
            width: 100px;
            height: 100px;  
            border-radius: 50%;
            margin-bottom: 20px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        .form-group label {
            display: block;
            text-align: left;
            margin: 1px 43px 14px;
            font-size: 12px;

        }

        .form-group {
            font-weight: bold;
        }

        .container .text{
            width: 70%;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .button-group {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        button {
            padding: 10px 20px;
            background-color: #29465d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .container button:hover {
            background-color: #7f92a0;
        }


        .container .back-to-login {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            font-size: 12px;
        }
        .container .back-to-login hr {
            flex: 1;
            border: none;
            border-top: 1px solid #000;
        }

        .container .back-to-login a {
            font-weight: bold;
            color: #000;
            text-decoration: none;
        }

        .container .button-group a {
            font-weight: bold;
            color: #000;
            text-decoration: none;
        }
        .container .back-to-login span {
            margin: 0 20px;
            font-weight: bold;
            text-align: center;
        }

        .container .footer {
            font-size: 11px;
            margin-top: 30px;
            font-weight: bold;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
            font-size: 15px;
        }
    </style>
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
                <input class ="text" type="password" id="kata_sandi_lama" name="kata_sandi_lama" placeholder='Masukkan kata sandi lama' required >

                <label for="nama">Kata Sandi Baru:</label>
                <input class ="text" type="password" id="kata_sandi_baru" name="kata_sandi_baru" placeholder='Masukkan kata sandi baru' required >

                <label for="email">Ulang Kata Sandi Baru</label>
                <input class ="text" type="password" id="konfirmasi_kata_sandi_baru" name="konfirmasi_kata_sandi_baru" placeholder='Masukkan kata sandi baru' required >
                
            </div>
            <div class="button-group">
                <button type="submit">Ganti Kata Sandi</button>
            </div>
        </form>
        <div class="back-to-login">
            <hr/>
            <span><a href='../admin/data-diri.php'>Kembali Ke Data Diri</a></span>
            <hr/>
        </div>
        <div class="footer">
            APPRIM <br>
            APlikasi Peminjaman Ruang Meeting
        </div>
    </div>
</body>
</html>