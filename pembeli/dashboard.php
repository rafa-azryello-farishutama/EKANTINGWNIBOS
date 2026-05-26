<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if(!isset($_SESSION['pembeli_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];

$id_users = $_SESSION['id_users'];
$username = $_SESSION['username'] ?? 'Pembeli';

function isStoreOpen($toko) {
    if (!$toko) return false;
    if (($toko['status'] ?? 'aktif') === 'tutup') {
        return false;
    }
    if (($toko['status'] ?? 'aktif') === 'buka') {
        return true;
    }
    if (empty($toko['jam_buka']) || empty($toko['jam_tutup']) || $toko['jam_buka'] == '--:--' || $toko['jam_tutup'] == '--:--') {
        return true;
    }
    date_default_timezone_set('Asia/Jakarta');
    $now = date('H:i');
    $buka = $toko['jam_buka'];
    $tutup = $toko['jam_tutup'];
    if ($buka <= $tutup) {
        return ($now >= $buka && $now <= $tutup);
    } else {
        return ($now >= $buka || $now <= $tutup);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembeli</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

            <!-- Header -->
            <header class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary flex items-center gap-2">
                    Selamat Datang, <?= htmlspecialchars($username) ?> <span class="inline-block hover:animate-bounce origin-bottom-right">👋</span>
                </h2>
                <p class="text-text-3 mt-1 text-sm">Temukan menu favoritmu dan pesan langsung dari sini.</p>
            </header>


            <!-- Hero Banner -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 relative overflow-hidden rounded-[24px] bg-gradient-to-r from-primary to-[#006800] px-8 py-10 text-white shadow-lg" style="animation-delay: 0.2s;">
                <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/5 rounded-full"></div>
                <div class="absolute -right-4 -bottom-6 w-32 h-32 bg-white/5 rounded-full"></div>
                <p class="text-white/70 text-xs uppercase tracking-widest font-semibold mb-2">✨ E-Kantin SMEA</p>
                <h3 class="text-3xl font-extrabold leading-tight mb-2">Makan Enak,<br><span class="text-yellow-300">Harga Terjangkau</span></h3>
                <p class="text-white/80 text-sm max-w-sm">Nikmati berbagai pilihan kantin dengan menu lezat setiap hari.</p>
                <a href="pesan.php" class="mt-5 inline-flex items-center gap-2 bg-white text-primary font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-yellow-50 transition-all hover:shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.874-7.148a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    Pesan Sekarang
                </a>
            </div>

            <!-- Status Pesanan (Table Layout) -->
        

            <!-- Menu Navigasi Cepat -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.3s;">
                <h3 class="font-bold text-text-1 text-base mb-3">Menu Cepat</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                    <a href="pesan.php" class="bg-white rounded-[20px] p-5 shadow-sm border border-blue-50 flex flex-col items-center gap-3 text-center hover:-translate-y-1 hover:shadow-md hover:shadow-blue-100/50 transition-all group">
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-text-1">Pesan Menu</p>
                            <p class="text-xs text-text-3">Pilih & pesan</p>
                        </div>
                    </a>

                    <a href="keranjang.php" class="bg-white rounded-[20px] p-5 shadow-sm border border-orange-50 flex flex-col items-center gap-3 text-center hover:-translate-y-1 hover:shadow-md hover:shadow-orange-100/50 transition-all group">
                        <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.874-7.148a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-text-1">Keranjang</p>
                            <p class="text-xs text-text-3">Lihat pesanan</p>
                        </div>
                    </a>

                    <a href="history.php" class="bg-white rounded-[20px] p-5 shadow-sm border border-green-50 flex flex-col items-center gap-3 text-center hover:-translate-y-1 hover:shadow-md hover:shadow-green-100/50 transition-all group">
                        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-text-1">Riwayat</p>
                            <p class="text-xs text-text-3">Pesanan lalu</p>
                        </div>
                    </a>

                    <a href="profil.php" class="bg-white rounded-[20px] p-5 shadow-sm border border-purple-50 flex flex-col items-center gap-3 text-center hover:-translate-y-1 hover:shadow-md hover:shadow-purple-100/50 transition-all group">
                        <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-text-1">Akun</p>
                            <p class="text-xs text-text-3">Profil saya</p>
                        </div>
                    </a>

                </div>
            </div>

            <!-- Pilihan Kantin -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 mb-8" style="animation-delay: 0.4s;">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-bold text-text-1 text-base">Pilihan Kantin</h3>
                        <p class="text-xs text-text-3">Temukan kantin favorit Anda di sini</p>
                    </div>
                    <a href="pesan.php" class="text-xs font-bold text-primary hover:underline underline-offset-4">Lihat Semua</a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 md:gap-4 mt-2">
                    <?php
                    $qKantin = $db_ekantin->query("SELECT t.*, COUNT(pk.id_produk) as total_menu, rk.nomor_ruang FROM toko t JOIN users u ON t.id_users = u.id_users LEFT JOIN produk_kantin pk ON t.id_toko = pk.id_toko AND pk.status_menu = 'aktif' LEFT JOIN ruang_kantin rk ON rk.id_toko = t.id_toko WHERE u.status = 'aktif' GROUP BY t.id_toko LIMIT 6");
                    $i = 0;
                    if ($qKantin && $qKantin->num_rows > 0):
                        while ($kantin = $qKantin->fetch_assoc()):
                            $i++;
                            $banner_toko = $kantin['banner_toko'] ?? null;
                            $banner_src  = $banner_toko ? "../assets/img_banner/$banner_toko" : null;
                            $initial     = strtoupper(substr($kantin['nama_toko'], 0, 1));
                    ?>
                    <a href="pesan.php?id_toko=<?= $kantin['id_toko'] ?>"
                        class="store-card opacity-0 animate-fadeInUp text-left bg-white rounded-xl border border-gray-100
                               hover:border-primary/40 hover:shadow-md active:scale-[0.98]
                               transition-all duration-200 flex flex-col group overflow-hidden h-full"
                        style="animation-delay:<?= 0.15 + ($i * 0.05) ?>s;">
                        <div class="store-icon w-full h-28 sm:h-32 flex-shrink-0 bg-input flex items-center justify-center group-hover:bg-primary/10 transition-colors duration-200 relative overflow-hidden">
                            <?php if ($banner_src && file_exists($banner_src)): ?>
                                <img src="<?= htmlspecialchars($banner_src) ?>"
                                     alt="Banner"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            <?php else: ?>
                                <img src="../assets/img/default_banner_app.jpg" alt="Default Kantin"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                                <div class="absolute inset-0 bg-black/10 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white/90 shadow-sm flex items-center justify-center backdrop-blur-sm">
                                        <span class="text-primary font-extrabold text-2xl"><?= $initial ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php $is_open_card = isStoreOpen($kantin); ?>
                            <?php if ($is_open_card): ?>
                                <span class="absolute top-2 left-2 text-[9px] font-bold bg-green-500 text-white px-2 py-0.5 rounded shadow-sm z-10">Buka</span>
                            <?php else: ?>
                                <span class="absolute top-2 left-2 text-[9px] font-bold bg-red-500 text-white px-2 py-0.5 rounded shadow-sm z-10">Tutup</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex flex-col flex-grow min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="store-name font-semibold text-text-1 text-sm sm:text-base leading-snug truncate"><?= htmlspecialchars($kantin['nama_toko']) ?></p>
                                <span class="flex-shrink-0 px-2 py-0.5 bg-primary/10 text-primary rounded text-[10px] font-bold">Ruang <?= htmlspecialchars($kantin['nomor_ruang'] ?? '-') ?></span>
                            </div>
                            <span class="store-tag inline-block mt-1.5 px-2 py-0.5 rounded-full bg-input text-text-3 text-[10px] sm:text-xs font-medium self-start">
                                <?= $kantin['total_menu'] ?> Menu
                            </span>
                            <p class="store-desc text-[11px] sm:text-xs text-text-3 mt-2 leading-relaxed line-clamp-2 flex-grow">
                                <?= htmlspecialchars($kantin['lokasi'] ?? 'Berbagai macam makanan dan minuman.') ?>
                            </p>
                            <p class="text-[10px] sm:text-[11px] font-semibold flex items-center gap-1 mt-3 pt-2 border-t border-gray-50 <?= $is_open_card ? 'text-green-600' : 'text-red-600' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?= htmlspecialchars($kantin['jam_buka'] ?? '--:--') ?> - <?= htmlspecialchars($kantin['jam_tutup'] ?? '--:--') ?> WIB
                            </p>
                        </div>
                    </a>
                    <?php endwhile; else: ?>
                    <div class="col-span-full text-center text-text-3 py-10 text-sm border border-gray-100 rounded-xl">
                        Belum ada kantin yang terdaftar.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menu Trending -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 mb-8" style="animation-delay: 0.5s;">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-bold text-text-1 text-base flex items-center gap-2">
                            Menu Trending
                            <span class="text-orange-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />
                                </svg>
                            </span>
                        </h3>
                        <p class="text-xs text-text-3">Paling banyak dipesan</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $qTrending = $db_ekantin->query("
                        SELECT p.*, t.nama_toko, IFNULL(SUM(dp.jumlah), 0) as total_terjual
                        FROM produk_kantin p
                        LEFT JOIN detail_pesanan dp ON p.id_produk = dp.id_produk
                        LEFT JOIN toko t ON p.id_toko = t.id_toko
                        WHERE p.stok > 0
                        GROUP BY p.id_produk
                        ORDER BY total_terjual DESC, p.id_produk DESC
                        LIMIT 3
                    ");
                    if ($qTrending && $qTrending->num_rows > 0):
                        while ($menu = $qTrending->fetch_assoc()):
                            $nama_menu  = htmlspecialchars($menu['nama_menu']);
                            $harga      = number_format($menu['harga'], 0, ',', '.');
                            $nama_toko  = htmlspecialchars($menu['nama_toko']);
                            $foto_menu  = $menu['file_foto'] ? "../assets/img_produk/" . $menu['file_foto'] : "../assets/img/Garpusendok.png";
                            $terjual    = $menu['total_terjual'];
                    ?>
                    <a href="pesan.php?id_toko=<?= $menu['id_toko'] ?>" class="bg-white rounded-[20px] shadow-sm border border-orange-50 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-100/50 hover:border-orange-100 transition-all duration-300 group">
                        <div class="h-32 w-full overflow-hidden relative bg-orange-50/50 flex-shrink-0">
                            <img src="<?= $foto_menu ?>" alt="<?= $nama_menu ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm text-orange-600 text-[10px] font-bold px-2 py-1 rounded-lg shadow-sm flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                </svg>
                                <?= $terjual ?>
                            </span>
                        </div>
                        <div class="p-3 flex flex-col gap-1 flex-1 justify-between">
                            <div>
                                <h4 class="font-bold text-text-1 text-sm leading-tight line-clamp-1"><?= $nama_menu ?></h4>
                                <p class="text-[10px] text-text-3 font-medium mt-0.5"><?= $nama_toko ?></p>
                            </div>
                            <p class="font-extrabold text-primary text-sm mt-2">Rp <?= $harga ?></p>
                        </div>
                    </a>
                    <?php endwhile; endif; ?>
                </div>
            </div>

            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.25s;">
                <div class="bg-white rounded-t-[15px] rounded-b-[15px] shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr style="background-color: #004e17; color: white;" class="text-[10px] md:text-xs tracking-wider font-bold">
                                    <th class="py-3 px-4 uppercase">ID</th>
                                    <th class="py-3 px-4 uppercase">Kantin</th>
                                    <th class="py-3 px-4 uppercase">Tanggal</th>
                                    <th class="py-3 px-4 uppercase">Total</th>
                                    <th class="py-3 px-4 uppercase">Status</th>
                                    <th class="py-3 px-4 uppercase text-center">Kelola</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm text-gray-700">
                                <?php
                                $qPesananTable = $db_ekantin->query("
                                    SELECT p.id_pesanan, t.nama_toko, p.total_harga, p.tanggal_pesan, p.status_pesanan 
                                    FROM pesanan p 
                                    LEFT JOIN toko t ON p.id_toko = t.id_toko 
                                    WHERE p.id_users = '$id_users' 
                                    ORDER BY p.tanggal_pesan DESC 
                                    LIMIT 3
                                ");
                                if ($qPesananTable && $qPesananTable->num_rows > 0):
                                    $row_idx = 0;
                                    while ($pRow = $qPesananTable->fetch_assoc()):
                                        $row_idx++;
                                        $bg_class = ($row_idx % 2 == 0) ? 'bg-[#fcfcfc]' : 'bg-white';
                                        
                                        // Badge styling
                                        $status = strtolower($pRow['status_pesanan']);
                                        if ($status == 'pending') {
                                            $badge = '<span class="bg-[#fef3c7] text-[#92400e] px-3 py-1 rounded-full font-bold text-[10px]">Pending</span>';
                                        } elseif ($status == 'diproses') {
                                            $badge = '<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-bold text-[10px]">Diproses</span>';
                                        } elseif ($status == 'selesai') {
                                            $badge = '<span class="bg-[#dcfce7] text-[#166534] px-3 py-1 rounded-full font-bold text-[10px]">Selesai</span>';
                                        } else {
                                            $badge = '<span class="bg-[#fee2e2] text-[#991b1b] px-3 py-1 rounded-full font-bold text-[10px]">Batal</span>';
                                        }
                                ?>
                                <tr class="<?= $bg_class ?> border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 font-semibold text-gray-900"><?= $pRow['id_pesanan'] ?></td>
                                    <td class="py-3 px-4 font-bold"><?= htmlspecialchars($pRow['nama_toko'] ?? 'Toko Dihapus') ?></td>
                                    <td class="py-3 px-4 text-gray-500"><?= date('d/m/Y', strtotime($pRow['tanggal_pesan'])) ?></td>
                                    <td class="py-3 px-4 font-semibold">Rp <?= number_format($pRow['total_harga'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-4"><?= $badge ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="history.php" class="inline-block bg-[#fef3c7] text-[#92400e] hover:bg-[#fde68a] font-bold px-4 py-1.5 rounded-lg text-[10px] md:text-xs transition-colors shadow-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-500 font-medium">Belum ada pesanan.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
                        <a href="history.php" class="text-xs font-bold text-[#004e17] hover:underline flex items-center gap-1">Lihat Semua Pesanan &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>