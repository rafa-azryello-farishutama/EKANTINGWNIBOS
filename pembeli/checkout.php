<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'pembeli'){
    header("Location: ../index.php");
    exit;
}

$id_toko = isset($_POST['id_toko']) ? (int)$_POST['id_toko'] : 0;
$cart_data_json = isset($_POST['cart_data']) ? $_POST['cart_data'] : '{}';
$cart = json_decode($cart_data_json, true);

if (empty($cart) || !$id_toko) {
    header("Location: pesan.php");
    exit;
}

// Fetch store details for display
$qStore = $db_ekantin->prepare("SELECT nama_toko FROM toko WHERE id_toko = ?");
$qStore->bind_param("i", $id_toko);
$qStore->execute();
$store = $qStore->get_result()->fetch_assoc();
$nama_toko = $store ? $store['nama_toko'] : 'Kantin';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pesanan</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "background":     "#fafbf9",
                        "primary":        "#004900",
                        "input":          "#f0f4f0",
                        "text-1":         "#191c1c",
                        "text-3":         "#5e6659",
                        "submit":         "#005300"
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-text-1">
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 sm:p-8">
        
        <div class="flex items-center gap-3 mb-6">
            <a href="pesan.php?id_toko=<?= $id_toko ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-input text-text-3 hover:bg-primary hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-primary">Konfirmasi Pesanan</h1>
                <p class="text-xs text-text-3">Dari: <?= htmlspecialchars($nama_toko) ?></p>
            </div>
        </div>

        <div class="space-y-4 mb-6">
            <h3 class="font-bold text-sm text-text-1 border-b pb-2">Rincian Pesanan</h3>
            <?php 
            $total_harga = 0;
            $total_item = 0;
            foreach ($cart as $id => $item): 
                $subtotal = $item['price'] * $item['qty'];
                $total_harga += $subtotal;
                $total_item += $item['qty'];
            ?>
            <div class="flex justify-between text-sm">
                <div class="flex flex-col">
                    <span class="font-semibold"><?= htmlspecialchars($item['name']) ?></span>
                    <span class="text-xs text-text-3"><?= $item['qty'] ?>x @ Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                </div>
                <span class="font-bold">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="border-t pt-4 mb-8">
            <div class="flex justify-between items-center text-lg font-extrabold text-primary">
                <span>Total Pembayaran</span>
                <span>Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
            </div>
        </div>

        <form action="proses_bayar.php" method="POST">
            <!-- Data untuk diproses ke database nanti -->
            <input type="hidden" name="id_toko" value="<?= $id_toko ?>">
            <input type="hidden" name="cart_data" value="<?= htmlspecialchars($cart_data_json) ?>">
            <input type="hidden" name="total_harga" value="<?= $total_harga ?>">
            
            <button type="button" onclick="alert('Ini hanya tampilan contoh Checkout. Anda bisa memprogram aksi nyatanya nanti di proses_bayar.php!')" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md">
                Konfirmasi & Bayar
            </button>
        </form>

    </div>
</div>
</body>
</html>
