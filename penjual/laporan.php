<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'penjual'){
    header("Location: ../index.php");
    exit;
}

$id_toko = $_SESSION['id_toko'];

// Tandai pesanan riwayat telah dilihat oleh penjual
$db_ekantin->query("UPDATE pesanan SET dilihat_penjual = 1 WHERE id_toko='{$_SESSION['id_toko']}' AND status_pesanan IN ('selesai', 'diambil', 'tidak_diambil', 'dibatalkan') AND dilihat_penjual = 0");


$filter_tanggal = $_POST['filter_tanggal'] ?? 'hari_ini';
$start_date     = $_POST['start_date'] ?? '';
$end_date       = $_POST['end_date'] ?? '';

$kondisi_tanggal = "DATE(p.tanggal_pesan) = CURDATE()";
if($filter_tanggal == 'minggu_ini') $kondisi_tanggal = "YEARWEEK(p.tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)";
if($filter_tanggal == 'bulan_ini') $kondisi_tanggal = "MONTH(p.tanggal_pesan) = MONTH(CURDATE()) AND YEAR(p.tanggal_pesan) = YEAR(CURDATE())";
if($filter_tanggal == 'semua_waktu') $kondisi_tanggal = "1=1";
if($filter_tanggal == 'kustom' && $start_date && $end_date) {
    // Tukar tanggal jika start_date lebih besar dari end_date
    if ($start_date > $end_date) {
        $temp = $start_date;
        $start_date = $end_date;
        $end_date = $temp;
    }
    $start = $db_ekantin->real_escape_string($start_date);
    $end   = $db_ekantin->real_escape_string($end_date);
    $kondisi_tanggal = "DATE(p.tanggal_pesan) >= '$start' AND DATE(p.tanggal_pesan) <= '$end'";
}

$active_tab = $_GET['tab'] ?? 'penjualan';

