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
$nama_toko = $_SESSION['nama_toko'] ?? 'Toko';

// Ambil status operasional toko
$qToko = $db_ekantin->query("SELECT * FROM toko WHERE id_toko = '$id_toko'");
$toko = $qToko->fetch_assoc();
$status_toko = $toko['status'] ?? 'aktif';

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

$is_open = isStoreOpen($toko);

// Handle Perubahan Status Manual/Otomatis
if (isset($_POST['set_status'])) {
    $new_status = $db_ekantin->real_escape_string($_POST['set_status']);
    if (in_array($new_status, ['aktif', 'tutup', 'buka'])) {
        $db_ekantin->query("UPDATE toko SET status = '$new_status' WHERE id_toko = '$id_toko'");
    }
    header("Location: dashboard.php");
    exit;
}

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

function badgeStatus($status)
{
    return match ($status) {
        'pending' => ['text-yellow-600', 'bg-yellow-100', 'Pending'],
        'diproses' => ['text-blue-600', 'bg-blue-100', 'Diproses'],
        'selesai' => ['text-green-600', 'bg-green-100', 'Selesai'],
        'dibatalkan' => ['text-red-600', 'bg-red-100', 'Dibatalkan'],
        default => ['text-gray-600', 'bg-gray-100', ucfirst($status)],
    };
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">

        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
            <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

                <!-- Header -->
                <header class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4" style="animation-delay: 0.1s;">
                    <div>
                        <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary flex items-center gap-2">
                            Selamat Datang, <?= htmlspecialchars($nama_toko) ?> <span
                                class="inline-block hover:animate-bounce origin-bottom-right">👋</span>
                        </h2>
                        <p class="text-text-3 mt-1 text-sm">Inilah keadaan toko kamu hari ini.</p>
                    </div>
                    <!-- Status Toko Control -->
                    <form method="POST" class="flex flex-wrap items-center gap-3 bg-white px-5 py-3 rounded-2xl border border-gray-100 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-widest text-text-3">Status Toko:</span>
                        
                        <?php if ($status_toko == 'buka'): ?>
                            <span class="text-xs font-semibold text-green-700 bg-green-100 px-3 py-1 rounded-full">Buka (Manual)</span>
                            <button type="submit" name="set_status" value="tutup" class="text-xs font-bold bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition-all active:scale-95 shadow-sm">
                                Tutup Toko
                            </button>
                            <button type="submit" name="set_status" value="aktif" class="text-xs font-bold bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-xl transition-all active:scale-95 shadow-sm">
                                Ikuti Jadwal
                            </button>
                        <?php elseif ($status_toko == 'tutup'): ?>
                            <span class="text-xs font-semibold text-red-600 bg-red-100 px-3 py-1 rounded-full">Tutup (Manual)</span>
                            <button type="submit" name="set_status" value="buka" class="text-xs font-bold bg-primary hover:bg-submit text-white px-4 py-2 rounded-xl transition-all active:scale-95 shadow-sm">
                                Buka Toko
                            </button>
                            <button type="submit" name="set_status" value="aktif" class="text-xs font-bold bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-xl transition-all active:scale-95 shadow-sm">
                                Ikuti Jadwal
                            </button>
                        <?php else: ?>
                            <!-- Status Aktif / Auto -->
                            <?php if ($is_open): ?>
                                <span class="text-xs font-semibold text-green-700 bg-green-100 px-3 py-1 rounded-full">Buka (Jadwal)</span>
                                <button type="submit" name="set_status" value="tutup" class="text-xs font-bold bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition-all active:scale-95 shadow-sm">
                                    Tutup Toko
                                </button>
                            <?php else: ?>
                                <span class="text-xs font-semibold text-red-600 bg-red-100 px-3 py-1 rounded-full">Tutup (Jadwal)</span>
                                <button type="submit" name="set_status" value="buka" class="text-xs font-bold bg-primary hover:bg-submit text-white px-4 py-2 rounded-xl transition-all active:scale-95 shadow-sm">
                                    Buka Toko
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>
                </header>

                <!-- Cards Statistik -->
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0"
                    style="animation-delay: 0.2s;">

                    <div
                        class="bg-white rounded-[20px] p-6 shadow-sm border border-blue-50 flex flex-col gap-2 transition-all hover:-translate-y-1 hover:shadow-md hover:shadow-blue-100/50 relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 w-28 h-28 bg-blue-100 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-500">
                        </div>
                        <div class="flex justify-between items-start relative z-10">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Total Pesanan</p>
                                <p class="text-4xl font-extrabold text-blue-600 mt-1"><?= $total_pesanan ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-text-2 relative z-10 mt-1">semua pesanan masuk</p>
                    </div>

                    <div
                        class="bg-white rounded-[20px] p-6 shadow-sm border border-orange-50 flex flex-col gap-2 transition-all hover:-translate-y-1 hover:shadow-md hover:shadow-orange-100/50 relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 w-28 h-28 bg-orange-100 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-500">
                        </div>
                        <div class="flex justify-between items-start relative z-10">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Total Menu</p>
                                <p class="text-4xl font-extrabold text-orange-500 mt-1"><?= $total_menu ?></p>
                            </div>
                            <div class="p-3 bg-orange-100 text-orange-500 rounded-xl shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-text-2 relative z-10 mt-1">menu terdaftar</p>
                    </div>

                    <div
                        class="bg-white rounded-[20px] p-6 shadow-sm border border-green-50 flex flex-col gap-2 col-span-2 lg:col-span-1 transition-all hover:-translate-y-1 hover:shadow-md hover:shadow-green-100/50 relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 w-28 h-28 bg-green-100 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-500">
                        </div>
                        <div class="flex justify-between items-start relative z-10">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pendapatan</p>
                                <p class="text-3xl font-extrabold text-green-600 leading-tight mt-1">
                                    Rp <?= number_format($pendapatan, 0, ',', '.') ?>
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 text-green-600 rounded-xl shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-text-2 relative z-10 mt-1">dari pesanan selesai</p>
                    </div>

                </div>

                <!-- Tabel Pesanan Terbaru -->
                <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0"
                    style="animation-delay: 0.3s;">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <p class="font-bold text-text-1">Pesanan Terbaru</p>
                        <a href="pesanan.php"
                            class="text-xs font-bold text-primary hover:underline underline-offset-4">Lihat Semua</a>
                    </div>

                    <!-- Header tabel (desktop) -->
                    <div
                        class="hidden md:grid grid-cols-[60px_1fr_140px_160px_110px] bg-gradient-to-r from-primary to-[#006800] px-6 py-3 gap-4">
                        <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
                        <p class="text-xs font-bold uppercase tracking-widest text-white">Pembeli</p>
                        <p class="text-xs font-bold uppercase tracking-widest text-white">Total</p>
                        <p class="text-xs font-bold uppercase tracking-widest text-white">Tanggal</p>
                        <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
                    </div>

                    <?php if ($qPesanan->num_rows > 0): ?>
                        <?php while ($row = $qPesanan->fetch_assoc()):
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
                                    <span
                                        class="text-xs font-semibold px-2 py-1 rounded-full w-fit <?= $tColor ?> <?= $bgColor ?>">
                                        <?= $label ?>
                                    </span>
                                </div>

                                <!-- Mobile -->
                                <div class="flex md:hidden items-center gap-3 px-4 py-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-text-1"><?= htmlspecialchars($row['username']) ?>
                                        </p>
                                        <p class="text-xs text-text-3"><?= $tanggal ?> · Rp
                                            <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                        </p>
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