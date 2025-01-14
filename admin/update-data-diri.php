<?php
// Sertakan koneksi database
include('../koneksi/koneksi.php');

// Mulai session untuk mendapatkan nik yang login
session_start();

// Cek apakah nik sudah diset di session (pastikan login)
if (!isset($_SESSION['nik'])) {
    die("Anda harus login terlebih dahulu.");
}

// Ambil nik dari session
$nik = $_SESSION['nik'];

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

// Inisialisasi variabel
$uploadDir = '../images/pengguna/';  // Direktori upload gambar
$defaultPhoto = $uploadDir . 'foto_default.jpg';  // Foto default
$foto_pengguna = !empty($user_data['foto_pengguna']) ? $uploadDir . $user_data['foto_pengguna'] : $defaultPhoto; // Menentukan foto pengguna (jika ada)

// Mengecek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengecek jika ada file yang diupload untuk foto profil
    if (isset($_FILES['foto_pengguna']) && $_FILES['foto_pengguna']['error'] == 0) {
        // Menggunakan timestamp dan nik untuk membuat nama file yang lebih panjang dan unik
        $fileExtension = strtolower(pathinfo($_FILES['foto_pengguna']['name'], PATHINFO_EXTENSION));
        
        // Generate unique file name
        $uniqueFileName = 'pengguna_' . $nik . '_' . uniqid() . '_' . bin2hex(random_bytes(5)) . '.' . $fileExtension;
        
        $targetFile = $uploadDir . $uniqueFileName;

        // Mengecek apakah tipe file yang diupload sesuai (hanya gambar yang diperbolehkan)
        $validImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $validImageTypes)) {
            // Jika file valid, kita akan memeriksa dan menghapus foto lama jika ada
            if (!empty($user_data['foto_pengguna']) && $user_data['foto_pengguna'] !== 'foto_default.jpg') {
                // Hapus foto lama
                $oldFile = $uploadDir . $user_data['foto_pengguna'];
                if (file_exists($oldFile)) {
                    unlink($oldFile); // Menghapus file lama
                }
            }

            // Pindahkan file baru ke folder tujuan
            if (move_uploaded_file($_FILES['foto_pengguna']['tmp_name'], $targetFile)) {
                // Update foto profil di database
                $stmt = $conn->prepare("UPDATE pengguna SET foto_pengguna = ? WHERE nik = ?");
                $stmt->bind_param("ss", $uniqueFileName, $nik);
                $stmt->execute();
                $stmt->close();
                $foto_pengguna = $targetFile; // Update foto pengguna yang baru
            } else {
                echo "Maaf, terjadi kesalahan saat mengunggah file Anda.";
            }
        } else {
            echo "Maaf, hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
        }
    }

    // Mengecek apakah ada perubahan pada email
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = htmlspecialchars($_POST['email']); // Menyaring input untuk keamanan

        // Update email di database
        $stmt = $conn->prepare("UPDATE pengguna SET email = ? WHERE nik = ?");
        $stmt->bind_param("ss", $email, $nik);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect ke halaman data-diri.php setelah perubahan disimpan
    header("Location: ../admin/data-diri.php");
    exit();
}
?>
