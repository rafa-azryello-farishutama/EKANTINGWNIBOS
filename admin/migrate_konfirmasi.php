<?php
require '../config/koneksi.php';

$sql = "ALTER TABLE produk_kantin ADD COLUMN status_konfirmasi ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'disetujui'";
if ($db_ekantin->query($sql)) {
    echo "Column added successfully.\n";
} else {
    echo "Error: " . $db_ekantin->error . "\n";
}
?>
