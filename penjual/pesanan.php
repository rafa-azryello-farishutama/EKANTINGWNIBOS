<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include "../config/koneksi.php";

if(!isset($_SESSION['penjual_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['penjual_id_users'];
$_SESSION['username'] = $_SESSION['penjual_username'];
$_SESSION['role']     = $_SESSION['penjual_role'];
$_SESSION['id_toko']   = $_SESSION['penjual_id_toko'];
$_SESSION['nama_toko'] = $_SESSION['penjual_nama_toko'];

// Tandai pesanan aktif telah dilihat oleh penjual
$db_ekantin->query("UPDATE pesanan SET dilihat_penjual = 1 WHERE id_toko='{$_SESSION['id_toko']}' AND status_pesanan IN ('pending', 'dikonfirmasi', 'diproses') AND dilihat_penjual = 0");

$id_toko = $_SESSION['id_toko'];

date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['update_status'])) {
    $id = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];
    $alasan_tolak = isset($_POST['alasan_tolak']) ? $db_ekantin->real_escape_string($_POST['alasan_tolak']) : '';

    $waktu_dikonfirmasi_query = "";
    if ($status_baru === 'dikonfirmasi') {
        $waktu_dikonfirmasi_query = ", waktu_dikonfirmasi = NOW()";
    }

    // Jika status final (riwayat), langsung tandai sudah dilihat agar tidak memunculkan notif merah
    $final_statuses = ['selesai', 'diambil', 'tidak_diambil', 'dibatalkan', 'tidak_diambil_lapor'];
    $dilihat_val = in_array($status_baru, $final_statuses) ? 1 : 0;
    $query_update = "UPDATE pesanan SET status_pesanan = '$status_baru', alasan_tolak = '$alasan_tolak', dilihat_penjual = $dilihat_val $waktu_dikonfirmasi_query WHERE id_pesanan = '$id'";
    $db_ekantin->query($query_update);

    // Update status bayar lunas untuk Cashless yang diverifikasi (masuk diproses)
    if ($status_baru === 'diproses') {
        $db_ekantin->query("UPDATE pembayaran SET status_bayar = 'lunas' WHERE id_pesanan = '$id' AND metode_bayar != 'cash' AND status_bayar = 'sudah_bayar'");
    }

    // Kembalikan stok jika pesanan ditolak
    if ($status_baru === 'dibatalkan') {
        $qDetail = $db_ekantin->query("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = '$id'");
        while ($det = $qDetail->fetch_assoc()) {
            $db_ekantin->query("UPDATE produk_kantin SET stok = stok + {$det['jumlah']}, diset_nol_oleh_penjual = 0 WHERE id_produk = '{$det['id_produk']}'");
        }
        
        // Refund poin & uang (jika cashless sudah dibayar)
        $qPoin = $db_ekantin->query("SELECT p.id_users, p.poin_digunakan, p.total_harga, pb.status_bayar, pb.metode_bayar FROM pesanan p LEFT JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan WHERE p.id_pesanan = '$id'");
        if ($qPoin && $qPoin->num_rows > 0) {
            $pData = $qPoin->fetch_assoc();
            $poin_dig = (int)$pData['poin_digunakan'];
            $total_harga = (int)$pData['total_harga'];
            $uid = $pData['id_users'];
            
            $pembeli_bohong = isset($_POST['pembeli_bohong']) && $_POST['pembeli_bohong'] == '1';
            
            // Refund penuh (poin awal + uang konversi poin)
            if (!$pembeli_bohong && $pData['metode_bayar'] !== 'cash' && in_array($pData['status_bayar'], ['sudah_bayar', 'lunas'])) {
                $poin_refund = $poin_dig + $total_harga;
                if ($poin_refund > 0) {
                    $db_ekantin->query("UPDATE users SET poin = poin + $poin_refund WHERE id_users = '$uid'");
                    $db_ekantin->query("UPDATE pesanan SET poin_digunakan = 0 WHERE id_pesanan = '$id'");
                }
            } else {
                // Refund normal (hanya poin awal)
                if ($poin_dig > 0) {
                    $db_ekantin->query("UPDATE users SET poin = poin + $poin_dig WHERE id_users = '$uid'");
                    $db_ekantin->query("UPDATE pesanan SET poin_digunakan = 0 WHERE id_pesanan = '$id'");
                }
            }
        }
    }
    
    // Jika Selesai/Diambil, beri poin ke user (berdasarkan kode unik)
    if ($status_baru === 'selesai' || $status_baru === 'diambil') {
        // Ambil ID User dan Kode Unik
        $qPesanan = $db_ekantin->query("SELECT id_users, kode_unik FROM pesanan WHERE id_pesanan = '$id'");
        if ($qPesanan && $qPesanan->num_rows > 0) {
            $pData = $qPesanan->fetch_assoc();
            $kode = (int)$pData['kode_unik'];
            if ($kode > 0) {
                $uid = $pData['id_users'];
                $db_ekantin->query("UPDATE users SET poin = poin + $kode WHERE id_users = '$uid'");
                // Hapus kode unik agar tidak di-double dan sesuaikan total pendapatan
                $db_ekantin->query("UPDATE pesanan SET kode_unik = 0, total_harga = GREATEST(0, total_harga - $kode) WHERE id_pesanan = '$id'");
            }
        }
        
        // Update status bayar lunas untuk Cash yang sudah diambil
        if ($status_baru === 'diambil') {
            $db_ekantin->query("UPDATE pembayaran SET status_bayar = 'lunas' WHERE id_pesanan = '$id' AND metode_bayar = 'cash'");
        }
    }
    
    // Logika Laporkan Pembeli (Jika Pesanan Tidak Diambil)
    if ($status_baru === 'tidak_diambil_lapor') {
        $status_baru = 'tidak_diambil';
        // Ambil ID User dan ID Penjual
        $qInfo = $db_ekantin->query("SELECT id_users FROM pesanan WHERE id_pesanan = '$id'");
        if ($qInfo && $qInfo->num_rows > 0) {
            $id_terlapor = $qInfo->fetch_assoc()['id_users'];
            $id_pelapor = $_SESSION['id_users'];
            $alasan = $alasan_tolak ?: "Pesanan tidak diambil oleh pembeli.";
            $db_ekantin->query("INSERT INTO laporan_pembeli (id_pelapor, id_terlapor, alasan) VALUES ('$id_pelapor', '$id_terlapor', '$alasan')");
        }
        
        // Ubah status jadi tidak_diambil (stok TIDAK dikembalikan karena makanan sudah dibuat)
        $db_ekantin->query("UPDATE pesanan SET status_pesanan = 'tidak_diambil', alasan_tolak = '$alasan_tolak' WHERE id_pesanan = '$id'");
    }

    header("Location: pesanan.php");
    exit;
}

