<?php
// Endpoint AJAX: Simpan cart baru ATAU update jumlah item
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['pembeli_id_users'])) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak diizinkan']);
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];
session_write_close();

$id_users = (int) $_SESSION['id_users'];
$aksi     = $_POST['aksi'] ?? '';

// ── AKSI 1: Simpan seluruh cart dari pesan.php ke DB ──
if ($aksi === 'simpan_cart') {
    $id_toko   = (int) ($_POST['id_toko'] ?? 0);
    $cart      = json_decode($_POST['cart_data'] ?? '{}', true);

    if (!$id_toko || empty($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }

    $stmt = $db_ekantin->prepare(
        "INSERT INTO keranjang (id_users, id_produk, id_toko, jumlah)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah)"
    );

    foreach ($cart as $item) {
        $id_produk = (int) $item['id'];
        $jumlah    = (int) $item['qty'];
        if ($jumlah <= 0) continue; // Jangan simpan produk jika jumlahnya nol atau negatif
        $stmt->bind_param("iiii", $id_users, $id_produk, $id_toko, $jumlah);
        $stmt->execute();
    }

    echo json_encode(['status' => 'ok']);
    exit;
}

// ── AKSI 2: Update jumlah satu item (dari keranjang.php) ──
if ($aksi === 'update_jumlah') {
    $id_keranjang = (int) ($_POST['id_keranjang'] ?? 0);
    $jumlah_baru  = (int) ($_POST['jumlah'] ?? 0);

    if ($jumlah_baru <= 0) {
        // Hapus item jika qty jadi 0
        $stmt = $db_ekantin->prepare("DELETE FROM keranjang WHERE id_keranjang = ? AND id_users = ?");
        $stmt->bind_param("ii", $id_keranjang, $id_users);
        $stmt->execute();
        echo json_encode(['status' => 'ok', 'aksi' => 'dihapus']);
    } else {
        // Update jumlah
        $stmt = $db_ekantin->prepare("UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ? AND id_users = ?");
        $stmt->bind_param("iii", $jumlah_baru, $id_keranjang, $id_users);
        $stmt->execute();
        echo json_encode(['status' => 'ok', 'aksi' => 'diupdate', 'jumlah' => $jumlah_baru]);
    }
    exit;
}

// ── AKSI 3: Hapus satu item ──
if ($aksi === 'hapus_item') {
    $id_keranjang = (int) ($_POST['id_keranjang'] ?? 0);
    $stmt = $db_ekantin->prepare("DELETE FROM keranjang WHERE id_keranjang = ? AND id_users = ?");
    $stmt->bind_param("ii", $id_keranjang, $id_users);
    $stmt->execute();
    echo json_encode(['status' => 'ok']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);
