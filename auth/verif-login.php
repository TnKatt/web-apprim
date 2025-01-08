<?php
session_start();
require '../koneksi/koneksi.php'; // Memuat koneksi ke database

// Cek apakah CSRF token valid
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token.');
}

// Ambil data yang dikirim melalui POST
$nik = $_POST['nik'];
$password = $_POST['kata_sandi'];

// Query untuk mengambil data pengguna berdasarkan NIK
$sql = "SELECT * FROM pengguna WHERE nik = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nik); // Mengikat parameter nik
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah pengguna ditemukan
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password menggunakan password_verify
    if (password_verify($password, $user['kata_sandi'])) {
        // Password cocok, simpan data pengguna di session
        $_SESSION['nik'] = $nik;
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['peran'] = $user['peran'];
        
        // Cek peran dan arahkan ke halaman yang sesuai
        switch ($user['peran']) {
            case 'Admin':
                header("Location: ../admin/halaman-utama.php");
                break;
            case 'Dosen':
                header("Location: ../dosen/halaman-utama.php");
                break;
            case 'Mahasiswa':
                header("Location: ../mahasiswa/halaman-utama.php");
                break;
            case 'PIC Ruangan':
                header("Location: ../pic/halaman-utama.php");
                break;
            default:
                header("Location: ../auth/login.php?error=peran tidak dikenali");
                break;
        }
        exit();
    } else {
        // Password tidak cocok
        header("Location: ../auth/login.php?error=Kata sandi salah");
        exit();
    }
} else {
    // Pengguna tidak ditemukan
    header("Location: ../auth/login.php?error=Pengguna tidak ditemukan");
    exit();
}

?>