$today = date('Y-m-d'); // Asia/Jakarta sudah di-set di atas

// --- AUTO CLEANUP PESANAN MENGGANTUNG & UPDATE ENUM ---
$db_ekantin->query("ALTER TABLE pesanan MODIFY COLUMN status_pesanan ENUM('pending','dikonfirmasi','diproses','selesai','dibatalkan','diambil','tidak_diambil') DEFAULT 'pending'");

$qToko = $db_ekantin->query("SELECT jam_tutup FROM toko WHERE id_toko='$id_toko'");
if ($qToko && $qToko->num_rows > 0) {
    $jam_tutup = $qToko->fetch_assoc()['jam_tutup'];
    $sekarang = date('H:i:s');
    
    // Cari pesanan yang batal karena timeout:
    // Hanya batalkan pesanan dikonfirmasi yang sudah lewat 10 menit tanpa transfer
    $qMenggantung = $db_ekantin->query("
        SELECT p.id_pesanan 
        FROM pesanan p
        JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan
        WHERE p.id_toko = '$id_toko' 
        AND p.status_pesanan = 'dikonfirmasi' 
        AND pb.status_bayar = 'menunggu_pembayaran' 
        AND p.waktu_dikonfirmasi < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    
    if($qMenggantung && $qMenggantung->num_rows > 0) {
        while ($pm = $qMenggantung->fetch_assoc()) {
            $id_batal = $pm['id_pesanan'];
            $db_ekantin->query("UPDATE pesanan SET status_pesanan = 'dibatalkan', alasan_tolak = 'Dibatalkan otomatis (Timeout)', sembunyikan_pembeli = 1, dilihat_penjual = 1 WHERE id_pesanan = '$id_batal'");
            
            $qDetail = $db_ekantin->query("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = '$id_batal'");
            while ($det = $qDetail->fetch_assoc()) {
                $db_ekantin->query("UPDATE produk_kantin SET stok = stok + {$det['jumlah']}, diset_nol_oleh_penjual = 0 WHERE id_produk = '{$det['id_produk']}'");
            }
            
            // Refund poin
            $qPoin = $db_ekantin->query("SELECT id_users, poin_digunakan FROM pesanan WHERE id_pesanan = '$id_batal'");
            if ($qPoin && $qPoin->num_rows > 0) {
                $pData = $qPoin->fetch_assoc();
                $poin_dig = (int)$pData['poin_digunakan'];
                if ($poin_dig > 0) {
                    $uid = $pData['id_users'];
                    $db_ekantin->query("UPDATE users SET poin = poin + $poin_dig WHERE id_users = '$uid'");
                    $db_ekantin->query("UPDATE pesanan SET poin_digunakan = 0 WHERE id_pesanan = '$id_batal'");
                }
            }
        }
    }
}
// --- END AUTO CLEANUP ---

// Pesanan yang masuk HARI INI saja
$qTotal = "SELECT * FROM pesanan WHERE id_toko = '$id_toko' AND DATE(tanggal_pesan) = '$today'";
$hasil = $db_ekantin->query($qTotal);
$jTotal = $hasil->num_rows;

// Semua pesanan yang masih PENDING (backlog keseluruhan)
$qPending = "SELECT * FROM pesanan WHERE status_pesanan = 'pending' AND id_toko = '$id_toko'";
$hTotal = $db_ekantin->query($qPending);
$pTotal = $hTotal->num_rows;

// Pesanan yang SELESAI HARI INI saja
$qSelesai = "SELECT * FROM pesanan WHERE status_pesanan = 'selesai' AND id_toko = '$id_toko' AND DATE(tanggal_pesan) = '$today'";
$hSelesai = $db_ekantin->query($qSelesai);
$sTotal = $hSelesai->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan</title>

    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8 overflow-x-hidden">

            <header class="mb-8">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Kelola Pesanan</h2>
                <p class="text-text-3 mt-1">Berikut seluruh status pemesanan</p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8">

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-blue-50 flex flex-col gap-2 group hover:-translate-y-1 hover:shadow-md hover:shadow-blue-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Hari Ini</p>
                        <p class="text-4xl font-extrabold text-blue-600"><?php echo $jTotal; ?></p>
                        <p class="text-xs text-text-2">total pesanan masuk</p>
                    </div>
                    <img src="../assets/img/user-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-yellow-50 flex flex-col gap-2 group hover:-translate-y-1 hover:shadow-md hover:shadow-yellow-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Pending</p>
                        <p class="text-4xl font-extrabold text-yellow-500"><?php echo $pTotal; ?></p>
                        <p class="text-xs text-text-2">menunggu diproses</p>
                    </div>
                    <img src="../assets/img/store-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-green-50 flex flex-col gap-2 group hover:-translate-y-1 hover:shadow-md hover:shadow-green-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Selesai Hari Ini</p>
                        <p class="text-4xl font-extrabold text-green-600"><?php echo $sTotal; ?></p>
                        <p class="text-xs text-text-2">pesanan selesai</p>
                    </div>
                    <img src="../assets/img/revenue-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

            </div>

            <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-1">
                <?php 
                $filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : 'hari_ini'; 
                $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'semua';
                ?>
                <form method="GET" id="form-filter" class="flex gap-2 w-full">
                    <!-- Hidden input untuk menyimpan filter_tanggal saat select berubah -->
                    <input type="hidden" name="filter_tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
                    
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
                    
                    <select name="filter_status" onchange="this.form.submit();" 
                            class="ml-auto px-4 py-2 rounded-full text-sm font-semibold border border-gray-200 text-text-2 bg-white hover:bg-gray-50 focus:outline-none focus:ring-0 focus:border-primary cursor-pointer transition-all">
                        <option value="semua" <?= $filter_status == 'semua' ? 'selected' : '' ?>>📋 Semua Status</option>
                        <option value="dikonfirmasi" <?= $filter_status == 'dikonfirmasi' ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="diproses" <?= $filter_status == 'diproses' ? 'selected' : '' ?>>🍳 Sedang Diproses</option>
                        <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>📦 Selesai Siap Ambil</option>
                        <option value="diambil" <?= $filter_status == 'diambil' ? 'selected' : '' ?>>✅ Sudah Diambil</option>
                        <option value="tidak_diambil" <?= $filter_status == 'tidak_diambil' ? 'selected' : '' ?>>⚠️ Tidak Diambil</option>
                        <option value="dibatalkan" <?= $filter_status == 'dibatalkan' ? 'selected' : '' ?>>❌ Dibatalkan</option>
                    </select>
                </form>
            </div>

            <div class="flex flex-col gap-3">
                <?php
                $id_toko = $_SESSION['id_toko'];
                $filter = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'semua';
                $where_status = ($filter !== 'semua') ? " AND p.status_pesanan = '$filter'" : "";
                
                $filter_tgl = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : 'hari_ini';
                $where_tanggal = match($filter_tgl) {
                    'hari_ini'   => " AND DATE(p.tanggal_pesan) = CURDATE()",
                    'minggu_ini' => " AND YEARWEEK(p.tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)",
                    'bulan_ini'  => " AND MONTH(p.tanggal_pesan) = MONTH(CURDATE()) AND YEAR(p.tanggal_pesan) = YEAR(CURDATE())",
                    'semua_waktu' => "",
                    default      => " AND DATE(p.tanggal_pesan) = CURDATE()", // fallback to hari_ini
                };

                $query = "SELECT p.*, u.username, pay.metode_bayar as metode_pembayaran, pay.bukti_bayar as bukti_pembayaran, pay.status_bayar 
              FROM pesanan p 
              JOIN users u ON p.id_users = u.id_users 
              LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
              WHERE p.id_toko = '$id_toko' $where_status $where_tanggal
              ORDER BY FIELD(p.status_pesanan, 'pending', 'dikonfirmasi', 'diproses', 'selesai', 'diambil', 'tidak_diambil', 'dibatalkan'), p.tanggal_pesan DESC";

                $pesanan = $db_ekantin->query($query);

                if ($pesanan && $pesanan->num_rows > 0) {
                    while ($row = $pesanan->fetch_assoc()) {
                        $waktu_pesan = strtotime($row['tanggal_pesan']);
                        $selisih_detik = time() - $waktu_pesan;

                        if ($selisih_detik < 0)
                            $selisih_detik = 0;

                        $menit = floor($selisih_detik / 60);

                        if ($menit < 1) {
                            $waktu_lalu = "Baru saja";
                        } elseif ($menit < 60) {
                            $waktu_lalu = $menit . " menit yang lalu";
                        } else {
                            $jam = floor($menit / 60);
                            $waktu_lalu = $jam . " jam yang lalu";
                        }

                        $tulisanTanggal = date('d M Y, H:i', $waktu_pesan);

                        $id_pesanan = $row['id_pesanan'];
                        $status = $row['status_pesanan'];

    
                        $badgeClass = match ($status) {
                            'pending' => 'text-yellow-700 bg-yellow-100 border border-yellow-200',
                            'dikonfirmasi' => 'text-indigo-700 bg-indigo-100 border border-indigo-200',
                            'diproses' => 'text-blue-700 bg-blue-100 border border-blue-200',
                            'selesai' => 'text-green-700 bg-green-100 border border-green-200',
                            'dibatalkan' => 'text-red-600 bg-red-100 border border-red-200',
                            default => 'text-gray-600 bg-gray-100 border border-gray-200'
                        };

                        $badgePill = match ($status) {
                            'pending' => 'text-yellow-700 bg-yellow-100 border border-yellow-200',
                            'dikonfirmasi' => 'text-yellow-700 bg-yellow-100 border border-yellow-200',
                            'diproses' => 'text-blue-700 bg-blue-100 border border-blue-200',
                            'selesai' => 'text-green-700 bg-green-100 border border-green-200',
                            'diambil' => 'text-teal-700 bg-teal-100 border border-teal-200',
                            'tidak_diambil' => 'text-orange-700 bg-orange-100 border border-orange-200',
                            'dibatalkan' => 'text-red-600 bg-red-100 border border-red-200',
                            default => 'text-gray-600 bg-gray-100 border border-gray-200'
                        };

                        $cardBorderClass = match ($status) {
                            'pending' => 'border-2 border-yellow-400 hover:border-yellow-500',
                            'dikonfirmasi' => 'border-2 border-yellow-400 hover:border-yellow-500',
                            'diproses' => 'border-blue-100 hover:border-blue-200',
                            'selesai' => 'border-green-100 hover:border-green-200',
                            'diambil' => 'border-teal-100 hover:border-teal-200',
                            'tidak_diambil' => 'border-orange-100 hover:border-orange-200',
                            'dibatalkan' => 'border-red-100 hover:border-red-200',
                            default => 'border-gray-100 hover:border-primary/30'
                        };
                        if ($status == 'pending') {
                            $tombolAksi = "<span class='text-xs font-bold bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl'>⏳ Pending</span>";
                        } else if ($status == 'dikonfirmasi') {
                            $tombolAksi = "<span class='text-xs font-bold bg-indigo-100 text-indigo-700 px-4 py-2 rounded-xl'>⌛ Menunggu Pembayaran/Proses</span>";
                        } else if ($status == 'diproses') {
                            $tombolAksi = "<span class='text-xs font-bold bg-blue-100 text-blue-700 px-4 py-2 rounded-xl'>🔄 Diproses</span>";
                        } else {
                            $tombolAksi = "";
                        }

                        $username = htmlspecialchars($row['username'], ENT_QUOTES);
                        $harga = "Rp " . number_format($row['total_harga'], 0, ',', '.');
                        $catatan = htmlspecialchars($row['catatan'] ?? '-', ENT_QUOTES);
                        $metode_pembayaran = htmlspecialchars($row['metode_pembayaran'] ?? 'transfer', ENT_QUOTES);
                        $bukti_pembayaran = htmlspecialchars($row['bukti_pembayaran'] ?? '', ENT_QUOTES);
                        $status_bayar = htmlspecialchars($row['status_bayar'] ?? 'belum_bayar', ENT_QUOTES);
                        
                        $tgl_pesan = date('Ymd', strtotime($row['tanggal_pesan']));
                        $id_show = $row['id_harian'] ? sprintf("%03d", $row['id_harian']) : sprintf("%04d", $row['id_pesanan']);
                        $display_id = "#ORD-" . $tgl_pesan . "-" . $id_show;
                        $poin_digunakan = (int)($row['poin_digunakan'] ?? 0);
                        
                        $indikatorTransfer = '';
                        if ($status_bayar === 'sudah_bayar') {
                            $indikatorTransfer = "<span class='text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full mb-1 inline-block'>💰 Sudah Transfer</span>";
                        }
                        if ($poin_digunakan > 0) {
                            $indikatorTransfer .= " <span class='text-[10px] font-bold bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full mb-1 inline-block ml-1'>🎁 Pakai $poin_digunakan Poin</span>";
                        }

                        $qMenu = $db_ekantin->query("SELECT dp.jumlah, pk.nama_menu FROM detail_pesanan dp JOIN produk_kantin pk ON dp.id_produk = pk.id_produk WHERE dp.id_pesanan = '$id_pesanan'");
                        $listMenu = [];
                        while ($m = $qMenu->fetch_assoc()) {
                            $listMenu[] = $m['nama_menu'] . " x" . $m['jumlah'];
                        }
                        $tampilMenu = implode(", ", $listMenu);

                        $display_status = ($status == 'dikonfirmasi') ? 'pending' : $status;
                        if ($display_status == 'selesai') {
                            $display_status_str = 'siap diambil';
                        } else {
                            $display_status_str = str_replace('_', ' ', $display_status);
                        }

                        echo "
            <div onclick=\"bukaPopup('$username', '$tulisanTanggal', '$status', '$tampilMenu', '$catatan', '$harga','$id_pesanan', '$metode_pembayaran', '$bukti_pembayaran', '$status_bayar', '$display_id', '$poin_digunakan')\"
                class='bg-white rounded-[24px] p-6 shadow-sm border $cardBorderClass cursor-pointer transition-all mb-2 hover:-translate-y-0.5 hover:shadow-md'>
                
                <div class='flex justify-between items-start mb-2'>
                    <div>
                        $indikatorTransfer
                        <p class='text-base md:text-lg font-bold text-text-1 mt-1'>$display_id</p>
                        <p class='text-xs text-text-3 mt-1'>$tulisanTanggal</p>
                    </div>
                    <div class='flex flex-col items-end gap-1.5'>
                        <span class='text-[11px] md:text-xs font-bold $badgePill px-3 py-1 md:px-4 md:py-1.5 rounded-full capitalize'>$display_status_str</span>
                        <p class='text-[11px] font-semibold px-2 py-0.5 bg-gray-100 rounded-md text-text-2 text-right'>$username</p>
                    </div>
                </div>

                <p class='text-sm text-text-2 mb-4'>$tampilMenu</p>

                <div class='flex justify-between items-center'>
                    <p class='text-xl font-extrabold text-text-1'>$harga</p>
                    $tombolAksi
                </div>
            </div>";
                    }
                } else {
                    echo "<div class='text-center py-10 text-text-3'>Belum ada pesanan masuk.</div>";
                }
                ?>
            </div>

        </main>
    </div>

    <div id="overlay-popup" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        onclick="tutupPopup()">
        <div class="bg-white rounded-[24px] w-full max-w-sm shadow-[0_20px_50px_-12px_rgba(0,73,0,0.15)] overflow-hidden max-h-[90vh] flex flex-col"
            onclick="event.stopPropagation()">

            <div class="bg-gradient-to-r from-primary to-[#006800] px-5 py-4 flex items-center justify-between flex-shrink-0">
                <div>
                    <p class="text-white/60 text-[10px] uppercase tracking-widest mb-0.5">Detail Pesanan</p>
                    <h2 id="popup-nama" class="text-white font-bold text-lg leading-tight">-</h2>
                </div>
                <button onclick="tutupPopup()"
                    class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-all focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-5 py-5 flex flex-col gap-4 overflow-y-auto">

                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Waktu Pesan</p>
                        <p id="popup-waktu" class="text-sm font-medium text-text-1 mt-1">-</p>
                    </div>
                    <div id="popup-badge"></div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3 mb-2">Item Pesanan</p>
                    <p id="popup-items" class="text-sm text-text-2 bg-input rounded-[12px] px-4 py-3">-</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3 mb-1">Catatan</p>
                    <p id="popup-catatan" class="text-sm text-text-2 italic">-</p>
                </div>

                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Pembayaran</p>
                        <p id="popup-metode" class="text-xs font-bold text-primary capitalize mt-0.5">-</p>
                    </div>
                    <div id="popup-bukti-wrapper" class="flex gap-2">
                        <a id="popup-bukti-link" href="#" target="_blank" class="text-[11px] font-bold bg-primary text-white hover:bg-submit px-3 py-1.5 rounded-lg transition-all shadow-sm">
                            Lihat Bukti Bayar
                        </a>
                        <!-- Struk hanya ditampilkan saat selesai via JS -->
                        <a id="popup-struk-link" href="#" target="_blank" class="hidden text-[11px] font-bold bg-blue-600 text-white hover:bg-blue-700 px-3 py-1.5 rounded-lg transition-all shadow-sm">
                            📄 Struk
                        </a>
                    </div>
                </div>

                <div class="flex justify-between items-center border-t border-gray-100 pt-4">
                    <p class="text-sm text-text-3">Total Harga</p>
                    <p id="popup-total" class="text-lg font-bold text-text-1">-</p>
                </div>

                <div id="popup-aksi"></div>

            </div>
        </div>
    </div>

    <script>

        function bukaPopup(nama, waktu, status, items, catatan, total, id_pesanan, metode_bayar, bukti_bayar, status_bayar, display_id, poin_digunakan) {
            document.getElementById('popup-nama').textContent = display_id + " (" + nama + ")";
            document.getElementById('popup-waktu').textContent = waktu;
            document.getElementById('popup-items').textContent = items;
            document.getElementById('popup-catatan').textContent = catatan;
            
            let totalHtml = total;
            if (parseInt(poin_digunakan) > 0) {
                totalHtml += `<br><span class="text-xs text-orange-600 font-semibold mt-1">🎁 Poin digunakan: ${poin_digunakan} Poin</span>`;
            }
            document.getElementById('popup-total').innerHTML = totalHtml;

            let metodeText = '🏦 Transfer Bank';
            if (metode_bayar === 'qr') metodeText = '📱 QRIS / QR Code';
            if (metode_bayar === 'cash') metodeText = '💵 Tunai (Bayar di Tempat)';
            
            document.getElementById('popup-metode').textContent = metodeText;

            const linkEl = document.getElementById('popup-bukti-link');
            if (bukti_bayar) {
                linkEl.href = "../assets/uploads_bukti/" + bukti_bayar;
                linkEl.style.display = 'inline-block';
            } else {
                linkEl.style.display = 'none';
            }

            document.getElementById('popup-struk-link').href = "../apps/struk.php?id_pesanan=" + id_pesanan;

            let isWaitingPayment = (metode_bayar !== 'cash' && status_bayar === 'menunggu_pembayaran');
            let btnProses = isWaitingPayment 
                ? `<button type="button" disabled style="background:#f3f4f6;" class="w-full h-[46px] rounded-[12px] text-gray-400 border border-gray-200 text-sm font-bold cursor-not-allowed transition-all">⏳ Menunggu Pembayaran</button>`
                : `<button type="submit" id="btn-proses" style="background:#2563eb;" class="w-full h-[46px] rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">Proses Pesanan</button>`;

            let notifTransfer = '';
            if (status_bayar === 'sudah_bayar') {
                notifTransfer = `<div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-center shadow-sm">
                    <p class="text-sm font-bold text-green-700">✅ Pembeli Telah Konfirmasi Transfer</p>
                    <p class="text-[11px] text-green-600 mt-1">Silakan cek mutasi rekening/e-wallet Anda sebelum memproses.</p>
                </div>`;
            }

            const btnProsesFinal = notifTransfer + btnProses;
            let statusKonfirmasiValue = metode_bayar === 'cash' ? 'diproses' : 'dikonfirmasi';
            let btnKonfirmasiText = metode_bayar === 'cash' ? 'Terima & Langsung Proses' : 'Konfirmasi Pesanan';

            let checkboxBohong = '';
            if (metode_bayar !== 'cash') {
                checkboxBohong = `
                <label class="flex items-center gap-2 mt-2 cursor-pointer bg-red-50/50 p-2 rounded-lg border border-red-100">
                    <input type="checkbox" name="pembeli_bohong" value="1" class="w-4 h-4 rounded text-red-500 focus:ring-red-500 border-red-300">
                    <span class="text-[11px] text-red-600 font-semibold leading-tight">Pembeli tidak transfer<br><span class="font-normal opacity-80 text-[10px]">Tolak tanpa mengembalikan total harga ke poin.</span></span>
                </label>`;
            }

            const aksiMap = {
                pending: `
        <form method="POST" class="flex flex-col gap-2.5" id="form-pesanan">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="status_baru" id="status-baru-input" value="${statusKonfirmasiValue}">
            
            <div id="alasan-tolak-section" class="hidden flex flex-col gap-1.5 border-t pt-3">
                <label class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Alasan Penolakan</label>
                <textarea name="alasan_tolak" id="alasan-tolak-input" rows="2" placeholder="Sebutkan alasan penolakan..." class="w-full border-gray-200 bg-input focus:bg-white focus:ring-primary focus:border-primary text-sm rounded-xl resize-none"></textarea>
                ${checkboxBohong}
            </div>

            <button type="submit" id="btn-konfirmasi" style="background:#2563eb;" class="w-full h-[46px] rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">${btnKonfirmasiText}</button>
            
            <button type="button" id="btn-tolak-init" onclick="showTolakSection()" class="w-full h-[46px] bg-red-50 text-red-600 border border-red-200 rounded-[12px] text-sm font-bold hover:bg-red-100 transition-all">
                Tolak Pesanan
            </button>
            <button type="submit" id="btn-tolak-submit" onclick="return setStatusBatal()" class="hidden w-full h-[46px] bg-red-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Kirim & Tolak Pesanan
            </button>
            <button type="button" id="btn-batal-tolak" onclick="hideTolakSection()" class="hidden w-full h-[36px] text-text-3 text-xs font-semibold hover:underline">
                Kembali
            </button>
        </form>`,
                dikonfirmasi: `
        <form method="POST" class="flex flex-col gap-2.5" id="form-pesanan">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="status_baru" id="status-baru-input" value="diproses">
            
            <div id="alasan-tolak-section" class="hidden flex flex-col gap-1.5 border-t pt-3">
                <label class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Alasan Penolakan</label>
                <textarea name="alasan_tolak" id="alasan-tolak-input" rows="2" placeholder="Sebutkan alasan penolakan..." class="w-full border-gray-200 bg-input focus:bg-white focus:ring-primary focus:border-primary text-sm rounded-xl resize-none"></textarea>
                ${checkboxBohong}
            </div>

            ${btnProsesFinal}
            
            <button type="button" id="btn-tolak-init" onclick="showTolakSection()" class="w-full h-[46px] bg-red-50 text-red-600 border border-red-200 rounded-[12px] text-sm font-bold hover:bg-red-100 transition-all">
                Tolak Pesanan
            </button>
            <button type="submit" id="btn-tolak-submit" onclick="return setStatusBatal()" class="hidden w-full h-[46px] bg-red-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Kirim & Tolak Pesanan
            </button>
            <button type="button" id="btn-batal-tolak" onclick="hideTolakSection()" class="hidden w-full h-[36px] text-text-3 text-xs font-semibold hover:underline">
                Kembali
            </button>
        </form>`,
                diproses: `
        <form method="POST">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="status_baru" value="selesai">
            <button type="submit" name="update_status" style="background:#16a34a;" class="w-full h-[46px] rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Tandai Siap Diambil (Selesai)
            </button>
        </form>`,
                selesai: `
        <form method="POST" class="flex flex-col gap-2.5" id="form-pesanan">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="status_baru" id="status-baru-input" value="diambil">

            <div id="alasan-tolak-section" class="hidden flex flex-col gap-1.5 border-t pt-3">
                <label class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Alasan Laporan</label>
                <textarea name="alasan_tolak" id="alasan-tolak-input" rows="2" placeholder="Jelaskan alasan (Misal: Pembeli tidak datang mengambil)..." class="w-full border-gray-200 bg-input focus:bg-white focus:ring-primary focus:border-primary text-sm rounded-xl resize-none"></textarea>
            </div>

            <button type="submit" id="btn-proses" onclick="document.getElementById('status-baru-input').value='diambil';" style="background:#0d9488;" class="w-full h-[46px] rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Sudah Diambil
            </button>
            
            <button type="button" id="btn-tolak-init" onclick="showTolakSection()" class="w-full h-[46px] bg-orange-50 text-orange-600 border border-orange-200 rounded-[12px] text-sm font-bold hover:bg-orange-100 transition-all">
                Tidak Diambil & Laporkan
            </button>
            <button type="submit" id="btn-tolak-submit" onclick="return setStatusBatalLapor()" class="hidden w-full h-[46px] bg-red-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Kirim Laporan & Tandai Tidak Diambil
            </button>
            <button type="button" id="btn-batal-tolak" onclick="hideTolakSection()" class="hidden w-full h-[36px] text-text-3 text-xs font-semibold hover:underline">
                Kembali
            </button>
        </form>`,
                diambil: `<div class="py-2 text-center text-teal-700 font-bold bg-teal-50 rounded-xl">✅ Pesanan Selesai & Diambil</div>`,
                tidak_diambil: `<div class="py-2 text-center text-orange-700 font-bold bg-orange-50 rounded-xl">⚠️ Pesanan Tidak Diambil Pembeli</div>`
            };

            document.getElementById('popup-aksi').innerHTML = aksiMap[status] || '';

            // Struk hanya tampil jika status diproses atau selesai
            const strukEl = document.getElementById('popup-struk-link');
            if (status === 'diproses' || status === 'selesai') {
                strukEl.href = "../apps/struk.php?id_pesanan=" + id_pesanan;
                strukEl.classList.remove('hidden');
            } else {
                strukEl.classList.add('hidden');
            }

            document.getElementById('overlay-popup').classList.remove('hidden');
        }

        function tutupPopup() {
            document.getElementById('overlay-popup').classList.add('hidden');
        }

        function showTolakSection() {
            document.getElementById('alasan-tolak-section').classList.remove('hidden');
            // Sembunyikan semua tombol aksi utama (bisa btn-proses atau btn-konfirmasi)
            const btnProses = document.getElementById('btn-proses');
            if (btnProses) btnProses.classList.add('hidden');
            const btnKonfirmasi = document.getElementById('btn-konfirmasi');
            if (btnKonfirmasi) btnKonfirmasi.classList.add('hidden');
            document.getElementById('btn-tolak-init').classList.add('hidden');
            document.getElementById('btn-tolak-submit').classList.remove('hidden');
            document.getElementById('btn-batal-tolak').classList.remove('hidden');
            document.getElementById('alasan-tolak-input').setAttribute('required', 'true');
            // Simpan nilai status asli sebelum diganti ke dibatalkan
            const inp = document.getElementById('status-baru-input');
            inp.dataset.original = inp.value;
            inp.value = 'dibatalkan';
        }

        function hideTolakSection() {
            document.getElementById('alasan-tolak-section').classList.add('hidden');
            // Tampilkan kembali tombol aksi utama
            const btnProses = document.getElementById('btn-proses');
            if (btnProses) btnProses.classList.remove('hidden');
            const btnKonfirmasi = document.getElementById('btn-konfirmasi');
            if (btnKonfirmasi) btnKonfirmasi.classList.remove('hidden');
            document.getElementById('btn-tolak-init').classList.remove('hidden');
            document.getElementById('btn-tolak-submit').classList.add('hidden');
            document.getElementById('btn-batal-tolak').classList.add('hidden');
            document.getElementById('alasan-tolak-input').removeAttribute('required');
            // Kembalikan status ke nilai asli sebelum tolak dibuka
            const inp = document.getElementById('status-baru-input');
            inp.value = inp.dataset.original || inp.value;
        }

        function setStatusBatal() {
            const input = document.getElementById('alasan-tolak-input');
            if (!input.value.trim()) {
                alert('Harap masukkan alasan penolakan.');
                return false;
            }
            document.getElementById('status-baru-input').value = 'dibatalkan';
            return true;
        }

        function setStatusBatalLapor() {
            const input = document.getElementById('alasan-tolak-input');
            if (!input.value.trim()) {
                alert('Harap masukkan alasan pelaporan.');
                return false;
            }
            document.getElementById('status-baru-input').value = 'tidak_diambil_lapor';
            return true;
        }
    </script>

</body>

</html>