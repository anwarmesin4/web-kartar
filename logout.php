<?php
session_start();
session_destroy(); // Ini perintah untuk menghapus 'cap stempel' login Anda
header("Location: login.php"); // Arahkan kembali ke halaman depan
?>