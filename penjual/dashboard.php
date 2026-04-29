<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "background": "#f7f8f9",
                        "primary": "#004900",
                        "second-primary": "#f9f9fb",
                        "input": "#f3f3f5",
                        "text-1": "#191c1c",
                        "text-2": "#4e5a48",
                        "text-3": "#5e6659",
                        "submit": "#005300"
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-surface pt-24 lg:pt-8">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
            <div>
                <h2 class="font-headline font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Selamat Datang, Warung_ayam_bakar!</h2>
                <p class="text-on-surface-variant font-body mt-2">Inilah keadaan kantin mu.</p>
            </div>
        </header>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">

        <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Anggota Aktif</p>
            <p class="text-4xl font-extrabold text-primary">128</p>
            <p class="text-xs text-text-2">pengguna terdaftar</p>
        </div>

        <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Toko Aktif</p>
            <p class="text-4xl font-extrabold text-primary">12</p>
            <p class="text-xs text-text-2">toko berjalan</p>
        </div>

        <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2 col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Toko Aktif</p>
            <p class="text-4xl font-extrabold text-primary">12</p>
            <p class="text-xs text-text-2">toko berjalan</p>
        </div>

        </div>

        <div class="w-full bg-white rounded-[20px] mt-6 shadow-sm border border-gray-100 overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <p class="font-bold text-text-1">Pesanan Terbaru</p>
        <a href="pesanan.php" class="text-xs font-bold text-primary hover:underline underline-offset-4">Lihat Semua</a>
    </div>

    <div class="hidden md:grid grid-cols-[60px_1fr_120px_130px_100px] bg-primary px-6 py-3 gap-4">
        <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
        <p class="text-xs font-bold uppercase tracking-widest text-white">Pembeli</p>
        <p class="text-xs font-bold uppercase tracking-widest text-white">Total</p>
        <p class="text-xs font-bold uppercase tracking-widest text-white">Tanggal</p>
        <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
    </div>

    <div class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
        <div class="hidden md:grid grid-cols-[60px_1fr_120px_130px_100px] px-6 py-4 gap-4 items-center">
            <p class="text-sm text-text-3">001</p>
            <p class="text-sm font-medium text-text-1">Budi Santoso</p>
            <p class="text-sm text-text-2">Rp 25.000</p>
            <p class="text-sm text-text-3">28 Apr 2026</p>
            <span class="text-xs font-semibold px-2 py-1 rounded-full w-fit text-yellow-600 bg-yellow-100">Pending</span>
        </div>
        <div class="flex md:hidden items-center gap-3 px-4 py-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-text-1">Budi Santoso</p>
                <p class="text-xs text-text-3">28 Apr 2026 · Rp 25.000</p>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full text-yellow-600 bg-yellow-100">Pending</span>
        </div>
    </div>

    <div class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
        <div class="hidden md:grid grid-cols-[60px_1fr_120px_130px_100px] px-6 py-4 gap-4 items-center">
            <p class="text-sm text-text-3">002</p>
            <p class="text-sm font-medium text-text-1">Sari Dewi</p>
            <p class="text-sm text-text-2">Rp 15.000</p>
            <p class="text-sm text-text-3">28 Apr 2026</p>
            <span class="text-xs font-semibold px-2 py-1 rounded-full w-fit text-blue-600 bg-blue-100">Diproses</span>
        </div>
        <div class="flex md:hidden items-center gap-3 px-4 py-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-text-1">Sari Dewi</p>
                <p class="text-xs text-text-3">28 Apr 2026 · Rp 15.000</p>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full text-blue-600 bg-blue-100">Diproses</span>
        </div>
    </div>

    <div class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
        <div class="hidden md:grid grid-cols-[60px_1fr_120px_130px_100px] px-6 py-4 gap-4 items-center">
            <p class="text-sm text-text-3">003</p>
            <p class="text-sm font-medium text-text-1">Ahmad Rizki</p>
            <p class="text-sm text-text-2">Rp 30.000</p>
            <p class="text-sm text-text-3">27 Apr 2026</p>
            <span class="text-xs font-semibold px-2 py-1 rounded-full w-fit text-green-600 bg-green-100">Selesai</span>
        </div>
        <div class="flex md:hidden items-center gap-3 px-4 py-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-text-1">Ahmad Rizki</p>
                <p class="text-xs text-text-3">27 Apr 2026 · Rp 30.000</p>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full text-green-600 bg-green-100">Selesai</span>
        </div>
    </div>

</div>


    </main>
    </div>
</body>
</html>