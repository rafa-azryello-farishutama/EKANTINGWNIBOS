<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'pembeli'){
    header("Location: ../index.php");
    exit;
}

$halaman = basename($_SERVER['PHP_SELF']);

// Periksa apakah kantin tertentu dipilih (ada di URL)
$id_toko_selected = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : null;
$store_details = null;

if ($id_toko_selected) {
    // Ambil detail kantin
    $qStore = $db_ekantin->prepare("SELECT * FROM toko WHERE id_toko = ?");
    $qStore->bind_param("i", $id_toko_selected);
    $qStore->execute();
    $resStore = $qStore->get_result();
    if ($resStore->num_rows > 0) {
        $store_details = $resStore->fetch_assoc();
    } else {
        // Jika kantin tidak ditemukan, kembali ke halaman awal pesan
        header("Location: pesan.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Menu</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background":     "#fafbf9",
                        "primary":        "#004900",
                        "second-primary": "#f9f9fb",
                        "input":          "#f0f4f0",
                        "text-1":         "#191c1c",
                        "text-2":         "#4e5a48",
                        "text-3":         "#5e6659",
                        "submit":         "#005300"
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%':   { opacity: '0', transform: 'translateY(15px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    },
                    animation: { fadeInUp: 'fadeInUp 0.5s ease-out forwards' }
                }
            }
        }
    </script>
    <style>
        /* Banner utama & toko */
        .banner-wrap      { height: 140px; }
        .banner-title     { font-size: 1.1rem; }
        .banner-deco      { font-size: 4rem; }
        @media (min-width: 640px) {
            .banner-wrap  { height: 180px; }
            .banner-title { font-size: 1.5rem; }
            .banner-deco  { font-size: 6rem; }
        }

        .banner-store-wrap      { height: 110px; }
        .banner-store-title     { font-size: 1rem; }
        .banner-store-deco      { font-size: 3.5rem; }
        @media (min-width: 640px) {
            .banner-store-wrap  { height: 140px; }
            .banner-store-title { font-size: 1.25rem; }
            .banner-store-deco  { font-size: 5rem; }
        }

        /* STORE CARD */
        .store-card {
            padding: 0; gap: 0; flex-direction: column; align-items: stretch; overflow: hidden;
        }
        .store-icon {
            width: 100%; height: 5rem; font-size: 2.5rem; border-radius: 0;
        }
        @media (min-width: 640px) { .store-icon { height: 7rem; font-size: 3rem; } }
        .store-desc { display: none; }
        @media (min-width: 640px) { .store-desc { display: block; } }
        .store-name { font-size: 0.7rem; }
        @media (min-width: 640px) { .store-name { font-size: 0.875rem; } }
        .store-tag { font-size: 0.65rem; }
        @media (min-width: 640px) { .store-tag { font-size: 0.75rem; } }

        /* MENU CARD */
        .menu-img { height: 5rem; font-size: 2rem; }
        @media (min-width: 640px) { .menu-img { height: 7rem; font-size: 2.5rem; } }
        .menu-name { font-size: 0.7rem; }
        .menu-desc { display: none; }
        .menu-price { font-size: 0.7rem; }
        @media (min-width: 640px) {
            .menu-name  { font-size: 0.875rem; }
            .menu-desc  { display: block; font-size: 0.75rem; }
            .menu-price { font-size: 0.875rem; }
        }
        .menu-add-btn { width: 1.5rem; height: 1.5rem; font-size: 1rem; }
        @media (min-width: 640px) { .menu-add-btn { width: 1.75rem; height: 1.75rem; font-size: 1.125rem; } }

        /* Toast */
        #toast {
            left: 1rem; right: 1rem; transform: translateY(80px); text-align: center; border-radius: 12px; width: auto;
        }
        @media (min-width: 640px) {
            #toast {
                left: 50%; right: auto; width: max-content; transform: translateX(-50%) translateY(80px); border-radius: 9999px;
            }
        }
        #toast.show-toast { transform: translateY(0); }
        @media (min-width: 640px) { #toast.show-toast { transform: translateX(-50%) translateY(0); } }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-3 sm:px-8 pb-8 pt-20 lg:pt-8">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-3 md:gap-6">

            <!-- Header -->
            <header class="opacity-0 animate-fadeInUp" style="animation-delay:0.1s;">
                <div class="flex items-center gap-2 md:gap-3">
                    <?php if ($store_details): ?>
                    <a href="pesan.php"
                        class="flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-full bg-input text-text-2
                               hover:bg-primary hover:text-white transition-all duration-200 flex-shrink-0"
                        aria-label="Kembali ke daftar kantin">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-xl bg-input text-2xl md:text-3xl flex-shrink-0 ring-2 ring-primary/10 shadow-sm overflow-hidden">
                        <?php 
                            $foto_toko_header = !empty($store_details['foto_toko']) ? "../assets/img_toko/" . $store_details['foto_toko'] : '';
                            if ($foto_toko_header):
                        ?>
                            <img src="<?= htmlspecialchars($foto_toko_header) ?>" alt="Logo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-primary font-bold text-xl"><?= strtoupper(substr($store_details['nama_toko'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="min-w-0 flex-grow">
                        <h2 class="font-extrabold text-xl sm:text-3xl md:text-4xl tracking-tight text-primary leading-tight truncate">
                            <?= $store_details ? htmlspecialchars($store_details['nama_toko']) : 'Pesan Menu' ?>
                        </h2>
                        <p class="text-text-3 mt-0.5 text-xs sm:text-sm truncate">
                            <?= $store_details ? htmlspecialchars($store_details['lokasi'] ?? 'Kantin Sekolah') : 'Pilih kantin dan menu favoritmu' ?>
                        </p>
                    </div>
                </div>
            </header>

            <?php if (!$store_details): ?>
            <!-- VIEW 1: Daftar Toko -->
            <!-- BANNER UTAMA -->
            <div class="opacity-0 animate-fadeInUp" style="animation-delay:0.2s;">
                <div class="relative rounded-xl sm:rounded-2xl overflow-hidden select-none banner-wrap bg-gradient-to-br from-primary to-[#00a800]">
                    <div class="min-w-full h-full relative flex items-center px-4 sm:px-8">
                        <div class="relative z-10 max-w-[65%] sm:max-w-none">
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1.5 sm:mb-3"
                                  style="background:rgba(255,255,255,0.2);color:#fff;">🔥 Kantin Pilihan</span>
                            <h3 class="text-white font-extrabold leading-tight banner-title">Temukan Makanan<br>Favoritmu Hari Ini!</h3>
                            <p class="text-green-200 text-xs mt-1">Berbagai pilihan kantin sekolah tersedia.</p>
                        </div>
                        <div class="absolute right-3 sm:right-6 bottom-0 opacity-30 leading-none banner-deco">🍱</div>
                    </div>
                </div>
            </div>

            <!-- STORE GRID -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 md:gap-4 mt-2">
                <?php
                $qKantin = $db_ekantin->query("SELECT t.*, (SELECT COUNT(id_produk) FROM produk_kantin WHERE id_toko = t.id_toko) as total_menu FROM toko t");
                $i = 0;
                if ($qKantin && $qKantin->num_rows > 0):
                    while ($kantin = $qKantin->fetch_assoc()):
                        $i++;
                        $foto_toko  = $kantin['foto_toko'] ?? null;
                        $foto_src   = $foto_toko ? "../assets/img_toko/$foto_toko" : null;
                        $initial    = strtoupper(substr($kantin['nama_toko'], 0, 1));
                ?>
                <a href="pesan.php?id_toko=<?= $kantin['id_toko'] ?>"
                    class="store-card opacity-0 animate-fadeInUp text-left bg-white rounded-xl border border-gray-100
                           hover:border-primary/40 hover:shadow-md active:scale-[0.98]
                           transition-all duration-200 flex group block"
                    style="animation-delay:<?= 0.15 + ($i * 0.05) ?>s;">
                    <div class="store-icon bg-input flex items-center justify-center group-hover:bg-primary/10 transition-colors duration-200 overflow-hidden relative">
                        <?php if ($foto_src): ?>
                            <img src="<?= $foto_src ?>" alt="Store" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        <?php else: ?>
                            <span class="text-primary font-bold text-3xl"><?= $initial ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-2 sm:p-3 flex flex-col flex-grow">
                        <p class="store-name font-semibold text-text-1 leading-snug truncate"><?= htmlspecialchars($kantin['nama_toko']) ?></p>
                        <span class="store-tag inline-block mt-1 px-1.5 py-0.5 rounded-full bg-input text-text-3 font-medium self-start">
                            <?= $kantin['total_menu'] ?> Menu
                        </span>
                        <p class="store-desc text-xs text-text-3 mt-1.5 leading-relaxed line-clamp-2">
                            <?= htmlspecialchars($kantin['lokasi'] ?? 'Berbagai macam makanan dan minuman.') ?>
                        </p>
                    </div>
                </a>
                <?php endwhile; else: ?>
                    <div class="col-span-full text-center text-text-3 py-10 text-sm">Belum ada kantin yang terdaftar.</div>
                <?php endif; ?>
            </div>


            <?php else: ?>
            <!-- VIEW 2: Produk Toko -->
            <div class="flex flex-col gap-3 md:gap-6">
                <!-- BANNER TOKO -->
                <div class="relative rounded-xl sm:rounded-2xl overflow-hidden select-none banner-store-wrap bg-gradient-to-br from-[#4a2800] to-[#b86600]">
                    <div class="min-w-full h-full relative flex items-center px-4 sm:px-7 overflow-hidden">
                        <div class="relative z-10 max-w-[70%] sm:max-w-none">
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
                                  style="background:rgba(255,255,255,0.2);color:#fff;">✨ Menu Spesial</span>
                            <h3 class="text-white font-extrabold leading-tight banner-store-title">Pesan Makanan<br>Dari Kantin Ini</h3>
                            <p class="text-white/70 text-xs mt-1">Lihat dan pilih menu favoritmu di bawah.</p>
                        </div>
                        <div class="absolute right-3 sm:right-5 bottom-0 opacity-25 leading-none banner-store-deco">🍽️</div>
                    </div>
                </div>

                <!-- MENU GRID -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 md:gap-4">
                    <?php
                    $qMenu = $db_ekantin->prepare("SELECT * FROM produk_kantin WHERE id_toko = ?");
                    $qMenu->bind_param("i", $id_toko_selected);
                    $qMenu->execute();
                    $resMenu = $qMenu->get_result();
                    $j = 0;
                    
                    if ($resMenu->num_rows > 0):
                        while ($menu = $resMenu->fetch_assoc()):
                            $j++;
                            $foto_produk = $menu['file_foto'] ?? null;
                            $foto_menu_src = $foto_produk ? "../assets/img_produk/$foto_produk" : null;
                    ?>
                        <div class="opacity-0 bg-white rounded-xl border border-gray-100 overflow-hidden
                                    hover:border-primary/30 hover:shadow-sm transition-all duration-200 flex flex-col"
                             style="animation:fadeInUp 0.4s ease-out <?= $j * 0.05 ?>s forwards;">
                            <div class="menu-img bg-input flex items-center justify-center overflow-hidden relative">
                                <?php if ($foto_menu_src): ?>
                                    <img src="<?= htmlspecialchars($foto_menu_src) ?>" alt="Menu" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-3xl">🍽️</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-2 sm:p-3 flex flex-col flex-grow">
                                <p class="menu-name font-semibold text-text-1 leading-snug"><?= htmlspecialchars($menu['nama_menu']) ?></p>
                                <p class="menu-desc text-text-3 mt-1 leading-relaxed flex-grow line-clamp-2">
                                    Stok: <?= $menu['stok'] ?> <?= $menu['tipe_produk'] == 'makanan' ? 'porsi' : 'gelas' ?>
                                </p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="menu-price text-primary font-bold">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></span>
                                    
                                    <?php 
                                        $btn_add = '<button onclick="addToCart('.$menu['id_produk'].', \''.addslashes($menu['nama_menu']).'\', '.$menu['harga'].')" class="w-7 h-7 rounded-full bg-primary text-white font-light flex items-center justify-center hover:bg-submit active:scale-95 transition-all duration-150 shadow-sm">+</button>';
                                    ?>
                                    <div class="flex items-center gap-1.5 min-h-[28px]" id="btn-group-<?= $menu['id_produk'] ?>" data-original="<?= htmlspecialchars($btn_add) ?>" data-stok="<?= $menu['stok'] ?>">
                                        <button onclick="addToCart(<?= $menu['id_produk'] ?>, '<?= addslashes($menu['nama_menu']) ?>', <?= $menu['harga'] ?>)"
                                            class="w-7 h-7 rounded-full bg-primary text-white font-light flex items-center justify-center hover:bg-submit active:scale-95 transition-all duration-150 shadow-sm"
                                            aria-label="Tambah Menu">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <div class="col-span-full bg-white rounded-xl border border-gray-100 p-8 text-center text-text-3 text-sm">
                            Menu belum tersedia di kantin ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.max-w-5xl -->
    </main>
</div>

<!-- Sticky Checkout Bar -->
<div id="sticky-cart" class="fixed bottom-0 left-0 right-0 bg-white shadow-[0_-4px_20px_rgba(0,0,0,0.1)] border-t border-gray-100 z-40 transform translate-y-full transition-transform duration-300 lg:pl-80">
    <div class="max-w-5xl mx-auto px-4 sm:px-8 py-3 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-[10px] sm:text-xs text-text-3 font-medium uppercase tracking-wider">Total Pesanan</span>
            <div class="flex items-center gap-2 mt-0.5">
                <span id="cart-total-items" class="bg-primary text-white text-xs font-bold px-2 py-0.5 rounded-full">0 item</span>
                <span id="cart-total-price" class="text-text-1 font-extrabold text-base sm:text-lg">Rp 0</span>
            </div>
        </div>
        
        <form action="checkout.php" method="POST" id="checkout-form">
            <input type="hidden" name="cart_data" id="cart-data-input">
            <input type="hidden" name="id_toko" value="<?= htmlspecialchars($id_toko_selected) ?>">
            <button type="submit" id="btn-checkout" class="bg-primary text-white font-bold text-xs sm:text-sm px-5 sm:px-6 py-2.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md flex items-center gap-2">
                Bayar Langsung
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>
</div>

<!-- Toast -->
<div id="toast"
     class="fixed bottom-[80px] sm:bottom-[90px] opacity-0 transition-all duration-300 bg-primary text-white text-xs sm:text-sm font-medium px-4 py-2.5 shadow-lg pointer-events-none z-50">
</div>

<script>
    let cart = {}; // Format: { id: {name, price, qty} }
    const fmt = n => "Rp " + n.toLocaleString("id-ID");
    
    function addToCart(id, name, price) {
        let btnGroup = document.getElementById("btn-group-" + id);
        let stok = parseInt(btnGroup.getAttribute("data-stok"));

        let currentQty = cart[id] ? cart[id].qty : 0;

        if (currentQty >= stok) {
            showToast("Stok " + name + " tidak cukup ⚠️");
            return;
        }

        if (!cart[id]) {
            cart[id] = { id: id, name: name, price: price, qty: 1 };
        } else {
            cart[id].qty += 1;
        }
        updateCartUI();
        showToast(name + " ditambahkan 🛒");
    }

    function removeFromCart(id) {
        if (cart[id]) {
            cart[id].qty -= 1;
            if (cart[id].qty <= 0) {
                delete cart[id];
            }
        }
        updateCartUI();
    }

    function updateCartUI() {
        let totalItems = 0;
        let totalPrice = 0;
        
        // 1. Hitung total dan Update tombol di tiap menu
        Object.keys(cart).forEach(id => {
            let item = cart[id];
            totalItems += item.qty;
            totalPrice += item.price * item.qty;
            
            // Ubah tombol [+] menjadi [-] QTY [+]
            let btnGroup = document.getElementById("btn-group-" + id);
            if (btnGroup) {
                btnGroup.innerHTML = `
                    <button onclick="removeFromCart(${id})" class="w-7 h-7 rounded-full bg-input text-primary font-bold flex items-center justify-center hover:bg-gray-200 transition-colors">-</button>
                    <span class="text-sm font-bold w-5 text-center text-text-1">${item.qty}</span>
                    <button onclick="addToCart(${id}, '${item.name.replace(/'/g,"\\'")}', ${item.price})" class="w-7 h-7 rounded-full bg-primary text-white font-bold flex items-center justify-center hover:bg-submit transition-colors shadow-sm">+</button>
                `;
            }
            
            let stok = parseInt(btnGroup.getAttribute("data-stok"));

            let btnPlus = btnGroup.querySelector("button:last-child");
            if (item.qty >= stok) {
                btnPlus.disabled = true;
                btnPlus.classList.add("opacity-40", "cursor-not-allowed");
            }
            let card = btnGroup.closest('.bg-white');
            if (card) card.classList.add('border-primary', 'ring-1', 'ring-primary/30');
        });

        // 2. Kembalikan tombol ke semula jika item sudah dihapus dari keranjang (qty = 0)
        document.querySelectorAll('[id^="btn-group-"]').forEach(btnGroup => {
            let id = btnGroup.id.replace('btn-group-', '');
            if (!cart[id]) {
                let originalBtn = btnGroup.getAttribute('data-original');
                if(originalBtn) btnGroup.innerHTML = originalBtn;

                let card = btnGroup.closest('.bg-white');
                if (card) card.classList.remove('border-primary', 'ring-1', 'ring-primary/30');
            }
        });

        // 3. Tampilkan / Sembunyikan Sticky Bar
        const stickyCart = document.getElementById("sticky-cart");
        const elTotalItems = document.getElementById("cart-total-items");
        const elTotalPrice = document.getElementById("cart-total-price");
        const cartInput = document.getElementById("cart-data-input");

        if (totalItems > 0) {
            stickyCart.classList.remove("translate-y-full");
            elTotalItems.textContent = totalItems + " item";
            elTotalPrice.textContent = fmt(totalPrice);
            // Simpan data keranjang ke hidden input form
            cartInput.value = JSON.stringify(cart);
        } else {
            stickyCart.classList.add("translate-y-full");
            cartInput.value = "";
        }
    }

    /* ── Toast Notification ── */
    let toastTimer;
    function showToast(msg) {
        const t = document.getElementById("toast");
        t.textContent = msg;
        t.classList.remove("opacity-0");
        t.classList.add("show-toast", "opacity-100");
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            t.classList.remove("show-toast", "opacity-100");
            t.classList.add("opacity-0");
        }, 1500);
    }
</script>
</body>
</html>