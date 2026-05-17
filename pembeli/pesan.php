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
?>

<?php 
$halaman = basename($_SERVER['PHP_SELF']);
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
        @keyframes slideInRight  { from{opacity:0;transform:translateX(40px)}  to{opacity:1;transform:translateX(0)} }
        @keyframes slideOutLeft  { from{opacity:1;transform:translateX(0)}      to{opacity:0;transform:translateX(-40px)} }
        @keyframes slideInLeft   { from{opacity:0;transform:translateX(-40px)} to{opacity:1;transform:translateX(0)} }
        @keyframes slideOutRight { from{opacity:1;transform:translateX(0)}      to{opacity:0;transform:translateX(40px)} }

        /* Banner utama */
        .banner-wrap      { height: 140px; }
        .banner-title     { font-size: 1.1rem; }
        .banner-deco      { font-size: 4rem; }
        @media (min-width: 640px) {
            .banner-wrap  { height: 180px; }
            .banner-title { font-size: 1.5rem; }
            .banner-deco  { font-size: 6rem; }
        }

        /* Banner toko */
        .banner-store-wrap      { height: 110px; }
        .banner-store-title     { font-size: 1rem; }
        .banner-store-deco      { font-size: 3.5rem; }
        @media (min-width: 640px) {
            .banner-store-wrap  { height: 140px; }
            .banner-store-title { font-size: 1.25rem; }
            .banner-store-deco  { font-size: 5rem; }
        }

        /* ═══ STORE CARD — model kartu menu (icon besar di atas, teks di bawah) ═══ */
        .store-card {
            padding: 0;
            gap: 0;
            flex-direction: column;
            align-items: stretch;
            overflow: hidden;
        }
        .store-card .store-arrow { display: none; }

        /* Area icon toko — full width seperti menu-img */
        .store-icon {
            width: 100%; height: 5rem;
            font-size: 2.5rem;
            border-radius: 0;
        }
        @media (min-width: 640px) { .store-icon { height: 7rem; font-size: 3rem; } }

        /* Deskripsi toko */
        .store-desc { display: none; }
        @media (min-width: 640px) { .store-desc { display: block; } }

        /* Nama toko */
        .store-name { font-size: 0.7rem; }
        @media (min-width: 640px) { .store-name { font-size: 0.875rem; } }

        /* Tag toko */
        .store-tag { font-size: 0.65rem; }
        @media (min-width: 640px) { .store-tag { font-size: 0.75rem; } }

        /* Gambar menu */
        .menu-img { height: 5rem; font-size: 2rem; }
        @media (min-width: 640px) { .menu-img { height: 7rem; font-size: 2.5rem; } }

        /* Teks menu card: lebih compact di mobile */
        .menu-name { font-size: 0.7rem; }
        .menu-desc { display: none; } /* sembunyikan deskripsi di mobile */
        .menu-price { font-size: 0.7rem; }
        @media (min-width: 640px) {
            .menu-name  { font-size: 0.875rem; }
            .menu-desc  { display: block; font-size: 0.75rem; }
            .menu-price { font-size: 0.875rem; }
        }

        /* Tombol + menu */
        .menu-add-btn {
            width: 1.5rem; height: 1.5rem; font-size: 1rem;
        }
        @media (min-width: 640px) {
            .menu-add-btn { width: 1.75rem; height: 1.75rem; font-size: 1.125rem; }
        }

        /* Toast */
        #toast {
            left: 1rem; right: 1rem;
            transform: translateY(80px);
            text-align: center;
            border-radius: 12px;
            width: auto;
        }
        @media (min-width: 640px) {
            #toast {
                left: 50%; right: auto;
                width: max-content;
                transform: translateX(-50%) translateY(80px);
                border-radius: 9999px;
            }
        }
        #toast.show-toast { transform: translateY(0); }
        @media (min-width: 640px) {
            #toast.show-toast { transform: translateX(-50%) translateY(0); }
        }
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
                    <button id="btn-back" onclick="backToStores()"
                        class="hidden items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-full bg-input text-text-2
                               hover:bg-primary hover:text-white transition-all duration-200 flex-shrink-0"
                        aria-label="Kembali ke daftar kantin">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <!-- Logo toko, hanya muncul saat di halaman produk toko -->
                    <div id="store-logo"
                         class="hidden items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-xl bg-input text-2xl md:text-3xl flex-shrink-0
                                ring-2 ring-primary/10 shadow-sm">
                    </div>
                    <div class="min-w-0 flex-grow">
                        <h2 id="page-title" class="font-extrabold text-xl sm:text-3xl md:text-4xl tracking-tight text-primary leading-tight truncate">Pesan Menu</h2>
                        <p id="page-sub" class="text-text-3 mt-0.5 text-xs sm:text-sm truncate">Pilih kantin dan menu favoritmu</p>
                    </div>
                </div>
            </header>

            <!-- ═══ BANNER A — halaman utama ═══ -->
            <div id="banner-main" class="opacity-0 animate-fadeInUp" style="animation-delay:0.2s;">
                <div class="relative rounded-xl sm:rounded-2xl overflow-hidden select-none banner-wrap">
                    <div id="banner-main-track" class="flex h-full transition-transform duration-500 ease-in-out">

                        <!-- Slide 1 -->
                        <div class="min-w-full h-full relative flex items-center px-4 sm:px-8"
                             style="background:linear-gradient(135deg,#004900 0%,#007a00 60%,#00a800 100%);">
                            <div class="relative z-10 max-w-[65%] sm:max-w-none">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1.5 sm:mb-3"
                                      style="background:rgba(255,255,255,0.2);color:#fff;">🔥 Promo Hari Ini</span>
                                <h3 class="text-white font-extrabold leading-tight banner-title">Gratis Minuman<br>untuk Setiap Pemesanan!</h3>
                                <p class="text-green-200 text-xs mt-1">Min. pembelian Rp 20.000</p>
                            </div>
                            <div class="absolute right-3 sm:right-6 bottom-0 opacity-30 leading-none banner-deco">🍱</div>
                        </div>

                    </div><!-- /banner-main-track -->

                    <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 flex gap-1.5" id="banner-main-dots"></div>
                    <button onclick="bannerMainPrev()"
                        class="absolute left-1.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center"
                        style="background:rgba(255,255,255,0.2);" aria-label="Sebelumnya">
                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button onclick="bannerMainNext()"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center"
                        style="background:rgba(255,255,255,0.2);" aria-label="Berikutnya">
                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div><!-- /.relative.rounded-xl -->
            </div><!-- /#banner-main -->

            <!-- ═══ CONTAINER VIEW ═══ -->
            <div class="overflow-hidden">

                <!-- VIEW 1: Daftar Toko -->
                <div id="view-stores">
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 md:gap-4" id="store-grid"></div>
                </div>

                <!-- VIEW 2: Produk Toko -->
                <div id="view-menus" class="hidden flex-col gap-3 md:gap-6">

                    <!-- ═══ BANNER B — dalam toko ═══ -->
                    <div class="relative rounded-xl sm:rounded-2xl overflow-hidden select-none banner-store-wrap">
                        <div id="banner-store-track" class="flex h-full transition-transform duration-500 ease-in-out">
                            <!-- Slides diisi dinamis oleh JS saat openStore() -->
                        </div>
                        <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 flex gap-1.5" id="banner-store-dots"></div>
                        <button onclick="bannerStorePrev()"
                            class="absolute left-1.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center"
                            style="background:rgba(255,255,255,0.2);" aria-label="Sebelumnya">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button onclick="bannerStoreNext()"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center"
                            style="background:rgba(255,255,255,0.2);" aria-label="Berikutnya">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 md:gap-4" id="menu-grid"></div>

                </div><!-- /#view-menus -->

            </div><!-- /.overflow-hidden (container view) -->

        </div><!-- /.max-w-5xl -->
    </main>
