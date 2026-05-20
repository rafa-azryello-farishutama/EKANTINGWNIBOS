<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['pembeli_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];

$halaman = basename($_SERVER['PHP_SELF']);

$keyword = '';
if (isset($_POST['cari_pembeli']) && isset($_POST['keyword'])) {
    $keyword = $db_ekantin->real_escape_string(trim($_POST['keyword']));
} elseif (isset($_GET['keyword'])) {
    $keyword = $db_ekantin->real_escape_string(trim($_GET['keyword']));
}

function isStoreOpen($toko) {
    if (!$toko) return false;
    if (($toko['status'] ?? 'aktif') === 'tutup') return false;
    if (($toko['status'] ?? 'aktif') === 'buka')  return true;
    if (empty($toko['jam_buka']) || empty($toko['jam_tutup']) || $toko['jam_buka'] == '--:--' || $toko['jam_tutup'] == '--:--') {
        return true;
    }
    date_default_timezone_set('Asia/Jakarta');
    $now   = date('H:i');
    $buka  = $toko['jam_buka'];
    $tutup = $toko['jam_tutup'];
    if ($buka <= $tutup) {
        return ($now >= $buka && $now <= $tutup);
    } else {
        return ($now >= $buka || $now <= $tutup);
    }
}

// Periksa apakah kantin tertentu dipilih
$id_toko_selected = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : null;
$store_details    = null;

if ($id_toko_selected) {
    $qStore = $db_ekantin->prepare("SELECT * FROM toko WHERE id_toko = ?");
    $qStore->bind_param("i", $id_toko_selected);
    $qStore->execute();
    $resStore = $qStore->get_result();
    if ($resStore->num_rows > 0) {
        $store_details = $resStore->fetch_assoc();
    } else {
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
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .custom-hero-slider {
            height: 150px;
        }
        .custom-store-banner-default {
            height: 150px;
        }
        .custom-store-banner-image {
            height: 170px;
        }
        @media (min-width: 640px) {
            .custom-hero-slider {
                height: 180px;
            }
            .custom-store-banner-default {
                height: 180px;
            }
            .custom-store-banner-image {
                height: 200px;
            }
        }
        @media (min-width: 1024px) {
            .custom-hero-slider {
                height: 210px;
            }
            .custom-store-banner-default {
                height: 210px;
            }
            .custom-store-banner-image {
                height: 240px;
            }
        }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-3 sm:px-8 pt-20 lg:pt-8" style="padding-bottom: 7rem;">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-3 md:gap-6">

            <?php if (isset($_SESSION['pesan_error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3 text-red-700 animate-fadeInUp">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-grow">
                    <p class="font-bold text-sm">Gagal Melakukan Pemesanan</p>
                    <p class="text-xs text-red-600/90 mt-0.5"><?= htmlspecialchars($_SESSION['pesan_error']) ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 font-bold text-base">&times;</button>
            </div>
            <?php unset($_SESSION['pesan_error']); endif; ?>

            <!-- Header -->
            <header class="opacity-0 animate-fadeInUp" style="animation-delay:0.1s;">
                <div class="flex items-center gap-2 md:gap-3">
                    <?php if ($store_details): ?>
                    <a href="pesan.php"
                        class="flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-full bg-input text-text-2
                               hover:bg-primary hover:text-white transition-all duration-200 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-xl bg-input text-2xl md:text-3xl flex-shrink-0 ring-2 ring-primary/10 shadow-sm overflow-hidden">
                        <?php
                            $foto_toko_header = !empty($store_details['foto_toko']) ? "../assets/img_toko/" . $store_details['foto_toko'] : '';
                            if ($foto_toko_header): ?>
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
                        <?php if ($store_details):
                            $is_open_header = isStoreOpen($store_details); ?>
                        <p class="text-[11px] sm:text-xs font-semibold mt-1 flex items-center gap-1 <?= $is_open_header ? 'text-green-600' : 'text-red-600' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= htmlspecialchars($store_details['jam_buka'] ?? '--:--') ?> - <?= htmlspecialchars($store_details['jam_tutup'] ?? '--:--') ?> WIB
                            <?= $is_open_header ? '(Buka)' : '(Tutup)' ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Search Bar -->
            <div class="w-full animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.15s;">
                <form method="POST" action="pesan.php<?= $id_toko_selected ? '?id_toko=' . $id_toko_selected : '' ?>" class="flex gap-3">
                    <div class="flex flex-1 items-center gap-3 bg-input rounded-xl px-4 h-12 focus-within:ring-2 focus-within:ring-primary transition-all">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="keyword"
                               placeholder="<?= $store_details ? 'Cari menu di kantin ini...' : 'Cari kantin...' ?>"
                               value="<?= htmlspecialchars($keyword) ?>"
                               class="w-full h-12 bg-transparent border-none outline-none text-text-1 placeholder-gray-500 text-sm focus:ring-0 p-0">
                        <?php if ($keyword !== ''): ?>
                            <a href="pesan.php<?= $id_toko_selected ? '?id_toko=' . $id_toko_selected : '' ?>"
                               class="text-text-3 hover:text-text-1 text-sm font-medium pr-1">Reset</a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="cari_pembeli"
                        class="h-12 px-6 bg-submit text-white rounded-xl text-sm font-bold hover:bg-primary transition-colors whitespace-nowrap">
                        Cari
                    </button>
                </form>
            </div>

            <?php if (!$store_details): ?>
            <?php
            $slides = [];
            // Slide 0: Banner Utama Awal
            $slides[] = [
                'type' => 'image',
                'image' => '../assets/img/default_banner_app.jpg',
                'badge' => '🔥 KANTIN PILIHAN',
                'title' => 'Temukan Makanan<br>Favoritmu Hari Ini!',
                'desc' => 'Berbagai pilihan kantin sekolah tersedia.',
                'url' => 'pesan.php'
            ];

            // Tambahkan toko-toko sebagai slide berikutnya
            $qSlides = $db_ekantin->query("SELECT * FROM toko LIMIT 5");
            if ($qSlides && $qSlides->num_rows > 0) {
                while ($tSlide = $qSlides->fetch_assoc()) {
                    $banner = $tSlide['banner_toko'] ?? null;
                    $bannerSrc = ($banner && file_exists("../assets/img_banner/$banner")) 
                        ? "../assets/img_banner/$banner" 
                        : '../assets/img/default_banner_app.jpg';
                    
                    $isOpen = isStoreOpen($tSlide);
                    $slides[] = [
                        'type' => 'image',
                        'image' => $bannerSrc,
                        'badge' => '🏪 KANTIN: ' . strtoupper($tSlide['nama_toko']),
                        'title' => 'Nikmati Hidangan Lezat<br>di <span class="text-yellow-300">' . htmlspecialchars($tSlide['nama_toko']) . '</span>',
                        'desc' => 'Lokasi: ' . htmlspecialchars($tSlide['lokasi'] ?? 'Kantin Sekolah') . ' | Status: ' . ($isOpen ? '🟢 Buka' : '🔴 Tutup'),
                        'url' => 'pesan.php?id_toko=' . $tSlide['id_toko']
                    ];
                }
            }
            ?>
            <div class="opacity-0 animate-fadeInUp" style="animation-delay: 0.2s;">
                <div class="relative w-full overflow-hidden rounded-2xl shadow-md select-none group custom-hero-slider" id="hero-slider">
                <!-- Slides Container -->
                <div class="flex transition-transform duration-500 ease-out h-full w-full" id="slider-track">
                    <?php foreach ($slides as $idx => $slide): ?>
                        <div class="min-w-full h-full relative flex-shrink-0">
                            <!-- Slide Gambar Toko -->
                            <img src="<?= $slide['image'] ?>" class="absolute inset-0 w-full h-full object-cover object-center" alt="Banner">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-transparent"></div>
                            <div class="absolute inset-0 flex items-center px-6 sm:px-12">
                                <div class="relative z-10 text-white max-w-[85%]">
                                    <span class="inline-block text-[10px] sm:text-xs font-semibold px-3 py-1 rounded-full mb-2 bg-white/20 backdrop-blur-sm">
                                        <?= $slide['badge'] ?>
                                    </span>
                                    <h3 class="text-xl sm:text-2xl md:text-3xl font-extrabold leading-tight drop-shadow-md">
                                        <?= $slide['title'] ?>
                                    </h3>
                                    <p class="text-white/80 text-xs sm:text-sm mt-1 drop-shadow-sm font-medium">
                                        <?= $slide['desc'] ?>
                                    </p>
                                    <a href="<?= $slide['url'] ?>" class="mt-3 inline-flex items-center gap-2 bg-white text-primary font-extrabold text-xs sm:text-sm px-4 py-2 rounded-xl hover:bg-yellow-50 hover:shadow-md transition-all">
                                        Pesan Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navigation Arrows -->
                <button id="slide-prev" class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/25 hover:bg-black/40 backdrop-blur-sm text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button id="slide-next" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/25 hover:bg-black/40 backdrop-blur-sm text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <!-- Indicators -->
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-20">
                    <?php foreach ($slides as $idx => $slide): ?>
                        <span class="slide-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white cursor-pointer transition-all duration-300 <?= $idx === 0 ? 'bg-white !w-5' : '' ?>" data-index="<?= $idx ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const track = document.getElementById('slider-track');
                const prevBtn = document.getElementById('slide-prev');
                const nextBtn = document.getElementById('slide-next');
                const dots = document.querySelectorAll('.slide-dot');
                const slider = document.getElementById('hero-slider');
                
                if (!track || !slider) return;

                let index = 0;
                const max = <?= count($slides) ?>;
                let startX = 0;
                let currentX = 0;
                let isDragging = false;
                let autoPlay = setInterval(nextSlide, 4000);

                function updateSlider() {
                    track.style.transform = `translateX(-${index * 100}%)`;
                    dots.forEach((dot, i) => {
                        if (i === index) {
                            dot.classList.add('bg-white', '!w-5');
                            dot.classList.remove('bg-white/40');
                        } else {
                            dot.classList.remove('bg-white', '!w-5');
                            dot.classList.add('bg-white/40');
                        }
                    });
                }

                function nextSlide() {
                    index = (index + 1) % max;
                    updateSlider();
                }

                function prevSlide() {
                    index = (index - 1 + max) % max;
                    updateSlider();
                }

                if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetTimer(); });
                if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetTimer(); });

                dots.forEach(dot => {
                    dot.addEventListener('click', (e) => {
                        index = parseInt(e.target.dataset.index);
                        updateSlider();
                        resetTimer();
                    });
                });

                function resetTimer() {
                    clearInterval(autoPlay);
                    autoPlay = setInterval(nextSlide, 4000);
                }

                // Touch and drag support
                track.addEventListener('mousedown', dragStart);
                track.addEventListener('touchstart', dragStart, { passive: true });
                
                window.addEventListener('mousemove', dragMove);
                window.addEventListener('touchmove', dragMove, { passive: true });
                
                window.addEventListener('mouseup', dragEnd);
                window.addEventListener('touchend', dragEnd);

                function dragStart(e) {
                    isDragging = true;
                    startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                    clearInterval(autoPlay);
                    track.style.transition = 'none';
                }

                function dragMove(e) {
                    if (!isDragging) return;
                    currentX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                    const diffX = currentX - startX;
                    const percent = (diffX / slider.offsetWidth) * 100;
                    track.style.transform = `translateX(calc(-${index * 100}% + ${percent}%))`;
                }

                function dragEnd(e) {
                    if (!isDragging) return;
                    isDragging = false;
                    track.style.transition = 'transform 0.5s ease-out';
                    const diffX = currentX - startX;
                    const threshold = slider.offsetWidth * 0.15;
                    
                    if (Math.abs(diffX) > threshold && diffX !== 0) {
                        if (diffX > 0) {
                            prevSlide();
                        } else {
                            nextSlide();
                        }
                    } else {
                        updateSlider();
                    }
                    resetTimer();
                }
            });
            </script>

            <!-- STORE GRID -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 md:gap-4 mt-2">
                <?php
                $sqlKantin = "SELECT t.*, (SELECT COUNT(id_produk) FROM produk_kantin WHERE id_toko = t.id_toko AND status_menu = 'aktif') as total_menu FROM toko t";
                if ($keyword !== '') {
                    $sqlKantin .= " WHERE t.nama_toko LIKE '%$keyword%' OR t.lokasi LIKE '%$keyword%'";
                }
                $qKantin = $db_ekantin->query($sqlKantin);
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
                            <span class="text-primary font-bold text-3xl"><?= $initial ?></span>
                        <?php endif; ?>
                        <?php $is_open_card = isStoreOpen($kantin); ?>
                        <?php if ($is_open_card): ?>
                            <span class="absolute top-2 left-2 text-[9px] font-bold bg-green-500 text-white px-2 py-0.5 rounded shadow-sm z-10">Buka</span>
                        <?php else: ?>
                            <span class="absolute top-2 left-2 text-[9px] font-bold bg-red-500 text-white px-2 py-0.5 rounded shadow-sm z-10">Tutup</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 flex flex-col flex-grow min-w-0">
                        <p class="store-name font-semibold text-text-1 text-sm sm:text-base leading-snug truncate"><?= htmlspecialchars($kantin['nama_toko']) ?></p>
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
                    <div class="col-span-full text-center text-text-3 py-10 text-sm">
                        <?= $keyword !== '' ? 'Tidak ada kantin yang cocok dengan pencarian "' . htmlspecialchars($keyword) . '".' : 'Belum ada kantin yang terdaftar.' ?>
                    </div>
                <?php endif; ?>
            </div>


            <?php else: ?>
            <!-- ═══════════════════════════════════════════ -->
            <!--  VIEW 2: Produk Toko                        -->
            <!-- ═══════════════════════════════════════════ -->
            <div class="flex flex-col gap-3 md:gap-6">
                <?php
                $is_open = isStoreOpen($store_details);
                if (!$is_open): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3 text-red-700 animate-fadeInUp">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="font-bold text-sm">Kantin Sedang Tutup</p>
                        <p class="text-xs text-red-600/90 mt-0.5">Maaf, saat ini kantin sedang tutup atau di luar jam operasional.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- BANNER TOKO
                     Prioritas: banner_toko dari toko ini → fallback gradient coklat default -->
                <?php
                $banner_toko_detail = $store_details['banner_toko'] ?? null;
                $banner_detail_src  = $banner_toko_detail
                                      ? "../assets/img_banner/" . htmlspecialchars($banner_toko_detail)
                                      : null;
                
                // Tinggi dinamis: Besar jika ada foto, sedang jika tidak ada (default)
                $banner_height_class = $banner_detail_src ? 'custom-store-banner-image' : 'custom-store-banner-default';
                ?>
                <div class="relative rounded-xl sm:rounded-2xl overflow-hidden select-none banner-store-wrap w-full <?= $banner_height_class ?> transition-all duration-300">

                    <?php if ($banner_detail_src): ?>
                        <!-- Banner custom dari penjual (image) -->
                        <img src="<?= $banner_detail_src ?>" alt="Banner <?= htmlspecialchars($store_details['nama_toko']) ?>"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                        <!-- Teks overlay -->
                        <div class="absolute inset-0 flex items-center px-4 sm:px-7 overflow-hidden">
                            <div class="relative z-10 max-w-[70%] sm:max-w-none">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
                                      style="background:rgba(255,255,255,0.25);color:#fff;">✨ Menu Spesial</span>
                                <h3 class="text-white font-extrabold leading-tight banner-store-title drop-shadow-md">
                                    <?= htmlspecialchars($store_details['nama_toko']) ?>
                                </h3>
                                <p class="text-white/80 text-xs mt-1 drop-shadow-sm">Lihat dan pilih menu favoritmu di bawah.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Fallback: gradient coklat default -->
                        <div class="absolute inset-0 bg-gradient-to-br from-[#4a2800] to-[#b86600]"></div>
                        <div class="min-w-full h-full relative flex items-center px-4 sm:px-7 overflow-hidden">
                            <div class="relative z-10 max-w-[70%] sm:max-w-none">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
                                      style="background:rgba(255,255,255,0.2);color:#fff;">✨ Menu Spesial</span>
                                <h3 class="text-white font-extrabold leading-tight banner-store-title">Pesan Makanan<br>Dari Kantin Ini</h3>
                                <p class="text-white/70 text-xs mt-1">Lihat dan pilih menu favoritmu di bawah.</p>
                            </div>
                            <div class="absolute right-3 sm:right-5 bottom-0 opacity-25 leading-none banner-store-deco">🍽️</div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- MENU GRID -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 md:gap-4">
                    <?php
                    $sqlMenu = "SELECT * FROM produk_kantin WHERE id_toko = ? AND status_menu = 'aktif'";
                    if ($keyword !== '') {
                        $sqlMenu .= " AND nama_menu LIKE ?";
                    }
                    $qMenu = $db_ekantin->prepare($sqlMenu);
                    if ($keyword !== '') {
                        $search_param = "%$keyword%";
                        $qMenu->bind_param("is", $id_toko_selected, $search_param);
                    } else {
                        $qMenu->bind_param("i", $id_toko_selected);
                    }
                    $qMenu->execute();
                    $resMenu = $qMenu->get_result();
                    $j = 0;

                    if ($resMenu->num_rows > 0):
                        while ($menu = $resMenu->fetch_assoc()):
                            $j++;
                            $foto_produk  = $menu['file_foto'] ?? null;
                            $foto_menu_src = $foto_produk ? "../assets/img_produk/$foto_produk" : null;
                    ?>
                        <div class="opacity-0 bg-white rounded-xl border border-gray-100 overflow-hidden
                                    hover:border-primary/30 hover:shadow-sm transition-all duration-200 flex flex-col h-full"
                             style="animation:fadeInUp 0.4s ease-out <?= $j * 0.05 ?>s forwards;">
                            <div class="menu-img w-full h-28 sm:h-32 flex-shrink-0 bg-input flex items-center justify-center overflow-hidden relative">
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
                                    <?php if ($is_open): ?>
                                        <div class="flex items-center gap-1.5 min-h-[28px]"
                                             id="btn-group-<?= $menu['id_produk'] ?>"
                                             data-stok="<?= $menu['stok'] ?>"
                                             data-original="<?= htmlspecialchars($btn_add) ?>">
                                            <button onclick="addToCart(<?= $menu['id_produk'] ?>, '<?= addslashes($menu['nama_menu']) ?>', <?= $menu['harga'] ?>)"
                                                class="w-7 h-7 rounded-full bg-primary text-white font-light flex items-center justify-center hover:bg-submit active:scale-95 transition-all duration-150 shadow-sm"
                                                aria-label="Tambah">+</button>
                                        </div>
                                    <?php else: ?>
                                        <button disabled
                                            class="w-7 h-7 rounded-full bg-gray-200 text-gray-400 font-bold flex items-center justify-center cursor-not-allowed shadow-sm"
                                            aria-label="Tutup">-</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <div class="col-span-full bg-white rounded-xl border border-gray-100 p-8 text-center text-text-3 text-sm">
                            <?= $keyword !== '' ? 'Tidak ada menu yang cocok dengan pencarian "' . htmlspecialchars($keyword) . '".' : 'Menu belum tersedia di kantin ini.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.max-w-5xl -->
    </main>
