<?php
session_start();
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];

    // Update status di database
    $query = "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = '$id'";
    
    if ($db_ekantin->query($query)) {
        // Kembali ke halaman kelola pesanan
        header("Location: kelola_pesanan.php"); 
    } else {
        echo "Gagal memperbarui status.";
    }
}
?>