<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// PROSES TAMBAH ACARA BARU
if ($_SESSION['role'] == 'admin' && isset($_POST['simpan_acara'])) {
    $nama_acara = $_POST['nama_acara'];
    $deskripsi = $_POST['deskripsi'];
    $tgl_input = $_POST['tanggal'];
    $jam_input = $_POST['jam'];
    
    // Gabungkan tanggal dan jam menjadi format DATETIME (YYYY-MM-DD HH:MM:00)
    $tanggal_pelaksanaan = $tgl_input . ' ' . $jam_input . ':00';

    $tambah = mysqli_query($koneksi, "INSERT INTO acara (nama_acara, deskripsi, tanggal_pelaksanaan) 
                                      VALUES ('$nama_acara', '$deskripsi', '$tanggal_pelaksanaan')");
    if ($tambah) {
        echo "<script>alert('Jadwal Acara berhasil ditambahkan!'); window.location='acara.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah acara.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Acara - Karang Taruna</title>
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
        
        /* Tata Letak Dua Kolom */
        .baris-atas { display: flex; gap: 20px; margin-bottom: 10px; }
        .kolom-kiri { flex: 1; }
        .kolom-kanan { flex: 1.5; }

        label { font-size: 13px; font-weight: bold; color: #555; display: block; margin-bottom: 4px; }
        input, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #dcdde1; border-radius: 6px; box-sizing: border-box; font-size: 14px; background-color: #f8f9fa; }
        input:focus, textarea:focus { border-color: #3498db; outline: none; background-color: #fff; }

        button { width: 100%; background-color: #9b59b6; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.2s; }
        button:hover { background-color: #8e44ad; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #eee; }
        th, td { padding: 12px; text-align: left; font-size: 14px; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; }
        tr:hover { background-color: #fcfcfc; }

        /* Animasi Blink (Kedip-kedip) untuk Acara Kurang dari 24 Jam */
        @keyframes kedip {
            0% { opacity: 1; background-color: #ffcccc; }
            50% { opacity: 0.4; background-color: #ffe6e6; }
            100% { opacity: 1; background-color: #ffcccc; }
        }
        .baris-blink {
            animation: kedip 1s infinite;
            font-weight: bold;
            color: #c0392b;
        }
        .badge-peringatan {
            background-color: #e74c3c;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 6px;
            display: inline-block;
        }
        .tabel-scroll { max-height: 350px; overflow-y: auto; }
    </style>
</head>
<body>

    <div class="menu-samping">
        <a href="dashboard.php">🏠 Dashboard</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="arus_kas.php">💰 Input Uang Kas</a>
            <a href="kas_warga.php">📒 Pembayaran Warga</a>
        <?php } ?>
        <a href="acara.php" style="background-color: #34495e;">📅 Jadwal Acara</a>
        <a href="galeri.php">📸 Galeri Foto</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="kelola_user.php">👥 Kelola Akun</a>
        <?php } ?>
        <a href="logout.php" class="keluar">🚪 Keluar (Logout)</a>
    </div>

    <div class="konten-utama">
        <h2>Jadwal Kegiatan Karang Taruna 📅</h2>
        
        <div class="baris-atas">
            
            <?php if ($_SESSION['role'] == 'admin') { ?>
            <!-- KOLOM KIRI: FORM TAMBAH ACARA -->
            <div class="kolom-kiri">
                <div class="kartu">
                    <h3 style="margin-top:0; color:#2c3e50;">+ Buat Acara Baru</h3>
                    <form method="POST">
                        <label>Nama Acara:</label>
                        <input type="text" name="nama_acara" placeholder="Contoh: Rapat Bulanan RT" required>

                        <label>Deskripsi Singkat:</label>
                        <textarea name="deskripsi" rows="3" placeholder="Contoh: Membahas persiapan..." required></textarea>

                        <label>Tanggal Pelaksanaan:</label>
                        <input type="date" name="tanggal" required>

                        <label>Jam Pelaksanaan:</label>
                        <input type="time" name="jam" required>

                        <button type="submit" name="simpan_acara">🚀 Simpan Jadwal</button>
                    </form>
                </div>
            </div>
            <?php } ?>

            <!-- KOLOM KANAN: ACARA YANG AKAN DATANG (DENGAN ALERT BLINK 24 JAM) -->
            <div class="<?php echo ($_SESSION['role'] == 'admin') ? 'kolom-kanan' : 'kolom-kiri'; ?>" style="flex: 2;">
                <div class="kartu">
                    <h3 style="margin-top:0; color:#2980b9;">🚀 Acara yang Akan Datang</h3>
                    <div class="tabel-scroll">
                        <table>
                            <tr>
                                <th>Waktu Pelaksanaan</th>
                                <th>Nama Acara</th>
                                <th>Deskripsi</th>
                            </tr>
                            <?php
                            // Mengambil acara yang waktunya >= waktu sekarang
                            $acara_mendatang = mysqli_query($koneksi, "SELECT * FROM acara WHERE tanggal_pelaksanaan >= NOW() ORDER BY tanggal_pelaksanaan ASC");
                            
                            if(mysqli_num_rows($acara_mendatang) > 0) {
                                while ($baris = mysqli_fetch_array($acara_mendatang)) {
                                    $waktu_acara = $baris['tanggal_pelaksanaan']; // Format: YYYY-MM-DD HH:MM:SS
                                    $timestamp_acara = strtotime($waktu_acara);
                                    $timestamp_sekarang = time();
                                    
                                    // Selisih dalam detik
                                    $selisih_detik = $timestamp_acara - $timestamp_sekarang;
                                    
                                    // Jika kurang dari atau sama dengan 24 jam (86400 detik) dan belum lewat
                                    $is_mendekati = ($selisih_detik >= 0 && $selisih_detik <= 86400);
                                    $kelas_baris = $is_mendekati ? 'baris-blink' : '';
                            ?>
                            <tr class="<?php echo $kelas_baris; ?>">
                                <td>
                                    <?php echo date('d-m-Y H:i', strtotime($waktu_acara)); ?> WIB
                                    <?php if ($is_mendekati) { echo "<span class='badge-peringatan'>SEGERA!</span>"; } ?>
                                </td>
                                <td><strong><?php echo $baris['nama_acara']; ?></strong></td>
                                <td><?php echo $baris['deskripsi']; ?></td>
                            </tr>
                            <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; color:#7f8c8d;'>Belum ada jadwal acara mendatang.</td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- BAWAH: ACARA YANG TELAH SELESAI -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#7f8c8d;">✅ Acara yang Telah Selesai</h3>
            <div class="tabel-scroll">
                <table>
                    <tr>
                        <th>Waktu Pelaksanaan</th>
                        <th>Nama Acara</th>
                        <th>Deskripsi</th>
                    </tr>
                    <?php
                    $acara_lalu = mysqli_query($koneksi, "SELECT * FROM acara WHERE tanggal_pelaksanaan < NOW() ORDER BY tanggal_pelaksanaan DESC");
                    if(mysqli_num_rows($acara_lalu) > 0) {
                        while ($baris = mysqli_fetch_array($acara_lalu)) {
                    ?>
                    <tr>
                        <td><?php echo date('d-m-Y H:i', strtotime($baris['tanggal_pelaksanaan'])); ?> WIB</td>
                        <td><strong><?php echo $baris['nama_acara']; ?></strong></td>
                        <td><?php echo $baris['deskripsi']; ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; color:#7f8c8d;'>Belum ada histori acara.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

    </div>

</body>
</html>