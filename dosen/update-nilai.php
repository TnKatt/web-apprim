<?php
session_start();
require_once('../koneksi/koneksi.php');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

$nik = $_SESSION['nik'];
$stmt_user = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
$stmt_user->bind_param("s", $nik);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Data pengguna tidak ditemukan.");
}

$user_data = $result_user->fetch_assoc();
$role_user = $user_data['peran'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $penilaian_baru = isset($_POST['penilaian']) ? $_POST['penilaian'] : 0;

    try {
        $conn->begin_transaction();

        // Ambil kode_ruangan berdasarkan id_peminjaman
        $stmt_kode_ruangan = $conn->prepare("SELECT kode_ruangan FROM peminjaman WHERE id_peminjaman = ?");
        $stmt_kode_ruangan->bind_param("i", $id_peminjaman);
        $stmt_kode_ruangan->execute();
        $result_kode_ruangan = $stmt_kode_ruangan->get_result();

        if ($result_kode_ruangan->num_rows === 0) {
            throw new Exception("Data peminjaman tidak ditemukan.");
        }

        $row_kode_ruangan = $result_kode_ruangan->fetch_assoc();
        $kode_ruangan = $row_kode_ruangan['kode_ruangan'];

        // Periksa total_penilaian dan jumlah_penilaian di tabel ruangan
        $stmt_check_ruangan = $conn->prepare("SELECT total_penilaian, jumlah_penilaian FROM ruangan WHERE kode_ruangan = ?");
        $stmt_check_ruangan->bind_param("s", $kode_ruangan);
        $stmt_check_ruangan->execute();
        $result_ruangan = $stmt_check_ruangan->get_result();

        if ($result_ruangan->num_rows > 0) {
            $row_ruangan = $result_ruangan->fetch_assoc();
            $total_penilaian = $row_ruangan['total_penilaian'];
            $jumlah_penilaian = $row_ruangan['jumlah_penilaian'];

            // Hitung total_penilaian baru dan jumlah_penilaian baru
            $total_penilaian += $penilaian_baru;
            $jumlah_penilaian += 1;

           // Hitung rata-rata penilaian dan bulatkan ke satu desimal
            $penilaian_rata2 = round($total_penilaian / $jumlah_penilaian, 1);

            // Update total_penilaian, jumlah_penilaian, dan penilaian di tabel ruangan
            $stmt_update_ruangan = $conn->prepare("UPDATE ruangan SET total_penilaian = ?, jumlah_penilaian = ?, penilaian = ? WHERE kode_ruangan = ?");
            $stmt_update_ruangan->bind_param("iids", $total_penilaian, $jumlah_penilaian, $penilaian_rata2, $kode_ruangan);
            $stmt_update_ruangan->execute();
        } else {
            throw new Exception("Data ruangan tidak ditemukan.");
        }

        // Simpan penilaian ke tabel 'penilaian'
        $stmt_insert_penilaian = $conn->prepare("INSERT INTO penilaian (id_peminjaman, nilai_penilaian) VALUES (?, ?)");
        $stmt_insert_penilaian->bind_param("ii", $id_peminjaman, $penilaian_baru);
        $stmt_insert_penilaian->execute();

        // Update penilaian pada tabel 'peminjaman'
        $stmt_update_peminjaman = $conn->prepare("UPDATE peminjaman SET penilaian = ? WHERE id_peminjaman = ?");
        $stmt_update_peminjaman->bind_param("ii", $penilaian_baru, $id_peminjaman);
        $stmt_update_peminjaman->execute();

        $conn->commit();

        echo "<script>alert('Penilaian berhasil dikirim!'); window.location.href = 'riwayat.php';</script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
}

?>
