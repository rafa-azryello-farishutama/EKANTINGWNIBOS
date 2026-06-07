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

$qStore = $db_ekantin->prepare("SELECT nama_toko, qris_image, info_bank, info_ewallet FROM toko WHERE id_toko = ?");
$qStore->bind_param("i", $id_toko);
$qStore->execute();
$store = $qStore->get_result()->fetch_assoc();
$nama_toko      = $store ? $store['nama_toko'] : 'Kantin';
$ada_bank       = !empty($store['info_bank']);
$ada_qris       = !empty($store['qris_image']);

$ewallet_parsed = json_decode($store['info_ewallet'] ?? '', true);
$ada_dana = false;
$ada_ovo = false;
$ada_gopay = false;
if (json_last_error() === JSON_ERROR_NONE && is_array($ewallet_parsed)) {
    $ada_dana = !empty($ewallet_parsed['DANA']);
    $ada_ovo = !empty($ewallet_parsed['OVO']);
    $ada_gopay = !empty($ewallet_parsed['GOPAY']);
} else if (!empty($store['info_ewallet'])) {
    $ada_dana = $ada_ovo = $ada_gopay = true;
}

$id_users = $_SESSION['id_users'];
$qUser = $db_ekantin->prepare("SELECT poin FROM users WHERE id_users = ?");
$qUser->bind_param("i", $id_users);
$qUser->execute();
$userData = $qUser->get_result()->fetch_assoc();
$poin_user = $userData ? (int)$userData['poin'] : 0;
?>
<!DOCTYPE html>
<html lang="id" class="no-scrollbar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pesanan</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Custom Background to bypass Tailwind compiler issues */
        .bg-custom-gradient {
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 50%, #ecfdf5 100%);
        }
        .blob-1 {
            position: absolute;
            top: -10%;
            right: -5%;
            width: 400px;
            height: 400px;
            background-color: rgba(21, 128, 61, 0.08); /* Green primary */
            border-radius: 50%;
            filter: blur(80px);
        }
        .blob-2 {
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background-color: rgba(250, 204, 21, 0.15); /* Yellow */
            border-radius: 50%;
            filter: blur(100px);
        }
        .bg-dots {
            position: absolute;
            inset: 0;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwgMCwgMCwgMC4wNCkiLz48L3N2Zz4=');
            opacity: 0.6;
        }
    </style>
</head>
<body class="text-text-1 relative min-h-screen no-scrollbar bg-slate-50">
    
    <!-- Beautiful Modern Gradient Background -->
    <div class="fixed inset-0 -z-10 bg-custom-gradient">
        <!-- Soft glowing orbs -->
        <div class="blob-1"></div>
        <div class="blob-2"></div>
        <!-- Subtle dot pattern -->
        <div class="bg-dots"></div>
    </div>

