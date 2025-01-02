<?php
// Masukkan file koneksi
require_once('../koneksi/koneksi.php');

// Pastikan session dimulai di awal file
session_start();

// Cek apakah CSRF token sudah ada, jika tidak buat baru
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Menghasilkan token yang aman
}

// Pastikan user sudah login
if (!isset($_SESSION['nik'])) {
    header("Location: ../auth/login.php"); // Jika belum login, redirect ke login page
    exit();
}

// Query untuk mengambil semua data user
$sql = "SELECT * FROM pengguna";
$result = $conn->query($sql);

// Ambil data user dari database
$nik = $_SESSION['nik']; // Ambil NIK dari session

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
$foto_user = !empty($user_data['foto_pengguna']) ? "../images/pengguna/" . htmlspecialchars($user_data['foto_pengguna']) : "../images/pengguna/foto_default.jpg";
$nama_lengkap = htmlspecialchars($user_data['nama_lengkap']);

// Ambil data role user
$role_user = $user_data['peran'];

// Validasi role untuk akses halaman
if ($role_user !== 'Admin') {
    die("Akses hanya untuk Admin.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPRIM | Data Pengguna</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../back-end/admin/data-pengguna.css">
    <style>

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="../images/aplikasi/logo.jpg" alt="Logo" class="logo-img">
            <span class="title">APPRIM</span>
        </div>
        <div class="profile-logout">
            <div class="profile">
                <img src="<?= $foto_user ?>" alt="User Profile" class="profile-img">
                <span class="username"><?= $nama_lengkap ?></span>
            </div>
            <div class="logout">
                <a href="../auth/logout.php">Keluar</a>
            </div>
        </div>
    </div>

    <div class="row">
            <!-- Navigation -->
            <div class="navigation">
                <ul class="nav-list">
                    <li><a href="../tampilan/beranda.php">Beranda</a></li>
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
                <li><a href="../admin/halaman-utama.php">Halaman Utama</a></li>
                <li><a href="../admin/data-diri.php">Data Diri</a></li>
                <li><a href="../admin/notifikasi.php">Notifikasi</a></li>
                <li><a href="../admin/riwayat.php">Riwayat</a></li>
                <li><a href="../admin/data-pengguna.php">Data Pengguna</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Data Pengguna</h1>
            <a href="#" id="tambahButton" class="tambah-pengguna">Tambah Pengguna</a>
            <?php
            if (isset($_GET['message'])) {
                switch ($_GET['message']) {
                    case 'add_success' :
                        echo "<p style='
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Pengguna berhasil ditambahkan.</p>";
                        break;
                    case 'delete_success' :
                        echo "<p style='
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Pengguna berhasil dihapus.</p>";
                        break;
                    case 'update_success':
                        echo "<p style='
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Data berhasil diperbarui.</p>";
                        break;
                    case 'error_empty_fields':
                        echo "<p style='
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Kolom NIK, Nama Lengkap, dan Peran wajib diisi.</p>";
                        break;
                    case 'error_invalid_email':
                        echo "<p style='
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Format email tidak valid. Silakan periksa kembali.</p>";
                        break;
                    case 'error_update_failed':
                        echo "<p style='
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Gagal memperbarui data. Silakan coba lagi.</p>";
                        break;
                    case 'error_exception':
                        echo "<p style='
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 16px;
                        '>Terjadi kesalahan pada sistem. Harap hubungi administrator.</p>";
                        break;
                }
            }
            ?>
            <table id="example" class="display" style="width: 100%">
                <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['nik']}</td>";
                        echo "<td>{$row['nama_lengkap']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['peran']}</td>";
                        echo "<td class='action-column'>
                            <a href='#' class='edit-button'  
                            data-nik='{$row['nik']}'
                            data-nama='{$row['nama_lengkap']}' 
                            data-email='{$row['email']}' 
                            data-role='{$row['peran']}'>Edit</a>
                            <a href='#' class='delete-button' 
                            data-nik='{$row['nik']}'>Hapus</a>
                        </td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada data ditemukan</td></tr>";
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
                        <li>Suci Aqila Nst</li>
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

<!-- Modal untuk Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Pengguna</h2>
        <form action="../admin/update-pengguna.php" method="POST">
            <div class="form-group">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <input type="hidden" name="editnik" id="editnik" value="<?= htmlspecialchars($user_data['nik']) ?>">

                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?= htmlspecialchars($user_data['nama_lengkap']) ?>" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="editemail" value="<?= htmlspecialchars($user_data['email']) ?>" >

                <label for="peran">Peran</label>
                <select name="peran" id="editperan" required>
                    <option value="" hidden>Pilih Peran</option>
                    <option value="Admin" <?= $user_data['peran'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="PIC Ruangan" <?= $user_data['peran'] == 'PIC Ruangan' ? 'selected' : '' ?>>PIC Ruangan</option>
                    <option value="Dosen" <?= $user_data['peran'] == 'Dosen' ? 'selected' : '' ?>>Dosen</option>
                    <option value="Mahasiswa" <?= $user_data['peran'] == 'Mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                </select>
            </div>

            <button class="submit-btn" type="submit">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal untuk Hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeDeleteModal">&times;</span>
        <br>
        <br>
        <h2>Apakah Anda Yakin Ingin Menghapus Pengguna Ini?</h2>
        <form id="deleteForm" method="POST" action="../admin/hapus_pengguna.php">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="hidden" name="nik" id="deleteNik">
            <button type="submit" class="delete-btn">Hapus</button>
        </form>
    </div>
</div>

<div id="tambahModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeTambahModal">&times;</span>
        <h2>Tambah Pengguna</h2>
        <form id="tambahform" action="../admin/tambah-pengguna.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nik">NIK</label>
                <input type="text" name="nik" id="nik" required>

                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required>

                <label for="kata_sandi">Kata Sandi</label>
                <input type="password" name="kata_sandi" id="kata_sandi" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email">

                <label for="peran">Peran Sebagai</label>
                <select name="peran" id="peran" required>
                    <option value="" hidden>Pilih Peran</option>
                    <option value="Admin">Admin</option>
                    <option value="PIC Ruangan">PIC Ruangan</option>
                    <option value="Dosen">Dosen</option>
                    <option value="Mahasiswa">Mahasiswa</option>
                </select>

                <label for="foto_pengguna">Foto Pengguna</label>
                <input type="file" name="foto_pengguna" id="foto_pengguna" accept="image/*">
            </div>
            
            <!-- Tambahkan input CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
            <button class="submit-btn" type="submit">Tambah</button>
        </form>
    </div>
</div>


<script>
$(document).ready(function () {
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
    const tambahModal = document.getElementById("tambahModal");

    // Fungsi untuk menampilkan modal tambah
    $("#tambahButton").click(function () {
        tambahModal.style.display = "block"; // Menampilkan modal tambah
    });

    // Fungsi untuk menampilkan modal edit
    $(document).on("click", ".edit-button", function () {
        $("#editnik").val($(this).data("nik")); // Mengisi input hidden dengan NIK
        $("#nama_lengkap").val($(this).data("nama")); // Mengisi input nama
        $("#editemail").val($(this).data("email")); // Mengisi input email
        $("#editperan").val($(this).data("role")); // Mengisi dropdown role
        editModal.style.display = "block"; // Menampilkan modal edit
    });

    // Fungsi untuk menampilkan modal delete
    $(document).on("click", ".delete-button", function () {
        $("#deleteNik").val($(this).data("nik"));
        deleteModal.style.display = "block";
    });

    // Menutup modal
    $(".close").click(function () {
        editModal.style.display = "none";
        deleteModal.style.display = "none";
        tambahModal.style.display = "none"; // Menutup modal tambah
    });

    $("#cancelDelete").click(function () {
        deleteModal.style.display = "none";
    });

    // Menutup modal ketika mengklik di luar modal
    window.onclick = function (event) {
        if (event.target === editModal) editModal.style.display = "none";
        if (event.target === deleteModal) deleteModal.style.display = "none";
        if (event.target === tambahModal) tambahModal.style.display = "none";
    };

    // Menangani submit form tambah data
    $("#tambahForm").submit(function (e) {
        e.preventDefault(); // Mencegah pengiriman form default

        // Ambil data dari form
        var nama = $("#tambahNama").val();
        var email = $("#tambahEmail").val();
        var role = $("#tambahRole").val();

        // Kirimkan data ke server (misalnya menggunakan AJAX)
        $.ajax({
            url: 'tambah_data.php', // URL untuk mengirim data
            type: 'POST',
            data: {
                nama_lengkap: nama,
                email: email,
                role: role
            },
            success: function (response) {
                // Jika sukses, tutup modal dan perbarui tabel
                alert("Data berhasil ditambahkan.");
                tambahModal.style.display = "none"; // Menutup modal
                $('#example').DataTable().ajax.reload(); // Reload DataTable
            },
            error: function () {
                alert("Terjadi kesalahan.");
            }
        });
    });
});
</script>
</body>
</html>