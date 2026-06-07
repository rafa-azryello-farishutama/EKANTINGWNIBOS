<?php
// Cek notifikasi pesanan aktif (ada update baru yang belum dilihat)
$q_notif_penjual = $db_ekantin->query("SELECT COUNT(*) as j FROM pesanan WHERE id_toko='{$_SESSION['id_toko']}' AND status_pesanan IN ('pending', 'dikonfirmasi', 'diproses') AND dilihat_penjual = 0");
$has_notif_penjual = ($q_notif_penjual && $q_notif_penjual->fetch_assoc()['j'] > 0);

// Cek notifikasi riwayat (pesanan selesai/batal yang belum dilihat)
$q_notif_riwayat = $db_ekantin->query("SELECT COUNT(*) as j FROM pesanan WHERE id_toko='{$_SESSION['id_toko']}' AND status_pesanan IN ('selesai', 'diambil', 'tidak_diambil', 'dibatalkan') AND dilihat_penjual = 0");
$has_notif_riwayat = ($q_notif_riwayat && $q_notif_riwayat->fetch_assoc()['j'] > 0);
?>
<!-- Overlay -->
<div id="overlay" onclick="tutupSidebar()" class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

<!-- Mobile Navbar -->
<div class="bg-primary lg:hidden fixed top-0 left-0 right-0 h-16 flex items-center justify-between px-6 z-50">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-background p-2">
            <img src="../assets/img/Garpusendok.png" class="w-full h-auto object-contain" alt="Logo">
        </div>
        <span class="font-headline font-bold text-background text-[20px]">E-Kantin</span>
    </div>
    <div class="flex items-center gap-2">
        <?php if($has_notif_penjual): ?>
            <span class="flex h-2.5 w-2.5 relative mr-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
            </span>
        <?php endif; ?>
        <button onclick="bukaSidebar()"
            class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</div>

<!-- Mobile Sidebar -->
<aside id="mobile-sidebar" class="fixed top-0 left-0 h-full w-[280px] bg-white z-50 lg:hidden
    -translate-x-full transition-transform duration-300 flex flex-col py-8 px-6 shadow-2xl border-r border-gray-100">

    <div class="flex items-center justify-between mb-8">
        <img src="../assets/img/logoBaru1.png" class="w-[140px] h-auto">
        <button onclick="tutupSidebar()"
            class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-text-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex flex-col gap-2">
        <a href="dashboard.php"
            class="<?= $halaman == 'dashboard.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <img src="<?= $halaman == 'dashboard.php' ? '../assets/img/dashboard.png' : '../assets/img/dashboardHitam.png' ?>"
                class="w-5 h-5 <?= $halaman != 'dashboard.php' ? 'opacity-40' : '' ?>">
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        <a href="produk.php"
            class="<?= $halaman == 'produk.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <img src="<?= $halaman == 'produk.php' ? '../assets/img/kotakGarpu_white.png' : '../assets/img/kotakGarpu_black.png' ?>"
                class="w-5 h-5 <?= $halaman != 'produk.php' ? 'opacity-40' : '' ?>">
            <span class="text-sm font-medium">Produk</span>
        </a>
        <a href="pesanan.php"
            class="<?= $halaman == 'pesanan.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300 justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= $halaman == 'pesanan.php' ? '../assets/img/menuBook_white.png' : '../assets/img/menuBook_black.png' ?>"
                    class="w-5 h-5 <?= $halaman != 'pesanan.php' ? 'opacity-40' : '' ?>">
                <span class="text-sm font-medium">Pesanan</span>
            </div>
            <?php if($has_notif_penjual): ?>
                <span class="flex h-3 w-3 relative ml-auto">
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            <?php endif; ?>
        </a>
        <a href="history.php"
            class="<?= $halaman == 'history.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300 justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= $halaman == 'history.php' ? '../assets/img/history_white.png' : '../assets/img/history_black.png' ?>"
                    class="w-5 h-5 <?= $halaman != 'history.php' ? 'opacity-40' : '' ?>">
                <span class="text-sm font-medium">Riwayat</span>
            </div>
            <?php if($has_notif_riwayat): ?>
                <span class="flex h-3 w-3 relative ml-auto">
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            <?php endif; ?>
        </a>
        <a href="ulasan.php"
            class="<?= $halaman == 'ulasan.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <svg class="w-5 h-5 <?= $halaman == 'ulasan.php' ? 'text-white' : 'text-text-2 opacity-40' ?>" fill="<?= $halaman == 'ulasan.php' ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            <span class="text-sm font-medium">Ulasan</span>
        </a>
        <a href="laporan.php"
            class="<?= $halaman == 'laporan.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <svg class="w-5 h-5 <?= $halaman == 'laporan.php' ? 'text-white' : 'text-text-2 opacity-40' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm font-medium">Laporan</span>
        </a>
        <a href="profil.php"
            class="<?= $halaman == 'profil.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <img src="<?= $halaman == 'profil.php' ? '../assets/img/akun_white.png' : '../assets/img/akun_black.png' ?>"
                class="w-5 h-5 <?= $halaman != 'profil.php' ? 'opacity-40' : '' ?>">
            <span class="text-sm font-medium">Profil</span>
        </a>
    </nav>

