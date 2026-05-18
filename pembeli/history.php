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
    <title>Riwayat Pesanan</title>
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
                    <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Riwayat Pesanan</h2>
                    <p class="text-text-3 mt-1 text-sm">Semua pesanan yang pernah kamu buat</p>
                </header>

                <!-- Filter Tab -->
                <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 flex items-center gap-2 flex-wrap"
                    style="animation-delay: 0.15s;">
                    <button
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-primary text-white shadow-sm transition-all duration-200">Semua</button>
                    <button
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-gray-100 text-text-2 hover:border-primary/30 hover:text-primary transition-all duration-200">Selesai</button>
                    <button
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-gray-100 text-text-2 hover:border-primary/30 hover:text-primary transition-all duration-200">Diproses</button>
                    <button
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-gray-100 text-text-2 hover:border-primary/30 hover:text-primary transition-all duration-200">Dibatalkan</button>
                </div>

                <!-- Daftar Riwayat -->
                <div class="flex flex-col gap-4">

                    <!-- Order 1: Selesai -->
                    <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-200"
                        style="animation-delay: 0.2s;">
                        <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-text-1 text-sm">#ORD-2025001</p>
                                    <p class="text-xs text-text-3 mt-0.5">Senin, 12 Mei 2025 · 10.32 WIB</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold px-3 py-1 rounded-full bg-green-100 text-green-700">Selesai</span>
                        </div>
                        <div class="px-5 py-3 flex flex-col gap-1.5">
                            <p class="text-xs font-semibold text-text-3 uppercase tracking-wider mb-1">Kantin Bu Sari
                            </p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Nasi Ayam Geprek <span class="text-text-3">×2</span></span>
                                <span class="font-semibold text-text-1">Rp 24.000</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Es Teh Manis <span class="text-text-3">×1</span></span>
                                <span class="font-semibold text-text-1">Rp 5.000</span>
                            </div>
                        </div>
                        <div class="px-5 pb-4 pt-2 flex items-center justify-between border-t border-gray-50">
                            <span class="text-xs text-text-3">3 item</span>
                            <span class="font-extrabold text-base text-primary">Rp 29.000</span>
                        </div>
                    </div>

                    <!-- Order 2: Diproses -->
                    <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-200"
                        style="animation-delay: 0.25s;">
                        <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-text-1 text-sm">#ORD-2025002</p>
                                    <p class="text-xs text-text-3 mt-0.5">Selasa, 13 Mei 2025 · 11.05 WIB</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">Diproses</span>
                        </div>
                        <div class="px-5 py-3 flex flex-col gap-1.5">
                            <p class="text-xs font-semibold text-text-3 uppercase tracking-wider mb-1">Kantin Pak Budi
                            </p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Mie Goreng Spesial <span class="text-text-3">×1</span></span>
                                <span class="font-semibold text-text-1">Rp 10.000</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Jus Alpukat <span class="text-text-3">×1</span></span>
                                <span class="font-semibold text-text-1">Rp 8.000</span>
                            </div>
                        </div>
                        <div class="px-5 pb-4 pt-2 flex items-center justify-between border-t border-gray-50">
                            <span class="text-xs text-text-3">2 item</span>
                            <span class="font-extrabold text-base text-primary">Rp 18.000</span>
                        </div>
                    </div>

                    <!-- Order 3: Selesai -->
                    <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-200"
                        style="animation-delay: 0.3s;">
                        <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-text-1 text-sm">#ORD-2025003</p>
                                    <p class="text-xs text-text-3 mt-0.5">Rabu, 14 Mei 2025 · 09.15 WIB</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold px-3 py-1 rounded-full bg-green-100 text-green-700">Selesai</span>
                        </div>
                        <div class="px-5 py-3 flex flex-col gap-1.5">
                            <p class="text-xs font-semibold text-text-3 uppercase tracking-wider mb-1">Kantin Bu Sari
                            </p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Nasi Uduk <span class="text-text-3">×1</span></span>
                                <span class="font-semibold text-text-1">Rp 12.000</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Tahu Goreng <span class="text-text-3">×3</span></span>
                                <span class="font-semibold text-text-1">Rp 6.000</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2">Air Mineral <span class="text-text-3">×1</span></span>
                                <span class="font-semibold text-text-1">Rp 3.000</span>
                            </div>
                        </div>
                        <div class="px-5 pb-4 pt-2 flex items-center justify-between border-t border-gray-50">
                            <span class="text-xs text-text-3">5 item</span>
                            <span class="font-extrabold text-base text-primary">Rp 21.000</span>
                        </div>
                    </div>

                    <!-- Order 4: Dibatalkan -->
                    <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-200"
                        style="animation-delay: 0.35s;">
                        <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-text-1 text-sm">#ORD-2025004</p>
                                    <p class="text-xs text-text-3 mt-0.5">Kamis, 15 Mei 2025 · 12.00 WIB</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold px-3 py-1 rounded-full bg-red-100 text-red-600">Dibatalkan</span>
                        </div>
                        <div class="px-5 py-3 flex flex-col gap-1.5">
                            <p class="text-xs font-semibold text-text-3 uppercase tracking-wider mb-1">Kantin Pak Budi
                            </p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-2 line-through opacity-50">Bakso Kuah <span
                                        class="text-text-3">×2</span></span>
                                <span class="font-semibold text-text-1 opacity-50">Rp 20.000</span>
                            </div>
                        </div>
                        <div class="px-5 pb-4 pt-2 flex items-center justify-between border-t border-gray-50">
                            <span class="text-xs text-text-3">2 item</span>
                            <span class="font-extrabold text-base text-text-3 line-through">Rp 20.000</span>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</body>


</html>