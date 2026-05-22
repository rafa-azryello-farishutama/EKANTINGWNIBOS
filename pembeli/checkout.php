<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['pembeli_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];

$id_toko = isset($_POST['id_toko']) ? (int)$_POST['id_toko'] : 0;
$cart_data_json = isset($_POST['cart_data']) ? $_POST['cart_data'] : '{}';
$cart = json_decode($cart_data_json, true);

if (empty($cart) || !$id_toko) {
    header("Location: pesan.php");
    exit;
}

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
    <link rel="stylesheet" href="../assets/css/tailwind.css">
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

        <form action="proses_bayar.php" method="POST" enctype="multipart/form-data" onsubmit="return validateCheckoutForm()">
            <input type="hidden" name="id_toko" value="<?= $id_toko ?>">
            <input type="hidden" name="cart_data" value="<?= htmlspecialchars($cart_data_json) ?>">
            <input type="hidden" name="total_harga" value="<?= $total_harga ?>">

            <!-- Input Catatan -->
            <div class="mb-5">
                <label for="catatan" class="block text-sm font-semibold text-text-1 mb-1.5">Catatan untuk Penjual (Opsional)</label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Misal: Pedas ya, jangan pakai daun bawang." 
                    class="w-full rounded-xl border-gray-200 bg-input focus:bg-white focus:ring-primary focus:border-primary text-sm transition-all resize-none"></textarea>
            </div>

            <!-- Pilihan Metode Pembayaran -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-text-1 mb-2.5">Metode Pembayaran</label>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                    <!-- Option 1: Transfer Bank -->
                    <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none border-primary/20 bg-primary/5" id="label-transfer">
                        <input type="radio" name="metode_pembayaran" value="transfer" checked class="sr-only" onchange="togglePaymentMethod('transfer')">
                        <span class="text-xs text-text-3 font-semibold">Metode</span>
                        <span class="text-sm font-bold text-primary mt-1 flex items-center gap-1.5">
                            🏦 Transfer Bank
                        </span>
                    </label>

                    <!-- Option 2: QRIS / QR Code -->
                    <label class="relative flex flex-col p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none" id="label-qr">
                        <input type="radio" name="metode_pembayaran" value="qr" class="sr-only" onchange="togglePaymentMethod('qr')">
                        <span class="text-xs text-text-3 font-semibold">Metode</span>
                        <span class="text-sm font-bold text-text-1 mt-1 flex items-center gap-1.5">
                            📱 QRIS / QR Code
                        </span>
                    </label>

                    <!-- Option 3: GoPay -->
                    <label class="relative flex flex-col p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none" id="label-gopay">
                        <input type="radio" name="metode_pembayaran" value="gopay" class="sr-only" onchange="togglePaymentMethod('gopay')">
                        <span class="text-xs text-text-3 font-semibold">E-Wallet</span>
                        <span class="text-sm font-bold text-text-1 mt-1 flex items-center gap-1.5">
                            🟢 GoPay
                        </span>
                    </label>

                    <!-- Option 4: OVO -->
                    <label class="relative flex flex-col p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none" id="label-ovo">
                        <input type="radio" name="metode_pembayaran" value="ovo" class="sr-only" onchange="togglePaymentMethod('ovo')">
                        <span class="text-xs text-text-3 font-semibold">E-Wallet</span>
                        <span class="text-sm font-bold text-text-1 mt-1 flex items-center gap-1.5">
                            🟣 OVO
                        </span>
                    </label>

                    <!-- Option 5: DANA -->
                    <label class="relative flex flex-col p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none" id="label-dana">
                        <input type="radio" name="metode_pembayaran" value="dana" class="sr-only" onchange="togglePaymentMethod('dana')">
                        <span class="text-xs text-text-3 font-semibold">E-Wallet</span>
                        <span class="text-sm font-bold text-text-1 mt-1 flex items-center gap-1.5">
                            🔵 DANA
                        </span>
                    </label>
                </div>

                <!-- Info Box: Payment Details -->
                <div id="info-payment" class="bg-input rounded-xl p-4 border border-gray-100 mb-4 transition-all duration-300">
                    <p id="payment-title" class="text-xs font-bold text-primary mb-1 uppercase tracking-widest">🏦 Detail Rekening</p>
                    
                    <div id="payment-text-wrapper">
                        <p id="payment-desc" class="text-sm font-semibold text-text-1">Bank BCA: <span class="font-bold select-all">869-214-5561</span></p>
                        <p id="payment-name" class="text-xs text-text-3 mt-0.5">a/n E-Kantin SMEA</p>
                    </div>

                    <div id="payment-qr-wrapper" class="hidden flex flex-col items-center">
                        <div class="flex justify-center bg-white p-3 rounded-lg border border-gray-100 w-44 h-44 mx-auto mb-3">
                            <img src="../assets/img/qr_dummy.png" alt="QRIS Dummy Code" class="w-full h-full object-contain">
                        </div>
                        <p class="text-center text-xs text-text-3 mb-3">Silakan scan kode QR di atas untuk melakukan pembayaran.</p>
                    </div>

                    <div class="mt-3 border-t border-gray-200/50 pt-3">
                        <label for="bukti_bayar" class="block text-xs font-bold text-text-3 mb-1.5">Upload Bukti Pembayaran (Wajib)</label>
                        <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/*" class="w-full text-xs text-text-3 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-submit file:cursor-pointer">
                    </div>
                </div>

            </div>
            
            <button type="submit" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md flex justify-center items-center gap-2">
                Konfirmasi & Bayar
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </form>

    </div>
</div>

<script>
    const paymentMethods = ['transfer', 'qr', 'gopay', 'ovo', 'dana'];

    function togglePaymentMethod(method) {
        paymentMethods.forEach(m => {
            const label = document.getElementById('label-' + m);
            if(label) {
                if(m === method) {
                    label.className = "relative flex flex-col p-4 border-2 border-primary/20 bg-primary/5 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none";
                    label.querySelector('.text-sm').className = "text-sm font-bold text-primary mt-1 flex items-center gap-1.5";
                } else {
                    label.className = "relative flex flex-col p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none";
                    label.querySelector('.text-sm').className = "text-sm font-bold text-text-1 mt-1 flex items-center gap-1.5";
                }
            }
        });
        
        const title = document.getElementById('payment-title');
        const textWrap = document.getElementById('payment-text-wrapper');
        const qrWrap = document.getElementById('payment-qr-wrapper');
        const desc = document.getElementById('payment-desc');
        const name = document.getElementById('payment-name');
        
        if (method === 'qr') {
            title.innerText = '📱 Scan QRIS';
            textWrap.classList.add('hidden');
            qrWrap.classList.remove('hidden');
        } else {
            textWrap.classList.remove('hidden');
            qrWrap.classList.add('hidden');
            if (method === 'transfer') {
                title.innerText = '🏦 Detail Rekening';
                desc.innerHTML = 'Bank BCA: <span class="font-bold select-all">869-214-5561</span>';
                name.innerText = 'a/n E-Kantin SMEA';
            } else if (method === 'gopay') {
                title.innerText = '🟢 Instruksi GoPay';
                desc.innerHTML = 'Nomor GoPay: <span class="font-bold select-all">0812-3456-7890</span>';
                name.innerText = 'a/n E-Kantin SMEA';
            } else if (method === 'ovo') {
                title.innerText = '🟣 Instruksi OVO';
                desc.innerHTML = 'Nomor OVO: <span class="font-bold select-all">0812-3456-7890</span>';
                name.innerText = 'a/n E-Kantin SMEA';
            } else if (method === 'dana') {
                title.innerText = '🔵 Instruksi DANA';
                desc.innerHTML = 'Nomor DANA: <span class="font-bold select-all">0812-3456-7890</span>';
                name.innerText = 'a/n E-Kantin SMEA';
            }
        }
    }

    function validateCheckoutForm() {
        const file = document.getElementById('bukti_bayar').files[0];
        if (!file) {
            alert('Silakan upload bukti pembayaran Anda terlebih dahulu!');
            return false;
        }
        return true;
    }
</script>
</body>
</html>