</div>

<!-- Toast -->
<div id="toast"
     class="fixed bottom-5 sm:bottom-6 opacity-0 transition-all duration-300 bg-primary text-white text-xs sm:text-sm font-medium px-4 py-2.5 shadow-lg pointer-events-none z-50">
</div>

<script>
    /* ══════════════════════════════════════════════════
       DATA — di proyek nyata ini dari DB via PHP/API
    ══════════════════════════════════════════════════ */
    const stores = [
        {
            id: 1, name: "Kantin Pak Budi", img: "", tag: "Nasi & Lauk",
            desc: "Nasi rumahan dengan berbagai pilihan lauk pauk",
            banners: [
                { bg: "linear-gradient(135deg,#5c3000 0%,#8b4800 60%,#b05a00 100%)", badge: "🍛 Menu Andalan", title: "Nasi Rumahan<br>Rasa Bunda!", sub: "Lauk lengkap, harga bersahabat", img: "../assets/img/banners/pak-budi-1.png" },
                { bg: "linear-gradient(135deg,#3d2000 0%,#7a4200 60%,#a05500 100%)", badge: "🔥 Terlaris",      title: "Nasi Rendang Sapi<br>Empuk & Gurih!",  sub: "Favorit pelanggan setia",    img: "../assets/img/banners/pak-budi-2.png" },
                { bg: "linear-gradient(135deg,#4a2800 0%,#8c5000 60%,#b86600 100%)", badge: "💰 Hemat",         title: "Paket Lengkap<br>Mulai Rp 9.000!",    sub: "Cocok buat kantong pelajar", img: "../assets/img/banners/pak-budi-3.png" },
            ]
        },
        {
            id: 2, name: "Warung Bu Sari", img: "", tag: "Mie & Bakso",
            desc: "Mie ayam dan bakso sapi segar setiap hari",
            banners: [
                { bg: "linear-gradient(135deg,#1a0a00 0%,#5c2000 60%,#8c3800 100%)", badge: "🍲 Spesial",      title: "Bakso Jumbo Sapi<br>Kuah Gurih Segar!", sub: "Daging sapi pilihan setiap hari", img: "../assets/img/banners/bu-sari-1.png" },
                { bg: "linear-gradient(135deg,#2a0d00 0%,#6b2c00 60%,#994000 100%)", badge: "🌶️ Ekstra Pedas", title: "Bakso Mercon<br>Berani Coba?!",        sub: "Level pedas 1–5, siap tantang!",  img: "../assets/img/banners/bu-sari-2.png" },
                { bg: "linear-gradient(135deg,#1f0800 0%,#4f1c00 60%,#7a2e00 100%)", badge: "✨ Favorit",       title: "Mie Ayam Bu Sari<br>Resep Rahasia!",   sub: "Bumbu spesial turun-temurun",     img: "../assets/img/banners/bu-sari-3.png" },
            ]
        },
        {
            id: 3, name: "Pojok Sehat", img: "", tag: "Sehat & Segar",
            desc: "Menu sehat, salad, dan jus buah pilihan",
            banners: [
                { bg: "linear-gradient(135deg,#003320 0%,#005c38 60%,#008050 100%)", badge: "🥗 Fresh",         title: "Hidup Sehat<br>Dimulai dari Sini!",    sub: "Bahan segar langsung dari petani", img: "../assets/img/banners/pojok-sehat-1.png" },
                { bg: "linear-gradient(135deg,#004020 0%,#007040 60%,#009955 100%)", badge: "🥤 Jus Segar",     title: "Jus Buah Tanpa<br>Pengawet & Gula!",   sub: "100% buah asli, bebas tambahan",   img: "../assets/img/banners/pojok-sehat-2.png" },
                { bg: "linear-gradient(135deg,#002b18 0%,#005030 60%,#007045 100%)", badge: "💪 Bergizi",       title: "Sandwich Gandum<br>Bergizi Tinggi!",   sub: "Cocok untuk sarapan & bekal",      img: "../assets/img/banners/pojok-sehat-3.png" },
            ]
        },
        {
            id: 4, name: "Kantin Minuman", img: "", tag: "Minuman",
            desc: "Minuman dingin dan hangat, boba hingga kopi",
            banners: [
                { bg: "linear-gradient(135deg,#00204a 0%,#003d8c 60%,#0055c8 100%)", badge: "🧋 Boba Hits",    title: "Boba & Matcha<br>Kini Hadir!",         sub: "Pearl tapioka kenyal, segar banget", img: "../assets/img/banners/minuman-1.png" },
                { bg: "linear-gradient(135deg,#1a0a30 0%,#3d1a70 60%,#5c28a8 100%)", badge: "☕ Kopi Pilihan",  title: "Es Kopi Susu<br>Arabika Premium!",     sub: "Biji kopi pilihan, susu segar",      img: "../assets/img/banners/minuman-2.png" },
                { bg: "linear-gradient(135deg,#003050 0%,#005580 60%,#007ab5 100%)", badge: "🍵 Hangat",        title: "Teh Tarik Creamy<br>Bikin Nagih!",     sub: "Khas Malaysia, tersedia tiap hari",  img: "../assets/img/banners/minuman-3.png" },
            ]
        },
        {
            id: 5, name: "Gorengan Mang Ujang", img: "", tag: "Gorengan",
            desc: "Gorengan crispy hangat tersedia dari pagi",
            banners: [
                { bg: "linear-gradient(135deg,#3d2600 0%,#7a4c00 60%,#a86800 100%)", badge: "🍟 Crispy",        title: "Gorengan Hangat<br>Renyah Setiap Saat!", sub: "Langsung goreng, gak pakai lama",   img: "../assets/img/banners/mang-ujang-1.png" },
                { bg: "linear-gradient(135deg,#4a2e00 0%,#8c5800 60%,#c07a00 100%)", badge: "🔥 Panas Selalu",  title: "Tahu & Tempe<br>Kriuk Tiada Duanya!",  sub: "Bumbu bawang putih khas Mang Ujang", img: "../assets/img/banners/mang-ujang-2.png" },
                { bg: "linear-gradient(135deg,#3a2200 0%,#704400 60%,#9c5e00 100%)", badge: "🍌 Baru!",         title: "Pisang Goreng Keju<br>Wajib Dicoba!",  sub: "Pisang kepok + keju meleleh",        img: "../assets/img/banners/mang-ujang-3.png" },
            ]
        },
    ];

    const menus = {
        1: [
            { name: "Nasi Ayam Goreng",   desc: "Nasi putih + ayam goreng renyah",       price: 12000, img: "../assets/img/menus/nasi-ayam-goreng.png" },
            { name: "Nasi Rendang",        desc: "Nasi putih + rendang sapi empuk",        price: 15000, img: "../assets/img/menus/nasi-rendang.png" },
            { name: "Nasi Telur Dadar",    desc: "Nasi putih + telur dadar spesial",       price: 9000,  img: "../assets/img/menus/nasi-telur-dadar.png" },
            { name: "Paket Lengkap",       desc: "Nasi + ayam + sayur + tempe",            price: 18000, img: "../assets/img/menus/paket-lengkap.png" },
        ],
        2: [
            { name: "Bakso Spesial",       desc: "Bakso sapi jumbo + mi + kuah gurih",     price: 13000, img: "../assets/img/menus/bakso-spesial.png" },
            { name: "Mie Ayam",            desc: "Mie kenyal dengan topping ayam cincang", price: 11000, img: "../assets/img/menus/mie-ayam.png" },
            { name: "Bakso Mercon",        desc: "Bakso ekstra pedas level 5",             price: 14000, img: "../assets/img/menus/bakso-mercon.png" },
            { name: "Mie Goreng",          desc: "Mie goreng bumbu rahasia Bu Sari",       price: 10000, img: "../assets/img/menus/mie-goreng.png" },
        ],
        3: [
            { name: "Salad Buah",          desc: "Buah segar + yogurt + madu",             price: 10000, img: "../assets/img/menus/salad-buah.png" },
            { name: "Sandwich Sehat",      desc: "Roti gandum + sayur + telur rebus",      price: 12000, img: "../assets/img/menus/sandwich-sehat.png" },
            { name: "Jus Alpukat",         desc: "Alpukat segar tanpa susu kental manis",  price: 8000,  img: "../assets/img/menus/jus-alpukat.png" },
            { name: "Oatmeal Cup",         desc: "Oatmeal hangat + topping granola",       price: 9000,  img: "../assets/img/menus/oatmeal-cup.png" },
        ],
        4: [
            { name: "Es Teh Manis",        desc: "Teh manis dingin segar",                 price: 4000,  img: "../assets/img/menus/es-teh-manis.png" },
            { name: "Teh Tarik",           desc: "Teh susu khas Malaysia creamy",          price: 7000,  img: "../assets/img/menus/teh-tarik.png" },
            { name: "Es Kopi Susu",        desc: "Kopi arabika + susu segar es batu",      price: 10000, img: "../assets/img/menus/es-kopi-susu.png" },
            { name: "Boba Matcha",         desc: "Matcha latte + pearl tapioka kenyal",    price: 13000, img: "../assets/img/menus/boba-matcha.png" },
        ],
        5: [
            { name: "Tahu Goreng (5pcs)",  desc: "Tahu crispy bumbu bawang putih",         price: 5000,  img: "../assets/img/menus/tahu-goreng.png" },
            { name: "Tempe Goreng (5pcs)", desc: "Tempe renyah krispi",                    price: 4000,  img: "../assets/img/menus/tempe-goreng.png" },
            { name: "Pisang Goreng",       desc: "Pisang kepok goreng keju",               price: 6000,  img: "../assets/img/menus/pisang-goreng.png" },
            { name: "Cireng Isi (4pcs)",   desc: "Cireng isi ayam pedas manis",            price: 7000,  img: "../assets/img/menus/cireng-isi.png" },
        ],
    };

    const fmt = n => "Rp " + n.toLocaleString("id-ID");

    function storeLogoHtml(s, cls = "") {
        return `<img src="${s.img}" alt="${s.name}" class="w-full h-full object-cover ${cls}"
            onerror="this.style.opacity='0.3';this.src='../assets/img/store-placeholder.png'">`;
    }

    /* ── Slider generik ── */
    function makeSlider(trackId, dotsId, total) {
        let idx = 0, timer;
        function go(i) {
            idx = (i + total) % total;
            document.getElementById(trackId).style.transform = `translateX(-${idx * 100}%)`;
            document.querySelectorAll(`#${dotsId} .sl-dot`).forEach((d, j) => {
                d.style.background = j === idx ? "#fff" : "rgba(255,255,255,0.4)";
                d.style.width      = j === idx ? "20px" : "8px";
            });
        }
        function next()  { go(idx + 1); reset(); }
        function prev()  { go(idx - 1); reset(); }
        function reset() { clearInterval(timer); timer = setInterval(() => go(idx + 1), 4000); }
        function start() { reset(); }
        function stop()  { clearInterval(timer); }
        function initDots() {
            document.getElementById(dotsId).innerHTML = Array.from({length: total}, (_, i) => `
                <button class="sl-dot rounded-full transition-all duration-300 h-2"
                    style="width:${i===0?'20px':'8px'};background:${i===0?'#fff':'rgba(255,255,255,0.4)'};"
                    aria-label="Slide ${i+1}"></button>
            `).join("");
            document.querySelectorAll(`#${dotsId} .sl-dot`).forEach((d, i) => {
                d.onclick = () => { go(i); reset(); };
            });
        }
        function reinit(newTotal) {
            total = newTotal;
            idx   = 0;
            stop();
            initDots();
        }
        return { go, next, prev, start, stop, initDots, reinit };
    }

    const sliderMain  = makeSlider("banner-main-track",  "banner-main-dots",  1);
    const sliderStore = makeSlider("banner-store-track", "banner-store-dots", 3);

    function bannerMainNext()  { sliderMain.next(); }
    function bannerMainPrev()  { sliderMain.prev(); }
    function bannerStoreNext() { sliderStore.next(); }
    function bannerStorePrev() { sliderStore.prev(); }

    /* ── Render toko ── */
    function renderStores() {
        document.getElementById("store-grid").innerHTML = stores.map((s, i) => `
            <button onclick="openStore(${s.id})"
                class="store-card opacity-0 animate-fadeInUp text-left bg-white rounded-xl border border-gray-100
                       hover:border-primary/40 hover:shadow-md active:scale-[0.98]
                       transition-all duration-200 flex group"
                style="animation-delay:${0.15 + i * 0.07}s;">
                <div class="store-icon bg-input flex items-center justify-center
                            group-hover:bg-primary/10 transition-colors duration-200">${storeLogoHtml(s)}</div>
                <div class="p-2 sm:p-3 flex flex-col flex-grow">
                    <p class="store-name font-semibold text-text-1 leading-snug truncate">${s.name}</p>
                    <span class="store-tag inline-block mt-1 px-1.5 py-0.5 rounded-full bg-input text-text-3 font-medium">${s.tag}</span>
                    <p class="store-desc text-xs text-text-3 mt-1.5 leading-relaxed line-clamp-2">${s.desc}</p>
                </div>
            </button>
        `).join("");
    }

    /* ── Buka toko ── */
    function openStore(id) {
        const store   = stores.find(s => s.id === id);
        const items   = menus[id] || [];
        const vStores = document.getElementById("view-stores");
        const vMenus  = document.getElementById("view-menus");
        const bMain   = document.getElementById("banner-main");

        vStores.style.animation = "slideOutLeft 0.25s ease-out forwards";
        bMain.style.animation   = "slideOutLeft 0.25s ease-out forwards";
        sliderMain.stop();

        setTimeout(() => {
            vStores.classList.add("hidden"); vStores.style.animation = "";
            bMain.classList.add("hidden");   bMain.style.animation   = "";

            document.getElementById("page-title").textContent = store.name;
            document.getElementById("page-sub").textContent   = store.tag + " · " + store.desc;
            document.getElementById("btn-back").classList.remove("hidden");
            document.getElementById("btn-back").classList.add("flex");

            const logoEl = document.getElementById("store-logo");
            logoEl.innerHTML = `<img src="${store.img}" alt="${store.name}" class="w-full h-full object-cover rounded-xl"
                onerror="this.style.opacity='0.3';this.src='../assets/img/store-placeholder.png'">`;
            logoEl.classList.remove("hidden");
            logoEl.classList.add("flex");

            const grid = document.getElementById("menu-grid");
            grid.innerHTML = items.length
                ? items.map((m, i) => `
                    <div class="opacity-0 bg-white rounded-xl border border-gray-100 overflow-hidden
                                hover:border-primary/30 hover:shadow-sm transition-all duration-200 flex flex-col"
                         style="animation:fadeInUp 0.4s ease-out ${i*0.06}s forwards;">
                        <div class="menu-img bg-input flex items-center justify-center overflow-hidden">
                            <img src="${m.img}" alt="${m.name}" class="w-full h-full object-cover"
                                 onerror="this.style.display='none';this.parentElement.style.fontSize='2rem';this.parentElement.textContent='🍽️'">
                        </div>
                        <div class="p-2 sm:p-3 flex flex-col flex-grow">
                            <p class="menu-name font-semibold text-text-1 leading-snug">${m.name}</p>
                            <p class="menu-desc text-text-3 mt-1 leading-relaxed flex-grow line-clamp-2">${m.desc}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="menu-price text-primary font-bold">${fmt(m.price)}</span>
                                <button onclick="addToCart('${m.name.replace(/'/g,"\\'")}')"
                                    class="menu-add-btn rounded-full bg-primary text-white font-light
                                           flex items-center justify-center hover:bg-submit active:scale-95 transition-all duration-150"
                                    aria-label="Tambah ${m.name}">+</button>
                            </div>
                        </div>
                    </div>`)
                  .join("")
                : `<div class="col-span-full bg-white rounded-xl border border-gray-100 p-8 text-center text-text-3 text-sm">
                       Menu belum tersedia.
                   </div>`;

            // Render banner slides khusus toko ini
            const track = document.getElementById("banner-store-track");
            track.innerHTML = store.banners.map(b => {
                const bgLayer = b.img
                    ? `<img src="${b.img}" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.style.display='none'">
                       <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);"></div>`
                    : '';
                const decoEl = (b.img || !b.deco)
                    ? ''
                    : `<div class="absolute right-3 sm:right-5 bottom-0 opacity-25 leading-none banner-store-deco">${b.deco}</div>`;
                return `
                <div class="min-w-full h-full relative flex items-center px-4 sm:px-7 overflow-hidden"
                     style="background:${b.bg};">
                    ${bgLayer}
                    <div class="relative z-10 max-w-[70%] sm:max-w-none">
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
                              style="background:rgba(255,255,255,0.2);color:#fff;">${b.badge}</span>
                        <h3 class="text-white font-extrabold leading-tight banner-store-title">${b.title}</h3>
                        <p class="text-white/70 text-xs mt-1">${b.sub}</p>
                    </div>
                    ${decoEl}
                </div>`;
            }).join("");

            sliderStore.reinit(store.banners.length);
            sliderStore.go(0);
            sliderStore.start();

            vMenus.classList.remove("hidden");
            vMenus.style.display  = "flex";
            vMenus.style.animation = "slideInRight 0.35s ease-out forwards";
            setTimeout(() => vMenus.style.animation = "", 400);
        }, 240);
    }

    /* ── Balik ke toko ── */
    function backToStores() {
        const vStores = document.getElementById("view-stores");
        const vMenus  = document.getElementById("view-menus");
        const bMain   = document.getElementById("banner-main");

        vMenus.style.animation = "slideOutRight 0.25s ease-out forwards";
        sliderStore.stop();

        setTimeout(() => {
            vMenus.classList.add("hidden");
            vMenus.style.display   = "";
            vMenus.style.animation = "";

            document.getElementById("page-title").textContent = "Pesan Menu";
            document.getElementById("page-sub").textContent   = "Pilih kantin dan menu favoritmu";
            document.getElementById("btn-back").classList.add("hidden");
            document.getElementById("btn-back").classList.remove("flex");

            const logoEl = document.getElementById("store-logo");
            logoEl.classList.add("hidden");
            logoEl.classList.remove("flex");

            bMain.classList.remove("hidden");
            bMain.style.animation   = "slideInLeft 0.35s ease-out forwards";
            vStores.classList.remove("hidden");
            vStores.style.animation = "slideInLeft 0.35s ease-out forwards";
            setTimeout(() => { bMain.style.animation = ""; vStores.style.animation = ""; }, 400);

            sliderMain.start();
        }, 240);
    }

    /* ── Toast ── */
    let toastTimer;
    function addToCart(name) {
        const t = document.getElementById("toast");
        t.textContent = name + " ditambahkan ke keranjang 🛒";
        t.classList.remove("opacity-0");
        t.classList.add("show-toast", "opacity-100");
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            t.classList.remove("show-toast", "opacity-100");
            t.classList.add("opacity-0");
        }, 2200);
    }

    /* ── Init ── */
    renderStores();
    sliderMain.initDots();
    sliderStore.initDots();
    sliderMain.start();
</script>
</body>
</html>