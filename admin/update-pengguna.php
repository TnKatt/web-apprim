<?php
// Masukkan file koneksi database
require_once('../koneksi/koneksi.php');

// Mulai sesi di awal file
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php"); // Jika belum login, redirect ke login page
    exit();
}

// Pastikan token CSRF valid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi token CSRF
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    // Ambil data dari form
    $nik = $_POST['editnik'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = !empty($_POST['email']) ? $_POST['email'] : null; // Izinkan email kosong (NULL)
    $peran = $_POST['peran'];

    // Validasi data input
    if (empty($nik) || empty($nama_lengkap) || empty($peran)) {
        header("Location: ../admin/data-pengguna.php?message=error_empty_fields");
        exit();
    }

    // Validasi email jika tidak kosong
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/data-pengguna.php?message=error_invalid_email");
        exit();
    }

    // Sanitasi input untuk mencegah XSS dan validasi email
    $nama_lengkap = htmlspecialchars($nama_lengkap, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $peran = htmlspecialchars($peran, ENT_QUOTES, 'UTF-8');

    try {
        // Gunakan prepared statement untuk keamanan
        $stmt = $conn->prepare("UPDATE pengguna SET nama_lengkap = ?, email = ?, peran = ? WHERE nik = ?");
        $stmt->bind_param("ssss", $nama_lengkap, $email, $peran, $nik);

        // Eksekusi query
        if ($stmt->execute()) {
            // Redirect ke halaman data pengguna dengan pesan sukses
            header("Location: ../admin/data-pengguna.php?message=update_success");
            exit();
        } else {
            // Tampilkan pesan error jika terjadi kegagalan
            header("Location: ../admin/data-pengguna.php?message=error_update_failed");
            exit();
        }

        // Tutup statement
        $stmt->close();
    } catch (Exception $e) {
        // Tampilkan pesan error jika terjadi exception
        header("Location: ../admin/data-pengguna.php?message=error_exception");
        exit();
    }
} else {
    // Jika tidak ada data POST, redirect ke halaman data pengguna
    header("Location: ../admin/data-pengguna.php");
    exit();
}

// Tutup koneksi database
$conn->close();
?>

