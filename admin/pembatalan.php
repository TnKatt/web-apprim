<?php
// Koneksi ke database
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari input POST
    $id_peminjaman = $_POST['id_peminjaman'];
    $nik = $_POST['nik'];

    // Validasi input
    if (empty($id_peminjaman) || empty($nik)) {
        die("Data tidak lengkap.");
    }

    try {
        // Mulai transaksi
        $pdo->beginTransaction();

        // 1. Update status_peminjaman di tabel peminjaman
        $sqlUpdate = "UPDATE peminjaman SET status_peminjaman = 'gagal' WHERE id_peminjaman = :id_peminjaman";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id_peminjaman', $id_peminjaman);
        $stmtUpdate->execute();

        // 2. Insert ke tabel notifikasi
        $sqlInsertNotifikasi = "INSERT INTO notifikasi (id_peminjaman, nik, status_peminjaman) VALUES (:id_peminjaman, :nik, 'dibatalkan')";
        $stmtNotifikasi = $pdo->prepare($sqlInsertNotifikasi);
        $stmtNotifikasi->bindParam(':id_peminjaman', $id_peminjaman);
        $stmtNotifikasi->bindParam(':nik', $nik);
        $stmtNotifikasi->execute();

        // 3. Insert ke tabel riwayat
        $sqlInsertRiwayat = "INSERT INTO riwayat (nik, id_peminjaman) VALUES (:nik, :id_peminjaman)";
        $stmtRiwayat = $pdo->prepare($sqlInsertRiwayat);
        $stmtRiwayat->bindParam(':nik', $nik);
        $stmtRiwayat->bindParam(':id_peminjaman', $id_peminjaman);
        $stmtRiwayat->execute();

        // Commit transaksi
        $pdo->commit();

        echo "Proses pembatalan berhasil.";
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $pdo->rollBack();
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
} else {
    echo "Metode request tidak valid.";
}
?>
