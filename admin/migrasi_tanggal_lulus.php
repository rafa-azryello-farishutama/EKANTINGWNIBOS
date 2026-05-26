<?php
include '../config/koneksi.php';
$result = $db_ekantin->query("ALTER TABLE users ADD COLUMN tanggal_lulus DATETIME NULL DEFAULT NULL");
if ($result) {
    echo "OK: Kolom tanggal_lulus berhasil ditambahkan.";
} else {
    echo "ERROR: " . $db_ekantin->error;
}
?>