</div>

<!-- Sticky Checkout Bar -->
<?php if ($id_toko_selected && $is_open): ?>
<div id="sticky-cart" class="fixed bottom-0 left-0 right-0 bg-white shadow-[0_-4px_20px_rgba(0,0,0,0.1)] border-t border-gray-100 z-40 transform translate-y-full transition-transform duration-300 lg:pl-80">
    <div class="max-w-5xl mx-auto px-4 sm:px-8 py-3 flex items-center justify-between gap-3">
        <div class="flex flex-col flex-shrink-0">
            <span class="text-[10px] sm:text-xs text-text-3 font-medium uppercase tracking-wider">Total Pesanan</span>
            <div class="flex items-center gap-2 mt-0.5">
                <span id="cart-total-items" class="bg-primary text-white text-xs font-bold px-2 py-0.5 rounded-full">0 item</span>
                <span id="cart-total-price" class="text-text-1 font-extrabold text-base sm:text-lg">Rp 0</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="simpanKeKeranjang()"
                class="border border-primary text-primary font-bold text-xs sm:text-sm px-3 sm:px-4 py-2.5 rounded-xl hover:bg-primary/5 active:scale-95 transition-all flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <span class="hidden sm:inline">Tambah ke</span> Keranjang
            </button>
            <form action="checkout.php" method="POST" id="checkout-form">
                <input type="hidden" name="cart_data" id="cart-data-input">
                <input type="hidden" name="id_toko" value="<?= htmlspecialchars($id_toko_selected) ?>">
                <button type="submit"
                    class="bg-primary text-white font-bold text-xs sm:text-sm px-4 sm:px-5 py-2.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md flex items-center gap-1.5">
                    Bayar Langsung
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Toast -->
<div id="toast"
     style="position:fixed; bottom:88px; left:50%; transform:translateX(-50%); opacity:0; transition:opacity 0.3s; background:#004900; color:white; font-size:0.85rem; font-weight:600; padding:10px 20px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.2); pointer-events:none; z-index:9999; white-space:nowrap;">
