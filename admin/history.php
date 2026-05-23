<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if (!isset($_SESSION['id_users']) || $_SESSION['role'] != 'admin') {
  header("Location: ../index.php");
  exit();
}

// Ambil filter (jika ada)
$filter = $_GET['filter'] ?? 'semua';
$where_status = "";
if ($filter !== 'semua') {
    // Validasi filter
    $allowed_filters = ['pending', 'diproses', 'selesai', 'dibatalkan'];
    if (in_array($filter, $allowed_filters)) {
        $where_status = " WHERE p.status_pesanan = '$filter'";
    } else {
        $filter = 'semua';
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Ambil data pesanan
$sql_pesanan = "SELECT p.id_pesanan, p.tanggal_pesan, p.total_harga, p.status_pesanan, p.catatan, p.alasan_tolak, pay.metode_bayar as metode_pembayaran, pay.bukti_bayar as bukti_pembayaran, 
                       t.nama_toko, u.username as nama_pelanggan 
                FROM pesanan p 
                JOIN toko t ON p.id_toko = t.id_toko 
                JOIN users u ON p.id_users = u.id_users
                LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
                $where_status
                ORDER BY p.tanggal_pesan DESC
                LIMIT $limit OFFSET $offset";

$stmt_pesanan = $db_ekantin->prepare($sql_pesanan);
$stmt_pesanan->execute();
$hasil_pesanan = $stmt_pesanan->get_result()->fetch_all(MYSQLI_ASSOC);

// Total items untuk pagination
$sql_count = "SELECT COUNT(*) as total FROM pesanan p $where_status";
$res_count = $db_ekantin->query($sql_count);
$total_items = $res_count->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Transaksi - E-Kantin Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 text-text-1">
  <div class="flex min-h-screen relative">
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow p-4 md:p-8 pt-24 lg:pt-8">
      <div class="w-full max-w-6xl mx-auto flex flex-col gap-6">
          <header class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
            <h2 class="font-bold text-3xl md:text-4xl tracking-tight text-primary">Riwayat Transaksi</h2>
            <p class="text-text-2 mt-2">Daftar seluruh pesanan yang terjadi di sistem E-Kantin.</p>
          </header>

          <!-- Filter Tab -->
          <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 flex items-center gap-2 flex-wrap" style="animation-delay: 0.15s;">
              <?php 
              $tabs = ['semua' => 'Semua', 'pending' => 'Pending', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
              foreach($tabs as $key => $label): 
                  $isActive = ($filter === $key);
                  $classActive = "bg-primary text-white shadow-sm";
                  $classInactive = "bg-white border border-gray-100 text-text-2 hover:border-primary/30 hover:text-primary";
              ?>
                  <a href="history.php?filter=<?= $key ?>"
                     class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 <?= $isActive ? $classActive : $classInactive ?>">
                      <?= $label ?>
                  </a>
              <?php endforeach; ?>
          </div>

          <!-- Container Tabel / List -->
          <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 w-full bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden" style="animation-delay: 0.2s;">
            <div class="grid grid-cols-[100px_1fr_1fr_130px_100px_80px] bg-primary px-6 py-4 gap-4 hidden md:grid">
                <p class="text-xs font-bold uppercase tracking-widest text-white">ID Pesanan</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Pelanggan</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Toko</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Total Harga</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white text-right">Detail</p>
            </div>
            
            <div class="flex flex-col">
              <?php if (empty($hasil_pesanan)): ?>
                  <div class="p-10 text-center">
                      <div class="w-16 h-16 bg-input rounded-full flex items-center justify-center text-3xl mx-auto mb-4">📝</div>
                      <p class="text-sm font-medium text-text-3">Belum ada transaksi ditemukan.</p>
                  </div>
              <?php else: ?>
                  <?php foreach ($hasil_pesanan as $pesanan): 
                      $status = strtolower($pesanan['status_pesanan']);
                      
                      $badgeClass = "";
                      $statusLabel = ucfirst($status);
                      if ($status == 'selesai') {
                          $badgeClass = "bg-green-100 text-green-700";
                      } elseif ($status == 'diproses') {
                          $badgeClass = "bg-blue-100 text-blue-700";
                      } elseif ($status == 'dibatalkan') {
                          $badgeClass = "bg-red-100 text-red-700";
                      } else {
                          $badgeClass = "bg-yellow-100 text-yellow-700";
                      }
                      
                      $waktu_pesan = date('d M Y · H:i', strtotime($pesanan['tanggal_pesan']));
                  ?>
                  <div class="grid grid-cols-1 md:grid-cols-[100px_1fr_1fr_130px_100px_80px] px-6 py-4 gap-4 border-b border-gray-50 items-center hover:bg-gray-50 transition-colors">
                      <div>
                          <p class="text-sm font-semibold text-text-2">#ORD-<?= sprintf("%04d", $pesanan['id_pesanan']) ?></p>
                          <p class="text-[10px] text-text-3 md:hidden mt-1"><?= $waktu_pesan ?></p>
                      </div>
                      <div>
                          <p class="text-sm font-bold text-text-1 truncate"><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></p>
                          <p class="text-xs text-text-3 hidden md:block mt-0.5"><?= $waktu_pesan ?></p>
                      </div>
                      <p class="text-sm text-text-2 truncate font-medium"><?= htmlspecialchars($pesanan['nama_toko']) ?></p>
                      <p class="text-sm font-bold text-primary truncate">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></p>
                      <div>
                          <span class="text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full w-fit <?= $badgeClass ?>"><?= $statusLabel ?></span>
                      </div>
                      <div class="text-left md:text-right">
                          <button onclick="toggleDetail('detail-<?= $pesanan['id_pesanan'] ?>')" class="text-xs font-bold text-primary bg-primary/10 hover:bg-primary hover:text-white px-3 py-1.5 rounded-lg transition-colors">Detail</button>
                      </div>
                      
                      <!-- Row for detail items -->
                      <div id="detail-<?= $pesanan['id_pesanan'] ?>" class="hidden col-span-1 md:col-span-6 mt-2 p-4 bg-white rounded-xl border border-gray-100 shadow-inner">
                          <p class="text-xs font-bold uppercase tracking-wider text-text-3 mb-2">Item Pesanan:</p>
                          <?php
                          $id_p = $pesanan['id_pesanan'];
                          $sql_detail = "SELECT dp.jumlah, pk.nama_menu, IF(dp.harga_satuan > 0, dp.harga_satuan, pk.harga) AS harga 
                                         FROM detail_pesanan dp 
                                         JOIN produk_kantin pk ON dp.id_produk = pk.id_produk 
                                         WHERE dp.id_pesanan = ?";
                          $stmt_det = $db_ekantin->prepare($sql_detail);
                          $stmt_det->bind_param("i", $id_p);
                          $stmt_det->execute();
                          $res_detail = $stmt_det->get_result();
                          
                          if($res_detail && $res_detail->num_rows > 0) {
                              while($item = $res_detail->fetch_assoc()) {
                                  echo '<div class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0">';
                                  echo '<span class="text-text-2">'.htmlspecialchars($item['nama_menu']).' <span class="text-text-3 font-semibold ml-1">×'.$item['jumlah'].'</span></span>';
                                  echo '<span class="font-bold text-text-1">Rp '.number_format($item['harga'] * $item['jumlah'], 0, ',', '.').'</span>';
                                  echo '</div>';
                              }
                          }
                          if (!empty($pesanan['catatan'])) {
                              echo '<div class="mt-3 text-xs italic text-text-3 bg-gray-50 p-2.5 rounded-lg">';
                              echo 'Catatan: "'.htmlspecialchars($pesanan['catatan']).'"';
                              echo '</div>';
                          }
                          if (!empty($pesanan['metode_pembayaran'])) {
                              $metodeLabel = ($pesanan['metode_pembayaran'] === 'qr') ? 'QRIS / QR Code' : 'Transfer Bank';
                              echo '<div class="mt-2 flex items-center justify-between text-xs text-text-3 bg-gray-50/50 p-2.5 rounded-lg border border-gray-100">';
                              echo '<span>Metode: <strong class="text-primary">'.$metodeLabel.'</strong></span>';
                              echo '<div class="flex gap-3 items-center">';
                              if (!empty($pesanan['bukti_pembayaran'])) {
                                  echo '<a href="../assets/uploads_bukti/'.htmlspecialchars($pesanan['bukti_pembayaran']).'" target="_blank" class="font-bold text-primary hover:underline">Lihat Bukti Bayar</a>';
                              }
                              echo '<a href="../apps/struk.php?id_pesanan='.$pesanan['id_pesanan'].'" target="_blank" class="font-bold text-blue-600 hover:underline">📄 Lihat Struk</a>';
                              echo '</div>';
                              echo '</div>';
                          }
                          if ($status === 'dibatalkan' && !empty($pesanan['alasan_tolak'])) {
                              echo '<div class="mt-2 text-xs font-semibold text-red-600 bg-red-50 border border-red-100 p-2.5 rounded-lg">';
                              echo 'Alasan Ditolak: "'.htmlspecialchars($pesanan['alasan_tolak']).'"';
                              echo '</div>';
                          }
                          ?>
                      </div>
                  </div>
                  <?php endforeach; ?>
              <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <!-- Pagination -->
            <div class="px-6 py-4 bg-white border-t border-gray-100 flex justify-center gap-2">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?filter=<?= $filter ?>&page=<?= $i ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors <?= ($page == $i) ? 'bg-primary text-white' : 'bg-white text-text-2 border border-gray-200 hover:border-primary hover:text-primary' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
          </div>
      </div>
    </main>
  </div>
  <script>
      function toggleDetail(id) {
          const el = document.getElementById(id);
          if (el.classList.contains('hidden')) {
              el.classList.remove('hidden');
          } else {
              el.classList.add('hidden');
          }
      }
  </script>
</body>
</html>
