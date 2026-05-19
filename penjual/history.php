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

$kondisi_status = "p.status_pesanan IN ('selesai','dibatalkan')";
if($filter_status == 'selesai') $kondisi_status = "p.status_pesanan = 'selesai'";
if($filter_status == 'dibatalkan') $kondisi_status = "p.status_pesanan = 'dibatalkan'";

$kondisi_tanggal = "DATE(p.tanggal_pesan) = CURDATE()";
if($filter_tanggal == 'minggu_ini') $kondisi_tanggal = "YEARWEEK(p.tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)";
if($filter_tanggal == 'bulan_ini') $kondisi_tanggal = "MONTH(p.tanggal_pesan) = MONTH(CURDATE()) AND YEAR(p.tanggal_pesan) = YEAR(CURDATE())";
if($filter_tanggal == 'semua_waktu') $kondisi_tanggal = "1=1";

$qTotal = $db_ekantin->query("SELECT COUNT(*) as total, SUM(total_harga) as pendapatan 
    FROM pesanan p 
    WHERE p.id_toko='$id_toko' AND p.status_pesanan='selesai' AND $kondisi_tanggal");
$dataTotal = $qTotal->fetch_assoc();
$total_pesanan   = $dataTotal['total'] ?? 0;
$total_pendapatan = number_format($dataTotal['pendapatan'] ?? 0, 0, ',', '.');

$qRiwayat = $db_ekantin->query("SELECT p.*, u.username
    FROM pesanan p
    JOIN users u ON p.id_users = u.id_users
    WHERE p.id_toko='$id_toko' AND $kondisi_status AND $kondisi_tanggal
    ORDER BY p.tanggal_pesan DESC");
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

            <header>
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Riwayat Pesanan</h2>
                <p class="text-text-3 mt-1 text-sm">Rekap seluruh pesanan yang sudah selesai atau dibatalkan</p>
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

                    $badge = $ps['status_pesanan'] == 'selesai'
                        ? "<span class='text-xs font-semibold text-green-700 bg-green-100 border border-green-200 px-3 py-1 rounded-full'>Selesai</span>"
                        : "<span class='text-xs font-semibold text-red-600 bg-red-100 border border-red-200 px-3 py-1 rounded-full'>Dibatalkan</span>";
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
</body>
</html>