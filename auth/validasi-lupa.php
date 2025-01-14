<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include library PHPMailer secara manual
require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nik = htmlspecialchars($_POST['nik']);
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);

    if (empty($nik) || empty($nama) || empty($email)) {
        echo "<script>
                alert('Semua harus diisi!');
                window.location.href = '../auth/lupa.php';
              </script>";
        exit();
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'adhykarya@gmail.com'; // Ganti dengan email SMTP Anda
        $mail->Password = 'igov mmas dmon fqsh'; // Ganti dengan password atau App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom($email, $nama); // Email pengguna sebagai pengirim
        $mail->addAddress('adhykarya@gmail.com'); // Email admin

        $mail->isHTML(true);
        $mail->Subject = 'Permintaan Lupa Kata Sandi - APPRIM';
        $mail->Body = "
            <h3>Permintaan Lupa Kata Sandi</h3>
            <p><b>NIK/NIM/NIDN:</b> $nik</p>
            <p><b>Nama Lengkap:</b> $nama</p>
            <p><b>Email:</b> $email</p>
        ";

        $mail->send();
        echo "<script>
                alert('Permintaan berhasil dikirim! Silakan menunggu konfirmasi dari admin.');
                window.location.href = 'lupa.php';
              </script>";
    } catch (Exception $e) {
        echo "<script>
                alert('Gagal mengirim email. Kesalahan: " . $mail->ErrorInfo . "');
                window.location.href = 'lupa.php';
              </script>";
    }
}
?>