</div>

<script>
    let cart = {};
    const fmt = n => "Rp " + n.toLocaleString("id-ID");

    function addToCart(id, name, price) {
        let btnGroup   = document.getElementById("btn-group-" + id);
        let stok       = parseInt(btnGroup.getAttribute("data-stok"));
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
            if (cart[id].qty <= 0) delete cart[id];
        }
        updateCartUI();
    }

    function updateCartUI() {
        let totalItems = 0;
        let totalPrice = 0;

        Object.keys(cart).forEach(id => {
            let item = cart[id];
            totalItems += item.qty;
            totalPrice += item.price * item.qty;

            let btnGroup = document.getElementById("btn-group-" + id);
            if (btnGroup) {
                btnGroup.innerHTML = `
                    <button onclick="removeFromCart(${id})" class="w-7 h-7 rounded-full bg-input text-primary font-bold flex items-center justify-center hover:bg-gray-200 transition-colors">-</button>
                    <span class="text-sm font-bold w-5 text-center text-text-1">${item.qty}</span>
                    <button onclick="addToCart(${id}, '${item.name.replace(/'/g,"\\'")}', ${item.price})" class="w-7 h-7 rounded-full bg-primary text-white font-bold flex items-center justify-center hover:bg-submit transition-colors shadow-sm">+</button>
                `;
                let stok    = parseInt(btnGroup.getAttribute("data-stok"));
                let btnPlus = btnGroup.querySelector("button:last-child");
                if (item.qty >= stok) {
                    btnPlus.disabled = true;
                    btnPlus.classList.add("opacity-40", "cursor-not-allowed");
                }
                let card = btnGroup.closest('.bg-white');
                if (card) card.classList.add('border-primary', 'ring-1', 'ring-primary/30');
            }
        });

        document.querySelectorAll('[id^="btn-group-"]').forEach(btnGroup => {
            let id = btnGroup.id.replace('btn-group-', '');
            if (!cart[id]) {
                let originalBtn = btnGroup.getAttribute('data-original');
                if (originalBtn) btnGroup.innerHTML = originalBtn;
                let card = btnGroup.closest('.bg-white');
                if (card) card.classList.remove('border-primary', 'ring-1', 'ring-primary/30');
            }
        });

        const stickyCart   = document.getElementById("sticky-cart");
        const elTotalItems = document.getElementById("cart-total-items");
        const elTotalPrice = document.getElementById("cart-total-price");
        const cartInput    = document.getElementById("cart-data-input");

        if (totalItems > 0) {
            stickyCart.classList.remove("translate-y-full");
            elTotalItems.textContent = totalItems + " item";
            elTotalPrice.textContent = fmt(totalPrice);
            cartInput.value = JSON.stringify(cart);
        } else {
            stickyCart.classList.add("translate-y-full");
            cartInput.value = "";
        }
    }

    let toastTimer;
    function showToast(msg) {
        const t = document.getElementById("toast");
        t.textContent = msg;
        t.style.opacity = "1";
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            t.style.opacity = "0";
        }, 1800);
    }

    function simpanKeKeranjang() {
        if (Object.keys(cart).length === 0) return;
        const formData = new FormData();
        formData.append("cart_data", JSON.stringify(cart));
        formData.append("id_toko", document.querySelector('#checkout-form input[name="id_toko"]').value);
        formData.append("aksi", "simpan_cart");

        fetch("ajax_keranjang.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "ok") {
                    showToast("Disimpan ke Keranjang! 🔖");
                } else {
                    showToast("Gagal menyimpan, coba lagi.");
                }
            });
    }
</script>
</body>
</html>