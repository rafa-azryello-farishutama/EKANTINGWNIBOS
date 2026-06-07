<?php
$id_produk    = $_POST['edit_id'];
$nama_produk  = $db_ekantin->real_escape_string($_POST['edit_nama']);
$harga_produk = $db_ekantin->real_escape_string($_POST['edit_harga']);
$tipe_produk  = $db_ekantin->real_escape_string($_POST['tipe_pesanan']);
$foto_lama    = $db_ekantin->real_escape_string($_POST['edit_foto_lama']);

$params = "&dari=edit&id=$id_produk&nama=".urlencode($nama_produk)."&harga=$harga_produk&tipe=$tipe_produk&foto=".urlencode($foto_lama);

$nama_foto_simpan = $foto_lama;

if (isset($_FILES['foto_produk_edit']) && $_FILES['foto_produk_edit']['error'] === UPLOAD_ERR_OK) {
    $nama_file      = $_FILES['foto_produk_edit']['name'];
    $tmp_name       = $_FILES['foto_produk_edit']['tmp_name'];
    $file_size      = $_FILES['foto_produk_edit']['size'];
    $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $ekstensi_valid = ['jpg', 'png', 'jpeg', 'webp'];
    $folder         = "../assets/img_produk/";
    $max_size       = 2097152;

    if ($file_size > $max_size) {
        header("Location: produk.php?error=ukuran$params");
        exit;
    }
    if (!in_array($ekstensi_file, $ekstensi_valid)) {
        header("Location: produk.php?error=format$params");
        exit;
    }

    $res = $db_ekantin->query("SELECT pk.id_toko, u.username 
                                FROM produk_kantin pk
                                JOIN toko t ON pk.id_toko = t.id_toko
                                JOIN users u ON t.id_users = u.id_users
                                WHERE pk.id_produk = '$id_produk'");
    $row_info  = $res->fetch_assoc();
    $id_toko   = $row_info['id_toko'];
    $nama_toko = $row_info['username'];

    $nama_bersih    = preg_replace('/[^a-zA-Z0-9]/', '', $nama_produk);
    $nama_foto_baru = $id_toko . '_' . $nama_bersih . '_' . time() . '_' . rand(100, 999) . '.' . $ekstensi_file;

    if (!move_uploaded_file($tmp_name, $folder . $nama_foto_baru)) {
        header("Location: produk.php?error=upload$params");
        exit;
    }

    if ($foto_lama && file_exists($folder . $foto_lama)) {
        unlink($folder . $foto_lama);
    }

    $nama_foto_simpan = $nama_foto_baru;
}

$status_menu  = isset($_POST['edit_status']) ? $db_ekantin->real_escape_string($_POST['edit_status']) : 'aktif';

$nama_foto_escaped = $db_ekantin->real_escape_string($nama_foto_simpan);
$db_ekantin->query("UPDATE produk_kantin 
                    SET nama_menu   = '$nama_produk',
                        harga       = '$harga_produk',
                        tipe_produk = '$tipe_produk',
                        status_menu = '$status_menu',
                        file_foto   = '$nama_foto_escaped'
                    WHERE id_produk = '$id_produk'");

header("Location: produk.php");
exit;