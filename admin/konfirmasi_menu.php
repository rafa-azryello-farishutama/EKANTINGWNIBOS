<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_users']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$error_msg = "";
$sukses_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST['setujui_menu'])) {
        $id_produk = $db_ekantin->real_escape_string($_POST['id_produk']);
        $db_ekantin->query("UPDATE produk_kantin SET status_konfirmasi = 'disetujui' WHERE id_produk = '$id_produk'");
        header("Location: konfirmasi_menu.php");
        exit;
    }
    if(isset($_POST['tolak_menu'])) {
        $id_produk = $db_ekantin->real_escape_string($_POST['id_produk']);
        $db_ekantin->query("UPDATE produk_kantin SET status_konfirmasi = 'ditolak' WHERE id_produk = '$id_produk'");
        header("Location: konfirmasi_menu.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Menu</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background":     "#f7f8f9",
                        "primary":        "#004900",
                        "second-primary": "#f9f9fb",
                        "input":          "#f3f3f5",
                        "text-1":         "#191c1c",
                        "text-2":         "#4e5a48",
                        "text-3":         "#5e6659",
                        "submit":         "#005300"
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
<div class="flex min-h-screen relative">
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8">

        <!-- Header -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Konfirmasi Menu</h2>
                <p class="text-text-3 font-body mt-2">Setujui menu baru yang ditambahkan oleh penjual kantin</p>
            </div>
        </header>

        <!-- Daftar Menu Menunggu Konfirmasi -->
        <div class="flex flex-col gap-6">
            <?php
            // Ambil daftar toko yang memiliki menu menunggu konfirmasi
            $qToko = $db_ekantin->query("SELECT DISTINCT t.id_toko, t.nama_toko, u.username 
                                         FROM toko t
                                         JOIN users u ON t.id_users = u.id_users
                                         JOIN produk_kantin pk ON pk.id_toko = t.id_toko
                                         WHERE pk.status_konfirmasi = 'menunggu'");

            if ($qToko && $qToko->num_rows > 0) {
                while ($toko = $qToko->fetch_assoc()) {
                    $id_toko = $toko['id_toko'];
                    $nama_toko = htmlspecialchars($toko['nama_toko']);
                    $username = htmlspecialchars($toko['username']);
                    ?>
                    
                    <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-primary/5 border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-lg text-primary"><?= $nama_toko ?></h3>
                                <p class="text-xs text-text-3">Penjual: <?= $username ?></p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <?php
                            $qMenu = $db_ekantin->query("SELECT * FROM produk_kantin WHERE id_toko='$id_toko' AND status_konfirmasi='menunggu'");
                            while ($menu = $qMenu->fetch_assoc()) {
                                $id_produk = $menu['id_produk'];
                                $nama_menu = htmlspecialchars($menu['nama_menu']);
                                $harga = number_format($menu['harga'], 0, ',', '.');
                                $harga_asli = $menu['harga'];
                                $stok = $menu['stok'];
                                $tipe_produk = htmlspecialchars($menu['tipe_produk']);
                                $file_foto = $menu['file_foto'];
                                $foto_src = $file_foto ? "../assets/img_produk/" . htmlspecialchars($file_foto) : "../assets/img/no-image.png";
                            ?>
                                <div class="bg-second-primary border border-gray-200 rounded-xl overflow-hidden flex flex-col shadow-sm">
                                    <div class="h-32 bg-gray-200 relative overflow-hidden">
                                        <img src="<?= $foto_src ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-4 flex flex-col flex-grow">
                                        <h4 class="font-bold text-text-1 line-clamp-1"><?= $nama_menu ?></h4>
                                        <div class="flex justify-between items-center mt-2">
                                            <p class="text-primary font-bold text-sm">Rp <?= $harga ?></p>
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium"><?= $stok ?> <?= $tipe_produk === 'makanan' ? 'porsi' : 'gelas' ?></span>
                                        </div>
                                        
                                        <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-2 gap-2">
                                            <form method="POST" onsubmit="return confirm('Tolak menu ini? Menu akan dikembalikan ke penjual.');">
                                                <input type="hidden" name="id_produk" value="<?= $id_produk ?>">
                                                <button type="submit" name="tolak_menu" class="w-full bg-red-50 text-red-600 font-bold py-2 rounded-lg text-xs hover:bg-red-100 transition-colors">Tolak</button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Setujui menu ini? Menu akan langsung aktif dengan logo Halal.');">
                                                <input type="hidden" name="id_produk" value="<?= $id_produk ?>">
                                                <button type="submit" name="setujui_menu" class="w-full bg-submit text-white font-bold py-2 rounded-lg text-xs hover:bg-primary transition-colors">Setujui (Halal)</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='bg-white rounded-[20px] p-8 text-center border border-gray-100 shadow-sm'>
                        <div class='w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                            <span class='text-2xl opacity-50'>✅</span>
                        </div>
                        <h3 class='text-lg font-bold text-text-1'>Tidak Ada Menunggu Konfirmasi</h3>
                        <p class='text-sm text-text-3 mt-1'>Semua menu saat ini sudah dikonfirmasi.</p>
                      </div>";
            }
            ?>
        </div>

    </main>
</div>

</body>
</html>