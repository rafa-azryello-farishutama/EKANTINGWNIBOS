<?php
include 'config/koneksi.php';
$r=$db_ekantin->query("SHOW COLUMNS FROM users LIKE 'status'");
print_r($r->fetch_assoc());
?>
