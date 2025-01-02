<?php
session_start();
require '../koneksi/koneksi.php'; // Memuat koneksi ke database

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php?error=Harap login terlebih dahulu.");
    exit();
}

// Tangkap data dari form
$nik = $_SESSION['nik']; // NIK dari sesi pengguna yang login
$kata_sandiLama = $_POST['kata_sandi_lama'];
$kata_sandiBaru = $_POST['kata_sandi_baru'];
$konfirmasikata_sandiBaru = $_POST['konfirmasi_kata_sandi_baru'];

// Validasi input
if (empty($kata_sandiLama) || empty($kata_sandiBaru) || empty($konfirmasikata_sandiBaru)) {
    header("Location: ganti-kata-sandi.php?error=Semua kolom wajib diisi.");
    exit();
}

if ($kata_sandiBaru !== $konfirmasikata_sandiBaru) {
    header("Location: ganti-kata-sandi.php?error=kata sandi baru dan ulangi kata sandi tidak cocok.");
    exit();
}

// Ambil data pengguna berdasarkan NIK
$sql = "SELECT * FROM pengguna WHERE nik = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah pengguna ditemukan
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verifikasi kata_sandi lama
    if (password_verify($kata_sandiLama, $user['kata_sandi'])) {
        // Hash kata_sandi baru
        $hashedkata_sandi = password_hash($kata_sandiBaru, PASSWORD_DEFAULT);

        // Update kata_sandi di database
        $updateSql = "UPDATE pengguna SET kata_sandi = ? WHERE nik = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $hashedkata_sandi, $nik);


        if ($updateStmt->execute()) {
            header("Location: ganti-kata-sandi.php?success=Kata sandi berhasil diubah.");
        } else {
            header("Location: ganti-kata-sandi.php?error=Terjadi kesalahan saat mengubah kata sandi.");
        }
    } else {
        header("Location: ganti-kata-sandi.php?error=Kata sandi lama tidak sesuai.");
    }
} else {
    header("Location: ganti-kata-sandi.php?error=Pengguna tidak ditemukan.");
}

$conn->close();
exit();
?>
