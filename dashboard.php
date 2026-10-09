<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// 1. MENGHITUNG TOTAL PEMASUKAN & PENGELUARAN DARI DATABASE
$query_masuk = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_masuk FROM arus_kas WHERE jenis='masuk'");
$data_masuk = mysqli_fetch_array($query_masuk);
$total_masuk = $data_masuk['total_masuk'] ? $data_masuk['total_masuk'] : 0;

$query_keluar = mysqli_query($koneksi, "SELECT SUM(nominal) AS total_keluar FROM arus_kas WHERE jenis='keluar'");
$data_keluar = mysqli_fetch_array($query_keluar);
$total_keluar = $data_keluar['total_keluar'] ? $data_keluar['total_keluar'] : 0;

// Menghitung Saldo Akhir
$total_saldo = $total_masuk - $total_keluar;

// 2. MENYIAPKAN DATA UNTUK GRAFIK PER BULAN (12 Bulan Terakhir / Tahun Berjalan)
$bulan_arr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$data_grafik_masuk = [];
$data_grafik_keluar = [];

// Mengambil data per bulan berdasarkan tahun saat ini
$tahun_ini = date('Y');
for ($i = 1; $i <= 12; $i++) {
    $angka_bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
    
    // Cek total pemasukan di bulan tersebut
    // Format tanggal diasumsikan YYYY-MM-DD di database arus_kas
    $q_m = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM arus_kas WHERE jenis='masuk' AND MONTH(tanggal) = '$i' AND YEAR(tanggal) = '$tahun_ini'");
    $d_m = mysqli_fetch_array($q_m);
    $data_grafik_masuk[] = $d_m['total'] ? $d_m['total'] : 0;

    // Cek total pengeluaran di bulan tersebut
    $q_k = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM arus_kas WHERE jenis='keluar' AND MONTH(tanggal) = '$i' AND YEAR(tanggal) = '$tahun_ini'");
    $d_k = mysqli_fetch_array($q_k);
    $data_grafik_keluar[] = $d_k['total'] ? $d_k['total'] : 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Transparansi - Karang Taruna</title>
    <!-- Memuat Chart.js dari CDN untuk Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #f0f2f5; color: #333; }
        .menu-samping { width: 220px; background-color: #2c3e50; color: white; padding: 20px; height: 100vh; position: fixed; }
        .menu-samping a { color: white; text-decoration: none; display: block; padding: 12px; margin-bottom: 5px; border-radius: 5px; font-size: 14px; transition: background 0.2s; }
        .menu-samping a:hover { background-color: #34495e; }
        .keluar { background-color: #e74c3c; margin-top: 30px; }
        .keluar:hover { background-color: #c0392b !important; }
        
        .konten-utama { margin-left: 260px; padding: 30px; }
        h2 { color: #2c3e50; margin-top: 0; }
        
        /* Kotak Ringkasan */
        .kotak-info-container { display: flex; gap: 20px; margin-bottom: 25px; }
        .kotak-info { flex: 1; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        .kotak-info h4 { margin: 0; color: #7f8c8d; font-size: 14px; }
        .kotak-info h2 { margin: 10px 0 0 0; font-size: 22px; }
        .saldo { border-left: 5px solid #2980b9; }
        .masuk { border-left: 5px solid #27ae60; }
        .keluar-box { border-left: 5px solid #e74c3c; }

        .kartu { background-color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 25px; }
        
        /* Wadah Grafik */
        .wadah-grafik { position: relative; height: 320px; width: 100%; margin-top: 15px; }

        /* Tabel & Teks */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #eee; }
        th, td { padding: 12px; text-align: left; font-size: 14px; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; }
        tr:hover { background-color: #fcfcfc; }
        
        .teks-merah { color: #e74c3c; font-weight: bold; }
        .teks-hijau { color: #27ae60; font-weight: bold; }

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
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 8px;
        }
    </style>
</head>
<body>

    <div class="menu-samping">
        <a href="dashboard.php" style="background-color: #34495e;">🏠 Dashboard</a>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <a href="arus_kas.php">💰 Input Uang Kas</a>
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
        <h2>Dashboard Transparansi Warga 👋</h2>
        
        <!-- 3 KOTAK RINGKASAN SALDO & KAS -->
        <div class="kotak-info-container" style="margin-top: 20px;">
            <div class="kotak-info saldo">
                <h4>Total Saldo Kas RT</h4>
                <h2 style="color: #2980b9;">Rp <?php echo number_format($total_saldo, 0, ',', '.'); ?></h2>
            </div>
            <div class="kotak-info masuk">
                <h4>Total Pemasukan</h4>
                <h2 style="color: #27ae60;">Rp <?php echo number_format($total_masuk, 0, ',', '.'); ?></h2>
            </div>
            <div class="kotak-info keluar-box">
                <h4>Total Pengeluaran</h4>
                <h2 style="color: #e74c3c;">Rp <?php echo number_format($total_keluar, 0, ',', '.'); ?></h2>
            </div>
        </div>

        <!-- GRAFIK GARIS PEMASUKAN & PENGELUARAN PER BULAN -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#2c3e50;">📈 Grafik Tren Keuangan Tahun <?php echo $tahun_ini; ?></h3>
            <p style="font-size: 13px; color: #7f8c8d; margin-top: -5px;">Perbandingan garis pemasukan (hijau) dan pengeluaran (merah) per bulan untuk melihat selisih kas.</p>
            <div class="wadah-grafik">
                <canvas id="grafikKeuangan"></canvas>
            </div>
        </div>

        <!-- TABEL ACARA MENDATANG DENGAN ALERT BLINK 24 JAM -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#2c3e50;">🚀 Jadwal Acara Mendatang</h3>
            <table>
                <tr>
                    <th>Waktu Pelaksanaan</th>
                    <th>Nama Acara</th>
                    <th>Deskripsi</th>
                </tr>
                <?php
                $acara_mendatang = mysqli_query($koneksi, "SELECT * FROM acara WHERE tanggal_pelaksanaan >= NOW() ORDER BY tanggal_pelaksanaan ASC");
                
                if(mysqli_num_rows($acara_mendatang) > 0) {
                    while ($baris = mysqli_fetch_array($acara_mendatang)) {
                        $waktu_acara = $baris['tanggal_pelaksanaan'];
                        $timestamp_acara = strtotime($waktu_acara);
                        $timestamp_sekarang = time();
                        $selisih_detik = $timestamp_acara - $timestamp_sekarang;
                        $is_mendekati = ($selisih_detik >= 0 && $selisih_detik <= 86400);
                        $kelas_baris = $is_mendekati ? 'baris-blink' : '';
                ?>
                <tr class="<?php echo $kelas_baris; ?>">
                    <td>
                        <?php echo date('d-m-Y H:i', strtotime($waktu_acara)); ?> WIB
                        <?php if ($is_mendekati) { echo "<span class='badge-peringatan'>SEGERA DIMULAI!</span>"; } ?>
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

        <!-- TABEL TRANSPARANSI: RIWAYAT PEMBAYARAN IURAN WARGA -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#2c3e50;">📒 Transparansi Riwayat Pembayaran Iuran Warga</h3>
            <table>
                <tr>
                    <th>Nama Warga</th>
                    <th>Bulan / Tahun</th>
                    <th>Tanggal Bayar</th>
                    <th>Nominal</th>
                </tr>
                <?php
                $data_iuran = mysqli_query($koneksi, "SELECT * FROM iuran_warga ORDER BY id DESC");
                if (mysqli_num_rows($data_iuran) > 0) {
                    while ($iuran = mysqli_fetch_array($data_iuran)) {
                ?>
                <tr>
                    <td><?php echo $iuran['nama_warga']; ?></td>
                    <td><?php echo $iuran['bulan'] . ' ' . $iuran['tahun']; ?></td>
                    <td><?php echo $iuran['tanggal_bayar']; ?></td>
                    <td>Rp <?php echo number_format($iuran['nominal'], 0, ',', '.'); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:#7f8c8d;'>Belum ada data pembayaran iuran warga.</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- TABEL TRANSPARANSI: ARUS KAS (PEMASUKAN & PENGELUARAN) -->
        <div class="kartu">
            <h3 style="margin-top:0; color:#2c3e50;">💰 Transparansi Arus Kas (Pemasukan & Pengeluaran RT)</h3>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Jenis Transaksi</th>
                    <th>Nominal</th>
                </tr>
                <?php
                $data_kas = mysqli_query($koneksi, "SELECT * FROM arus_kas ORDER BY tanggal DESC");
                if (mysqli_num_rows($data_kas) > 0) {
                    while ($kas = mysqli_fetch_array($data_kas)) {
                        if ($kas['jenis'] == 'masuk') {
                            $warna_teks = "teks-hijau";
                            $jenis_teks = "Pemasukan";
                        } else {
                            $warna_teks = "teks-merah";
                            $jenis_teks = "Pengeluaran";
                        }
                ?>
                <tr>
                    <td><?php echo $kas['tanggal']; ?></td>
                    <td><?php echo $kas['keterangan']; ?></td>
                    <td class="<?php echo $warna_teks; ?>"><?php echo $jenis_teks; ?></td>
                    <td>Rp <?php echo number_format($kas['nominal'], 0, ',', '.'); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:#7f8c8d;'>Belum ada data arus kas.</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>

    <!-- SCRIPT RENDER GRAFIK MENGGUNAKAN CHART.JS -->
    <script>
        const ctx = document.getElementById('grafikKeuangan').getContext('2d');
        const grafikKeuangan = new Chart(ctx, {
            type: 'line', // Jenis grafik garis
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Pemasukan (Rp)',
                        data: <?php echo json_encode($data_grafik_masuk); ?>,
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Pengeluaran (Rp)',
                        data: <?php echo json_encode($data_grafik_keluar); ?>,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>