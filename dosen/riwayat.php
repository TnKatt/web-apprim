<?php
// Mulai session untuk menggunakan session variables
session_start();

// Periksa apakah session user sudah ada
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Masukkan file koneksi
require_once('../koneksi/koneksi.php');

// Set locale ke bahasa Indonesia
setlocale(LC_TIME, 'id_ID.UTF-8');

// Ambil data user dari database
$nik = $_SESSION['nik'];
$stmt_user = $conn->prepare("SELECT * FROM pengguna WHERE nik = ?");
$stmt_user->bind_param("s", $nik);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Data pengguna tidak ditemukan.");
}

$user_data = $result_user->fetch_assoc();
$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);
$role_user = $user_data['peran'];

if ($role_user !== 'Dosen') {
    die("Akses hanya untuk Dosen.");
}

// Ambil riwayat peminjaman
$query = "SELECT r.nik, r.id_peminjaman, p.kode_ruangan, p.tanggal_pemakaian, p.waktu_mulai, p.waktu_selesai, p.keperluan, p.status_peminjaman, p.penilaian
        FROM riwayat r
        JOIN peminjaman p ON r.id_peminjaman = p.id_peminjaman
        WHERE r.nik = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Kesalahan prepare statement riwayat: " . $conn->error);
}
$stmt->bind_param("s", $nik);
$stmt->execute();
$result_riwayat = $stmt->get_result();

if (!empty($_SESSION['notification'])) {
    unset($_SESSION['notification']);
}

// Fungsi untuk format tanggal
function formatTanggalIndonesia($tanggal, $formatWaktu = false) {
    $bulanIndonesia = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $hariIndonesia = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];

    $timestamp = strtotime($tanggal);
    if (!$timestamp) {
        return '-'; // Jika tanggal tidak valid
    }

    $hari = date('d', $timestamp);
    $bulan = $bulanIndonesia[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);

    if ($formatWaktu) {
        $waktu = date('H:i', $timestamp) . ' WIB';
        $hariNama = $hariIndonesia[date('l', $timestamp)];
        return "{$waktu}, {$hariNama} {$hari} {$bulan} {$tahun}";
    }

    return "{$hari} {$bulan} {$tahun}";
}

date_default_timezone_set('Asia/Jakarta'); // PHP

// Fungsi untuk menentukan visibilitas tombol "Batalkan"
function isBatalkanButtonVisible($waktuMulai) {
    $waktuMulaiTimestamp = strtotime($waktuMulai);
    $waktuSekarang = time();

    // Tombol batalkan hanya muncul jika waktu sekarang lebih kecil dari waktu mulai
    return $waktuMulaiTimestamp > $waktuSekarang;
}

// Fungsi untuk menentukan visibilitas tombol "Nilai"
function isNilaiButtonVisible($waktuSelesai, $penilaian) {
    $waktuSelesaiTimestamp = strtotime($waktuSelesai);
    $waktuSekarang = time();

    // Tombol nilai muncul jika waktu sekarang >= waktu selesai dan penilaian belum diberikan
    return $waktuSelesaiTimestamp <= $waktuSekarang && ($penilaian === NULL || $penilaian == 0);
}