</aside>

<!-- Desktop Sidebar -->
<aside class="hidden lg:flex flex-col top-0 left-0 w-80 h-full fixed py-8 bg-white rounded-r-2xl z-40 shadow-[4px_0_24px_rgba(0,73,0,0.08)] border-r border-green-50">
<div class="flex items-center justify-center mb-4">
    <img src="../assets/img/logoBaru1.png" class="w-[200px] h-auto">
</div>

<nav class="flex-grow space-y-2 px-6 mt-4">

    <a href="dashboard.php" class="<?= $halaman == 'dashboard.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <img src="<?= $halaman == 'dashboard.php' ? '../assets/img/dashboard.png' : '../assets/img/dashboardHitam.png' ?>"
            class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'dashboard.php' ? 'opacity-40' : '' ?>">
        <span class="font-medium text-sm">Dashboard</span>
    </a>

    <a href="produk.php" class="<?= $halaman == 'produk.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <img src="<?= $halaman == 'produk.php' ? '../assets/img/kotakGarpu_white.png' : '../assets/img/kotakGarpu_black.png' ?>"
            class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'produk.php' ? 'opacity-40' : '' ?>">
        <span class="font-medium text-sm">Produk</span>
    </a>

    <a href="pesanan.php" class="<?= $halaman == 'pesanan.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group justify-between">
        <div class="flex items-center">
            <img src="<?= $halaman == 'pesanan.php' ? '../assets/img/menuBook_white.png' : '../assets/img/menuBook_black.png' ?>"
                class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'pesanan.php' ? 'opacity-40' : '' ?>">
            <span class="font-medium text-sm">Pesanan</span>
        </div>
        <?php if($has_notif_penjual): ?>
            <span class="flex h-3 w-3 relative ml-auto">
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
        <?php endif; ?>
    </a>

    <a href="history.php" class="<?= $halaman == 'history.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group justify-between">
        <div class="flex items-center">
            <img src="<?= $halaman == 'history.php' ? '../assets/img/history_white.png' : '../assets/img/history_black.png' ?>"
                class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'history.php' ? 'opacity-40' : '' ?>">
            <span class="font-medium text-sm">Riwayat</span>
        </div>
        <?php if($has_notif_riwayat): ?>
            <span class="flex h-3 w-3 relative ml-auto">
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
        <?php endif; ?>
    </a>

    <a href="ulasan.php" class="<?= $halaman == 'ulasan.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <svg class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman == 'ulasan.php' ? 'text-white' : 'text-text-2 opacity-40' ?>" fill="<?= $halaman == 'ulasan.php' ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
        <span class="font-medium text-sm">Ulasan</span>
    </a>

    <a href="laporan.php" class="<?= $halaman == 'laporan.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <svg class="w-[25px] h-[25px] mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman == 'laporan.php' ? 'text-white' : 'text-text-2 opacity-40' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="font-medium text-sm">Laporan</span>
    </a>

    <a href="profil.php" class="<?= $halaman == 'profil.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <img src="<?= $halaman == 'profil.php' ? '../assets/img/akun_white.png' : '../assets/img/akun_black.png' ?>"
            class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'profil.php' ? 'opacity-40' : '' ?>">
        <span class="font-medium text-sm">Profil</span>
    </a>
</nav>

</aside>

<script>
    function bukaSidebar() {
        document.getElementById('mobile-sidebar').classList.remove('-translate-x-full');
        document.getElementById('overlay').classList.remove('hidden');
    }
    function tutupSidebar() {
        document.getElementById('mobile-sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
    }
</script>