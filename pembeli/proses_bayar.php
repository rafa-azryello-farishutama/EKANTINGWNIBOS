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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pesan.php");
    exit;
}

$id_users    = (int) $_SESSION['id_users'];
$id_toko     = (int) ($_POST['id_toko'] ?? 0);
$total_harga = (int) ($_POST['total_harga'] ?? 0);
$catatan     = trim($_POST['catatan'] ?? '');
$cart_data   = json_decode($_POST['cart_data'] ?? '{}', true);

if (!$id_toko || empty($cart_data) || $total_harga <= 0) {
    header("Location: pesan.php");
    exit;
}

// Validasi Server-Side: Apakah Toko Sedang Buka/Dalam Jam Operasional?
$qTokoVal = $db_ekantin->prepare("SELECT * FROM toko WHERE id_toko = ?");
$qTokoVal->bind_param("i", $id_toko);
$qTokoVal->execute();
$tokoVal = $qTokoVal->get_result()->fetch_assoc();

function isStoreOpenBackend($toko) {
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

if (!isStoreOpenBackend($tokoVal)) {
    $_SESSION['pesan_error'] = "Gagal memproses pesanan: Toko saat ini sedang tutup atau di luar jam operasional.";
    header("Location: pesan.php?id_toko=" . $id_toko);
    exit;
}

// Bukti pembayaran diupload nanti di halaman unggah_bukti.php
$metode_pembayaran = $_POST['metode_pembayaran'] ?? 'transfer';
$bukti_pembayaran = NULL;
$gunakan_poin = isset($_POST['gunakan_poin']) ? (int)$_POST['gunakan_poin'] : 0;

// Gunakan transaksi untuk memastikan semua operasi (insert & delete) berhasil
$db_ekantin->begin_transaction();

try {
    // A. Validasi Harga & Kecukupan Stok Sisi Server (dengan penguncian data / lock)
    $calculated_total = 0;
    $stmtCekProduk = $db_ekantin->prepare("SELECT nama_menu, harga, stok FROM produk_kantin WHERE id_produk = ? AND id_toko = ? FOR UPDATE");
    
    foreach ($cart_data as $item) {
        $id_produk = (int) $item['id'];
        $jumlah    = (int) $item['qty'];

        if ($jumlah <= 0) {
            throw new Exception("Jumlah pesanan tidak valid (harus lebih dari 0).");
        }

        $stmtCekProduk->bind_param("ii", $id_produk, $id_toko);
        $stmtCekProduk->execute();
        $resProduk = $stmtCekProduk->get_result();
        
        if ($resProduk->num_rows === 0) {
            throw new Exception("Salah satu produk tidak ditemukan di toko ini.");
        }
        
        $produk = $resProduk->fetch_assoc();
        
        // Validasi Stok
        if ($produk['stok'] < $jumlah) {
            throw new Exception("Stok untuk produk '" . $produk['nama_menu'] . "' tidak cukup! Tersisa: " . $produk['stok']);
        }
        
        // Akumulasi harga
        $calculated_total += (int) $produk['harga'] * $jumlah;
    }

    // Validasi Total Harga
    if ($calculated_total !== $total_harga) {
        throw new Exception("Total harga pembayaran tidak cocok dengan rincian keranjang belanja.");
    }

    // Proses Poin
    $potongan_poin = 0;
    if ($gunakan_poin) {
        $stmtCekPoin = $db_ekantin->prepare("SELECT poin FROM users WHERE id_users = ? FOR UPDATE");
        $stmtCekPoin->bind_param("i", $id_users);
        $stmtCekPoin->execute();
        $resPoin = $stmtCekPoin->get_result();
        if ($resPoin->num_rows > 0) {
            $userPoin = (int)$resPoin->fetch_assoc()['poin'];
            $potongan_poin = min($userPoin, $total_harga);
            
            // Kurangi poin user
            $sisa_poin = $userPoin - $potongan_poin;
            $stmtKurangiPoin = $db_ekantin->prepare("UPDATE users SET poin = ? WHERE id_users = ?");
            $stmtKurangiPoin->bind_param("ii", $sisa_poin, $id_users);
            $stmtKurangiPoin->execute();

            $total_harga -= $potongan_poin;
        }
    }

    // 1. Hitung id_harian (nomor antrian hari ini)
    date_default_timezone_set('Asia/Jakarta');
    $today = date('Y-m-d');
    $qHarian = $db_ekantin->query("SELECT MAX(id_harian) as max_harian FROM pesanan WHERE DATE(tanggal_pesan) = '$today' AND id_toko = '$id_toko'");
    $id_harian = 1;
    if ($qHarian && $qHarian->num_rows > 0) {
        $row_harian = $qHarian->fetch_assoc();
        if ($row_harian['max_harian']) {
            $id_harian = (int)$row_harian['max_harian'] + 1;
        }
    }

    // 2. Simpan ke tabel pesanan
    $status_awal = 'pending';
    // Gunakan prepared statement untuk keamanan dari SQL injection
    $stmtPesanan = $db_ekantin->prepare("INSERT INTO pesanan (id_users, id_toko, total_harga, status_pesanan, catatan, id_harian, poin_digunakan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtPesanan->bind_param("iiissii", $id_users, $id_toko, $total_harga, $status_awal, $catatan, $id_harian, $potongan_poin);
    $stmtPesanan->execute();
    
    // Dapatkan ID pesanan yang baru saja dibuat
    $id_pesanan = $db_ekantin->insert_id;

    $kode_unik = 0;
    if ($metode_pembayaran !== 'cash') {
        $kode_unik = $id_harian;
        $total_harga += $kode_unik;
        
        // Update total harga dan kode_unik
        $stmtUpdate = $db_ekantin->prepare("UPDATE pesanan SET total_harga = ?, kode_unik = ? WHERE id_pesanan = ?");
        $stmtUpdate->bind_param("iii", $total_harga, $kode_unik, $id_pesanan);
        $stmtUpdate->execute();
    }

    // Simpan data pembayaran ke tabel pembayaran
    $status_bayar = ($metode_pembayaran === 'cash') ? 'belum_bayar' : 'menunggu_pembayaran';
    $stmtPembayaran = $db_ekantin->prepare("INSERT INTO pembayaran (id_pesanan, id_toko, metode_bayar, jumlah_bayar, bukti_bayar, status_bayar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtPembayaran->bind_param("iisdss", $id_pesanan, $id_toko, $metode_pembayaran, $total_harga, $bukti_pembayaran, $status_bayar);
    $stmtPembayaran->execute();

    // 2. Simpan setiap item ke tabel detail_pesanan
    $stmtDetail = $db_ekantin->prepare("INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmtKurangiStok = $db_ekantin->prepare("UPDATE produk_kantin SET stok = stok - ? WHERE id_produk = ?");
    $stmtHapusKeranjang = $db_ekantin->prepare("DELETE FROM keranjang WHERE id_users = ? AND id_produk = ?");

    foreach ($cart_data as $item) {
        $id_produk = (int) $item['id'];
        $jumlah    = (int) $item['qty'];
        $harga_sat = (float) $item['price'];
        $subtotal  = $harga_sat * $jumlah;

        // Insert detail
        $stmtDetail->bind_param("iiidd", $id_pesanan, $id_produk, $jumlah, $harga_sat, $subtotal);
        $stmtDetail->execute();

        // Kurangi stok (mencegah over-order)
        $stmtKurangiStok->bind_param("ii", $jumlah, $id_produk);
        $stmtKurangiStok->execute();

        // 3. Hapus item yang dibeli dari keranjang pengguna ini (jika ada)
        $stmtHapusKeranjang->bind_param("ii", $id_users, $id_produk);
        $stmtHapusKeranjang->execute();
    }

    // Commit jika semua berhasil
    $db_ekantin->commit();

    // Redirect ke halaman pesanan
    if ($metode_pembayaran === 'cash') {
        $_SESSION['pesan_sukses'] = "Pesanan berhasil dibuat! Silakan menuju kantin untuk melakukan pembayaran.";
        header("Location: pesanan.php");
    } else {
        // Tampilkan halaman QRIS/nominal + upload bukti
        header("Location: pesanan.php?id=" . $id_pesanan);
    }
    exit;

} catch (Exception $e) {
    // Batalkan transaksi jika terjadi kesalahan
    $db_ekantin->rollback();
    $_SESSION['pesan_error'] = "Gagal memproses pesanan: " . $e->getMessage();
    header("Location: pesan.php?id_toko=" . $id_toko);
    exit;
}
?>
