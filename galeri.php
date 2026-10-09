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
    <title>Galeri Foto - Karang Taruna</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #f0f2f5; color: #333; }
        .menu-samping { width: 220px; background-color: #2c3e50; color: white; padding: 20px; height: 100vh; position: fixed; }
        .menu-samping a { color: white; text-decoration: none; display: block; padding: 12px; margin-bottom: 5px; border-radius: 5px; font-size: 14px; transition: background 0.2s; }
        .menu-samping a:hover { background-color: #34495e; }
        .keluar { background-color: #e74c3c; margin-top: 30px; }
        .keluar:hover { background-color: #c0392b !important; }
        
        .konten-utama { margin-left: 260px; padding: 30px; }
        h2 { color: #2c3e50; margin-top: 0; }
        
        .kartu { background-color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 6px 15px rgba(0,0,0,0.06); }
        
        /* Ornamen Header Meriah */
        .header-galeri {
            background: linear-gradient(135deg, #182075, #d35400);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(18, 31, 117, 0.3);
        }
        .header-galeri h2 { color: white; margin: 0 0 8px 0; }
        .header-galeri p { margin: 0; font-size: 14px; opacity: 0.9; }

        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #dcdde1; border-radius: 6px; box-sizing: border-box; font-size: 14px; background-color: #f8f9fa; }
        input:focus, select:focus { border-color: #e67e22; outline: none; background-color: #fff; }

        button { background-color: #e67e22; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.2s; }
        button:hover { background-color: #d35400; }

        /* Grid Galeri Modern & Meriah */
        .grid-galeri { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 15px; }
        .item-galeri { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
        .item-galeri:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
        
        .wadah-gambar { position: relative; overflow: hidden; height: 200px; background: #000; cursor: pointer; }
        .wadah-gambar img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .item-galeri:hover .wadah-gambar img { transform: scale(1.05); }
        
        .label-acara { position: absolute; top: 10px; left: 10px; background-color: rgba(44, 62, 83, 0.85); backdrop-filter: blur(4px); color: white; padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 20px; }
        
        .info-foto { padding: 15px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .info-foto p { margin: 0 0 15px 0; font-size: 14px; color: #444; }

        /* Tombol Aksi Foto (Download, Print, Preview) */
        .aksi-foto { display: flex; gap: 8px; border-top: 1px solid #eee; padding-top: 12px; }
        .btn-aksi-foto { flex: 1; padding: 7px; font-size: 12px; font-weight: bold; text-align: center; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; transition: background 0.2s; }
        .btn-preview { background-color: #3498db; color: white; }
        .btn-preview:hover { background-color: #2980b9; }
        .btn-download { background-color: #27ae60; color: white; }
        .btn-download:hover { background-color: #219653; }
        .btn-print { background-color: #7f8c8d; color: white; }
        .btn-print:hover { background-color: #626d71; }

        /* Modal Preview Gambar (Zoom In/Out) */
        #modalPreview { display: none; position: fixed; z-index: 999; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); backdrop-filter: blur(5px); justify-content: center; align-items: center; flex-direction: column; }
        #modalPreview img { max-width: 90%; max-height: 75vh; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); transition: transform 0.2s ease; cursor: grab; }
        .kontrol-zoom { margin-top: 15px; display: flex; gap: 10px; align-items: center; }
        .kontrol-zoom button { background: white; color: #333; border: none; padding: 8px 15px; border-radius: 20px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .kontrol-zoom button:hover { background: #f1f1f1; }
        .tutup-modal { position: absolute; top: 20px; right: 30px; color: white; font-size: 35px; font-weight: bold; cursor: pointer; }

        @media print {
            .menu-samping, .header-galeri, .kartu-form, .aksi-foto, .keluar, button { display: none !important; }
            .konten-utama { margin-left: 0; padding: 0; }
            .grid-galeri { display: block; }
            .item-galeri { page-break-inside: avoid; margin-bottom: 20px; box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
    <script>
        let skalaGambar = 1;

        function bukaModal(srcGambar, ketGambar) {
            let modal = document.getElementById('modalPreview');
            let img = document.getElementById('gambarModal');
            let caption = document.getElementById('captionModal');
            
            img.src = srcGambar;
            caption.innerText = ketGambar;
            skalaGambar = 1;
            img.style.transform = `scale(${skalaGambar})`;
            modal.style.display = 'flex';
        }

        function tutupModal() {
            document.getElementById('modalPreview').style.display = 'none';
        }

        function zoomIn() {
            if (skalaGambar < 3) {
                skalaGambar += 0.25;
                document.getElementById('gambarModal').style.transform = `scale(${skalaGambar})`;
            }
        }

        function zoomOut() {
            if (skalaGambar > 0.5) {
                skalaGambar -= 0.25;
                document.getElementById('gambarModal').style.transform = `scale(${skalaGambar})`;
            }
        }

        function cetakFotoSatuan(urlGambar, judul) {
            let jendelacetak = window.open('', '_blank');
            jendelacetak.document.write(`
                <html>
                <head><title>Cetak Foto - ${judul}</title></head>
                <body style="text-align:center; font-family:sans-serif; padding: 30px;">
                    <h2>${judul}</h2>
                    <img src="${urlGambar}" style="max-width:100%; max-height:80vh; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.2);"/>
                </body>
                </html>
            `);
            jendelacetak.document.close();
            jendelacetak.focus();
            setTimeout(() => { jendelacetak.print(); }, 500);
        }
    </script>
</head>
<body>

    <div class="menu-samping">
        <a href="dashboard.php">🏠 Dashboard</a>
        <?php if (isset($_SESSION['role']) &&$_SESSION['role'] == 'admin') { ?>
            <a href="arus_kas.php">💰 Input Uang Kas</a>
            <a href="kas_warga.php">📒 Pembayaran Warga</a>
        <?php } ?>
        <a href="acara.php">📅 Jadwal Acara</a>
        <a href="galeri.php" style="background-color: #34495e;">📸 Galeri Foto</a>
        <?php if (isset($_SESSION['role']) &&$_SESSION['role'] == 'admin') { ?>
            <a href="kelola_user.php">👥 Kelola Akun</a>
        <?php } ?>
        <a href="logout.php" class="keluar">🚪 Keluar (Logout)</a>
    </div>

    <div class="konten-utama">
        
        <!-- HEADER ORNAMEN MERIAH -->
        <div class="header-galeri">
            <h2>Galeri Dokumentasi Kegiatan 📸✨</h2>
            <p>Kumpulan momen berharga, keseruan acara, dan kegiatan warga RT. Klik pada foto untuk memperbesar.</p>
        </div>
        
        <?php 
        if ($_SESSION['role'] == 'admin' && isset($_POST['upload_foto'])) {
            $acara_id =$_POST['acara_id'];
            $keterangan =$_POST['keterangan'];
            $nama_file =$_FILES['foto']['name'];
            $tmp_file =$_FILES['foto']['tmp_name'];
            
            // Buat nama unik agar tidak bentrok
            $nama_file_unik = time() . '_' .$nama_file;
            $lokasi_simpan = "uploads/" . $nama_file_unik;
            
            if (move_uploaded_file($tmp_file,$lokasi_simpan)) {
                $simpan = mysqli_query($koneksi, "INSERT INTO galeri_foto (acara_id, nama_file_foto, keterangan_foto) 
                                                  VALUES ('$acara_id', '$nama_file_unik', '$keterangan')");
                if ($simpan) {
                    echo "<script>alert('Foto berhasil diunggah ke Galeri!'); window.location='galeri.php';</script>";
                }
            }
        }

        if ($_SESSION['role'] == 'admin') { 
        ?>
        <div class="kartu kartu-form">
            <h3 style="margin-top:0; color:#e67e22;">+ Unggah Foto Kegiatan Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <label style="font-weight:bold; font-size:13px; color:#555;">Pilih Acara Terkait:</label>
                <select name="acara_id" required>
                    <option value="">-- Pilih Nama Acara --</option>
                    <?php
                    $data_acara = mysqli_query($koneksi, "SELECT id, nama_acara FROM acara ORDER BY id DESC");
                    while ($acara = mysqli_fetch_array($data_acara)) {
                        echo "<option value='".$acara['id']."'>".$acara['nama_acara']."</option>";
                    }
                    ?>
                </select>

                <label style="font-weight:bold; font-size:13px; color:#555;">Pilih File Foto (JPG/PNG):</label>
                <input type="file" name="foto" accept="image/*" required>

                <label style="font-weight:bold; font-size:13px; color:#555;">Keterangan Foto:</label>
                <input type="text" name="keterangan" placeholder="Contoh: Keseruan warga saat lomba 17an" required>

                <button type="submit" name="upload_foto">📤 Unggah Foto ke Galeri</button>
            </form>
        </div>
        <?php } ?>

        <!-- ALBUM FOTO GRID -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#2c3e50;">Album Kenangan Warga</h3>
            
            <div class="grid-galeri">
                <?php
                $data_galeri = mysqli_query($koneksi, "
                    SELECT galeri_foto.*, acara.nama_acara 
                    FROM galeri_foto 
                    JOIN acara ON galeri_foto.acara_id = acara.id 
                    ORDER BY galeri_foto.id DESC
                ");
                
                if(mysqli_num_rows($data_galeri) > 0) {
                    while ($foto = mysqli_fetch_array($data_galeri)) {
                        $url_foto = "uploads/" . $foto['nama_file_foto'];
                        $nama_acara = htmlspecialchars($foto['nama_acara'], ENT_QUOTES);
                        $ket_foto = htmlspecialchars($foto['keterangan_foto'], ENT_QUOTES);
                ?>
                    <div class="item-galeri">
                        <div class="wadah-gambar" onclick="bukaModal('<?php echo $url_foto; ?>', '<?php echo$ket_foto; ?>')">
                            <img src="<?php echo $url_foto; ?>" alt="Foto Kegiatan">
                            <span class="label-acara">📌 <?php echo $foto['nama_acara']; ?></span>
                        </div>
                        <div class="info-foto">
                            <p><strong><?php echo $foto['keterangan_foto']; ?></strong></p>
                            
                            <!-- Tombol Aksi (Preview, Download, Print) untuk User & Admin -->
                            <div class="aksi-foto">
                                <button class="btn-aksi-foto btn-preview" onclick="bukaModal('<?php echo $url_foto; ?>', '<?php echo$ket_foto; ?>')">🔍 Zoom</button>
                                <a href="<?php echo $url_foto; ?>" download class="btn-aksi-foto btn-download">📥 Unduh</a>
                                <button class="btn-aksi-foto btn-print" onclick="cetakFotoSatuan('<?php echo $url_foto; ?>', '<?php echo$foto['nama_acara']; ?>')">🖨️ Cetak</button>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align:center; color:#7f8c8d; padding: 30px;'>Belum ada foto kegiatan yang diunggah.</p>";
                }
                ?>
            </div>
        </div>

    </div>

    <!-- MODAL POPUP PREVIEW / ZOOM GAMBAR -->
    <div id="modalPreview">
        <span class="tutup-modal" onclick="tutupModal()">&times;</span>
        <img id="gambarModal" src="" alt="Preview Ukuran Asli">
        <p id="captionModal" style="color: white; margin-top: 15px; font-size: 16px; font-weight: bold; text-align: center;"></p>
        <div class="kontrol-zoom">
            <button onclick="zoomIn()">➕ Perbesar (+)</button>
            <button onclick="zoomOut()">➖ Perkecil (-)</button>
            <button onclick="tutupModal()">Tutup (X)</button>
        </div>
    </div>

</body>
</html>