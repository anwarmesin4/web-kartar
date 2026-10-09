<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// 1. PROSES TAMBAH NAMA WARGA BARU
if ($_SESSION['role'] == 'admin' && isset($_POST['tambah_nama_warga'])) {
    $nama_baru = trim($_POST['nama_warga_baru']);
    if(!empty($nama_baru)) {
        $cek_warga = mysqli_query($koneksi, "SELECT * FROM warga WHERE nama_warga='$nama_baru'");
        if(mysqli_num_rows($cek_warga) > 0) {
            echo "<script>alert('Nama warga tersebut sudah ada di dalam daftar!');</script>";
        } else {
            mysqli_query($koneksi, "INSERT INTO warga (nama_warga) VALUES ('$nama_baru')");
            echo "<script>alert('Nama warga berhasil ditambahkan!'); window.location='kas_warga.php';</script>";
        }
    }
}

// 2. PROSES EDIT NAMA WARGA
if ($_SESSION['role'] == 'admin' && isset($_POST['edit_nama_warga'])) {
    $id_warga = $_POST['id_warga'];
    $nama_edit = trim($_POST['nama_warga_edit']);
    if(!empty($nama_edit)) {
        mysqli_query($koneksi, "UPDATE warga SET nama_warga='$nama_edit' WHERE id='$id_warga'");
        echo "<script>alert('Data nama warga berhasil diperbarui!'); window.location='kas_warga.php';</script>";
    }
}

// 3. PROSES HAPUS NAMA WARGA
if ($_SESSION['role'] == 'admin' && isset($_GET['hapus_warga'])) {
    $id_warga = $_GET['hapus_warga'];
    mysqli_query($koneksi, "DELETE FROM warga WHERE id='$id_warga'");
    echo "<script>alert('Nama warga berhasil dihapus dari daftar!'); window.location='kas_warga.php';</script>";
}