<div class="min-h-screen flex items-center justify-center p-4 py-8">
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

        <form action="proses_bayar.php" method="POST" enctype="multipart/form-data" onsubmit="return validateCheckoutForm()">
            <input type="hidden" name="id_toko" value="<?= $id_toko ?>">
            <input type="hidden" name="cart_data" value="<?= htmlspecialchars($cart_data_json) ?>">
            <input type="hidden" name="total_harga" value="<?= $total_harga ?>">

        <div class="border-t pt-4 mb-6">
            <?php if ($poin_user > 0): ?>
            <div class="flex items-center justify-between bg-yellow-50 p-3 rounded-xl border border-yellow-100 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-yellow-700">Saldo Poin: Rp <?= number_format($poin_user, 0, ',', '.') ?></p>
                        <p class="text-[10px] text-yellow-600">Gunakan poin untuk memotong tagihan</p>
                    </div>
                </div>
                <label class="flex items-center cursor-pointer gap-2 bg-yellow-500/10 px-3 py-1.5 rounded-lg border border-yellow-500/20 hover:bg-yellow-500/20 transition-all">
                    <input type="checkbox" id="checkbox_poin" name="gunakan_poin" value="1" class="w-5 h-5 text-yellow-600 rounded border-gray-300 focus:ring-yellow-500 cursor-pointer" onchange="updateTotal()">
                    <span class="text-xs font-bold text-yellow-800">Pakai Poin</span>
                </label>
            </div>
            <?php endif; ?>

            <div class="flex justify-between items-center text-lg font-extrabold text-primary">
                <span>Total Pembayaran</span>
                <span id="display_total">Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
            </div>
            <div id="info_potongan" class="hidden text-right text-xs text-yellow-600 font-semibold mt-1">
                - Rp <span id="display_potongan">0</span> (Poin)
            </div>
        </div>

            <!-- Input Catatan -->
            <div class="mb-5">
                <label for="catatan" class="block text-sm font-semibold text-text-1 mb-1.5">Catatan untuk Penjual (Opsional)</label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Misal: Pedas ya, jangan pakai daun bawang." 
                    class="w-full rounded-xl border-gray-200 bg-input focus:bg-white focus:ring-primary focus:border-primary text-sm transition-all resize-none"></textarea>
            </div>

            <!-- Pilihan Metode Pembayaran -->
            <div id="metode-pembayaran-section" class="mb-6">
                <label class="block text-sm font-semibold text-text-1 mb-2.5">Metode Pembayaran</label>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-4">

                    <?php
                    // Helper: render payment option card
                    // $available = bool, $value, $label, $sublabel, $emoji, $checkedDefault
                    $cls_available   = 'relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none border-gray-100';
                    $cls_unavailable = 'relative flex flex-col p-4 border-2 rounded-xl cursor-not-allowed select-none border-red-100 bg-red-50/60 opacity-70';
                    ?>

                    <!-- Option 1: Transfer Bank -->
                    <label class="<?= $ada_bank ? $cls_available : $cls_unavailable ?>" id="label-transfer">
                        <input type="radio" name="metode_pembayaran" value="transfer"
                            <?= (!$ada_bank) ? 'disabled' : '' ?>
                            <?= ($ada_bank) ? 'checked onchange="togglePaymentMethod(\'transfer\')"' : 'onchange="togglePaymentMethod(\'transfer\')"' ?>
                            class="sr-only">
                        <span class="text-xs text-text-3 font-semibold">Metode</span>
                        <span class="text-sm font-bold mt-1 flex items-center gap-1.5 <?= $ada_bank ? 'text-primary' : 'text-red-400' ?>">
                            🏦 Transfer Bank
                        </span>
                        <?php if (!$ada_bank): ?>
                        <span class="mt-1 text-[10px] font-bold text-red-500 flex items-center gap-1">⛔ Belum tersedia</span>
                        <?php endif; ?>
                    </label>

                    <!-- Option 2: QRIS / QR Code -->
                    <label class="<?= $ada_qris ? $cls_available : $cls_unavailable ?>" id="label-qr">
                        <input type="radio" name="metode_pembayaran" value="qr"
                            <?= (!$ada_qris) ? 'disabled' : '' ?>
                            <?= ($ada_qris && !$ada_bank) ? 'checked onchange="togglePaymentMethod(\'qr\')"' : 'onchange="togglePaymentMethod(\'qr\')"' ?>
                            class="sr-only">
                        <span class="text-xs text-text-3 font-semibold">Metode</span>
                        <span class="text-sm font-bold mt-1 flex items-center gap-1.5 <?= $ada_qris ? 'text-text-1' : 'text-red-400' ?>">
                            📱 QRIS / QR Code
                        </span>
                        <?php if (!$ada_qris): ?>
                        <span class="mt-1 text-[10px] font-bold text-red-500 flex items-center gap-1">⛔ Belum tersedia</span>
                        <?php endif; ?>
                    </label>

                    <!-- Option 3: GoPay -->
                    <label class="<?= $ada_gopay ? $cls_available : $cls_unavailable ?>" id="label-gopay">
                        <input type="radio" name="metode_pembayaran" value="gopay"
                            <?= (!$ada_gopay) ? 'disabled' : '' ?>
                            onchange="togglePaymentMethod('gopay')"
                            class="sr-only">
                        <span class="text-xs text-text-3 font-semibold">E-Wallet</span>
                        <span class="text-sm font-bold mt-1 flex items-center gap-1.5 <?= $ada_gopay ? 'text-text-1' : 'text-red-400' ?>">
                            🟢 GoPay
                        </span>
                        <?php if (!$ada_gopay): ?>
                        <span class="mt-1 text-[10px] font-bold text-red-500 flex items-center gap-1">⛔ Belum tersedia</span>
                        <?php endif; ?>
                    </label>

                    <!-- Option 4: OVO -->
                    <label class="<?= $ada_ovo ? $cls_available : $cls_unavailable ?>" id="label-ovo">
                        <input type="radio" name="metode_pembayaran" value="ovo"
                            <?= (!$ada_ovo) ? 'disabled' : '' ?>
                            onchange="togglePaymentMethod('ovo')"
                            class="sr-only">
                        <span class="text-xs text-text-3 font-semibold">E-Wallet</span>
                        <span class="text-sm font-bold mt-1 flex items-center gap-1.5 <?= $ada_ovo ? 'text-text-1' : 'text-red-400' ?>">
                            🟣 OVO
                        </span>
                        <?php if (!$ada_ovo): ?>
                        <span class="mt-1 text-[10px] font-bold text-red-500 flex items-center gap-1">⛔ Belum tersedia</span>
                        <?php endif; ?>
                    </label>

                    <!-- Option 5: DANA -->
                    <label class="<?= $ada_dana ? $cls_available : $cls_unavailable ?>" id="label-dana">
                        <input type="radio" name="metode_pembayaran" value="dana"
                            <?= (!$ada_dana) ? 'disabled' : '' ?>
                            onchange="togglePaymentMethod('dana')"
                            class="sr-only">
                        <span class="text-xs text-text-3 font-semibold">E-Wallet</span>
                        <span class="text-sm font-bold mt-1 flex items-center gap-1.5 <?= $ada_dana ? 'text-text-1' : 'text-red-400' ?>">
                            🔵 DANA
                        </span>
                        <?php if (!$ada_dana): ?>
                        <span class="mt-1 text-[10px] font-bold text-red-500 flex items-center gap-1">⛔ Belum tersedia</span>
                        <?php endif; ?>
                    </label>

                    <!-- Option 6: Uang Tunai (always available) -->
                    <label class="<?= $cls_available ?>" id="label-cash">
                        <input type="radio" name="metode_pembayaran" value="cash"
                            <?= (!$ada_bank && !$ada_qris && !$ada_ewallet) ? 'checked' : '' ?>
                            onchange="togglePaymentMethod('cash')"
                            class="sr-only">
                        <span class="text-xs text-text-3 font-semibold">Langsung</span>
                        <span class="text-sm font-bold text-text-1 mt-1 flex items-center gap-1.5">
                            💵 Uang Tunai
                        </span>
                    </label>

                </div>

                <!-- Info Box: Payment Details -->
                <div id="info-payment" class="bg-input rounded-xl p-4 border border-gray-100 mb-4 transition-all duration-300 hidden">
                    <p class="text-xs font-bold text-primary mb-1 uppercase tracking-widest">ℹ️ Informasi</p>
                    <p class="text-sm font-semibold text-text-1">Instruksi transfer, nominal pasti (termasuk kode unik), dan informasi rekening/QRIS Kantin akan ditampilkan di halaman selanjutnya setelah pesanan dibuat.</p>
                </div>

            </div>
            
            <!-- Pesan jika total Rp 0 -->
            <div id="lunas-message" class="hidden mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex-col gap-2 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-xl leading-none">🎉</span>
                    <p class="text-sm font-bold text-green-700">Pesanan Anda Sepenuhnya Gratis!</p>
                </div>
                <p class="text-xs text-green-600 mt-1 font-medium">Seluruh total pesanan Anda telah ditutupi oleh poin. Anda tidak perlu memilih metode pembayaran atau melakukan transfer.</p>
                <input type="hidden" name="metode_pembayaran" id="metode_hidden_0" value="cash" disabled>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md flex justify-center items-center gap-2">
                Buat Pesanan
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </form>

    </div>
