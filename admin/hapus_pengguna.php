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

    // Ambil NIK dari form
    $nik = $_POST['nik'];

    // Pastikan NIK tidak kosong
    if (empty($nik)) {
        header("Location: ../admin/data-pengguna.php?message=error_empty_nik");
        exit();
    }

    // Sanitasi input untuk mencegah XSS
    $nik = htmlspecialchars($nik, ENT_QUOTES, 'UTF-8');

    try {
        // Gunakan prepared statement untuk menghapus pengguna berdasarkan NIK
        $stmt = $conn->prepare("DELETE FROM pengguna WHERE nik = ?");
        $stmt->bind_param("s", $nik);

        // Eksekusi query
        if ($stmt->execute()) {
            // Redirect ke halaman data pengguna dengan pesan sukses
            header("Location: ../admin/data-pengguna.php?message=delete_success");
            exit();
        } else {
            // Tampilkan pesan error jika terjadi kegagalan
            header("Location: ../admin/data-pengguna.php?message=error_delete_failed");
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
