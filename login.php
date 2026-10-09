<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $cek_data = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($cek_data) > 0) {
        $data_akun = mysqli_fetch_assoc($cek_data);
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $data_akun['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('Gagal! Username atau Password salah.');</script>";
    }
}

if (isset($_POST['daftar'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'user';

    $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Pendaftaran Gagal! Username tersebut sudah dipakai orang lain.');</script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama_lengkap', '$username', '$password', '$role')");
        if ($simpan) {
            echo "<script>alert('Pendaftaran Berhasil! Silakan Masuk (Login).'); window.location='login.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Masuk & Daftar - Karang Taruna RT 04 / RW 03</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .bungkus-utama {
            display: flex;
            background: white;
            width: 800px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .sisi-kiri {
            background: #2c3e50;
            color: white;
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        /* Styling Logo */
        .sisi-kiri img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #f39c12;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .sisi-kiri h1 { font-size: 22px; margin-bottom: 10px; }
        .sisi-kiri p { font-size: 13px; color: #bdc3c7; line-height: 1.5; margin: 0; }
        
        .sisi-kanan {
            flex: 1.2;
            padding: 40px;
            background: #ffffff;
            max-height: 480px;
            overflow-y: auto;
        }
        .form-grup { display: none; }
        .form-grup.aktif { display: block; }
        
        h2 { margin-top: 0; color: #2c3e50; font-size: 20px; margin-bottom: 20px; }
        label { font-size: 13px; color: #7f8c8d; font-weight: bold; }
        input {
            width: 100%;
            padding: 10px;
            margin: 6px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background-color: #2980b9; }
        .tombol-daftar { background-color: #27ae60; }
        .tombol-daftar:hover { background-color: #219653; }
        
        .pindah-form {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: #7f8c8d;
        }
        .pindah-form a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .pindah-form a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="bungkus-utama">
        <!-- SISI KIRI (Logo & Sambutan) -->
        <div class="sisi-kiri">
            <!-- Memanggil file logo yang sudah disimpan -->
            <img src="logo.png" alt="Logo Karang Taruna RT 04 / RW 03">
            <h1>Karang Taruna RT 04 / RW 03</h1>
            <p>Sistem Informasi Pengelolaan Keuangan, Jadwal Kegiatan, dan Galeri Lingkungan.</p>
        </div>

        <!-- SISI KANAN (Formulir Login & Register) -->
        <div class="sisi-kanan">
            
            <!-- FORMULIR LOGIN -->
            <div id="form-login" class="form-grup aktif">
                <h2>Pintu Masuk Akun</h2>
                <form method="POST">
                    <label>Username:</label>
                    <input type="text" name="username" placeholder="Masukkan username" required>
                    
                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                    
                    <button type="submit" name="login">Masuk ke Sistem</button>
                </form>
                <div class="pindah-form">
                    Belum punya akun? <a href="#" onclick="gantiForm('daftar')">Daftar di sini</a>
                </div>
            </div>

            <!-- FORMULIR PENDAFTARAN (REGISTER) -->
            <div id="form-daftar" class="form-grup">
                <h2>Pendaftaran Akun Baru</h2>
                <form method="POST">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama_lengkap" placeholder="Contoh: Budi Santoso" required>

                    <label>Buat Username:</label>
                    <input type="text" name="username" placeholder="Contoh: budi123" required>
                    
                    <label>Buat Password:</label>
                    <input type="password" name="password" placeholder="Buat password rahasia" required>
                    
                    <button type="submit" name="daftar" class="tombol-daftar">Daftar Sekarang</button>
                </form>
                <div class="pindah-form">
                    Sudah punya akun? <a href="#" onclick="gantiForm('login')">Masuk di sini</a>
                </div>
            </div>

        </div>
    </div>

    <script>
        function jenisForm(tujuan) {
            document.getElementById('form-login').classList.remove('aktif');
            document.getElementById('form-daftar').classList.remove('aktif');
            if(tujuan === 'daftar') {
                document.getElementById('form-daftar').classList.add('aktif');
            } else {
                document.getElementById('form-login').classList.add('aktif');
            }
        }
        function gantiForm(tujuan) { jenisForm(tujuan); }
    </script>

</body>
</html>