<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Uang Kas - Karang Taruna</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #f0f2f5; }
        .menu-samping { width: 220px; background-color: #2c3e50; color: white; padding: 20px; height: 100vh; position: fixed; }
        .menu-samping a { color: white; text-decoration: none; display: block; padding: 12px; margin-bottom: 5px; border-radius: 5px; }
        .menu-samping a:hover { background-color: #34495e; }
        .keluar { background-color: #e74c3c; margin-top: 30px; }
        .keluar:hover { background-color: #c0392b !important; }
        .konten-utama { margin-left: 260px; padding: 30px; }
        .kartu { background-color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 8px; margin: 8px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #27ae60; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #2ecc71; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .teks-merah { color: red; font-weight: bold; }
        .teks-hijau { color: green; font-weight: bold; }
    </style>
</head>
<body>

    <div class="menu-samping">
        <a href="dashboard.php">🏠 Dashboard</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="arus_kas.php" style="background-color: #34495e;">💰 Input Uang Kas</a>
            <a href="kas_warga.php">📒 Pembayaran Warga</a>
        <?php } ?>
        <a href="acara.php">📅 Jadwal Acara</a>
        <a href="galeri.php">📸 Galeri Foto</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="kelola_user.php">👥 Kelola Akun</a>
        <?php } ?>
        <a href="logout.php" class="keluar">🚪 Keluar (Logout)</a>
    </div>

    <div class="konten-utama">
        <h2>Buku Kas Karang Taruna 💰</h2>
        
        <?php 
        if ($_SESSION['role'] == 'admin' && isset($_POST['simpan'])) {
            $jenis = $_POST['jenis'];
            $nominal = $_POST['nominal'];
            $keterangan = $_POST['keterangan'];
            $tanggal = $_POST['tanggal'];

            $tambah = mysqli_query($koneksi, "INSERT INTO arus_kas (jenis, nominal, keterangan, tanggal) VALUES ('$jenis', '$nominal', '$keterangan', '$tanggal')");
            if ($tambah) {
                echo "<script>alert('Data kas berhasil disimpan!');</script>";
            }
        }
        
        if ($_SESSION['role'] == 'admin') { 
        ?>
        <div class="kartu">
            <h3>Catat Transaksi Baru</h3>
            <form method="POST">
                <label>Jenis Transaksi:</label>
                <select name="jenis" required>
                    <option value="masuk">Pemasukan (Uang Masuk)</option>
                    <option value="keluar">Pengeluaran (Uang Keluar)</option>
                </select>

                <label>Nominal Uang (Rp):</label>
                <input type="number" name="nominal" placeholder="Contoh: 50000" required>

                <label>Keterangan:</label>
                <input type="text" name="keterangan" placeholder="Contoh: Beli sapu lidi" required>

                <label>Tanggal:</label>
                <input type="date" name="tanggal" required>

                <button type="submit" name="simpan">💾 Simpan Catatan</button>
            </form>
        </div>
        <?php } ?>

        <div class="kartu">
            <h3>Riwayat Kas</h3>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Jenis</th>
                    <th>Nominal</th>
                </tr>
                <?php
                $data_kas = mysqli_query($koneksi, "SELECT * FROM arus_kas ORDER BY tanggal DESC");
                while ($baris = mysqli_fetch_array($data_kas)) {
                    if ($baris['jenis'] == 'masuk') {
                        $warna_teks = "teks-hijau";
                        $jenis_teks = "Pemasukan";
                    } else {
                        $warna_teks = "teks-merah";
                        $jenis_teks = "Pengeluaran";
                    }
                ?>
                <tr>
                    <td><?php echo $baris['tanggal']; ?></td>
                    <td><?php echo $baris['keterangan']; ?></td>
                    <td class="<?php echo $warna_teks; ?>"><?php echo $jenis_teks; ?></td>
                    <td>Rp <?php echo number_format($baris['nominal'], 0, ',', '.'); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</body>
</html>