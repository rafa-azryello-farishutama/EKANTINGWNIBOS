<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if (!isset($_SESSION['pembeli_id_users'])) {
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];

$id_users = (int) $_SESSION['id_users'];

// Ambil flash message
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null;
$pesan_error  = $_SESSION['pesan_error']  ?? null;
unset($_SESSION['pesan_sukses'], $_SESSION['pesan_error']);

// Jika ada ?id=X, tampilkan halaman konfirmasi pesanan itu
$id_pesanan_fokus = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pesanan_fokus    = null;
$detail_fokus     = [];

if ($id_pesanan_fokus) {
    $qFokus = $db_ekantin->prepare("
        SELECT p.*, t.nama_toko, t.qris_image, t.info_bank, t.info_ewallet,
               pb.metode_bayar, pb.status_bayar, pb.bukti_bayar
        FROM pesanan p
        JOIN toko t ON p.id_toko = t.id_toko
        LEFT JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan
        WHERE p.id_pesanan = ? AND p.id_users = ?
    ");
    $qFokus->bind_param("ii", $id_pesanan_fokus, $id_users);
    $qFokus->execute();
    $pesanan_fokus = $qFokus->get_result()->fetch_assoc();

    if ($pesanan_fokus) {
        $qDetail = $db_ekantin->prepare("
            SELECT dp.*, pk.nama_menu
            FROM detail_pesanan dp
            JOIN produk_kantin pk ON dp.id_produk = pk.id_produk
            WHERE dp.id_pesanan = ?
        ");
        $qDetail->bind_param("i", $id_pesanan_fokus);
        $qDetail->execute();
        $detail_fokus = $qDetail->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Otomatis anggap lunas jika pesanan sudah diterima/diproses penjual
        if ($pesanan_fokus['status_bayar'] === 'sudah_bayar' && in_array($pesanan_fokus['status_pesanan'], ['diproses', 'selesai', 'diambil'])) {
            $pesanan_fokus['status_bayar'] = 'lunas';
        }
    }
}

// Handle konfirmasi transfer (menggantikan upload bukti)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_transfer'])) {
    $id_konfirmasi = (int) ($_POST['id_pesanan'] ?? 0);
    if ($id_konfirmasi) {
        // Cek status pesanan saat ini
        $stmtCek = $db_ekantin->prepare("SELECT status_pesanan, (waktu_dikonfirmasi < DATE_SUB(NOW(), INTERVAL 10 MINUTE)) as is_expired FROM pesanan WHERE id_pesanan = ? AND id_users = ?");
        $stmtCek->bind_param("ii", $id_konfirmasi, $id_users);
        $stmtCek->execute();
        $resCek = $stmtCek->get_result();
        
        if ($resCek->num_rows > 0) {
            $pesanan_saat_ini = $resCek->fetch_assoc();
            // Hanya izinkan konfirmasi transfer jika statusnya sudah dikonfirmasi
            if ($pesanan_saat_ini['status_pesanan'] === 'dikonfirmasi') {
                if ($pesanan_saat_ini['is_expired']) {
                    // Batalkan otomatis
                    $db_ekantin->query("UPDATE pesanan SET status_pesanan = 'dibatalkan', alasan_tolak = 'Dibatalkan otomatis (Timeout)' WHERE id_pesanan = '$id_konfirmasi'");
                    
                    $qDet = $db_ekantin->query("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = '$id_konfirmasi'");
                    while ($det = $qDet->fetch_assoc()) {
                        $db_ekantin->query("UPDATE produk_kantin SET stok = stok + {$det['jumlah']} WHERE id_produk = '{$det['id_produk']}'");
                    }
                    
                    $qPoinAuto = $db_ekantin->query("SELECT poin_digunakan FROM pesanan WHERE id_pesanan = '$id_konfirmasi'");
                    if ($qPoinAuto && $qPoinAuto->num_rows > 0) {
                        $poin_dig_auto = (int)$qPoinAuto->fetch_assoc()['poin_digunakan'];
                        if ($poin_dig_auto > 0) {
                            $db_ekantin->query("UPDATE users SET poin = poin + $poin_dig_auto WHERE id_users = '$id_users'");
                            $db_ekantin->query("UPDATE pesanan SET poin_digunakan = 0 WHERE id_pesanan = '$id_konfirmasi'");
                        }
                    }
                    $_SESSION['pesan_error'] = "Waktu transfer (10 menit) telah habis. Pesanan otomatis dibatalkan.";
                } else {
                    $stmt = $db_ekantin->prepare("UPDATE pembayaran SET status_bayar = 'sudah_bayar' WHERE id_pesanan = ?");
                    $stmt->bind_param("i", $id_konfirmasi);
                    if ($stmt->execute()) {
                        $_SESSION['pesan_sukses'] = "Konfirmasi transfer terkirim! Menunggu penjual mengecek mutasi.";
                    } else {
                        $_SESSION['pesan_error']  = "Gagal mengonfirmasi transfer.";
                    }
                }
            } else {
                $_SESSION['pesan_error'] = "Tidak dapat mentransfer. Pesanan belum dikonfirmasi penjual.";
            }
        }
    }
    header("Location: pesanan.php");
    exit;
}
// Handle batalkan pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batalkan_pesanan'])) {
    $id_batal = (int) ($_POST['id_pesanan'] ?? 0);
    $qCek = $db_ekantin->prepare("SELECT status_pesanan FROM pesanan WHERE id_pesanan = ? AND id_users = ?");
    $qCek->bind_param("ii", $id_batal, $id_users);
    $qCek->execute();
    $resCek = $qCek->get_result();
    $dataCek = $resCek->fetch_assoc();

    // Pembeli hanya boleh batalkan saat status pending atau dikonfirmasi (belum dimasak)
    $bisa_batal = $dataCek && in_array($dataCek['status_pesanan'], ['pending', 'dikonfirmasi']);

    if ($bisa_batal) {
        // Ubah status menjadi dibatalkan
        $db_ekantin->query("UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE id_pesanan = '$id_batal'");

        // Kembalikan stok produk
        $qDet = $db_ekantin->prepare("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = ?");
        $qDet->bind_param("i", $id_batal);
        $qDet->execute();
        $resDet = $qDet->get_result();
        $stmtStok = $db_ekantin->prepare("UPDATE produk_kantin SET stok = stok + ? WHERE id_produk = ?");
        while ($det = $resDet->fetch_assoc()) {
            $stmtStok->bind_param("ii", $det['jumlah'], $det['id_produk']);
            $stmtStok->execute();
        }

        // Refund poin — kembalikan poin yang dipakai saat pesanan ini dibuat
        $qPoin = $db_ekantin->query("SELECT poin_digunakan FROM pesanan WHERE id_pesanan = '$id_batal'");
        if ($qPoin && $qPoin->num_rows > 0) {
            $poin_dig = (int)$qPoin->fetch_assoc()['poin_digunakan'];
            if ($poin_dig > 0) {
                $db_ekantin->query("UPDATE users SET poin = poin + $poin_dig WHERE id_users = '$id_users'");
                $db_ekantin->query("UPDATE pesanan SET poin_digunakan = 0 WHERE id_pesanan = '$id_batal'");
            }
        }

        $pesan_sukses = "Pesanan berhasil dibatalkan.";
    } else {
        $pesan_error = "Pesanan tidak dapat dibatalkan karena sudah mulai diproses.";
    }
    header("Location: pesanan.php");
    exit;
}

