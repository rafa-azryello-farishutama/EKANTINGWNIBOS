<?php
include 'config/koneksi.php';
$res = $db_ekantin->query('DESCRIBE ruang_kantin');
while($r = $res->fetch_assoc()) {
    print_r($r);
}
echo "-----\n";
$res2 = $db_ekantin->query('SELECT * FROM ruang_kantin LIMIT 5');
while($r = $res2->fetch_assoc()) {
    print_r($r);
}
?>
