<?php 
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if (!isset($_SESSION['id_users']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// PROSES UPDATE STATUS (AKTIF/NONAKTIF) UNTUK TERLAPOR
if (isset($_POST['update_status'])) {
    $id_target = $_POST['id_target'];
    $status_baru = $_POST['status_aksi'];
    
    // Update status user
    $db_ekantin->query("UPDATE users SET status='$status_baru' WHERE id_users='$id_target'");
    
    // Opsional: Tandai laporan sebagai diproses (jika ada kolom status_laporan nanti, saat ini kita biarkan)
    header("Location: laporan.php?success=1");
    exit;
}

// Data Stats
$total_laporan = $db_ekantin->query("SELECT id_laporan FROM laporan_pembeli")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Laporan Pembeli</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .popup-enter { animation: popupIn 0.25s cubic-bezier(.4,0,.2,1) both; }
        @keyframes popupIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8">
            <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
                <div>
                    <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Daftar Laporan</h2>
                    <p class="text-text-3 font-body mt-2">Daftar pembeli yang dilaporkan oleh penjual.</p>
                </div>
            </header>

            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div id="notif-success" class="mb-6 px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl flex items-center gap-2 w-fit shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Status pengguna berhasil diperbarui.
                    <button onclick="document.getElementById('notif-success').remove()" class="ml-2 opacity-70 hover:opacity-100">&times;</button>
                </div>
            <?php endif; ?>

            <div class="w-full bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="text-left text-xs font-bold uppercase tracking-widest px-5 py-4">Tanggal</th>
                            <th class="text-left text-xs font-bold uppercase tracking-widest px-4 py-4">Pelapor (Penjual)</th>
                            <th class="text-left text-xs font-bold uppercase tracking-widest px-4 py-4">Terlapor (Pembeli)</th>
                            <th class="text-left text-xs font-bold uppercase tracking-widest px-4 py-4">Alasan</th>
                            <th class="text-left text-xs font-bold uppercase tracking-widest px-4 py-4">Status Akun</th>
                            <th class="text-center text-xs font-bold uppercase tracking-widest px-4 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $query = "
                        SELECT l.*, 
                               u1.username AS nama_pelapor, 
                               u2.username AS nama_terlapor,
                               u2.status AS status_terlapor
                        FROM laporan_pembeli l
                        LEFT JOIN users u1 ON l.id_pelapor = u1.id_users
                        LEFT JOIN users u2 ON l.id_terlapor = u2.id_users
                        ORDER BY l.waktu_laporan DESC
                    ";
                    $result = $db_ekantin->query($query);
                    if ($result && $result->num_rows > 0):
                        while($data = $result->fetch_assoc()):
                            $tgl_lapor = date('d M Y, H:i', strtotime($data['waktu_laporan']));
                            if ($data['status_terlapor'] == 'aktif') {
                                $status_badge = "<span class='text-[10px] font-bold text-green-600 bg-green-100 px-2.5 py-1 rounded-full uppercase'>Aktif</span>";
                            } else {
                                $status_badge = "<span class='text-[10px] font-bold text-red-500 bg-red-100 px-2.5 py-1 rounded-full uppercase'>Nonaktif</span>";
                            }
                    ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 text-xs text-text-3 whitespace-nowrap"><?= $tgl_lapor ?></td>
                            <td class="px-4 py-4 font-semibold text-text-1"><?= htmlspecialchars($data['nama_pelapor'] ?? 'Unknown') ?></td>
                            <td class="px-4 py-4">
                                <p class="font-bold text-primary"><?= htmlspecialchars($data['nama_terlapor'] ?? 'Unknown') ?></p>
                                <p class="text-xs text-text-3 mt-0.5">ID: <?= htmlspecialchars($data['id_terlapor']) ?></p>
                            </td>
                            <td class="px-4 py-4 text-text-2 max-w-[200px]"><?= htmlspecialchars($data['alasan']) ?></td>
                            <td class="px-4 py-4"><?= $status_badge ?></td>
                            <td class="px-4 py-4 text-center">
                                <?php if ($data['status_terlapor'] == 'aktif'): ?>
                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memblokir akun ini?');">
                                    <input type="hidden" name="id_target" value="<?= $data['id_terlapor'] ?>">
                                    <input type="hidden" name="status_aksi" value="nonaktif">
                                    <button type="submit" name="update_status" class="text-[11px] font-bold bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg transition-all whitespace-nowrap">🚫 Blokir</button>
                                </form>
                                <?php else: ?>
                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan akun ini?');">
                                    <input type="hidden" name="id_target" value="<?= $data['id_terlapor'] ?>">
                                    <input type="hidden" name="status_aksi" value="aktif">
                                    <button type="submit" name="update_status" class="text-[11px] font-bold bg-green-50 text-green-600 hover:bg-green-100 px-4 py-2 rounded-lg transition-all whitespace-nowrap">✅ Buka Blokir</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-text-3">Belum ada laporan.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
