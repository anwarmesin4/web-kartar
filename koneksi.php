<?php
$host = "localhost"; // Nama server lokal bawaan XAMPP
$user = "root";      // Username default XAMPP
$pass = "";          // Password default XAMPP (biarkan kosong)
$db   = "db_karang_taruna"; // Nama database yang kita buat tadi

// Perintah untuk menghubungkan
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah jembatan berhasil dibuat
if (!$koneksi) {
    die("Waduh, gagal terhubung ke database nih!");
}
?>