// 4. PROSES CATAT PEMBAYARAN IURAN (OTOMATIS MASUK KE ARUS KAS)
if ($_SESSION['role'] == 'admin' && isset($_POST['simpan_iuran'])) {
    $nama = $_POST['nama_warga'];
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];
    $nominal = $_POST['nominal'];
    $tanggal = $_POST['tanggal_bayar'];

    $simpan_iuran = mysqli_query($koneksi, "INSERT INTO iuran_warga (nama_warga, bulan, tahun, nominal, tanggal_bayar) 
                                              VALUES ('$nama', '$bulan', '$tahun', '$nominal', '$tanggal')");

    $keterangan_kas = "Iuran Warga: " . $nama . " (" . $bulan . " " . $tahun . ")";
    $simpan_kas = mysqli_query($koneksi, "INSERT INTO arus_kas (jenis, nominal, keterangan, tanggal) 
                                          VALUES ('masuk', '$nominal', '$keterangan_kas', '$tanggal')");

    if ($simpan_iuran && $simpan_kas) {
        echo "<script>alert('Iuran berhasil dicatat dan otomatis masuk ke Saldo Kas RT!'); window.location='kas_warga.php';</script>";
    } else {
        echo "<script>alert('Gagal mencatat pembayaran.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kas Warga - Karang Taruna</title>
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
        
        /* Tata Letak Modern Dua Kolom di Atas */
        .baris-atas { display: flex; gap: 20px; margin-bottom: 10px; }
        .kolom-kiri { flex: 1; }
        .kolom-kanan { flex: 1; }

        label { font-size: 13px; font-weight: bold; color: #555; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #dcdde1; border-radius: 6px; box-sizing: border-box; font-size: 14px; background-color: #f8f9fa; }
        input:focus, select:focus { border-color: #3498db; outline: none; background-color: #fff; }

        button { width: 100%; background-color: #2980b9; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.2s; }
        button:hover { background-color: #3498db; }
        .btn-tambah { background-color: #16a085; }
        .btn-tambah:hover { background-color: #1abc9c; }
        .btn-ekspor { background-color: #27ae60; padding: 8px 14px; color: white; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; font-weight: bold; margin-right: 5px; }
        .btn-ekspor:hover { background-color: #219653; }
        
        .btn-aksi { padding: 4px 8px; font-size: 12px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block; margin-right: 3px; }
        .btn-edit { background-color: #f39c12; color: white; }
        .btn-edit:hover { background-color: #d68910; }
        .btn-hapus { background-color: #e74c3c; color: white; }
        .btn-hapus:hover { background-color: #c0392b; }

        .wadah-pencarian { display: flex; gap: 10px; margin-top: 15px; margin-bottom: 10px; }
        .wadah-pencarian input { margin: 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #eee; }
        th, td { padding: 12px; text-align: left; font-size: 14px; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; }
        tr:hover { background-color: #fcfcfc; }

        /* Wadah Tabel Master Warga agar bisa di-scroll jika datanya banyak */
        .tabel-scroll { max-height: 250px; overflow-y: auto; border: 1px solid #eee; border-radius: 6px; }

        @media print {
            .menu-samping, .kartu-form, .wadah-pencarian, .keluar, button, .kolom-aksi { display: none !important; }
            .konten-utama { margin-left: 0; padding: 0; }
            .kartu { box-shadow: none; border: none; padding: 0; }
        }
    </style>
    <script>
        function cariData() {
            let input = document.getElementById("inputPencarian").value.toLowerCase();
            let tabel = document.getElementById("tabelIuran");
            let baris = tabel.getElementsByTagName("tr");

            for (let i = 1; i < baris.length; i++) {
                let barisTampil = false;
                let kolom = baris[i].getElementsByTagName("td");
                for (let j = 0; j < kolom.length; j++) {
                    if (kolom[j]) {
                        let teks = kolom[j].textContent || kolom[j].innerText;
                        if (teks.toLowerCase().indexOf(input) > -1) {
                            barisTampil = true;
                        }
                    }
                }
                if (barisTampil) {
                    baris[i].style.display = "";
                } else {
                    baris[i].style.display = "none";
                }
            }
        }
    </script>
</head>
<body>

    <div class="menu-samping">
        <a href="dashboard.php">🏠 Dashboard</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="arus_kas.php">💰 Input Uang Kas</a>
            <a href="kas_warga.php" style="background-color: #34495e;">📒 Pembayaran Warga</a>
        <?php } ?>
        <a href="acara.php">📅 Jadwal Acara</a>
        <a href="galeri.php">📸 Galeri Foto</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="kelola_user.php">👥 Kelola Akun</a>
        <?php } ?>
        <a href="logout.php" class="keluar">🚪 Keluar (Logout)</a>
    </div>

    <div class="konten-utama">
        <h2>Pencatatan Kas Warga Bulanan 📒</h2>
        
        <?php if ($_SESSION['role'] == 'admin') { 
            $mode_edit = false;
            $data_edit = null;
            if(isset($_GET['edit_warga'])) {
                $id_edit = $_GET['edit_warga'];
                $q_edit = mysqli_query($koneksi, "SELECT * FROM warga WHERE id='$id_edit'");
                if(mysqli_num_rows($q_edit) > 0) {
                    $mode_edit = true;
                    $data_edit = mysqli_fetch_array($q_edit);
                }
            }
        ?>

        <!-- BAGIAN ATAS: TATA LETAK DUA KOLOM (FORM & MASTER WARGA) -->
        <div class="baris-atas kartu-form">
            
            <!-- KOLOM KIRI: FORM TAMBAH/EDIT MASTER WARGA & FORM PEMBAYARAN -->
            <div class="kolom-kiri">
                <div class="kartu">
                    <?php if($mode_edit) { ?>
                        <h3 style="margin-top:0; color:#2c3e50;">✏️ Edit Master Nama Warga</h3>
                        <form method="POST">
                            <input type="hidden" name="id_warga" value="<?php echo $data_edit['id']; ?>">
                            <label>Nama Lengkap Warga:</label>
                            <input type="text" name="nama_warga_edit" value="<?php echo $data_edit['nama_warga']; ?>" required>
                            <button type="submit" name="edit_nama_warga" style="background-color: #f39c12; margin-bottom: 10px;">💾 Simpan Perubahan</button>
                            <a href="kas_warga.php" style="color:#e74c3c; text-decoration:none; font-size:13px; font-weight:bold;">Batal Edit</a>
                        </form>
                    <?php } else { ?>
                        <h3 style="margin-top:0; color:#2c3e50;">+ Tambah Nama Warga</h3>
                        <form method="POST">
                            <label>Nama Lengkap Warga:</label>
                            <input type="text" name="nama_warga_baru" placeholder="Contoh: Budi Santoso" required>
                            <button type="submit" name="tambah_nama_warga" class="btn-tambah">📁 Simpan ke Daftar Warga</button>
                        </form>
                    <?php } ?>
                </div>

                <div class="kartu">
                    <h3 style="margin-top:0; color:#2c3e50;">💳 Catat Pembayaran Iuran</h3>
                    <form method="POST">
                        <label>Pilih Nama Warga:</label>
                        <select name="nama_warga" required>
                            <option value="">-- Pilih Warga dari Daftar --</option>
                            <?php
                            $dt_warga = mysqli_query($koneksi, "SELECT * FROM warga ORDER BY nama_warga ASC");
                            while($w = mysqli_fetch_array($dt_warga)) {
                                echo "<option value='".$w['nama_warga']."'>".$w['nama_warga']."</option>";
                            }
                            ?>
                        </select>

                        <label>Bulan Pembayaran:</label>
                        <select name="bulan" required>
                            <option value="Januari">Januari</option>
                            <option value="Februari">Februari</option>
                            <option value="Maret">Maret</option>
                            <option value="April">April</option>
                            <option value="Mei">Mei</option>
                            <option value="Juni">Juni</option>
                            <option value="Juli">Juli</option>
                            <option value="Agustus">Agustus</option>
                            <option value="September">September</option>
                            <option value="Oktober">Oktober</option>
                            <option value="November">November</option>
                            <option value="Desember">Desember</option>
                        </select>

                        <label>Tahun:</label>
                        <input type="number" name="tahun" value="<?php echo date('Y'); ?>" required>

                        <label>Nominal (Rp):</label>
                        <input type="number" name="nominal" placeholder="Contoh: 25000" required>

                        <label>Tanggal Bayar:</label>
                        <input type="date" name="tanggal_bayar" required>

                        <button type="submit" name="simpan_iuran">💾 Simpan Pembayaran</button>
                    </form>
                </div>
            </div>

            <!-- KOLOM KANAN: TABEL DAFTAR MASTER NAMA WARGA -->
            <div class="kolom-kanan">
                <div class="kartu" style="height: 100%; box-sizing: border-box;">
                    <h3 style="margin-top:0; color:#2c3e50;">👥 Daftar Master Nama Warga</h3>
                    <div class="tabel-scroll">
                        <table>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Warga</th>
                                <th class="kolom-aksi" style="width: 110px; text-align: center;">Aksi</th>
                            </tr>
                            <?php
                            $no_w = 1;
                            $q_warga_list = mysqli_query($koneksi, "SELECT * FROM warga ORDER BY nama_warga ASC");
                            if(mysqli_num_rows($q_warga_list) > 0) {
                                while($w_item = mysqli_fetch_array($q_warga_list)) {
                            ?>
                            <tr>
                                <td><?php echo $no_w++; ?></td>
                                <td><?php echo $w_item['nama_warga']; ?></td>
                                <td class="kolom-aksi" style="text-align: center;">
                                    <a href="kas_warga.php?edit_warga=<?php echo $w_item['id']; ?>" class="btn-aksi btn-edit">Edit</a>
                                    <a href="kas_warga.php?hapus_warga=<?php echo $w_item['id']; ?>" class="btn-aksi btn-hapus" onclick="return confirm('Yakin ingin menghapus nama warga ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; color:#7f8c8d;'>Belum ada data warga.</td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <?php } ?>

        <!-- BAGIAN BAWAH: RIWAYAT PEMBAYARAN IURAN WARGA -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#2c3e50;">📊 Riwayat Pembayaran Iuran Warga</h3>
            
            <div class="wadah-pencarian">
                <input type="text" id="inputPencarian" onkeyup="cariData()" placeholder="🔍 Ketik untuk mencari nama, bulan, tanggal, atau nominal...">
                <div style="display: flex; align-items: center;">
                    <a href="#" onclick="window.print();" class="btn-ekspor" style="background-color: #34495e;">🖨️ Print</a>
                    <button onclick="eksporKeExcel()" class="btn-ekspor" style="background-color: #27ae60; border:none; height: 38px; cursor:pointer;">📊 Export Excel</button>
                </div>
            </div>

            <table id="tabelIuran">
                <tr>
                    <th>Nama Warga</th>
                    <th>Bulan / Tahun</th>
                    <th>Tanggal Bayar</th>
                    <th>Nominal</th>
                </tr>
                <?php
                $data_iuran = mysqli_query($koneksi, "SELECT * FROM iuran_warga ORDER BY id DESC");
                if(mysqli_num_rows($data_iuran) > 0) {
                    while ($baris = mysqli_fetch_array($data_iuran)) {
                ?>
                <tr>
                    <td><?php echo $baris['nama_warga']; ?></td>
                    <td><?php echo $baris['bulan'] . ' ' . $baris['tahun']; ?></td>
                    <td><?php echo $baris['tanggal_bayar']; ?></td>
                    <td>Rp <?php echo number_format($baris['nominal'], 0, ',', '.'); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:#7f8c8d;'>Belum ada riwayat pembayaran iuran.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <script>
        function eksporKeExcel() {
            let htmlTabel = document.getElementById("tabelIuran").outerHTML;
            let a = document.createElement('a');
            a.href = 'data:application/vnd.ms-excel,' + encodeURIComponent(htmlTabel);
            a.download = 'Rekap_Kas_Warga_RT.xls';
            a.click();
        }
    </script>

</body>
</html>