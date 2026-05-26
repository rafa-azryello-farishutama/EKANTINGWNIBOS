<?php
// Cek notifikasi pesanan aktif pembeli
$q_hash = $db_ekantin->query("SELECT GROUP_CONCAT(CONCAT(id_pesanan, '-', status_pesanan)) as hash FROM pesanan WHERE id_users='{$_SESSION['id_users']}'");
$current_hash = $q_hash ? md5($q_hash->fetch_assoc()['hash'] ?? '') : '';

$has_notif_pembeli = false;
if ($current_hash != '') { // Only notify if there are actual orders
    if (!isset($_SESSION['last_seen_orders_hash']) || $_SESSION['last_seen_orders_hash'] !== $current_hash) {
        $has_notif_pembeli = true;
    }
}
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
        <?php if($has_notif_pembeli): ?>
            <span class="flex h-3 w-3 relative ml-auto">
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
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

        <a href="pesan.php"
            class="<?= $halaman == 'pesan.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <img src="<?= $halaman == 'pesan.php' ? '../assets/img/menuBook_white.png' : '../assets/img/menuBook_black.png' ?>"
                class="w-5 h-5 <?= $halaman != 'pesan.php' ? 'opacity-40' : '' ?>">
            <span class="text-sm font-medium">Pesan</span>
        </a>

        <a href="keranjang.php"
            class="<?= $halaman == 'keranjang.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <img src="<?= $halaman == 'keranjang.php' ? '../assets/img/keranjang_white.png' : '../assets/img/keranjang_black.png' ?>"
                class="w-5 h-5 <?= $halaman != 'keranjang.php' ? 'opacity-40' : '' ?>">
            <span class="text-sm font-medium">Keranjang</span>
        </a>
        <a href="history.php"
            class="<?= $halaman == 'history.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300 justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= $halaman == 'history.php' ? '../assets/img/history_white.png' : '../assets/img/history_black.png' ?>"
                    class="w-5 h-5 <?= $halaman != 'history.php' ? 'opacity-40' : '' ?>">
                <span class="text-sm font-medium">Riwayat</span>
            </div>
            <?php if($has_notif_pembeli): ?>
                <span class="flex h-3 w-3 relative ml-auto">
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            <?php endif; ?>
        </a>
        <a href="profil.php"
            class="<?= $halaman == 'profil.php' ? 'bg-primary text-white shadow-md shadow-primary/20 translate-x-1' : 'text-text-2 hover:bg-primary/10 hover:translate-x-1' ?> rounded-xl flex items-center gap-3 px-4 py-3 transition-all duration-300">
            <img src="<?= $halaman == 'profil.php' ? '../assets/img/akun_white.png' : '../assets/img/akun_black.png' ?>"
                class="w-5 h-5 <?= $halaman != 'profil.php' ? 'opacity-40' : '' ?>">
            <span class="text-sm font-medium">Akun</span>
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

    <a href="pesan.php" class="<?= $halaman == 'pesan.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?>
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <img src="<?= $halaman == 'pesan.php' ? '../assets/img/menuBook_white.png' : '../assets/img/menuBook_black.png' ?>"
            class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'pesan.php' ? 'opacity-40' : '' ?>">
        <span class="font-medium text-sm">Pesan</span>
    </a>



    <a href="keranjang.php" class="<?= $halaman == 'keranjang.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?>
                rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group">
        <img src="<?= $halaman == 'keranjang.php' ? '../assets/img/keranjang_white.png' : '../assets/img/keranjang_black.png' ?>"
            class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'keranjang.php' ? 'opacity-40' : '' ?>">
        <span class="font-medium text-sm">Keranjang</span>
    </a>

    <a href="history.php" class="<?= $halaman == 'history.php' ? 'bg-primary text-white shadow-[0_4px_20px_rgba(0,73,0,0.3)] translate-x-2' : 'text-text-2 hover:bg-primary/10 hover:translate-x-2' ?>
            rounded-xl flex items-center px-8 py-4 transition-all duration-300 cursor-pointer group justify-between">
    <div class="flex items-center">
        <img src="<?= $halaman == 'history.php' ? '../assets/img/history_white.png' : '../assets/img/history_black.png' ?>"
            class="w-[25px] h-auto mr-3 group-hover:scale-110 transition-transform duration-300 <?= $halaman != 'history.php' ? 'opacity-40' : '' ?>">
        <span class="font-medium text-sm">Riwayat</span>
    </div>
    <?php if($has_notif_pembeli): ?>
        <span class="flex h-3 w-3 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
    <?php endif; ?>
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