// Fungsi untuk menentukan visibilitas tombol "Berlangsung"
function isBerlangsungButtonVisible($waktuMulai, $waktuSelesai) {
    $waktuMulaiTimestamp = strtotime($waktuMulai);
    $waktuSelesaiTimestamp = strtotime($waktuSelesai);
    $waktuSekarang = time();

    // Tombol "Berlangsung" muncul jika waktu sekarang berada di antara waktu mulai dan waktu selesai
    return $waktuSekarang >= $waktuMulaiTimestamp && $waktuSekarang <= $waktuSelesaiTimestamp;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Riwayat</title>
    <link rel="stylesheet" href="../back-end/dosen/riwayat.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style> 
        .datatables_filter {
            margin-bottom: 10px;
        }
    </style>
    <style>
        .content .display {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <!-- Header -->
            <div class="header">
                <div class="logo">
                    <img src="../images/aplikasi/logo.jpg" alt="Logo" class="logo-img">
                    <span class="title">APPRIM</span>
                </div>
                <div class="profile-logout">
                    <div class="profile">
                        <img src="<?= $foto_user ?>" alt="user Profile" class="profile-img">
                        <span class="username"><?= htmlspecialchars($nama_lengkap) ?></span>
                    </div>
                    <div class="logout">
                        <a href="../auth/logout.php">Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Navigation -->
            <div class="navigation">
                <ul class="nav-list">
                    <li><a href="../tampilan/roleda.php">Beranda</a></li>
                    <li><a href="../tampilan/tentang.php">Tentang Kami</a></li>
                    <li><a href="../tampilan/fitur.php">Fitur</a></li>
                    <li><a href="../tampilan/kontak.php">Kontak</a></li>
                </ul>
            </div>
        </div>

        <!-- Sidebar dan Content -->
        <div class="row">
            <div class="sidebar">
                <h2>Menu</h2>
                <ul class="sidebar-list">
                    <li><a href="../dosen/halaman-utama.php">Halaman Utama</a></li>
                    <li><a href="../dosen/data-diri.php">Data Diri</a></li>
                    <li><a href="../dosen/notifikasi.php">Notifikasi</a></li>
                    <li><a href="../dosen/riwayat.php">Riwayat</a></li>
                </ul>
            </div>
            <div class="content">
                <h1>Riwayat Peminjaman</h1>
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Kode Ruangan</th>
                            <th>Tanggal Pemakaian</th>
                            <th>Waktu Pemakaian</th>
                            <th>Keperluan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if ($result_riwayat->num_rows > 0) {
                            while ($row = $result_riwayat->fetch_assoc()) {
                                $waktuMulai = $row['tanggal_pemakaian'] . ' ' . $row['waktu_mulai'];
                                $waktuSelesai = $row['tanggal_pemakaian'] . ' ' . $row['waktu_selesai'];

                                // Tentukan visibilitas tombol
                                $tombolBatalkanVisible = isBatalkanButtonVisible($waktuMulai);
                                $tombolNilaiVisible = isNilaiButtonVisible($waktuSelesai, $row['penilaian']);
                                $tombolBerlangsungVisible = isBerlangsungButtonVisible($waktuMulai, $waktuSelesai);

                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['kode_ruangan']) . '</td>';
                                echo '<td>' . formatTanggalIndonesia($row['tanggal_pemakaian']) . '</td>';
                                echo '<td>' . htmlspecialchars(substr($row['waktu_mulai'], 0, 5) . " - " . substr($row['waktu_selesai'], 0, 5)) . '</td>';
                                echo '<td>' . htmlspecialchars($row['keperluan']) . '</td>';
                                echo '<td>';

                                // Tombol Batalkan
                                if ($tombolBatalkanVisible) {
                                    echo '<a href="../dosen/pembatalan.php?id=' . $row['id_peminjaman'] . '">';
                                    echo '<button class="delete-button" style="margin-right:10px;">Batalkan</button>';
                                    echo '</a>';
                                }

                                // Tombol Berlangsung
                                if ($tombolBerlangsungVisible) {
                                    echo '<button class="berlangusng" style="
                                        border: none;
                                        background-color: green;
                                        border-radius: 5px;
                                        font-weight: bold;
                                        color: white;
                                        padding: 5px 5px;
                                        margin-right:10px">Berlangsung</button>';
                                }

                                // Tombol Nilai
                                if ($tombolNilaiVisible) {
                                    echo '<a href="../dosen/isi-penilaian.php?id_peminjaman=' . $row['id_peminjaman'] . '">';
                                    echo '<button class="edit-button" style="margin-right:10px;">Nilai</button>';
                                    echo '</a>';
                                } else if ($row['penilaian'] !== NULL && $row['penilaian'] != 0) {
                                    echo '<span style="margin-right:10px;">Anda Menilai ' . htmlspecialchars($row['penilaian']). ' / 10</span>';
                                }

                                // Tombol Detail
                                echo '<a href="../dosen/detail-riwayat.php?id_peminjaman=' . $row['id_peminjaman'] . '">';
                                echo '<button class="edit-button">Detail</button>';
                                echo '</a>';
                                echo '</td>';
                                echo '</tr>';
                                }
                                } else {
                                echo '<tr>';
                                echo '<td colspan="6">Tidak ada riwayat peminjaman ditemukan.</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="footer">
                <div class="footer-left">
                    <img src="../images/aplikasi/polibatam.jpg" alt="Logo" class="footer-logo">
                </div>
                <div class="footer-center">
                    <h3>Anggota Kelompok</h3>
                    <ul>
                        <li>Adhcya Hafeez Wibowo</li>
                        <li>Nayla Nur Nabila</li>
                        <li>Hermansa</li>
                        <li>Berkat Tua Siallagan</li>
                        <li>Suci Aqilah Nst</li>
                        <li>Ray Refaldo</li>
                    </ul>
                </div>
                <div class="footer-right">
                    <h3>NIM Anggota</h3>
                    <ul>
                        <li>4342401080</li>
                        <li>4342401083</li>
                        <li>4342401084</li>
                        <li>4342401085</li>
                        <li>4342401087</li>
                        <li>4342401088</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
    
</body>
<script>
    // Sidebar menu toggle
    const menuItems = document.querySelectorAll('.sidebar-list li.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });     

    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none'; // Sembunyikan error

        // Inisialisasi DataTable tanpa paginasi
        $('#example').DataTable({
            paging: false, // Nonaktifkan paginasi
            searching: true, // Tetap aktifkan fitur pencarian
            info: false, // Nonaktifkan informasi jumlah data (opsional)
            language: {
                search: "Cari:"
            }
        });

        // Modal dan event lainnya tetap sama
        const editModal = document.getElementById("editModal");
        const deleteModal = document.getElementById("deleteModal");

        $(document).on("click", ".edit-button", function () {
            editModal.style.display = "block";
        });

        $(document).on("click", ".delete-button", function () {
            $("#deleteNik").val($(this).data("nik"));
            deleteModal.style.display = "block";
        });

        $(".close").click(function () {
            editModal.style.display = "none";
            deleteModal.style.display = "none";
        });

        $("#cancelDelete").click(function () {
            deleteModal.style.display = "none";
        });

        window.onclick = function (event) {
            if (event.target === editModal) editModal.style.display = "none";
            if (event.target === deleteModal) deleteModal.style.display = "none";
        };
    });
</script>
</html>
