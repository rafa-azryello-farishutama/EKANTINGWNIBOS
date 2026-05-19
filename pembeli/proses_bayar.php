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

// Proses Upload Bukti Pembayaran
$metode_pembayaran = $_POST['metode_pembayaran'] ?? 'transfer';
$file_input_name = ($metode_pembayaran === 'qr') ? 'bukti_qr' : 'bukti_transfer';
$bukti_pembayaran = NULL;

if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES[$file_input_name]['tmp_name'];
    $fileName = $_FILES[$file_input_name]['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generate nama file yang unik untuk menghindari tabrakan nama file
    $newFileName = time() . '_' . md5(uniqid()) . '.' . $fileExtension;
    
    $uploadFileDir = '../assets/uploads_bukti/';
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0777, true);
    }
    
    $dest_path = $uploadFileDir . $newFileName;
    
    if(move_uploaded_file($fileTmpPath, $dest_path)) {
        $bukti_pembayaran = $newFileName;
    } else {
        $_SESSION['pesan_error'] = "Gagal menyimpan berkas bukti pembayaran di server.";
        header("Location: pesan.php?id_toko=" . $id_toko);
        exit;
    }
} else {
    $_SESSION['pesan_error'] = "Bukti pembayaran wajib diunggah untuk konfirmasi pesanan.";
    header("Location: pesan.php?id_toko=" . $id_toko);
    exit;
}

// Gunakan transaksi untuk memastikan semua operasi (insert & delete) berhasil
$db_ekantin->begin_transaction();

try {
    // 1. Simpan ke tabel pesanan
    $status_awal = 'pending';
    // Gunakan prepared statement untuk keamanan dari SQL injection
    $stmtPesanan = $db_ekantin->prepare("INSERT INTO pesanan (id_users, id_toko, total_harga, status_pesanan, catatan, metode_pembayaran, bukti_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtPesanan->bind_param("iiissss", $id_users, $id_toko, $total_harga, $status_awal, $catatan, $metode_pembayaran, $bukti_pembayaran);
    $stmtPesanan->execute();
    
    // Dapatkan ID pesanan yang baru saja dibuat
    $id_pesanan = $db_ekantin->insert_id;

    // 2. Simpan setiap item ke tabel detail_pesanan
    // (Diasumsikan tabel detail_pesanan minimal butuh: id_pesanan, id_produk, jumlah)
    // Harga per item biasanya diambil dari tabel produk_kantin, tapi karena total_harga
    // sudah disimpan, kita simpan jumlahnya saja. 
    // Kita kurangi juga stok kantin (opsional, tapi disarankan).
    
    $stmtDetail = $db_ekantin->prepare("INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah) VALUES (?, ?, ?)");
    $stmtKurangiStok = $db_ekantin->prepare("UPDATE produk_kantin SET stok = stok - ? WHERE id_produk = ?");
    $stmtHapusKeranjang = $db_ekantin->prepare("DELETE FROM keranjang WHERE id_users = ? AND id_produk = ?");

    foreach ($cart_data as $item) {
        $id_produk = (int) $item['id'];
        $jumlah    = (int) $item['qty'];

        // Insert detail
        $stmtDetail->bind_param("iii", $id_pesanan, $id_produk, $jumlah);
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

    // Redirect ke halaman riwayat pesanan
    $_SESSION['pesan_sukses'] = "Pesanan berhasil dibuat!";
    header("Location: history.php");
    exit;

} catch (Exception $e) {
    // Rollback jika ada yang gagal
    $db_ekantin->rollback();
    die("Terjadi kesalahan sistem: " . $e->getMessage());
}
?>
