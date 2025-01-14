<?php
// Menampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Memulai sesi
session_start();

// Koneksi ke database
require_once '../koneksi/koneksi.php'; // Pastikan jalur ini benar

// Validasi input
if (!isset($_GET['id_peminjaman']) || !is_numeric($_GET['id_peminjaman'])) {
    die('ID Peminjaman tidak valid');
}
$id_peminjaman = (int) $_GET['id_peminjaman'];

// Periksa apakah NIK pengguna tersedia
$nik = isset($_SESSION['nik']) ? $_SESSION['nik'] : null;
if (!$nik) {
    die('Pengguna tidak terautentikasi.');
}

// Validasi bahwa NIK ada di tabel pengguna
$queryCheckNik = "SELECT nik FROM pengguna WHERE nik = ?";
$stmtCheckNik = $conn->prepare($queryCheckNik);
$stmtCheckNik->bind_param('s', $nik);
$stmtCheckNik->execute();
$stmtCheckNik->store_result();

if ($stmtCheckNik->num_rows === 0) {
    die('NIK tidak ditemukan dalam database.');
}

$stmtCheckNik->close();

// Ambil data peminjaman yang ingin dibatalkan
$querySelectPeminjaman = "SELECT id_peminjaman, nik, kode_ruangan, tanggal_pemakaian, waktu_mulai, waktu_selesai, tanggal_peminjaman, keperluan, status_peminjaman, penilaian
                          FROM peminjaman
                          WHERE id_peminjaman = ?";
$stmtSelectPeminjaman = $conn->prepare($querySelectPeminjaman);
$stmtSelectPeminjaman->bind_param('i', $id_peminjaman);
$stmtSelectPeminjaman->execute();
$stmtSelectPeminjaman->store_result();

if ($stmtSelectPeminjaman->num_rows === 0) {
    die('Peminjaman tidak ditemukan.');
}

// Mengambil hasil query
$stmtSelectPeminjaman->bind_result($old_id_peminjaman, $old_nik, $old_kode_ruangan, $old_tanggal_pemakaian, $old_waktu_mulai, $old_waktu_selesai, $old_tanggal_peminjaman, $old_keperluan, $old_status_peminjaman, $old_penilaian);
$stmtSelectPeminjaman->fetch();

// Memulai transaksi
try {
    $conn->autocommit(FALSE);

    // 1. Insert data ke tabel peminjaman dengan status "Dibatalkan"
    $queryInsertPeminjaman = "INSERT INTO peminjaman (nik, kode_ruangan, tanggal_pemakaian, waktu_mulai, waktu_selesai, tanggal_peminjaman, keperluan, status_peminjaman, penilaian)
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'Dibatalkan', ?)";
    $stmtInsertPeminjaman = $conn->prepare($queryInsertPeminjaman);
    $stmtInsertPeminjaman->bind_param('ssssssss', $old_nik, $old_kode_ruangan, $old_tanggal_pemakaian, $old_waktu_mulai, $old_waktu_selesai, $old_tanggal_peminjaman, $old_keperluan, $old_penilaian);
    $stmtInsertPeminjaman->execute();

    // Mendapatkan id_peminjaman yang baru saja diinsert
    $new_id_peminjaman = $conn->insert_id;

    // 2. Insert data ke tabel notifikasi
    $queryInsertNotifikasi = "INSERT INTO notifikasi (id_peminjaman, nik, status_peminjaman) 
                              VALUES (?, ?, 'Dibatalkan')";
    $stmtInsertNotifikasi = $conn->prepare($queryInsertNotifikasi);
    $stmtInsertNotifikasi->bind_param('is', $new_id_peminjaman, $nik);
    $stmtInsertNotifikasi->execute();

    // 3. Hapus data dari tabel riwayat
    $queryDeleteRiwayat = "DELETE FROM riwayat WHERE id_peminjaman = ?";
    $stmtDeleteRiwayat = $conn->prepare($queryDeleteRiwayat);
    $stmtDeleteRiwayat->bind_param('i', $id_peminjaman);
    $stmtDeleteRiwayat->execute();

    // 4. Hapus data notifikasi yang terkait dengan peminjaman yang dibatalkan
    $queryDeleteNotifikasi = "DELETE FROM notifikasi WHERE id_peminjaman = ?";
    $stmtDeleteNotifikasi = $conn->prepare($queryDeleteNotifikasi);
    $stmtDeleteNotifikasi->bind_param('i', $id_peminjaman);
    $stmtDeleteNotifikasi->execute();

    // 5. Hapus data peminjaman yang ingin dibatalkan dari tabel peminjaman
    $queryDeletePeminjaman = "DELETE FROM peminjaman WHERE id_peminjaman = ?";
    $stmtDeletePeminjaman = $conn->prepare($queryDeletePeminjaman);
    $stmtDeletePeminjaman->bind_param('i', $id_peminjaman);
    $stmtDeletePeminjaman->execute();

    // Commit transaksi
    $conn->commit();

    // Redirect dengan pesan sukses
    header("Location: ../admin/riwayat.php?message=Peminjaman%20Berhasil%20Dibatalkan");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika terjadi kesalahan
    $conn->rollback();
    echo "Terjadi kesalahan: " . $e->getMessage();
} finally {
    // Tutup statement jika telah diinisialisasi
    if (isset($stmtSelectPeminjaman)) $stmtSelectPeminjaman->close();
    if (isset($stmtInsertPeminjaman)) $stmtInsertPeminjaman->close();
    if (isset($stmtInsertNotifikasi)) $stmtInsertNotifikasi->close();
    if (isset($stmtDeleteRiwayat)) $stmtDeleteRiwayat->close();
    if (isset($stmtDeleteNotifikasi)) $stmtDeleteNotifikasi->close();
    if (isset($stmtDeletePeminjaman)) $stmtDeletePeminjaman->close();
    $conn->close();
}
?>