</div>

<script>
    const paymentMethods = ['transfer', 'qr', 'gopay', 'ovo', 'dana', 'cash'];

    function togglePaymentMethod(method) {
        paymentMethods.forEach(m => {
            const label = document.getElementById('label-' + m);
            if (!label) return;
            const radio = label.querySelector('input[type=radio]');
            // Skip disabled options
            if (radio && radio.disabled) return;
            if (m === method) {
                label.classList.add('border-primary/20', 'bg-primary/5');
                label.classList.remove('border-gray-100');
                const span = label.querySelector('.text-sm');
                if (span) { span.classList.add('text-primary'); span.classList.remove('text-text-1'); }
            } else {
                label.classList.remove('border-primary/20', 'bg-primary/5');
                label.classList.add('border-gray-100');
                const span = label.querySelector('.text-sm');
                if (span) { span.classList.remove('text-primary'); span.classList.add('text-text-1'); }
            }
        });
        
        const infoBox = document.getElementById('info-payment');
        if (method === 'cash') {
            infoBox.classList.add('hidden');
        } else {
            infoBox.classList.remove('hidden');
        }
    }

    // Auto-highlight the checked option on page load
    document.addEventListener('DOMContentLoaded', function() {
        const checked = document.querySelector('input[name="metode_pembayaran"]:checked');
        if (checked) togglePaymentMethod(checked.value);
    });

        const originalTotal = <?= $total_harga ?>;
        const poinUser = <?= $poin_user ?>;

        function updateTotal() {
            let finalTotal = originalTotal;
            let potongan = 0;
            const cbPoin = document.getElementById('checkbox_poin');
            const infoPotongan = document.getElementById('info_potongan');
            const displayPotongan = document.getElementById('display_potongan');
            const displayTotal = document.getElementById('display_total');
            const paymentSection = document.getElementById('metode-pembayaran-section');
            const lunasMessage = document.getElementById('lunas-message');
            const hiddenMethod = document.getElementById('metode_hidden_0');

            if (cbPoin && cbPoin.checked) {
                // Potong sebanyak poin yang dimiliki (max sebesar total_harga)
                potongan = Math.min(poinUser, originalTotal);
                finalTotal = originalTotal - potongan;
                
                infoPotongan.classList.remove('hidden');
                displayPotongan.textContent = potongan.toLocaleString('id-ID');
            } else {
                if (infoPotongan) infoPotongan.classList.add('hidden');
            }

            if (displayTotal) {
                displayTotal.textContent = 'Rp ' + finalTotal.toLocaleString('id-ID');
            }
            
            // Toggle payment section if total is 0
            if (finalTotal <= 0) {
                if (paymentSection) paymentSection.classList.add('hidden');
                if (lunasMessage) {
                    lunasMessage.classList.remove('hidden');
                    lunasMessage.classList.add('flex');
                }
                if (hiddenMethod) hiddenMethod.disabled = false;
            } else {
                if (paymentSection) paymentSection.classList.remove('hidden');
                if (lunasMessage) {
                    lunasMessage.classList.add('hidden');
                    lunasMessage.classList.remove('flex');
                }
                if (hiddenMethod) hiddenMethod.disabled = true;
            }
        }

    function validateCheckoutForm() {
        // Karena bukti bayar dipindah ke halaman pesanan.php, tidak perlu validasi disini
        return true;
    }
</script>
</body>
</html>
