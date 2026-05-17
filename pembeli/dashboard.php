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

$id_users = $_SESSION['id_users'];
$username = $_SESSION['username'] ?? 'Pembeli';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembeli</title>
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
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.4s;">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-bold text-text-1 text-base">Pilihan Kantin</h3>
                        <p class="text-xs text-text-3">Temukan kantin favorit Anda di sini</p>
                    </div>
                    <a href="pesan.php" class="text-xs font-bold text-primary hover:underline underline-offset-4">Lihat Semua</a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php
                    $qKantin = $db_ekantin->query("SELECT t.*, COUNT(pk.id_produk) as total_menu FROM toko t LEFT JOIN produk_kantin pk ON t.id_toko = pk.id_toko GROUP BY t.id_toko LIMIT 6");
                    if ($qKantin && $qKantin->num_rows > 0):
                        while ($kantin = $qKantin->fetch_assoc()):
                            $nama       = htmlspecialchars($kantin['nama_toko']);
                            $lokasi     = htmlspecialchars($kantin['lokasi'] ?? 'Kantin Sekolah');
                            $total_menu = $kantin['total_menu'];
                            $foto_toko  = $kantin['foto_toko'] ?? null;
                            $foto_src   = $foto_toko ? "../assets/img_toko/$foto_toko" : null;
                            $initial    = strtoupper(substr($nama, 0, 1));
                    ?>
                    <a href="pesan.php?id_toko=<?= $kantin['id_toko'] ?>"
                        class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg hover:shadow-green-100/50 hover:border-green-100 transition-all duration-300 group">

                        <!-- Area Gambar -->
                        <div class="h-[140px] w-full overflow-hidden relative bg-gradient-to-br from-primary/5 to-green-100/60 flex-shrink-0">
                            <?php if ($foto_src): ?>
                                <img src="<?= $foto_src ?>" alt="<?= $nama ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-2xl bg-white/80 shadow-sm flex items-center justify-center">
                                        <span class="text-primary font-extrabold text-3xl"><?= $initial ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <span class="absolute top-3 right-3 text-[10px] font-bold bg-white/90 backdrop-blur-sm text-green-700 px-2 py-1 rounded-full shadow-sm">
                                <?= $total_menu ?> menu
                            </span>
                        </div>

                        <!-- Info Kantin -->
                        <div class="p-4 flex flex-col gap-1 flex-1">
                            <h4 class="font-bold text-text-1 text-sm leading-tight line-clamp-1"><?= $nama ?></h4>
                            <p class="text-xs text-text-3 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span class="truncate"><?= $lokasi ?></span>
                            </p>
                            <div class="mt-auto pt-3">
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-primary group-hover:gap-2 transition-all duration-300">
                                    Lihat Menu
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; else: ?>
                    <div class="col-span-3 bg-white rounded-[20px] px-6 py-10 text-center text-sm text-text-3 border border-gray-100">
                        Belum ada kantin terdaftar.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>