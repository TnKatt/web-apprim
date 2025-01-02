<?php
// Mulai session
session_start();

// Hancurkan semua session
session_unset();
session_destroy();

// Arahkan ke halaman login setelah logout
header("Location: ../auth/login.php");
exit();

?>