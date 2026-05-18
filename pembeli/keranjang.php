<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include '../config/koneksi.php';

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
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background": "#fafbf9",
                        "primary": "#004900",
                        "second-primary": "#f9f9fb",
                        "input": "#f0f4f0",
                        "text-1": "#191c1c",
                        "text-2": "#4e5a48",
                        "text-3": "#5e6659",
                        "submit": "#005300"
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(15px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
    <div class="flex min-h-screen relative">

        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
            <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

                <!-- Header -->
                <header class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Keranjang</h2>
                            <p class="text-text-3 mt-1 text-sm">Pesanan yang sedang kamu siapkan</p>
                        </div>
                        <button
                            class="flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-xl transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Semua
                        </button>
                    </div>
                </header>

                <!-- Layout: Cart Items + Ringkasan -->
                <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 grid grid-cols-1 lg:grid-cols-3 gap-5"
                    style="animation-delay: 0.2s;">

                    <!-- Kiri: Daftar Item -->
                    <div class="lg:col-span-2 flex flex-col gap-3">

                        <!-- Item 1 -->
                        <div
                            class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-4 flex items-center gap-4 hover:border-primary/20 hover:shadow-md transition-all duration-200">
                            <div
                                class="w-14 h-14 flex-shrink-0 rounded-xl bg-input flex items-center justify-center text-2xl">
                                🍱
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="font-bold text-text-1 text-sm leading-snug">Nasi Ayam Geprek</p>
                                <p class="text-xs text-text-3 mt-0.5">Rp 12.000 / item</p>
                                <div class="flex items-center gap-2 mt-2.5">
                                    <button
                                        class="w-7 h-7 rounded-full bg-input text-primary font-bold flex items-center justify-center hover:bg-red-100 hover:text-red-600 transition-colors text-base">−</button>
                                    <span class="font-bold text-sm w-6 text-center text-text-1">2</span>
                                    <button
                                        class="w-7 h-7 rounded-full bg-primary text-white font-bold flex items-center justify-center hover:bg-submit transition-colors text-base">+</button>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="font-extrabold text-sm text-primary">Rp 24.000</span>
                                <button
                                    class="text-text-3 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                    title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Item 2 -->
                        <div
                            class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-4 flex items-center gap-4 hover:border-primary/20 hover:shadow-md transition-all duration-200">
                            <div
                                class="w-14 h-14 flex-shrink-0 rounded-xl bg-input flex items-center justify-center text-2xl">
                                🥤
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="font-bold text-text-1 text-sm leading-snug">Es Teh Manis</p>
                                <p class="text-xs text-text-3 mt-0.5">Rp 5.000 / item</p>
                                <div class="flex items-center gap-2 mt-2.5">
                                    <button
                                        class="w-7 h-7 rounded-full bg-input text-primary font-bold flex items-center justify-center hover:bg-red-100 hover:text-red-600 transition-colors text-base">−</button>
                                    <span class="font-bold text-sm w-6 text-center text-text-1">1</span>
                                    <button
                                        class="w-7 h-7 rounded-full bg-primary text-white font-bold flex items-center justify-center hover:bg-submit transition-colors text-base">+</button>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="font-extrabold text-sm text-primary">Rp 5.000</span>
                                <button
                                    class="text-text-3 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                    title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Item 3 -->
                        <div
                            class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-4 flex items-center gap-4 hover:border-primary/20 hover:shadow-md transition-all duration-200">
                            <div
                                class="w-14 h-14 flex-shrink-0 rounded-xl bg-input flex items-center justify-center text-2xl">
                                🍱
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="font-bold text-text-1 text-sm leading-snug">Mie Goreng Spesial</p>
                                <p class="text-xs text-text-3 mt-0.5">Rp 10.000 / item</p>
                                <div class="flex items-center gap-2 mt-2.5">
                                    <button
                                        class="w-7 h-7 rounded-full bg-input text-primary font-bold flex items-center justify-center hover:bg-red-100 hover:text-red-600 transition-colors text-base">−</button>
                                    <span class="font-bold text-sm w-6 text-center text-text-1">1</span>
                                    <button
                                        class="w-7 h-7 rounded-full bg-primary text-white font-bold flex items-center justify-center hover:bg-submit transition-colors text-base">+</button>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="font-extrabold text-sm text-primary">Rp 10.000</span>
                                <button
                                    class="text-text-3 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                    title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Tambah Menu -->
                        <a href="pesan.php"
                            class="flex items-center justify-center gap-2 text-sm font-semibold text-primary hover:text-submit border border-dashed border-primary/30 hover:border-primary/60 hover:bg-primary/5 py-3 rounded-[20px] transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Menu Lagi
                        </a>
                    </div>

                    <!-- Kanan: Ringkasan -->
                    <div class="flex flex-col gap-4">
                        <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] shadow-sm border border-gray-100 p-5 flex flex-col gap-4 sticky top-6"
                            style="animation-delay: 0.3s;">
                            <h3 class="font-bold text-text-1 text-base border-b border-gray-100 pb-3">Ringkasan Pesanan
                            </h3>

                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-text-2">Nasi Ayam Geprek <span
                                            class="text-text-3">×2</span></span>
                                    <span class="font-semibold text-text-1">Rp 24.000</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-text-2">Es Teh Manis <span class="text-text-3">×1</span></span>
                                    <span class="font-semibold text-text-1">Rp 5.000</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-text-2">Mie Goreng Spesial <span
                                            class="text-text-3">×1</span></span>
                                    <span class="font-semibold text-text-1">Rp 10.000</span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-gray-200 pt-3 flex items-center justify-between">
                                <span class="font-bold text-text-1">Total</span>
                                <span class="font-extrabold text-xl text-primary">Rp 39.000</span>
                            </div>

                            <button
                                class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Lanjut Checkout
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</body>

</html>