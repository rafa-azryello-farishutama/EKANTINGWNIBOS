<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'penjual'){
    header("Location: ../index.php");
    exit;
}

$id_toko = $_SESSION['id_toko'];

$filter_status = $_POST['filter_status'] ?? 'semua';
$filter_tanggal = $_POST['filter_tanggal'] ?? 'hari_ini';

$kondisi_status = "p.status_pesanan IN ('selesai','diambil','tidak_diambil','dibatalkan')";
if($filter_status == 'selesai') $kondisi_status = "p.status_pesanan IN ('selesai','diambil','tidak_diambil')";
if($filter_status == 'dibatalkan') $kondisi_status = "p.status_pesanan = 'dibatalkan'";

$kondisi_tanggal = "DATE(p.tanggal_pesan) = CURDATE()";
if($filter_tanggal == 'minggu_ini') $kondisi_tanggal = "YEARWEEK(p.tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)";
if($filter_tanggal == 'bulan_ini') $kondisi_tanggal = "MONTH(p.tanggal_pesan) = MONTH(CURDATE()) AND YEAR(p.tanggal_pesan) = YEAR(CURDATE())";
if($filter_tanggal == 'semua_waktu') $kondisi_tanggal = "1=1";

$qTotal = $db_ekantin->query("
    SELECT COUNT(DISTINCT p.id_pesanan) as total, SUM(IF(dp.harga_satuan > 0, dp.harga_satuan * dp.jumlah, pk.harga * dp.jumlah)) as pendapatan 
    FROM pesanan p
    LEFT JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
    LEFT JOIN produk_kantin pk ON dp.id_produk = pk.id_produk
    WHERE p.id_toko='$id_toko' AND p.status_pesanan IN ('selesai','diambil','tidak_diambil') AND $kondisi_tanggal
");
$dataTotal = $qTotal->fetch_assoc();
$total_pesanan   = $dataTotal['total'] ?? 0;
$total_pendapatan = number_format($dataTotal['pendapatan'] ?? 0, 0, ',', '.');

$qRiwayat = $db_ekantin->query("SELECT p.*, u.username
    FROM pesanan p
    JOIN users u ON p.id_users = u.id_users
    WHERE p.id_toko='$id_toko' AND $kondisi_status AND $kondisi_tanggal
    ORDER BY p.tanggal_pesan DESC");

// Query untuk Laporan Struk
$qStore = $db_ekantin->query("SELECT nama_toko FROM toko WHERE id_toko='$id_toko'");
$tokoData = $qStore->fetch_assoc();
$nama_toko = $tokoData['nama_toko'] ?? 'Toko';

$qTerjual = $db_ekantin->query("
    SELECT pk.nama_menu, SUM(dp.jumlah) as qty, SUM(IF(dp.harga_satuan > 0, dp.harga_satuan * dp.jumlah, pk.harga * dp.jumlah)) as sub
    FROM detail_pesanan dp
    JOIN pesanan p ON dp.id_pesanan = p.id_pesanan
    JOIN produk_kantin pk ON dp.id_produk = pk.id_produk
    WHERE p.id_toko='$id_toko' AND p.status_pesanan IN ('selesai','diambil','tidak_diambil') AND $kondisi_tanggal
    GROUP BY pk.id_produk
");
$itemTerjual = [];
if ($qTerjual) {
    while($row = $qTerjual->fetch_assoc()) {
        $itemTerjual[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>

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
                    <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Riwayat Pesanan</h2>
                    <p class="text-text-3 mt-1 text-sm">Rekap seluruh pesanan yang sudah selesai atau dibatalkan</p>
                </div>
                <button type="button" onclick="document.getElementById('modalLaporan').classList.remove('hidden')" class="bg-primary hover:bg-submit text-white text-sm font-bold py-2.5 px-5 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95">
                    📄 Cetak Laporan
                </button>
            </header>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-[20px] p-5 shadow-sm border border-green-50 flex flex-col gap-1 relative overflow-hidden group hover:-translate-y-1 hover:shadow-md hover:shadow-green-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Selesai</p>
                        <p class="text-4xl font-extrabold text-green-600"><?= $total_pesanan ?></p>
                        <p class="text-xs text-text-2">dalam periode ini</p>
                    </div>
                </div>
                <div class="bg-white rounded-[20px] p-5 shadow-sm border border-blue-50 flex flex-col gap-1 relative overflow-hidden group hover:-translate-y-1 hover:shadow-md hover:shadow-blue-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Total Pendapatan</p>
                        <p class="text-2xl font-extrabold text-blue-600">Rp <?= $total_pendapatan ?></p>
                        <p class="text-xs text-text-2">dari pesanan selesai</p>
                    </div>
                </div>
            </div>

            <form method="POST" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="filter_status" value="<?= $filter_status ?>">

                <div class="flex gap-2 overflow-x-auto pb-1 flex-1">
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

                <div class="flex gap-2 overflow-x-auto pb-1">
                    <?php
                    $status_options = [
                        'semua'      => 'Semua',
                        'selesai'    => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ];
                    foreach($status_options as $val => $label):
                        $aktif = $filter_status == $val ? 'bg-submit text-white' : 'bg-white border border-gray-200 text-text-2 hover:bg-gray-50';
                    ?>
                    <button type="submit" name="filter_status" value="<?= $val ?>"
                        class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all <?= $aktif ?>">
                        <?= $label ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </form>

            <div class="flex flex-col gap-3">
                <?php if($qRiwayat && $qRiwayat->num_rows > 0): ?>
                <?php while($ps = $qRiwayat->fetch_assoc()):
                    $waktu_pesan = new DateTime($ps['tanggal_pesan']);
                    $tanggal     = $waktu_pesan->format('d M Y');
                    $jam         = $waktu_pesan->format('H:i');
                    $total       = number_format($ps['total_harga'], 0, ',', '.');

                    $qMenu = $db_ekantin->query("SELECT dp.jumlah, pk.nama_menu 
                        FROM detail_pesanan dp 
                        JOIN produk_kantin pk ON dp.id_produk = pk.id_produk 
                        WHERE dp.id_pesanan='{$ps['id_pesanan']}'");
                    $listMenu = [];
                    while($m = $qMenu->fetch_assoc()){
                        $listMenu[] = $m['nama_menu'] . ' x' . $m['jumlah'];
                    }
                    $tampilMenu = implode(', ', $listMenu) ?: '-';

                    $badge = in_array($ps['status_pesanan'], ['selesai', 'diambil', 'tidak_diambil'])
                        ? "<span class='px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full capitalize'>" . str_replace('_', ' ', $ps['status_pesanan']) . "</span>"
                        : "<span class='px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full capitalize'>" . $ps['status_pesanan'] . "</span>";
                ?>
                <div class="bg-white rounded-[20px] p-5 shadow-sm border border-gray-100 flex flex-col gap-3 hover:-translate-y-0.5 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-text-1 text-sm">#ORD-<?= sprintf("%04d", $ps['id_pesanan']) ?></p>
                                <p class="text-xs text-text-3 px-2 py-0.5 bg-gray-100 rounded-md"><?= htmlspecialchars($ps['username']) ?></p>
                            </div>
                            <p class="text-xs text-text-3 mt-1"><?= $tanggal ?> · <?= $jam ?></p>
                        </div>
                        <?= $badge ?>
                    </div>
                    <p class="text-sm text-text-2"><?= htmlspecialchars($tampilMenu) ?></p>
                    <?php if($ps['catatan']): ?>
                    <p class="text-xs text-text-3 italic">Catatan: <?= htmlspecialchars($ps['catatan']) ?></p>
                    <?php endif; ?>
                    <div class="flex justify-between items-center border-t border-gray-100 pt-3">
                        <a href="../apps/struk.php?id_pesanan=<?= $ps['id_pesanan'] ?>" target="_blank" class="text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 px-3 py-1.5 rounded-lg transition-all">
                            📄 Struk
                        </a>
                        <p class="text-base font-bold text-text-1">Rp <?= $total ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="text-center py-16 text-text-3 text-sm">
                    Belum ada riwayat pesanan dalam periode ini.
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>
<!-- Modal Laporan Struk -->
<div id="modalLaporan" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-gray-100 p-4 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto flex flex-col items-center">
        <!-- Bentuk Excel / Spreadsheet -->
        <div id="strukContent" class="bg-white w-full p-6 text-sm font-sans text-gray-800 shadow-sm border border-gray-200">
            <div class="mb-5 flex flex-col gap-1 border-b border-gray-300 pb-3">
                <h3 class="font-extrabold text-2xl uppercase tracking-wider" style="color: #107c41;">Laporan Penjualan</h3>
                <p class="font-semibold text-gray-700 text-base">Toko: <?= htmlspecialchars($nama_toko) ?></p>
                <p class="text-sm text-gray-500">Periode: <?= str_replace('_', ' ', ucwords($filter_tanggal)) ?></p>
            </div>
            
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
        </div>
        
        <div class="flex gap-2 w-full mt-4">
            <button type="button" onclick="document.getElementById('modalLaporan').classList.add('hidden')" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition-all">Tutup</button>
            <button type="button" onclick="printStruk()" class="flex-1 text-white font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2" style="background-color: #107c41;" onmouseover="this.style.backgroundColor='#0c6132'" onmouseout="this.style.backgroundColor='#107c41'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print / Simpan PDF
            </button>
        </div>
    </div>
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