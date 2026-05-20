<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['pembeli_id_users'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id_users = (int) $_SESSION['pembeli_id_users'];

// Lepaskan lock session agar tidak memblokir tab lain
session_write_close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$id_pesanan = (int) ($_POST['id_pesanan'] ?? 0);
$reviews    = $_POST['reviews'] ?? [];

if (!$id_pesanan || empty($reviews)) {
    echo json_encode(['status' => 'error', 'message' => 'Data review tidak lengkap.']);
    exit;
}

// Validasi: pesanan milik user & status selesai
$qCek = $db_ekantin->prepare("SELECT id_pesanan FROM pesanan WHERE id_pesanan = ? AND id_users = ? AND status_pesanan = 'selesai'");
$qCek->bind_param("ii", $id_pesanan, $id_users);
$qCek->execute();
$resCek = $qCek->get_result();

if ($resCek->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak valid atau belum selesai.']);
    exit;
}

// Validasi: produk memang bagian dari pesanan ini
$qItems = $db_ekantin->prepare("SELECT id_produk FROM detail_pesanan WHERE id_pesanan = ?");
$qItems->bind_param("i", $id_pesanan);
$qItems->execute();
$resItems = $qItems->get_result();
$valid_produk = [];
while ($row = $resItems->fetch_assoc()) {
    $valid_produk[] = (int) $row['id_produk'];
}

$db_ekantin->begin_transaction();

try {
    $stmtInsert = $db_ekantin->prepare(
        "INSERT INTO review (id_pesanan, id_produk, id_users, rating, komentar) VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = VALUES(rating), komentar = VALUES(komentar)"
    );

    $inserted = 0;
    foreach ($reviews as $rev) {
        $id_produk = (int) ($rev['id_produk'] ?? 0);
        $rating    = (int) ($rev['rating'] ?? 0);
        $komentar  = trim($rev['komentar'] ?? '');

        // Validasi rating 1-5
        if ($rating < 1 || $rating > 5) continue;
        // Validasi produk ada di pesanan
        if (!in_array($id_produk, $valid_produk)) continue;

        $stmtInsert->bind_param("iiiis", $id_pesanan, $id_produk, $id_users, $rating, $komentar);
        $stmtInsert->execute();
        $inserted++;
    }

    if ($inserted === 0) {
        throw new Exception("Tidak ada review valid yang bisa disimpan.");
    }

    $db_ekantin->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Review berhasil disimpan!', 'count' => $inserted]);

} catch (Exception $e) {
    $db_ekantin->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
