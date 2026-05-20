<?php
$c = mysqli_connect('localhost', 'root', '', 'ekantinsmea');
$r = mysqli_query($c, 'SHOW CREATE TABLE produk_kantin');
echo mysqli_fetch_row($r)[1];
?>
