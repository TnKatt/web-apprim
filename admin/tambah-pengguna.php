<?php
// Masukkan file koneksi database
require_once('../koneksi/koneksi.php');

// Mulai sesi untuk memastikan CSRF token dan autentikasi
session_start();

// Pastikan CSRF token ada dan valid
if (!isset($_SESSION['csrf_token'])) {
    die('CSRF token missing');
}

// Pastikan token CSRF valid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi CSRF token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    // Ambil data dari form
    $nik = $_POST['nik'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $kata_sandi = $_POST['kata_sandi'];
    $email = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : null; // Email bisa kosong (null)
    $peran = $_POST['peran'];
    $foto_pengguna = isset($_FILES['foto_pengguna']) ? $_FILES['foto_pengguna'] : null;

    // Validasi data input
    if (empty($nik) || empty($nama_lengkap) || empty($kata_sandi) || empty($peran)) {
        header("Location: ../admin/data-pengguna.php?message=error_empty_fields");
        exit();
    }

    // Validasi email jika ada input
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/data-pengguna.php?message=error_invalid_email");
        exit();
    }

    // Validasi file gambar
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    // Tentukan nama file default jika tidak ada foto yang diupload
    $new_file_name = 'foto_default.jpg';

    if ($foto_pengguna && $foto_pengguna['error'] === UPLOAD_ERR_OK) {
        $file_name = $foto_pengguna['name'];
        $file_tmp = $foto_pengguna['tmp_name'];
        $file_size = $foto_pengguna['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Cek ekstensi file
        if (!in_array($file_ext, $allowed_extensions)) {
            header("Location: ../admin/data-pengguna.php?message=error_invalid_file_extension");
            exit();
        }

        // Cek ukuran file
        if ($file_size > $max_file_size) {
            header("Location: ../admin/data-pengguna.php?message=error_file_too_large");
            exit();
        }

        // Ganti nama file untuk menghindari konflik
        $new_file_name = uniqid('foto_', true) . '.' . $file_ext;

        // Tentukan direktori untuk menyimpan gambar
        $upload_dir = '../images/pengguna/';
        if (!move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            header("Location: ../admin/data-pengguna.php?message=error_file_upload_failed");
            exit();
        }
    }

    // Enkripsi kata sandi
    $kata_sandi_enkripsi = password_hash($kata_sandi, PASSWORD_BCRYPT);

    try {
        // Gunakan prepared statement untuk mencegah SQL Injection
        // Pastikan hanya mengirimkan 6 parameter
        $stmt = $conn->prepare("INSERT INTO pengguna (nik, nama_lengkap, kata_sandi, email, peran, foto_pengguna) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Jika tidak ada foto, kirim foto default
        // Jika email kosong, kirim NULL
        $stmt->bind_param("ssssss", $nik, $nama_lengkap, $kata_sandi_enkripsi, $email, $peran, $new_file_name);

        // Eksekusi query
        if ($stmt->execute()) {
            // Redirect dengan pesan sukses
            header("Location: ../admin/data-pengguna.php?message=add_success");
            exit();
        } else {
            // Tampilkan pesan error jika gagal
            header("Location: ../admin/data-pengguna.php?message=error_insert_failed");
            exit();
        }

        // Tutup statement
        $stmt->close();
    } catch (Exception $e) {
        // Tampilkan pesan error jika terjadi exception
        error_log($e->getMessage());
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
