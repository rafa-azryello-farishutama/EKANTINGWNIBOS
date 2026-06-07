<?php
$conn = new mysqli("localhost", "root", "", "ekantinsmea");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE produk_kantin ADD COLUMN diset_nol_oleh_penjual TINYINT(1) DEFAULT 0";
if ($conn->query($sql) === TRUE) {
    echo "Table altered successfully";
} else {
    echo "Error altering table: " . $conn->error;
}
$conn->close();
?>
