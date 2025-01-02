<?php
$host = "localhost"; // Nama host
$username = "root";  // Username
$password = "";      // Password
$dbname = "db_apprim"; // Nama database

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