$qTotal = $db_ekantin->query("
    SELECT COUNT(p.id_pesanan) as total, SUM(p.total_harga) as pendapatan 
    FROM pesanan p
    WHERE p.id_toko='$id_toko' AND p.status_pesanan IN ('selesai','diambil','tidak_diambil') AND $kondisi_tanggal
");
$dataTotal = $qTotal->fetch_assoc();

$qPoinReport = $db_ekantin->query("
    SELECT SUM(p.poin_digunakan) as total_poin
    FROM pesanan p
    WHERE p.id_toko='$id_toko' AND p.status_pesanan IN ('selesai','diambil','tidak_diambil') AND $kondisi_tanggal
");
$total_poin_report = $qPoinReport->fetch_assoc()['total_poin'] ?? 0;
$total_pesanan   = $dataTotal['total'] ?? 0;
$total_pendapatan = number_format($dataTotal['pendapatan'] ?? 0, 0, ',', '.');

// Query untuk Laporan Struk Penjualan & Stok
$qStore = $db_ekantin->query("SELECT nama_toko FROM toko WHERE id_toko='$id_toko'");
$tokoData = $qStore->fetch_assoc();
$nama_toko = $tokoData['nama_toko'] ?? 'Toko';

// Terjual Query for both reports
$qTerjual = $db_ekantin->query("
    SELECT pk.id_produk, pk.nama_menu, pk.stok, SUM(dp.jumlah) as qty, SUM(IF(dp.harga_satuan > 0, dp.harga_satuan * dp.jumlah, pk.harga * dp.jumlah)) as sub
    FROM detail_pesanan dp
    JOIN pesanan p ON dp.id_pesanan = p.id_pesanan
    JOIN produk_kantin pk ON dp.id_produk = pk.id_produk
    WHERE p.id_toko='$id_toko' AND p.status_pesanan IN ('selesai','diambil','tidak_diambil') AND $kondisi_tanggal
    GROUP BY pk.id_produk
");
$itemTerjual = [];
$terjualMap = [];
if ($qTerjual) {
    while($row = $qTerjual->fetch_assoc()) {
        $itemTerjual[] = $row;
        $terjualMap[$row['id_produk']] = $row['qty'];
    }
}

$allProducts = [];
$qProducts = $db_ekantin->query("SELECT id_produk, nama_menu, stok FROM produk_kantin WHERE id_toko='$id_toko' ORDER BY nama_menu ASC");
if ($qProducts) {
    while($row = $qProducts->fetch_assoc()) {
        $allProducts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan <?= $active_tab == 'stok' ? 'Stok' : 'Penjualan' ?></title>

    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .popup-enter { animation: popupIn 0.25s cubic-bezier(.4,0,.2,1) both; }
        @keyframes popupIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
<div class="flex min-h-screen relative">
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

            <header class="flex justify-between items-end flex-wrap gap-4">
                <div>
                    <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Laporan <?= $active_tab == 'stok' ? 'Stok' : 'Penjualan' ?></h2>
                    <p class="text-text-3 mt-1 text-sm">Lihat ringkasan dan cetak laporan <?= $active_tab == 'stok' ? 'stok menu' : 'penjualan' ?> berdasarkan periode</p>
                </div>
            </header>

            <!-- Navigation Tabs -->
            <div class="flex border-b border-gray-200">
                <a href="?tab=penjualan" class="px-6 py-3 text-sm font-bold border-b-4 transition-colors <?= $active_tab == 'penjualan' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">Laporan Penjualan</a>
                <a href="?tab=stok" class="px-6 py-3 text-sm font-bold border-b-4 transition-colors <?= $active_tab == 'stok' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">Laporan Stok</a>
            </div>

            <form method="POST" action="?tab=<?= htmlspecialchars($active_tab) ?>" class="flex flex-col md:flex-row gap-3 items-end bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex-grow w-full md:w-auto overflow-x-auto pb-1 flex gap-2">
                    <button type="button" onclick="document.getElementById('customDateInputs').classList.toggle('hidden')"
                        class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all <?= $filter_tanggal == 'kustom' ? 'bg-submit text-white' : 'bg-white border border-gray-200 text-text-2 hover:bg-gray-50' ?>">
                        Kustom Tanggal
                    </button>
                    <?php
                    $tgl_options = [
                        'hari_ini'    => 'Hari Ini',
                        'minggu_ini'  => 'Minggu Ini',
                        'bulan_ini'   => 'Bulan Ini',
                        'semua_waktu' => 'Semua',
                    ];
                    foreach($tgl_options as $val => $label):
                        $aktif = $filter_tanggal == $val ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-text-2 hover:bg-gray-50';
                    ?>
                    <button type="submit" name="filter_tanggal" value="<?= $val ?>"
                        class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all <?= $aktif ?>">
                        <?= $label ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <div id="customDateInputs" class="flex flex-wrap items-center gap-2 w-full md:w-auto <?= $filter_tanggal == 'kustom' ? '' : 'hidden' ?>">
                    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" onchange="document.getElementById('end_date').min = this.value" class="border border-gray-200 rounded-lg text-sm px-2 py-1.5 focus:ring-0 focus:border-primary text-text-1 flex-1 sm:flex-none">
                    <span class="text-text-3 text-sm shrink-0">s/d</span>
                    <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" onchange="document.getElementById('start_date').max = this.value" class="border border-gray-200 rounded-lg text-sm px-2 py-1.5 focus:ring-0 focus:border-primary text-text-1 flex-1 sm:flex-none">
                    <button type="submit" name="filter_tanggal" value="kustom" class="bg-submit text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-green-700 transition-colors shrink-0">Terapkan</button>
                </div>
            </form>

            <!-- Laporan Struk -->
            <div class="bg-gray-100 p-4 rounded-xl shadow-sm w-full">
        <div id="strukContent" class="bg-white w-full p-6 text-sm font-sans text-gray-800 shadow-sm border border-gray-200">
            <div class="mb-5 flex flex-col gap-1 border-b border-gray-300 pb-3">
                <h3 class="font-extrabold text-2xl uppercase tracking-wider" style="color: #107c41;">Laporan <?= $active_tab == 'stok' ? 'Stok' : 'Penjualan' ?></h3>
                <p class="font-semibold text-gray-700 text-base">Toko: <?= htmlspecialchars($nama_toko) ?></p>
                <p class="text-sm text-gray-500">Periode: <?= $filter_tanggal == 'kustom' ? htmlspecialchars($start_date) . ' s/d ' . htmlspecialchars($end_date) : str_replace('_', ' ', ucwords($filter_tanggal)) ?></p>
            </div>
            
            <?php if ($active_tab == 'penjualan'): ?>
            <table class="w-full text-sm border-collapse border border-gray-300 mb-6">
                <thead>
                    <tr class="text-white" style="background-color: #107c41;">
                        <th class="border border-gray-300 px-3 py-2.5 text-center font-semibold w-12">No</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-left font-semibold">Nama Item / Menu</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-center font-semibold w-28">Qty Terjual</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-right font-semibold w-40">Subtotal Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $total_qty_semua = 0;
                    foreach($itemTerjual as $it): 
                        $total_qty_semua += $it['qty'];
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="border border-gray-300 px-3 py-2 text-center"><?= $no++ ?></td>
                        <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($it['nama_menu']) ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><?= $it['qty'] ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-right">Rp <?= number_format($it['sub'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($itemTerjual)): ?>
                    <tr>
                        <td colspan="4" class="border border-gray-300 px-3 py-6 text-center text-gray-500 italic">Tidak ada penjualan selesai pada periode ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <?php if ($total_poin_report > 0): ?>
                    <tr class="bg-red-50 text-red-700">
                        <td colspan="3" class="border border-gray-300 px-3 py-2 text-right italic font-semibold">Pembayaran via Poin</td>
                        <td class="border border-gray-300 px-3 py-2 text-right font-semibold">- Rp <?= number_format($total_poin_report, 0, ',', '.') ?></td>
                    </tr>
                    <?php endif; ?>
                    
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="2" class="border border-gray-300 px-3 py-3 text-right uppercase text-gray-700">Total Keseluruhan</td>
                        <td class="border border-gray-300 px-3 py-3 text-center text-gray-700 font-bold"><?= isset($total_qty_semua) && $total_qty_semua > 0 ? $total_qty_semua : '-' ?></td>
                        <td class="border border-gray-300 px-3 py-3 text-right text-base font-bold" style="color: #107c41;">Rp <?= $total_pendapatan ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="flex justify-between items-end text-xs text-gray-500 mt-4">
                <div>
                    <p class="font-semibold text-gray-700 mb-0.5">Ringkasan:</p>
                    <p>Total Transaksi Selesai: <span class="font-bold text-gray-700"><?= $total_pesanan ?> pesanan</span></p>
                </div>
                <div class="text-right">
                    <p>Dicetak pada: <span class="font-semibold"><?= date('d/m/Y H:i:s') ?></span></p>
                    <p>Sistem E-Kantin SMEA</p>
                </div>
            </div>
            
            <?php elseif ($active_tab == 'stok'): ?>
            <table class="w-full text-sm border-collapse border border-gray-300 mb-6">
                <thead>
                    <tr class="text-white" style="background-color: #107c41;">
                        <th class="border border-gray-300 px-3 py-2.5 text-center font-semibold w-12">No</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-left font-semibold">Nama Item / Menu</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-center font-semibold w-24">Stok Awal</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-center font-semibold w-24">Terjual</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-center font-semibold w-24">Stok Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $total_awal = 0;
                    $total_terjual = 0;
                    $total_akhir = 0;
                    foreach($allProducts as $p): 
                        $qty_terjual = $terjualMap[$p['id_produk']] ?? 0;
                        $stok_akhir = $p['stok'];
                        $stok_awal = $stok_akhir + $qty_terjual;
                        
                        $total_awal += $stok_awal;
                        $total_terjual += $qty_terjual;
                        $total_akhir += $stok_akhir;
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="border border-gray-300 px-3 py-2 text-center"><?= $no++ ?></td>
                        <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($p['nama_menu']) ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-center font-medium"><?= $stok_awal ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-primary font-bold"><?= $qty_terjual ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-center font-medium <?= $stok_akhir <= 0 ? 'text-red-600' : '' ?>"><?= $stok_akhir ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($allProducts)): ?>
                    <tr>
                        <td colspan="5" class="border border-gray-300 px-3 py-6 text-center text-gray-500 italic">Tidak ada menu produk terdaftar di toko ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold text-center">
                        <td colspan="2" class="border border-gray-300 px-3 py-3 text-right uppercase text-gray-700">Total Keseluruhan</td>
                        <td class="border border-gray-300 px-3 py-3 text-gray-700 font-bold"><?= $total_awal ?></td>
                        <td class="border border-gray-300 px-3 py-3 text-primary font-bold"><?= $total_terjual ?></td>
                        <td class="border border-gray-300 px-3 py-3 text-gray-700 font-bold"><?= $total_akhir ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="flex justify-between items-end text-xs text-gray-500 mt-4">
                <div>
                    <p class="font-semibold text-gray-700 mb-0.5">Catatan Stok:</p>
                    <p class="max-w-[400px]">Data stok awal merupakan proyeksi berdasarkan stok akhir dijumlahkan dengan penjualan pada periode terpilih.</p>
                </div>
                <div class="text-right">
                    <p>Dicetak pada: <span class="font-semibold"><?= date('d/m/Y H:i:s') ?></span></p>
                    <p>Sistem E-Kantin SMEA</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="flex justify-end gap-2 w-full mt-4">
            <button type="button" onclick="printStruk()" class="w-full md:w-auto text-white font-bold py-3 px-8 rounded-xl transition-all flex items-center justify-center gap-2 shadow-md" style="background-color: #107c41;" onmouseover="this.style.backgroundColor='#0c6132'" onmouseout="this.style.backgroundColor='#107c41'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print / Simpan PDF
            </button>
        </div>

        </div>
    </main>
</div>

<script>
function printStruk() {
    const printContents = document.getElementById('strukContent').outerHTML;
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = '<div style="display:flex; justify-content:center; align-items:flex-start; padding: 20px; min-height:100vh; background:white;">' + printContents + '</div>';
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload(); 
}
</script>

</body>
</html>