// --- AUTO CLEANUP PESANAN MENGGANTUNG (PEMBELI) ---
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$sekarang = date('H:i:s');
$jam_tutup = '23:59:00'; 

$qMenggantung = $db_ekantin->query("
    SELECT p.id_pesanan 
    FROM pesanan p
    JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan
    WHERE p.id_users = '$id_users' 
    AND (
        (p.status_pesanan IN ('pending', 'diproses') AND pb.metode_bayar = 'cash' AND (DATE(p.tanggal_pesan) < '$today' OR (DATE(p.tanggal_pesan) = '$today' AND '$sekarang' > '$jam_tutup')))
        OR 
        (p.status_pesanan = 'dikonfirmasi' AND p.waktu_dikonfirmasi < DATE_SUB(NOW(), INTERVAL 10 MINUTE))
    )
");

if($qMenggantung && $qMenggantung->num_rows > 0) {
    while ($pm = $qMenggantung->fetch_assoc()) {
        $id_batal_auto = $pm['id_pesanan'];
        $db_ekantin->query("UPDATE pesanan SET status_pesanan = 'dibatalkan', alasan_tolak = 'Dibatalkan otomatis (Timeout)' WHERE id_pesanan = '$id_batal_auto'");
        
        $qDetailAuto = $db_ekantin->query("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = '$id_batal_auto'");
        while ($det = $qDetailAuto->fetch_assoc()) {
            $db_ekantin->query("UPDATE produk_kantin SET stok = stok + {$det['jumlah']} WHERE id_produk = '{$det['id_produk']}'");
        }
        
        // Refund poin
        $qPoinAuto = $db_ekantin->query("SELECT id_users, poin_digunakan FROM pesanan WHERE id_pesanan = '$id_batal_auto'");
        if ($qPoinAuto && $qPoinAuto->num_rows > 0) {
            $pDataAuto = $qPoinAuto->fetch_assoc();
            $poin_dig_auto = (int)$pDataAuto['poin_digunakan'];
            if ($poin_dig_auto > 0) {
                $uid_auto = $pDataAuto['id_users'];
                $db_ekantin->query("UPDATE users SET poin = poin + $poin_dig_auto WHERE id_users = '$uid_auto'");
                $db_ekantin->query("UPDATE pesanan SET poin_digunakan = 0 WHERE id_pesanan = '$id_batal_auto'");
            }
        }
    }
}
// --- END AUTO CLEANUP ---

// Ambil semua pesanan aktif (bukan selesai/dibatalkan)
$qAktif = $db_ekantin->query("
    SELECT p.*, t.nama_toko, t.qris_image, t.info_bank, t.info_ewallet,
           pb.metode_bayar, pb.status_bayar, pb.bukti_bayar
    FROM pesanan p
    JOIN toko t ON p.id_toko = t.id_toko
    LEFT JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan
    WHERE p.id_users = '$id_users'
      AND p.status_pesanan NOT IN ('diambil','tidak_diambil','dibatalkan')
    ORDER BY p.tanggal_pesan DESC
");
$pesanan_aktif = $qAktif ? $qAktif->fetch_all(MYSQLI_ASSOC) : [];
foreach ($pesanan_aktif as &$pa) {
    if ($pa['status_bayar'] === 'sudah_bayar' && in_array($pa['status_pesanan'], ['diproses', 'selesai', 'diambil'])) {
        $pa['status_bayar'] = 'lunas';
    }
}
unset($pa);

// Helper label status
function labelStatus($s) {
    $map = [
        'pending'   => ['label' => 'Menunggu Konfirmasi', 'bg' => 'bg-yellow-100 text-yellow-700'],
        'diproses'  => ['label' => 'Sedang Diproses',    'bg' => 'bg-blue-100 text-blue-700'],
        'selesai'   => ['label' => 'Siap Diambil',       'bg' => 'bg-green-100 text-green-700'],
    ];
    return $map[$s] ?? ['label' => ucfirst($s), 'bg' => 'bg-gray-100 text-gray-600'];
}
function labelBayar($s) {
    $map = [
        'menunggu_pembayaran' => ['label' => 'Belum Bayar',       'bg' => 'bg-red-100 text-red-600'],
        'sudah_bayar'         => ['label' => 'Menunggu Verifikasi','bg' => 'bg-orange-100 text-orange-700'],
        'lunas'               => ['label' => 'Lunas',              'bg' => 'bg-green-100 text-green-700'],
        'belum_bayar'         => ['label' => 'Bayar di Tempat',   'bg' => 'bg-gray-100 text-gray-600'],
    ];
    return $map[$s] ?? ['label' => ucfirst($s), 'bg' => 'bg-gray-100 text-gray-600'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya – E-Kantin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .card-enter { animation: cardIn .3s cubic-bezier(.4,0,.2,1) both; }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .qris-glow { box-shadow: 0 0 0 4px rgba(22,163,74,.15), 0 8px 32px rgba(0,0,0,.10); }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-10 pt-[72px] lg:pt-8">
        <div class="w-full max-w-2xl mx-auto flex flex-col gap-6">

            <header>
                <h2 class="font-extrabold text-3xl tracking-tight text-primary">Pesanan Saya</h2>
                <p class="text-text-3 mt-1 text-sm">Pantau status & selesaikan pembayaran pesanan aktif Anda</p>
            </header>

            <?php if ($pesan_sukses): ?>
            <div class="px-4 py-3 bg-green-50 border border-green-100 rounded-xl text-sm text-green-700 font-medium flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <?= htmlspecialchars($pesan_sukses) ?>
            </div>
            <?php endif; ?>
            <?php if ($pesan_error): ?>
            <div class="px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-600 font-medium">
                <?= htmlspecialchars($pesan_error) ?>
            </div>
            <?php endif; ?>

            <?php if ($pesanan_fokus): ?>
            <!-- ══════════════════════════════════════
                 KONFIRMASI PESANAN (MODE FOKUS)
            ══════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-enter">

                <!-- Header toko -->
                <div class="bg-gradient-to-r from-primary to-[#006800] px-6 py-4 flex items-center justify-between">
                    <div>
                        <?php 
                        $tgl_pesan = date('Ymd', strtotime($pesanan_fokus['tanggal_pesan']));
                        $id_show = $pesanan_fokus['id_harian'] ? sprintf("%03d", $pesanan_fokus['id_harian']) : sprintf("%04d", $pesanan_fokus['id_pesanan']);
                        ?>
                        <p class="text-white/70 text-xs font-semibold uppercase tracking-widest">Pesanan #ORD-<?= $tgl_pesan ?>-<?= $id_show ?></p>
                        <h3 class="text-white font-extrabold text-lg"><?= htmlspecialchars($pesanan_fokus['nama_toko']) ?></h3>
                    </div>
                    <?php $st = labelStatus($pesanan_fokus['status_pesanan']); ?>
                    <span class="text-[11px] font-bold px-3 py-1.5 rounded-full <?= $st['bg'] ?> shadow-sm">
                        <?= $st['label'] ?>
                    </span>
                </div>

                <div class="p-6 flex flex-col gap-6">

                    <!-- Rincian item -->
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 mb-3">Rincian Pesanan</p>
                        <div class="flex flex-col divide-y divide-gray-50">
                            <?php foreach ($detail_fokus as $d): ?>
                            <div class="flex justify-between items-center py-2 text-sm">
                                <div>
                                    <span class="font-semibold text-text-1"><?= htmlspecialchars($d['nama_menu']) ?></span>
                                    <span class="text-text-3 ml-2">×<?= $d['jumlah'] ?></span>
                                </div>
                                <span class="font-bold">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Nominal pembayaran -->
                    <div class="bg-primary/5 rounded-xl p-4 flex flex-col items-center text-center border border-primary/10">
                        <p class="text-xs font-bold uppercase tracking-widest text-text-3 mb-1">Total yang Harus Dibayar</p>
                        <p class="text-4xl font-extrabold text-primary tracking-tight">
                            Rp <?= number_format($pesanan_fokus['total_harga'], 0, ',', '.') ?>
                        </p>
                        <?php if (!empty($pesanan_fokus['kode_unik']) && $pesanan_fokus['kode_unik'] > 0): ?>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs font-semibold bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full">
                                Sudah termasuk kode unik <b>+Rp <?= $pesanan_fokus['kode_unik'] ?></b>
                            </span>
                        </div>
                        <p class="text-[11px] text-text-3 mt-2">
                            Transfer <b>tepat nominal di atas</b> agar penjual dapat memverifikasi pesanan Anda dengan mudah.
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Info Pembayaran & Transfer -->
                    <?php if ($pesanan_fokus['status_pesanan'] === 'dibatalkan'): ?>
                    <!-- Pesanan ditolak/dibatalkan — jangan tampilkan form pembayaran -->
                    <?php 
                        $sudah_transfer = in_array($pesanan_fokus['status_bayar'] ?? '', ['sudah_bayar', 'lunas']);
                    ?>
                    <div class="flex flex-col gap-3">
                        <!-- Kotak status dibatalkan -->
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                            <span class="text-xl leading-none">❌</span>
                            <div>
                                <p class="text-sm font-bold text-red-700">Pesanan Ditolak / Dibatalkan</p>
                                <?php if (!empty($pesanan_fokus['alasan_tolak'])): ?>
                                <p class="text-xs text-red-600 mt-1">Pesan dari penjual: <b>"<?= htmlspecialchars($pesanan_fokus['alasan_tolak']) ?>"</b></p>
                                <?php endif; ?>
                                <?php if (($pesanan_fokus['poin_digunakan'] ?? 0) > 0): ?>
                                <p class="text-xs text-green-700 font-semibold mt-2">✅ <?= $pesanan_fokus['poin_digunakan'] ?> poin yang Anda gunakan sudah dikembalikan.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($sudah_transfer): ?>
                        <!-- Kotak peringatan refund offline -->
                        <div class="bg-orange-50 border-2 border-orange-300 rounded-xl p-4 flex items-start gap-3">
                            <span class="text-xl leading-none">⚠️</span>
                            <div>
                                <p class="text-sm font-bold text-orange-700">Dana Anda belum dikembalikan!</p>
                                <p class="text-xs text-orange-600 mt-1">Karena Anda sudah mentransfer, silakan datang langsung ke kantin untuk melakukan <b>pengembalian dana secara tunai (refund offline)</b> bersama penjual.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php elseif ($pesanan_fokus['status_bayar'] === 'menunggu_pembayaran'): ?>
                        <?php if ($pesanan_fokus['status_pesanan'] === 'pending'): ?>
                        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
                            <p class="text-sm font-bold text-yellow-700">⏳ Menunggu Konfirmasi Penjual</p>
                            <p class="text-xs text-yellow-600 mt-1">Pesanan Anda sedang diperiksa oleh penjual. <b>Mohon jangan melakukan transfer dulu</b> sebelum pesanan ini dikonfirmasi untuk menghindari transfer saat stok habis.</p>
                        </div>
                        <?php elseif ($pesanan_fokus['status_pesanan'] === 'dikonfirmasi'): ?>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 mb-3">Cara Pembayaran</p>

                            <?php if ($pesanan_fokus['metode_bayar'] === 'qr'): ?>
                            <!-- QRIS -->
                            <div class="flex flex-col items-center gap-3">
                                <p class="text-sm font-semibold text-text-2 flex items-center gap-1.5">📱 Scan QRIS Toko</p>
                                <?php if (!empty($pesanan_fokus['qris_image']) && file_exists('../assets/img/qris/' . $pesanan_fokus['qris_image'])): ?>
                                    <img src="../assets/img/qris/<?= htmlspecialchars($pesanan_fokus['qris_image']) ?>"
                                         alt="QRIS" class="w-52 h-52 object-contain rounded-2xl border border-gray-200 bg-white qris-glow">
                                    <p class="text-xs text-text-3 text-center max-w-xs">
                                        Scan kode QR di atas menggunakan aplikasi e-wallet/bank Anda, dengan nominal <b>tepat Rp <?= number_format($pesanan_fokus['total_harga'], 0, ',', '.') ?></b>
                                    </p>
                                <?php else: ?>
                                    <div class="w-52 h-52 rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-text-3 gap-2">
                                        <svg class="w-10 h-10 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <p class="text-xs font-semibold">QRIS belum diatur penjual</p>
                                        <p class="text-[11px] text-center px-4">Silakan minta QRIS langsung di kantin</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php elseif ($pesanan_fokus['metode_bayar'] === 'transfer'): ?>
                            <!-- Transfer Bank -->
                            <div class="bg-input rounded-xl p-4 border border-gray-100">
                                <p class="text-xs font-bold text-primary mb-1 uppercase tracking-widest">🏦 Rekening Bank</p>
                                <p class="text-lg font-bold select-all text-text-1 mt-1"><?= htmlspecialchars($pesanan_fokus['info_bank'] ?: 'Belum diatur — tanya penjual') ?></p>
                            </div>

                            <?php else: ?>
                            <!-- E-Wallet -->
                            <div class="bg-input rounded-xl p-4 border border-gray-100">
                                <p class="text-xs font-bold text-primary mb-1 uppercase tracking-widest">🟡 E-Wallet (<?= strtoupper($pesanan_fokus['metode_bayar']) ?>)</p>
                                <p class="text-lg font-bold select-all text-text-1 mt-1"><?= htmlspecialchars($pesanan_fokus['info_ewallet'] ?: 'Belum diatur — tanya penjual') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tombol Konfirmasi Transfer -->
                        <div class="border-t pt-5">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 mb-3">Konfirmasi Pembayaran</p>
                            
                            <?php
                            // Hitung sisa waktu dari server
                            $waktu_dikonfirmasi_ts = strtotime($pesanan_fokus['waktu_dikonfirmasi']);
                            $batas_waktu_ts = $waktu_dikonfirmasi_ts + (10 * 60);
                            $sisa_detik = max(0, $batas_waktu_ts - time());
                            ?>
                            
                            <!-- Countdown Timer -->
                            <div id="transfer-timer-box" class="mb-4 rounded-xl p-3 border flex items-center gap-3 <?= $sisa_detik > 0 ? 'bg-yellow-50 border-yellow-200' : 'bg-red-50 border-red-200' ?>">
                                <div class="text-2xl"><?= $sisa_detik > 0 ? '⏱️' : '⌛' ?></div>
                                <div class="flex-1">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-text-3 mb-0.5">Batas Waktu Transfer</p>
                                    <?php if ($sisa_detik > 0): ?>
                                    <p id="timer-display" class="text-xl font-extrabold text-yellow-600 font-mono tabular-nums">--:--</p>
                                    <p class="text-[11px] text-yellow-700 mt-0.5">Transfer sebelum waktu habis</p>
                                    <?php else: ?>
                                    <p class="text-base font-bold text-red-600">Waktu Habis!</p>
                                    <p class="text-[11px] text-red-500 mt-0.5">Pesanan akan dibatalkan otomatis</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="text-xs text-text-3 mb-4">
                                Jika Anda sudah mentransfer sesuai nominal (termasuk kode unik), silakan klik tombol di bawah.
                            </p>
                            <form method="POST" class="flex flex-col gap-3" onsubmit="return confirm('Apakah Anda yakin sudah mentransfer sejumlah yang ditentukan? Penjual akan memverifikasi mutasi rekening mereka.')">
                                <input type="hidden" name="id_pesanan" value="<?= $pesanan_fokus['id_pesanan'] ?>">
                                <button type="submit" name="konfirmasi_transfer" value="1" id="btn-konfirmasi-transfer"
                                    class="w-full bg-primary text-white font-bold py-3 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md"
                                    <?= $sisa_detik <= 0 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
                                    Saya Sudah Transfer
                                </button>
                            </form>
                            
                            <script>
                            (function() {
                                var sisaDetik = <?= $sisa_detik ?>;
                                var timerDisplay = document.getElementById('timer-display');
                                var timerBox = document.getElementById('transfer-timer-box');
                                var btnTransfer = document.getElementById('btn-konfirmasi-transfer');
                                
                                if (sisaDetik <= 0) return; // Sudah expired, tidak perlu timer
                                
                                function updateTimer() {
                                    if (sisaDetik <= 0) {
                                        // Timer habis
                                        if (timerDisplay) {
                                            timerDisplay.textContent = '00:00';
                                        }
                                        if (timerBox) {
                                            timerBox.className = 'mb-4 rounded-xl p-3 border flex items-center gap-3 bg-red-50 border-red-200';
                                            timerBox.innerHTML = '<div class="text-2xl">⌛</div><div class="flex-1"><p class="text-[10px] font-bold uppercase tracking-widest text-text-3 mb-0.5">Batas Waktu Transfer</p><p class="text-base font-bold text-red-600">Waktu Habis!</p><p class="text-[11px] text-red-500 mt-0.5">Pesanan akan dibatalkan otomatis saat kamu klik tombol</p></div>';
                                        }
                                        if (btnTransfer) {
                                            btnTransfer.disabled = false; // Biarkan bisa diklik agar server yang batalkan
                                            btnTransfer.style.background = '#ef4444';
                                            btnTransfer.textContent = 'Waktu Habis — Klik untuk Lanjut';
                                        }
                                        return;
                                    }
                                    
                                    var menit = Math.floor(sisaDetik / 60);
                                    var detik = sisaDetik % 60;
                                    var display = String(menit).padStart(2, '0') + ':' + String(detik).padStart(2, '0');
                                    
                                    if (timerDisplay) timerDisplay.textContent = display;
                                    
                                    // Ubah warna jadi merah saat sisa < 2 menit
                                    if (sisaDetik <= 120 && timerDisplay) {
                                        timerDisplay.className = 'text-xl font-extrabold text-red-600 font-mono tabular-nums';
                                        timerBox.className = 'mb-4 rounded-xl p-3 border flex items-center gap-3 bg-red-50 border-red-200';
                                    }
                                    
                                    sisaDetik--;
                                    setTimeout(updateTimer, 1000);
                                }
                                
                                updateTimer();
                            })();
                            </script>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($pesanan_fokus['status_bayar'] === 'sudah_bayar'): ?>
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
                        <p class="text-sm font-bold text-orange-700">⏳ Konfirmasi terkirim, menunggu verifikasi penjual</p>
                        <p class="text-xs text-orange-500 mt-1">Penjual sedang mengecek mutasi rekening/e-wallet mereka.</p>
                    </div>
                    <?php elseif ($pesanan_fokus['status_bayar'] === 'lunas'): ?>
                    <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
                        <p class="text-sm font-bold text-green-700">✅ Pembayaran sudah terverifikasi!</p>
                    </div>
                    <?php elseif ($pesanan_fokus['status_bayar'] === 'belum_bayar'): ?>
                        <?php if ($pesanan_fokus['status_pesanan'] === 'selesai'): ?>
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-center">
                            <p class="text-sm font-bold text-text-2">💵 Bayar Tunai — Silakan menuju kantin</p>
                        </div>
                        <?php else: ?>
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-center">
                            <p class="text-sm font-bold text-text-2">💵 Uang Tunai — Bayar saat pesanan selesai disiapkan</p>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Batalkan (hanya jika pending) -->
                    <?php if ($pesanan_fokus['status_pesanan'] === 'pending'): ?>
                    <form method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        <input type="hidden" name="id_pesanan" value="<?= $pesanan_fokus['id_pesanan'] ?>">
                        <button type="submit" name="batalkan_pesanan"
                            class="w-full border border-red-200 text-red-500 font-semibold py-2.5 rounded-xl hover:bg-red-50 active:scale-95 transition-all text-sm">
                            Batalkan Pesanan
                        </button>
                    </form>
                    <?php endif; ?>

                    <a href="pesanan.php" class="text-center text-sm text-primary font-semibold hover:underline">← Kembali ke Semua Pesanan</a>
                </div>
            </div>

            <?php else: ?>
            <!-- ══════════════════════════════════════
                 DAFTAR PESANAN AKTIF
            ══════════════════════════════════════ -->
            <?php if (empty($pesanan_aktif)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 flex flex-col items-center text-center gap-3">
                <div class="w-16 h-16 rounded-2xl bg-primary/5 flex items-center justify-center mb-1">
                    <svg class="w-8 h-8 text-primary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-text-2 font-semibold">Tidak ada pesanan aktif</p>
                <p class="text-text-3 text-sm">Semua pesanan Anda sudah selesai atau belum ada pesanan baru.</p>
                <a href="pesan.php" class="mt-2 px-5 py-2.5 bg-primary text-white text-sm font-bold rounded-xl hover:opacity-90 transition-all">Pesan Sekarang</a>
            </div>
            <?php else: ?>
            <?php foreach ($pesanan_aktif as $p):
                $st  = labelStatus($p['status_pesanan']);
                $stb = labelBayar($p['status_bayar'] ?? 'belum_bayar');
                $needsPayment = ($p['status_bayar'] ?? '') === 'menunggu_pembayaran';
            ?>
            <div class="bg-white rounded-2xl shadow-sm border <?= $needsPayment ? 'border-orange-200' : 'border-gray-100' ?> overflow-hidden card-enter mb-4">

                <!-- Header -->
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <?php 
                        $tgl_pesan = date('Ymd', strtotime($p['tanggal_pesan']));
                        $id_show = $p['id_harian'] ? sprintf("%03d", $p['id_harian']) : sprintf("%04d", $p['id_pesanan']);
                        ?>
                        <p class="text-xs text-text-3 font-semibold">#ORD-<?= $tgl_pesan ?>-<?= $id_show ?> · <?= htmlspecialchars($p['nama_toko']) ?></p>
                        <p class="text-xs text-text-3 mt-0.5"><?= date('d M Y, H:i', strtotime($p['tanggal_pesan'])) ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full <?= $st['bg'] ?>"><?= $st['label'] ?></span>
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full <?= $stb['bg'] ?>"><?= $stb['label'] ?></span>
                    </div>
                </div>

                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-text-3">Total Pembayaran</p>
                        <p class="text-xl font-extrabold text-primary">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>
                        <?php if (!empty($p['kode_unik']) && $p['kode_unik'] > 0): ?>
                        <p class="text-[11px] text-yellow-600 font-semibold mt-0.5">+Rp <?= $p['kode_unik'] ?> kode unik</p>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-2 items-end">
                        <a href="pesanan.php?id=<?= $p['id_pesanan'] ?>"
                           class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-submit active:scale-95 transition-all shadow-sm">
                            <?= $needsPayment ? '💳 Bayar Sekarang' : 'Lihat Detail' ?>
                        </a>
                        <?php if ($p['status_pesanan'] === 'pending'): ?>
                        <form method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
                            <input type="hidden" name="id_pesanan" value="<?= $p['id_pesanan'] ?>">
                            <button type="submit" name="batalkan_pesanan"
                                class="text-xs text-red-400 hover:text-red-600 font-semibold transition-colors">
                                Batalkan
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($needsPayment): ?>
                <div class="px-5 pb-4">
                    <div class="bg-orange-50 border border-orange-100 rounded-xl px-4 py-2.5 text-xs font-semibold text-orange-700 flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Pesanan menunggu pembayaran — klik "Bayar Sekarang"
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="text-center mt-6">
                <a href="history.php" class="text-sm text-primary font-semibold hover:underline">Lihat Riwayat Semua Pesanan →</a>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>
</body>

</html>
