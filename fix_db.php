<?php
$mysqli = new mysqli('localhost', 'root', '', 'EKANTIN_SMEA');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->query("ALTER TABLE produk_kantin MODIFY harga INT(11);");
if ($mysqli->error) {
    echo "Error: " . $mysqli->error;
} else {
    echo "Success altering column to INT(11)";
}
$mysqli->close();
?>
