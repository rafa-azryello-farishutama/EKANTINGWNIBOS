<?php
include 'config/koneksi.php';
$sql = "ALTER TABLE users MODIFY COLUMN status ENUM('aktif', 'nonaktif', 'pending') DEFAULT 'pending'";
if ($db_ekantin->query($sql)) {
    echo "Success altering status column\n";
} else {
    echo "Error: " . $db_ekantin->error;
}
?>
