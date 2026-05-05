<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_users']) || $_SESSION['role'] != 'admin') {
  header("Location: ../index.php");
  exit();
}

function formatSingkat($n)
{
  if ($n >= 1000000000) {
    return 'Rp ' . round($n / 1000000000, 1) . 'M';
  } elseif ($n >= 1000000) {
    return 'Rp ' . round($n / 1000000, 1) . 'jt';
  } elseif
  ($n >= 1000) {
    return 'Rp ' . round($n / 1000, 1) . 'rb';
  }
  return 'Rp ' .
    number_format($n, 0, ',', '.');
}

$total_user = $db_ekantin->query("SELECT * FROM users")->num_rows;

$total_toko = $db_ekantin->query("SELECT * FROM toko")->num_rows;

$pesanan_pending = $db_ekantin->query("SELECT * FROM pesanan WHERE status_pesanan ='pending'")->num_rows;

// Ambil Total Transaksi (Semua Pesanan) 
$total_transaksi = $db_ekantin->query("SELECT * FROM
pesanan")->num_rows;

// Ambil Total Pendapatan (Hanya yang Selesai)
$res_pendapatan = $db_ekantin->query("SELECT SUM(total_harga) AS total FROM
pesanan WHERE status_pesanan = 'selesai'");
$data_pendapatan =
  $res_pendapatan->fetch_assoc();
$total_pendapatan = $data_pendapatan['total'] ??
  0; ?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin - E-Kantin</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

  <link rel="stylesheet" href="../assets/css/style.css" />

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            background: "#f7f8f9",
            primary: "#004900",
            "text-1": "#191c1c",
            "text-2": "#4e5a48",
            "text-3": "#5e6659",
          },
        },
      },
    };
  </script>
</head>

