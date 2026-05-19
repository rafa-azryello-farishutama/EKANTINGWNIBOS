<?php
session_start();
include '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['id_users'])) {
    header("Location: ../index.php");
    exit;
}

$id_pesanan = isset($_GET['id_pesanan']) ? (int)$_GET['id_pesanan'] : 0;
if ($id_pesanan <= 0) {
    echo "<div style='text-align:center; padding: 50px; font-family: sans-serif;'>Pesanan tidak ditemukan.</div>";
    exit;
}

// Ambil data pesanan
$query = "SELECT p.*, t.nama_toko, u.username AS nama_pelanggan
          FROM pesanan p
          JOIN toko t ON p.id_toko = t.id_toko
          JOIN users u ON p.id_users = u.id_users
          WHERE p.id_pesanan = ?";
$stmt = $db_ekantin->prepare($query);
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();

if (!$pesanan) {
    echo "<div style='text-align:center; padding: 50px; font-family: sans-serif;'>Pesanan tidak ditemukan.</div>";
    exit;
}

// Otorisasi Sederhana:
// Hanya pembeli yang bersangkutan, penjual yang memiliki toko tersebut, atau admin yang bisa melihat.
$user_role = $_SESSION['role'];
$user_id = $_SESSION['id_users'];

if ($user_role === 'pembeli' && $pesanan['id_users'] != $user_id) {
    echo "<div style='text-align:center; padding: 50px; font-family: sans-serif; color: red;'>Akses ditolak.</div>";
    exit;
}
if ($user_role === 'penjual' && $pesanan['id_toko'] != $_SESSION['id_toko']) {
    echo "<div style='text-align:center; padding: 50px; font-family: sans-serif; color: red;'>Akses ditolak.</div>";
    exit;
}

// Tentukan URL Kembali berdasarkan role secara dinamis
$back_url = "../index.php";
if ($user_role === 'pembeli') {
    $back_url = "../pembeli/history.php";
} elseif ($user_role === 'penjual') {
    $back_url = "../penjual/pesanan.php";
} elseif ($user_role === 'admin') {
    $back_url = "../admin/history.php";
}

// Ambil detail pesanan
$qItems = $db_ekantin->prepare("SELECT dp.jumlah, pk.nama_menu, pk.harga 
                                FROM detail_pesanan dp 
                                JOIN produk_kantin pk ON dp.id_produk = pk.id_produk 
                                WHERE dp.id_pesanan = ?");
$qItems->bind_param("i", $id_pesanan);
$qItems->execute();
$items = $qItems->get_result()->fetch_all(MYSQLI_ASSOC);

$waktu = date('d-m-Y H:i:s', strtotime($pesanan['tanggal_pesan']));
$status = strtoupper($pesanan['status_pesanan']);
$metode = strtoupper($pesanan['metode_pembayaran'] ?? 'TRANSFER');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja #ORD-<?= sprintf("%04d", $id_pesanan) ?></title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <style>
        @font-face {
            font-family: 'ReceiptFont';
            src: local('Courier New'), local('Courier'), monospace;
        }
        .receipt {
            font-family: 'ReceiptFont', monospace;
            max-width: 360px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
            }
            .receipt-shadow {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 10px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Floating Actions Buttons (Hidden on print) -->
    <div class="no-print mb-4 flex gap-3 w-full max-w-[360px]">
        <a href="<?= $back_url ?>" class="flex-1 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold py-2.5 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-1.5 decoration-none">
            ← Kembali
        </a>
        <button onclick="window.print()" class="flex-1 bg-green-700 hover:bg-green-800 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-1.5">
            🖨️ Cetak Struk
        </button>
    </div>

    <!-- Receipt Paper -->
    <div class="receipt receipt-shadow bg-white p-6 border border-gray-200 shadow-lg rounded-lg w-full max-w-[360px] text-gray-800 text-sm">
        
        <!-- Header -->
        <div class="text-center mb-4">
            <h2 class="font-bold text-lg">E-KANTIN SMEA</h2>
            <p class="text-xs uppercase font-semibold mt-0.5"><?= htmlspecialchars($pesanan['nama_toko']) ?></p>
            <p class="text-[11px] text-gray-500 mt-1">Struk Bukti Pemesanan Makanan</p>
        </div>

        <div class="border-b border-dashed border-gray-300 my-3"></div>

        <!-- Info -->
        <div class="flex flex-col gap-1 text-xs">
            <div class="flex justify-between">
                <span>No. Pesanan:</span>
                <span class="font-bold">#ORD-<?= sprintf("%04d", $id_pesanan) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span><?= $waktu ?></span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span class="font-semibold"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Pembayaran:</span>
                <span class="font-semibold"><?= $metode === 'QR' ? 'QRIS / QR CODE' : 'TRANSFER BANK' ?></span>
            </div>
            <div class="flex justify-between">
                <span>Status:</span>
                <span class="font-bold text-green-700"><?= $status ?></span>
            </div>
        </div>

        <div class="border-b border-dashed border-gray-300 my-3"></div>

        <!-- Items Table -->
        <div class="flex flex-col gap-2 my-2 text-xs">
            <?php foreach ($items as $item): ?>
                <div class="flex justify-between items-start">
                    <div class="flex flex-col">
                        <span><?= htmlspecialchars($item['nama_menu']) ?></span>
                        <span class="text-gray-500 text-[10px]"><?= $item['jumlah'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                    </div>
                    <span>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="border-b border-dashed border-gray-300 my-3"></div>

        <!-- Summary -->
        <div class="flex flex-col gap-1 text-xs">
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL AKHIR:</span>
                <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
            </div>
        </div>

        <?php if (!empty($pesanan['catatan'])): ?>
            <div class="border-b border-dashed border-gray-300 my-3"></div>
            <div class="text-[11px] text-gray-600 italic">
                Catatan: "<?= htmlspecialchars($pesanan['catatan']) ?>"
            </div>
        <?php endif; ?>

        <?php if ($pesanan['status_pesanan'] === 'dibatalkan' && !empty($pesanan['alasan_tolak'])): ?>
            <div class="border-b border-dashed border-gray-300 my-3"></div>
            <div class="text-[11px] text-red-600 font-semibold">
                Alasan Ditolak: "<?= htmlspecialchars($pesanan['alasan_tolak']) ?>"
            </div>
        <?php endif; ?>

        <div class="border-b border-dashed border-gray-300 my-4"></div>

        <!-- Footer Message -->
        <div class="text-center text-[10px] text-gray-500 flex flex-col gap-0.5">
            <p>Terima kasih atas pesanan Anda.</p>
            <p>Harap tunjukkan struk ini ke kasir</p>
            <p>kantin saat mengambil pesanan Anda.</p>
        </div>

    </div>

</body>
</html>
