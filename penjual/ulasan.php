<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if (!isset($_SESSION['penjual_id_users'])) {
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['penjual_id_users'];
$_SESSION['username'] = $_SESSION['penjual_username'];
$_SESSION['role']     = $_SESSION['penjual_role'];
$_SESSION['id_toko']   = $_SESSION['penjual_id_toko'];
$_SESSION['nama_toko'] = $_SESSION['penjual_nama_toko'];

$id_toko = $_SESSION['id_toko'];

// Filter rating
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$filter_query = "";
if ($rating_filter > 0 && $rating_filter <= 5) {
    $filter_query = " AND r.rating = $rating_filter";
}

// Ambil ulasan produk untuk toko ini
$sql = "SELECT r.*, pk.nama_menu, pk.file_foto, u.username, u.foto_profil, p.tanggal_pesan 
        FROM review r
        JOIN produk_kantin pk ON r.id_produk = pk.id_produk
        JOIN users u ON r.id_users = u.id_users
        JOIN pesanan p ON r.id_pesanan = p.id_pesanan
        WHERE pk.id_toko = ? $filter_query
        ORDER BY r.created_at DESC";

$stmt = $db_ekantin->prepare($sql);
$stmt->bind_param("i", $id_toko);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung statistik ulasan
$sql_stats = "SELECT COUNT(*) as total_review, AVG(rating) as avg_rating FROM review r JOIN produk_kantin pk ON r.id_produk = pk.id_produk WHERE pk.id_toko = ?";
$stmt_stats = $db_ekantin->prepare($sql_stats);
$stmt_stats->bind_param("i", $id_toko);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$total_review = $stats['total_review'] ?? 0;
$avg_rating = number_format($stats['avg_rating'] ?? 0, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Produk</title>

    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">
    
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-7xl mx-auto flex flex-col gap-6">

            <!-- Header -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
                <h1 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Ulasan Pelanggan</h1>
                <p class="text-text-3 mt-2 font-medium">Lihat tanggapan pembeli mengenai produk dari tokomu.</p>
            </div>

            <!-- Statistik Singkat -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.2s;">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-text-3 uppercase tracking-wider">Rata-Rata Rating</p>
                        <p class="text-3xl font-extrabold text-primary"><?= $avg_rating ?> <span class="text-sm font-medium text-text-3">/ 5.0</span></p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-text-3 uppercase tracking-wider">Total Ulasan</p>
                        <p class="text-3xl font-extrabold text-primary"><?= $total_review ?> <span class="text-sm font-medium text-text-3">ulasan</span></p>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap gap-2 animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.3s;">
                <a href="ulasan.php" class="px-4 py-2 rounded-xl text-sm font-bold transition-all <?= $rating_filter == 0 ? 'bg-primary text-white shadow-md' : 'bg-white text-text-3 hover:bg-gray-50 border border-gray-100' ?>">Semua</a>
                <?php for($i=5; $i>=1; $i--): ?>
                    <a href="ulasan.php?rating=<?= $i ?>" class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-1 <?= $rating_filter == $i ? 'bg-yellow-50 text-yellow-600 border border-yellow-200' : 'bg-white text-text-3 hover:bg-gray-50 border border-gray-100' ?>">
                        <?= $i ?> <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    </a>
                <?php endfor; ?>
            </div>

            <!-- List Ulasan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <?php if (empty($reviews)): ?>
                    <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white rounded-2xl border border-gray-100 border-dashed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="text-text-3 font-medium text-center">Belum ada ulasan untuk filter ini.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $delay = 0.4;
                    foreach ($reviews as $rev): 
                        $delay += 0.1;
                        $foto_src = $rev['file_foto'] ? "../assets/img_produk/" . htmlspecialchars($rev['file_foto']) : "../assets/img/no-image.png";
                        $tanggal = date('d M Y', strtotime($rev['created_at']));
                    ?>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-4 animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: <?= $delay ?>s;">
                        <div class="flex gap-4">
                            <!-- Foto Produk -->
                            <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                                <img src="<?= $foto_src ?>" alt="<?= htmlspecialchars($rev['nama_menu']) ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex flex-col flex-grow justify-center">
                                <h3 class="font-bold text-text-1"><?= htmlspecialchars($rev['nama_menu']) ?></h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-text-3">Oleh:</p>
                                    <div class="flex items-center gap-1.5">
                                        <?php if(!empty($rev['foto_profil'])): ?>
                                            <img src="../assets/img/profil/<?= htmlspecialchars($rev['foto_profil']) ?>" class="w-5 h-5 rounded-full object-cover border border-gray-200">
                                        <?php else: ?>
                                            <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                                <span class="text-[10px] font-bold text-primary"><?= strtoupper(substr($rev['username'], 0, 1)) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <span class="font-semibold text-text-1 text-xs"><?= htmlspecialchars($rev['username']) ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center mt-2">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?= $i <= $rev['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($rev['komentar'])): ?>
                        <div class="bg-gray-50/70 rounded-xl p-3 text-sm text-text-2 italic border border-gray-100">
                            "<?= htmlspecialchars($rev['komentar']) ?>"
                        </div>
                        <?php endif; ?>
                        <div class="text-right">
                            <span class="text-[10px] font-medium text-text-3"><?= $tanggal ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
        </div>
    </main>
</div>

<script src="../assets/js/navbar.js"></script>
</body>
</html>
