<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>
<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'pembeli'){
    header("Location: ../index.php");
    exit;
}

$id_users = (int) $_SESSION['id_users'];

// Hapus seluruh isi satu kantin dari keranjang
if (isset($_GET['hapus_toko']) && is_numeric($_GET['hapus_toko'])) {
    $id_toko_del = (int) $_GET['hapus_toko'];
    $stmt = $db_ekantin->prepare("DELETE FROM keranjang WHERE id_users = ? AND id_toko = ?");
    $stmt->bind_param("ii", $id_users, $id_toko_del);
    $stmt->execute();
    header("Location: keranjang.php");
    exit;
}

// Ambil semua item keranjang, diurutkan per kantin
$sql = "SELECT k.id_keranjang, k.id_produk, k.id_toko, k.jumlah,
               p.nama_menu, p.harga, p.file_foto, p.tipe_produk, p.stok,
               t.nama_toko
        FROM keranjang k
        JOIN produk_kantin p ON k.id_produk = p.id_produk
        JOIN toko t          ON k.id_toko   = t.id_toko
        WHERE k.id_users = ?
        ORDER BY k.id_toko, k.disimpan_at ASC";

$stmt = $db_ekantin->prepare($sql);
$stmt->bind_param("i", $id_users);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Kelompokkan item per kantin
$per_toko = [];
foreach ($items as $item) {
    $per_toko[$item['id_toko']]['nama_toko'] = $item['nama_toko'];
    $per_toko[$item['id_toko']]['id_toko']   = $item['id_toko'];
    $per_toko[$item['id_toko']]['items'][]   = $item;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "background": "#fafbf9", "primary": "#004900",
                        "input": "#f0f4f0", "text-1": "#191c1c",
                        "text-2": "#4e5a48", "text-3": "#5e6659", "submit": "#005300"
                    },
                    keyframes: { fadeInUp: { '0%': { opacity: '0', transform: 'translateY(15px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } } },
                    animation: { fadeInUp: 'fadeInUp 0.5s ease-out forwards' }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-10 pt-20 lg:pt-8">
        <div class="w-full max-w-xl mx-auto flex flex-col gap-6">

            <!-- Header -->
            <header class="opacity-0 animate-fadeInUp" style="animation-delay:0.1s;">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Keranjang</h2>
                <p class="text-text-3 mt-1 text-sm">Setiap kantin memiliki struk pesanan tersendiri</p>
            </header>

            <?php if (empty($per_toko)): ?>
            <!-- Kosong -->
            <div class="opacity-0 animate-fadeInUp flex flex-col items-center py-20 gap-4 text-center" style="animation-delay:0.2s;">
                <div class="w-20 h-20 bg-input rounded-full flex items-center justify-center text-4xl">🛒</div>
                <p class="font-bold text-text-1 text-lg">Keranjang Kosong</p>
                <p class="text-text-3 text-sm">Pilih menu lalu tekan "Tambah ke Keranjang" untuk menyimpan pesanan di sini.</p>
                <a href="pesan.php" class="mt-2 bg-primary text-white font-bold text-sm px-6 py-2.5 rounded-xl hover:bg-submit transition-colors shadow-md">
                    Pilih Menu Sekarang
                </a>
            </div>

            <?php else: ?>
            <!-- STRUK PER KANTIN -->
            <?php $delay = 0.15; foreach ($per_toko as $id_toko => $toko): 
                $total_toko = 0;
                foreach ($toko['items'] as $it) $total_toko += $it['harga'] * $it['jumlah'];
            ?>
            <div class="opacity-0 animate-fadeInUp" style="animation-delay:<?= $delay ?>s;" id="struk-<?= $id_toko ?>">
                <?php $delay += 0.1; ?>

                <!-- Card Struk -->
                <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden">

                    <!-- Header Struk: Nama Kantin -->
                    <div class="flex items-center justify-between px-5 py-3.5 bg-primary/5 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🏪</span>
                            <span class="font-bold text-primary text-sm"><?= htmlspecialchars($toko['nama_toko']) ?></span>
                        </div>
                        <a href="keranjang.php?hapus_toko=<?= $id_toko ?>"
                           onclick="return confirm('Hapus semua pesanan dari <?= htmlspecialchars($toko['nama_toko']) ?>?')"
                           class="text-xs font-medium text-red-400 hover:text-red-600 transition-colors">
                            Hapus Semua
                        </a>
                    </div>

                    <!-- Daftar Item dengan kontrol +/- -->
                    <div class="divide-y divide-gray-50" id="items-toko-<?= $id_toko ?>">
                        <?php foreach ($toko['items'] as $item):
                            $subtotal = $item['harga'] * $item['jumlah'];
                            $foto_src = $item['file_foto'] ? "../assets/img_produk/{$item['file_foto']}" : null;
                        ?>
                        <div class="flex items-center gap-3 px-5 py-3 group" id="row-<?= $item['id_keranjang'] ?>">

                            <!-- Foto -->
                            <div class="w-10 h-10 flex-shrink-0 rounded-lg bg-input overflow-hidden flex items-center justify-center text-lg">
                                <?php if ($foto_src): ?>
                                    <img src="<?= htmlspecialchars($foto_src) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= $item['tipe_produk'] == 'makanan' ? '🍱' : '🥤' ?>
                                <?php endif; ?>
                            </div>

                            <!-- Nama + Harga satuan -->
                            <div class="flex-grow min-w-0">
                                <p class="font-semibold text-text-1 text-sm leading-snug truncate"><?= htmlspecialchars($item['nama_menu']) ?></p>
                                <p class="text-xs text-text-3">@ Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                            </div>

                            <!-- Kontrol Qty + Subtotal -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <!-- Tombol - -->
                                <button onclick="ubahQty(<?= $item['id_keranjang'] ?>, -1, <?= $id_toko ?>)"
                                    class="w-7 h-7 rounded-full bg-input text-primary font-bold flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-colors text-base">−</button>

                                <!-- Jumlah (editable) -->
                                <span class="font-bold text-sm w-5 text-center text-text-1" id="qty-<?= $item['id_keranjang'] ?>"><?= $item['jumlah'] ?></span>

                                <!-- Tombol + -->
                                <button onclick="ubahQty(<?= $item['id_keranjang'] ?>, 1, <?= $id_toko ?>)"
                                    class="w-7 h-7 rounded-full bg-primary text-white font-bold flex items-center justify-center hover:bg-submit transition-colors text-base">+</button>

                                <!-- Subtotal -->
                                <span class="font-bold text-sm text-text-1 w-20 text-right" id="sub-<?= $item['id_keranjang'] ?>">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Total per Kantin -->
                    <div class="px-5 py-4 border-t border-dashed border-gray-200 flex items-center justify-between" id="total-row-<?= $id_toko ?>">
                        <span class="font-bold text-text-1">Total Pembayaran</span>
                        <span class="font-extrabold text-xl text-primary" id="total-toko-<?= $id_toko ?>">Rp <?= number_format($total_toko, 0, ',', '.') ?></span>
                    </div>
                </div>

                <!-- Tombol Aksi per Kantin -->
                <div class="flex gap-3 mt-3">
                    <a href="pesan.php?id_toko=<?= $id_toko ?>" class="flex-1 border border-primary text-primary font-bold text-xs sm:text-sm py-2.5 rounded-xl hover:bg-primary/5 transition-colors text-center">
                        + Tambah Menu
                    </a>
                    <form action="checkout.php" method="POST" class="flex-1">
                        <?php
                        $cart_co = [];
                        foreach ($toko['items'] as $it) {
                            $cart_co[$it['id_produk']] = [
                                'id' => $it['id_produk'], 'name' => $it['nama_menu'],
                                'price' => $it['harga'],  'qty'  => $it['jumlah'],
                            ];
                        }
                        ?>
                        <input type="hidden" name="cart_data" class="checkout-cart-data-<?= $id_toko ?>" value="<?= htmlspecialchars(json_encode($cart_co)) ?>">
                        <input type="hidden" name="id_toko" value="<?= $id_toko ?>">
                        <button type="submit" class="w-full bg-primary text-white font-bold text-xs sm:text-sm py-2.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md">
                            Bayar Sekarang
                        </button>
                    </form>
                </div>

            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-5 left-1/2 -translate-x-1/2 opacity-0 transition-all duration-300 bg-primary text-white text-xs sm:text-sm font-medium px-5 py-2.5 rounded-full shadow-lg pointer-events-none z-50 whitespace-nowrap"></div>

<script>
    // Menyimpan data qty lokal agar perhitungan cepat tanpa reload
    const hargaPerItem = {
        <?php
        foreach ($items as $item) {
            echo $item['id_keranjang'] . ': ' . $item['harga'] . ',';
        }
        ?>
    };

    // qtyLokal: simpan qty terkini per id_keranjang
    const qtyLokal = {
        <?php
        foreach ($items as $item) {
            echo $item['id_keranjang'] . ': ' . $item['jumlah'] . ',';
        }
        ?>
    };

    // stokPerItem: simpan stok maksimum dari database
    const stokPerItem = {
        <?php
        foreach ($items as $item) {
            echo $item['id_keranjang'] . ': ' . $item['stok'] . ',';
        }
        ?>
    };

    function ubahQty(id_keranjang, delta, id_toko) {
        // Cek stok jika mencoba menambah
        if (delta > 0 && qtyLokal[id_keranjang] >= stokPerItem[id_keranjang]) {
            showToast("Stok tidak cukup ⚠️");
            return;
        }

        qtyLokal[id_keranjang] = (qtyLokal[id_keranjang] || 0) + delta;

        if (qtyLokal[id_keranjang] < 0) qtyLokal[id_keranjang] = 0;

        const formData = new FormData();
        formData.append("aksi", "update_jumlah");
        formData.append("id_keranjang", id_keranjang);
        formData.append("jumlah", qtyLokal[id_keranjang]);

        fetch("ajax_keranjang.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.aksi === "dihapus") {
                    // Hapus baris dari tampilan
                    const row = document.getElementById("row-" + id_keranjang);
                    row.style.transition = "opacity 0.25s, transform 0.25s";
                    row.style.opacity = "0";
                    row.style.transform = "translateX(15px)";
                    setTimeout(() => {
                        row.remove();
                        hitungUlangTotal(id_toko);
                        // Jika toko sudah kosong, hapus seluruh card struk
                        const container = document.getElementById("items-toko-" + id_toko);
                        if (container && container.children.length === 0) {
                            document.getElementById("struk-" + id_toko).remove();
                            // Jika tidak ada struk lagi, reload untuk tampil kosong
                            if (!document.querySelector('[id^="struk-"]')) location.reload();
                        }
                    }, 270);
                } else {
                    // Update tampilan angka qty dan subtotal
                    document.getElementById("qty-" + id_keranjang).textContent = qtyLokal[id_keranjang];
                    const subtotal = hargaPerItem[id_keranjang] * qtyLokal[id_keranjang];
                    document.getElementById("sub-" + id_keranjang).textContent = "Rp " + subtotal.toLocaleString("id-ID");
                    hitungUlangTotal(id_toko);
                }
            });
    }

    function hitungUlangTotal(id_toko) {
        // Hitung ulang total dari semua baris yang masih ada
        let total = 0;
        document.querySelectorAll(`#items-toko-${id_toko} [id^="row-"]`).forEach(row => {
            const id_ker = row.id.replace("row-", "");
            total += (hargaPerItem[id_ker] || 0) * (qtyLokal[id_ker] || 0);
        });
        const el = document.getElementById("total-toko-" + id_toko);
        if (el) el.textContent = "Rp " + total.toLocaleString("id-ID");
    }

    let toastTimer;
    function showToast(msg) {
        const t = document.getElementById("toast");
        t.textContent = msg;
        t.classList.remove("opacity-0");
        t.classList.add("opacity-100");
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { t.classList.remove("opacity-100"); t.classList.add("opacity-0"); }, 1800);
    }
</script>
</body>
</html>