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
$foto_user = $user_data['foto_pengguna'];

// Periksa apakah foto pengguna bukan foto default
if (!empty($foto_user) && $foto_user !== 'foto_default.jpg') {
    $uploadDir = '../images/pengguna/';
    $filePath = $uploadDir . $foto_user;

    // Hapus file foto dari server
    if (file_exists($filePath)) {
        unlink($filePath); // Menghapus foto dari server
    }

    // Update foto_pengguna menjadi NULL di database
    $stmt = $conn->prepare("UPDATE pengguna SET foto_pengguna = NULL WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $stmt->close();
}

// Redirect kembali ke halaman data diri
header("Location: ../admin/data-diri.php");
exit();
?>
