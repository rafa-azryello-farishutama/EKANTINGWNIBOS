<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['penjual_id_users'])){
    header("Location: ../index.php");
    exit;
}

$id_toko = $_SESSION['id_toko'];
$halaman = basename($_SERVER['PHP_SELF']);

// Ambil statistik rating
$qStat = $db_ekantin->prepare("
    SELECT 
        COUNT(r.id_review) as total_review,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(CASE WHEN r.rating = 5 THEN 1 END) as r5,
        COUNT(CASE WHEN r.rating = 4 THEN 1 END) as r4,
        COUNT(CASE WHEN r.rating = 3 THEN 1 END) as r3,
        COUNT(CASE WHEN r.rating = 2 THEN 1 END) as r2,
        COUNT(CASE WHEN r.rating = 1 THEN 1 END) as r1
    FROM review r
    JOIN produk_kantin pk ON r.id_produk = pk.id_produk
    WHERE pk.id_toko = ?
");
$qStat->bind_param("i", $id_toko);
$qStat->execute();
$stat = $qStat->get_result()->fetch_assoc();

$total_review = $stat['total_review'];
$avg_rating = round($stat['avg_rating'], 1);

// Ambil daftar review
$qReview = $db_ekantin->prepare("
    SELECT r.*, u.username, pk.nama_menu, p.tanggal_pesan
    FROM review r
    JOIN users u ON r.id_users = u.id_users
    JOIN produk_kantin pk ON r.id_produk = pk.id_produk
    JOIN pesanan p ON r.id_pesanan = p.id_pesanan
    WHERE pk.id_toko = ?
    ORDER BY r.created_at DESC
");
$qReview->bind_param("i", $id_toko);
$qReview->execute();
$reviews = $qReview->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Produk</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-background text-text-1">

<div class="flex min-h-screen relative">
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-10 pt-20 lg:pt-8 overflow-y-auto">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

            <header class="animate-[fadeInUp_0.3s_ease-out_forwards] opacity-0">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Ulasan & Penilaian</h2>
                <p class="text-text-3 mt-1 text-sm">Lihat apa kata pelanggan tentang produk Anda</p>
            </header>

            <!-- Statistik -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row gap-8 items-center animate-[fadeInUp_0.4s_ease-out_forwards] opacity-0">
                <div class="flex flex-col items-center text-center">
                    <span class="text-5xl font-extrabold text-primary"><?= $avg_rating ?></span>
                    <div class="flex items-center gap-1 my-2">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <span class="text-lg <?= $i <= round($avg_rating) ? 'text-yellow-400' : 'text-gray-200' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="text-sm text-text-3 font-medium"><?= $total_review ?> Ulasan</span>
                </div>

                <div class="flex-grow w-full flex flex-col gap-2">
                    <?php for($i=5; $i>=1; $i--): 
                        $count = $stat["r$i"];
                        $pct = $total_review > 0 ? ($count / $total_review) * 100 : 0;
                    ?>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-20 text-right font-medium text-text-2 whitespace-nowrap"><?= $i ?> Bintang</span>
                        <div class="flex-grow h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                        <span class="w-8 text-text-3"><?= $count ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Daftar Review -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-bold text-lg text-text-1">Daftar Ulasan</h3>
                </div>
                
                <div class="divide-y divide-gray-50">
                    <?php if (empty($reviews)): ?>
                    <div class="p-10 text-center text-text-3">
                        <div class="text-4xl mb-3">💬</div>
                        <p class="font-semibold text-text-2">Belum ada ulasan</p>
                        <p class="text-sm mt-1">Ulasan pelanggan akan muncul di sini.</p>
                    </div>
                    <?php else: ?>
                        <?php foreach($reviews as $r): ?>
                        <div class="p-6 flex gap-4 hover:bg-gray-50/50 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                                <?= strtoupper(substr($r['username'], 0, 1)) ?>
                            </div>
                            <div class="flex-grow min-w-0">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                    <h4 class="font-bold text-text-1 text-sm"><?= htmlspecialchars($r['username']) ?></h4>
                                    <span class="text-xs text-text-3"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></span>
                                </div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex items-center gap-0.5">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="text-sm <?= $i <= $r['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-xs font-semibold text-text-2 bg-input px-2 py-0.5 rounded-md">
                                        <?= htmlspecialchars($r['nama_menu']) ?>
                                    </span>
                                </div>
                                <?php if($r['komentar']): ?>
                                <p class="text-sm text-text-2 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($r['komentar'])) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>
