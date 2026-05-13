<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'penjual'){
    header("Location: ../index.php");
    exit;
}

$id_toko  = $_SESSION['id_toko'];
$nama_toko = $_SESSION['nama_toko'] ?? 'Toko';

$qTotalPesanan = $db_ekantin->query("SELECT COUNT(*) as total FROM pesanan WHERE id_toko='$id_toko'");
$total_pesanan = $qTotalPesanan->fetch_assoc()['total'] ?? 0;

$qTotalMenu = $db_ekantin->query("SELECT COUNT(*) as total FROM produk_kantin WHERE id_toko='$id_toko'");
$total_menu = $qTotalMenu->fetch_assoc()['total'] ?? 0;

$qPendapatan = $db_ekantin->query("SELECT SUM(total_harga) as total FROM pesanan WHERE id_toko='$id_toko' AND status_pesanan='selesai'");
$pendapatan = $qPendapatan->fetch_assoc()['total'] ?? 0;

$qPesanan = $db_ekantin->query("
    SELECT p.id_pesanan, u.username, p.total_harga, p.tanggal_pesan, p.status_pesanan
    FROM pesanan p
    JOIN users u ON p.id_users = u.id_users
    WHERE p.id_toko = '$id_toko'
    ORDER BY p.tanggal_pesan DESC
    LIMIT 5
");

function badgeStatus($status) {
    return match($status) {
        'pending'   => ['text-yellow-600', 'bg-yellow-100', 'Pending'],
        'diproses'  => ['text-blue-600',   'bg-blue-100',   'Diproses'],
        'selesai'   => ['text-green-600',  'bg-green-100',  'Selesai'],
        'dibatalkan'=> ['text-red-600',    'bg-red-100',    'Dibatalkan'],
        default     => ['text-gray-600',   'bg-gray-100',   ucfirst($status)],
    };
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
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

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

            <!-- Header -->
            <header>
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">
                    Selamat Datang, <?= htmlspecialchars($nama_toko) ?>!
                </h2>
                <p class="text-text-3 mt-1 text-sm">Inilah keadaan toko kamu hari ini.</p>
            </header>

            <!-- Cards Statistik -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

                <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Total Pesanan</p>
                    <p class="text-4xl font-extrabold text-primary"><?= $total_pesanan ?></p>
                    <p class="text-xs text-text-2">semua pesanan masuk</p>
                </div>

                <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Total Menu</p>
                    <p class="text-4xl font-extrabold text-primary"><?= $total_menu ?></p>
                    <p class="text-xs text-text-2">menu terdaftar</p>
                </div>

                <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2 col-span-2 lg:col-span-1">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pendapatan</p>
                    <p class="text-3xl font-extrabold text-primary leading-tight">
                        Rp <?= number_format($pendapatan, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-text-2">dari pesanan selesai</p>
                </div>

            </div>

            <!-- Tabel Pesanan Terbaru -->
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <p class="font-bold text-text-1">Pesanan Terbaru</p>
                    <a href="pesanan.php" class="text-xs font-bold text-primary hover:underline underline-offset-4">Lihat Semua</a>
                </div>

                <!-- Header tabel (desktop) -->
                <div class="hidden md:grid grid-cols-[60px_1fr_140px_160px_110px] bg-primary px-6 py-3 gap-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Pembeli</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Total</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Tanggal</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
                </div>

                <?php if($qPesanan->num_rows > 0): ?>
                    <?php while($row = $qPesanan->fetch_assoc()):
                        [$tColor, $bgColor, $label] = badgeStatus($row['status_pesanan']);
                        $tanggal = date('d M Y', strtotime($row['tanggal_pesan']));
                    ?>
                    <div class="border-b border-gray-100 hover:bg-gray-50 transition-colors last:border-b-0">

                        <!-- Desktop -->
                        <div class="hidden md:grid grid-cols-[60px_1fr_140px_160px_110px] px-6 py-4 gap-4 items-center">
                            <p class="text-sm text-text-3">#<?= $row['id_pesanan'] ?></p>
                            <p class="text-sm font-medium text-text-1"><?= htmlspecialchars($row['username']) ?></p>
                            <p class="text-sm text-text-2">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></p>
                            <p class="text-sm text-text-3"><?= $tanggal ?></p>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full w-fit <?= $tColor ?> <?= $bgColor ?>">
                                <?= $label ?>
                            </span>
                        </div>

                        <!-- Mobile -->
                        <div class="flex md:hidden items-center gap-3 px-4 py-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-text-1"><?= htmlspecialchars($row['username']) ?></p>
                                <p class="text-xs text-text-3"><?= $tanggal ?> · Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></p>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $tColor ?> <?= $bgColor ?>">
                                <?= $label ?>
                            </span>
                        </div>

                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="px-6 py-10 text-center text-sm text-text-3">
                        Belum ada pesanan masuk.
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </main>
</div>
</body>
</html>