<body class="bg-slate-50 text-text-1">
  <div class="flex min-h-screen relative">
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow p-4 md:p-8 pt-24 lg:pt-8">
      <header class="mb-10">
        <h2 class="font-bold text-3xl md:text-4xl tracking-tight text-primary">
          Halo Sang Administrator!
        </h2>
        <p class="text-text-2 mt-2">
          Inilah ringkasan statistik E-Kantin hari ini.
        </p>
      </header>

      <div class="flex flex-wrap justify-center gap-4 md:gap-6">
        <div
          class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)]">
          <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">
              Anggota Aktif
            </p>
            <p class="text-4xl font-extrabold text-primary">
              <?php echo $total_user; ?>
            </p>
            <p class="text-xs text-text-2">pengguna terdaftar</p>
          </div>
          <img src="../assets/img/user-icon.png"
            class="absolute -bottom-2 -right-2 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
        </div>

        <div
          class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)]">
          <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">
              Toko Aktif
            </p>
            <p class="text-4xl font-extrabold text-primary">
              <?php echo $total_toko; ?>
            </p>
            <p class="text-xs text-text-2">toko berjalan</p>
          </div>
          <img src="../assets/img/store-icon.png"
            class="absolute -bottom-2 -right-2 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
        </div>

        <div
          class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)]">
          <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">
              Total Transaksi
            </p>
            <p class="text-4xl font-extrabold text-primary">
              <?php echo number_format($total_transaksi, 0, ',', '.'); ?>
            </p>
            <p class="text-xs text-text-2">seluruh pesanan masuk</p>
          </div>
          <img src="../assets/img/transaksi-icon.png"
            class="absolute -bottom-2 -right-2 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
        </div>

        <div
          class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)]">
          <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-text-3">
              Total Pendapatan
            </p>
            <p class="text-3xl font-extrabold text-primary">
              <?php echo formatSingkat($total_pendapatan); ?>
            </p>
            <p class="text-xs text-text-2">akumulasi uang beredar</p>
          </div>
          <img src="../assets/img/revenue-icon.png"
            class="absolute -bottom-2 -right-2 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
        </div>

        <div
          class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-orange-100 flex flex-col gap-2 group w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)]">
          <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-orange-600">
              Pesanan Pending
            </p>
            <p class="text-4xl font-extrabold text-orange-600">
              <?php echo $pesanan_pending; ?>
            </p>
            <p class="text-xs text-orange-700/60">belum diproses toko</p>
          </div>
          <img src="../assets/img/pending-icon.png"
            class="absolute -bottom-2 -right-2 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
        </div>
      </div>

      <!-- Header Bagian Tabel -->
      <div class="flex justify-between items-end mt-10 mb-4">
        <div>
          <h3 class="font-bold text-xl text-primary">
            Pesanan Perlu Diproses
          </h3>
          <p class="text-xs text-text-3 mt-1">
            5 daftar pesanan terbaru yang masih berstatus pending.
          </p>
        </div>
        <!-- Tombol Lihat Semua merujuk ke halaman kelola_pesanan.php -->
        <a href="kelola_pesanan.php"
          class="text-xs font-bold bg-primary/10 text-primary hover:bg-primary hover:text-white px-4 py-2 rounded-[10px] transition-all">
          Lihat Semua →
        </a>
      </div>

      <!-- Container Tabel -->
      <div class="w-full bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header Grid -->
        <div class="grid grid-cols-[80px_1fr_1fr_130px_100px] bg-primary px-6 py-4 gap-4">
          <p class="text-xs font-bold uppercase tracking-widest text-white">
            ID Pesanan
          </p>
          <p class="text-xs font-bold uppercase tracking-widest text-white">
            Pelanggan
          </p>
          <p class="text-xs font-bold uppercase tracking-widest text-white">
            Toko
          </p>
          <p class="text-xs font-bold uppercase tracking-widest text-white">
            Total Harga
          </p>
          <p class="text-xs font-bold uppercase tracking-widest text-white">
            Status
          </p>
        </div>
        <!-- Isi Tabel -->
        <div class="overflow-y-auto max-h-[400px]">
          <?php
          // Query untuk mengambil pesanan dengan status 'pending', dibatasi 5 data terbaru 
          $query_pending = "SELECT p.id_pesanan, u.username, t.nama_toko, p.total_harga, p.status_pesanan 
                              FROM pesanan p 
                              JOIN users u ON p.id_users = u.id_users 
                              JOIN toko t ON p.id_toko = t.id_toko 
                              WHERE p.status_pesanan = 'pending' 
                              ORDER BY p.tanggal_pesan DESC LIMIT 5";

          $result_pending = $db_ekantin->query($query_pending);

          if ($result_pending && $result_pending->num_rows > 0) {
            while ($data_pesanan = $result_pending->fetch_assoc()) {

              $id_pesanan = $data_pesanan['id_pesanan'];
              $nama_pelanggan = htmlspecialchars($data_pesanan['username'], ENT_QUOTES);
              $nama_toko = htmlspecialchars($data_pesanan['nama_toko'], ENT_QUOTES);
              $total_harga = "Rp " . number_format($data_pesanan['total_harga'], 0, ',', '.');
              $status = $data_pesanan['status_pesanan'];

              $tulisanStatus = "<span class='text-[11px] font-bold uppercase tracking-wider text-orange-600 bg-orange-100 px-3 py-1 rounded-full w-fit'>Pending</span>";

             
              echo "
      <div class='grid grid-cols-[80px_1fr_1fr_130px_100px] px-6 py-4 gap-4 border-b border-gray-100 items-center hover:bg-gray-50 transition-colors'>
        <p class='text-sm font-semibold text-text-2'>#ORD-$id_pesanan</p>
        <p class='text-sm font-medium text-text-1 truncate'>$nama_pelanggan</p>
        <p class='text-sm text-text-2 truncate'>$nama_toko</p>
        <p class='text-sm font-bold text-primary truncate'>$total_harga</p>
        <div>$tulisanStatus</div>
      </div>
      ";
            }
          } else {
            echo "
    <div class='p-8 text-center'>
      <p class='text-sm font-medium text-text-3'>Hore! Semua pesanan sudah diproses toko.</p>
    </div>
    ";
          } ?>
        </div>
      </div>
    </main>
  </div>
</body>

</html>