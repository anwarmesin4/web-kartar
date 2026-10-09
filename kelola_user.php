<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin.'); window.location='dashboard.php';</script>";
    exit;
}

// 1. PROSES TAMBAH USER BARU
if (isset($_POST['tambah_user'])) {
    $nama = trim($_POST['nama_lengkap']);
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $role = $_POST['role'];

    $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$user'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Gagal! Username tersebut sudah digunakan.');</script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama', '$user', '$pass', '$role')");
        if ($simpan) {
            echo "<script>alert('Akun baru berhasil dibuat!'); window.location='kelola_user.php';</script>";
        }
    }
}

// 2. PROSES EDIT USER
if (isset($_POST['edit_user'])) {
    $id_user = $_POST['id_user'];
    $nama = trim($_POST['nama_lengkap']);
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // Jika password diisi, ubah passwordnya. Jika kosong, biarkan password lama.
    if (!empty($pass)) {
        $update = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama', username='$user', password='$pass', role='$role' WHERE id='$id_user'");
    } else {
        $update = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama', username='$user', role='$role' WHERE id='$id_user'");
    }

    if ($update) {
        echo "<script>alert('Data akun berhasil diperbarui!'); window.location='kelola_user.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui akun.');</script>";
    }
}

// 3. PROSES HAPUS USER
if (isset($_GET['hapus_user'])) {
    $id_user = $_GET['hapus_user'];
    
    // Cek agar admin tidak menghapus akunnya sendiri yang sedang aktif login
    $q_cek_aktif = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id_user'");
    $d_aktif = mysqli_fetch_array($q_cek_aktif);
    
    if ($d_aktif['username'] == $_SESSION['username']) {
        echo "<script>alert('Peringatan: Anda tidak dapat menghapus akun yang sedang Anda gunakan untuk login!'); window.location='kelola_user.php';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM users WHERE id='$id_user'");
        echo "<script>alert('Akun berhasil dihapus!'); window.location='kelola_user.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Akun - Karang Taruna</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #f0f2f5; color: #333; }
        .menu-samping { width: 220px; background-color: #2c3e50; color: white; padding: 20px; height: 100vh; position: fixed; }
        .menu-samping a { color: white; text-decoration: none; display: block; padding: 12px; margin-bottom: 5px; border-radius: 5px; font-size: 14px; transition: background 0.2s; }
        .menu-samping a:hover { background-color: #34495e; }
        .keluar { background-color: #e74c3c; margin-top: 30px; }
        .keluar:hover { background-color: #c0392b !important; }
        
        .konten-utama { margin-left: 260px; padding: 30px; }
        h2 { color: #2c3e50; margin-top: 0; }
        
        .kartu { background-color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        /* Tata Letak Modern Dua Kolom */
        .baris-atas { display: flex; gap: 20px; margin-bottom: 10px; }
        .kolom-kiri { flex: 1; }
        .kolom-kanan { flex: 1.5; }

        label { font-size: 13px; font-weight: bold; color: #555; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #dcdde1; border-radius: 6px; box-sizing: border-box; font-size: 14px; background-color: #f8f9fa; }
        input:focus, select:focus { border-color: #16a085; outline: none; background-color: #fff; }

        button { width: 100%; background-color: #16a085; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.2s; }
        button:hover { background-color: #1abc9c; }

        .btn-aksi { padding: 4px 8px; font-size: 12px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block; margin-right: 3px; }
        .btn-edit { background-color: #f39c12; color: white; }
        .btn-edit:hover { background-color: #d68910; }
        .btn-hapus { background-color: #e74c3c; color: white; }
        .btn-hapus:hover { background-color: #c0392b; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #eee; }
        th, td { padding: 12px; text-align: left; font-size: 14px; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; }
        tr:hover { background-color: #fcfcfc; }

        .badge-admin { background-color: #e74c3c; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-user { background-color: #2980b9; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .tabel-scroll { max-height: 450px; overflow-y: auto; }
    </style>
</head>
<body>

    <div class="menu-samping">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="arus_kas.php">💰 Input Uang Kas</a>
        <a href="kas_warga.php">📒 Pembayaran Warga</a>
        <a href="acara.php">📅 Jadwal Acara</a>
        <a href="galeri.php">📸 Galeri Foto</a>
        <a href="kelola_user.php" style="background-color: #34495e;">👥 Kelola Akun</a>
        <a href="logout.php" class="keluar">🚪 Keluar (Logout)</a>
    </div>

    <div class="konten-utama">
        <h2>Manajemen Pengguna / Akun 👥</h2>
        
        <?php 
        $mode_edit = false;
        $data_edit = null;
        if(isset($_GET['edit_user'])) {
            $id_edit = $_GET['edit_user'];
            $q_edit = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id_edit'");
            if(mysqli_num_rows($q_edit) > 0) {
                $mode_edit = true;
                $data_edit = mysqli_fetch_array($q_edit);
            }
        }
        ?>

        <div class="baris-atas">
            
            <!-- KOLOM KIRI: FORM TAMBAH / EDIT AKUN -->
            <div class="kolom-kiri">
                <div class="kartu">
                    <?php if($mode_edit) { ?>
                        <h3 style="margin-top:0; color:#f39c12;">✏️ Edit Data Akun</h3>
                        <form method="POST">
                            <input type="hidden" name="id_user" value="<?php echo $data_edit['id']; ?>">
                            
                            <label>Nama Lengkap:</label>
                            <input type="text" name="nama_lengkap" value="<?php echo $data_edit['nama_lengkap']; ?>" required>

                            <label>Username:</label>
                            <input type="text" name="username" value="<?php echo $data_edit['username']; ?>" required>

                            <label>Password Baru (Kosongkan jika tidak ingin diubah):</label>
                            <input type="password" name="password" placeholder="Masukkan password baru...">

                            <label>Peran (Role):</label>
                            <select name="role" required>
                                <option value="user" <?php if($data_edit['role'] == 'user') echo 'selected'; ?>>User (Warga)</option>
                                <option value="admin" <?php if($data_edit['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                            </select>

                            <button type="submit" name="edit_user" style="background-color: #f39c12; margin-bottom: 10px;">💾 Simpan Perubahan</button>
                            <a href="kelola_user.php" style="color:#e74c3c; text-decoration:none; font-size:13px; font-weight:bold; display:block; text-align:center;">Batal Edit</a>
                        </form>
                    <?php } else { ?>
                        <h3 style="margin-top:0; color:#16a085;">+ Buat Akun Baru</h3>
                        <form method="POST">
                            <label>Nama Lengkap:</label>
                            <input type="text" name="nama_lengkap" placeholder="Contoh: Ahmad Fauzi" required>

                            <label>Username untuk Login:</label>
                            <input type="text" name="username" placeholder="Contoh: ahmad_fauzi" required>

                            <label>Password:</label>
                            <input type="password" name="password" placeholder="Masukkan password rahasia" required>

                            <label>Pilih Peran (Role):</label>
                            <select name="role" required>
                                <option value="user">User (Hanya bisa melihat data)</option>
                                <option value="admin">Admin (Bisa input & kelola data)</option>
                            </select>

                            <button type="submit" name="tambah_user">✨ Simpan Akun Baru</button>
                        </form>
                    <?php } ?>
                </div>
            </div>

            <!-- KOLOM KANAN: DAFTAR AKUN TERDAFTAR -->
            <div class="kolom-kanan">
                <div class="kartu">
                    <h3 style="margin-top:0; color:#2c3e50;">Daftar Akun Terdaftar</h3>
                    <div class="tabel-scroll">
                        <table>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th style="width: 110px; text-align: center;">Aksi</th>
                            </tr>
                            <?php
                            $no = 1;
                            $data_user = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id ASC");
                            while ($u = mysqli_fetch_array($data_user)) {
                                $badge = ($u['role'] == 'admin') ? 'badge-admin' : 'badge-user';
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo $u['nama_lengkap']; ?></strong></td>
                                <td><?php echo $u['username']; ?></td>
                                <td><span class="<?php echo $badge; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                                <td style="text-align: center;">
                                    <a href="kelola_user.php?edit_user=<?php echo $u['id']; ?>" class="btn-aksi btn-edit">Edit</a>
                                    <a href="kelola_user.php?hapus_user=<?php echo $u['id']; ?>" class="btn-aksi btn-hapus" onclick="return confirm('Yakin ingin menghapus akun ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>