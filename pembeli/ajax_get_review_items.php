<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['pembeli_id_users'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id_users = (int) $_SESSION['pembeli_id_users'];
$id_pesanan = isset($_GET['id_pesanan']) ? (int) $_GET['id_pesanan'] : 0;

if ($id_pesanan <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID pesanan tidak valid']);
    exit;
}

// Cek apakah pesanan milik user ini dan berstatus selesai
$cek = $db_ekantin->prepare("SELECT id_pesanan FROM pesanan WHERE id_pesanan = ? AND id_users = ? AND status_pesanan = 'selesai'");
$cek->bind_param("ii", $id_pesanan, $id_users);
$cek->execute();
if ($cek->get_result()->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan atau belum selesai']);
    exit;
}

// Ambil item pesanan
$sql = "SELECT dp.id_produk, dp.jumlah, pk.nama_menu, pk.file_foto as foto 
        FROM detail_pesanan dp 
        JOIN produk_kantin pk ON dp.id_produk = pk.id_produk 
        WHERE dp.id_pesanan = ?";
$stmt = $db_ekantin->prepare($sql);
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    'status' => 'success',
    'items' => $items
]);
