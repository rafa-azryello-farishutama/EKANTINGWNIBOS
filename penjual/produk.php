<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'penjual'){
    header("Location: ../index.php");
    exit;
}

$error_edit = null;
$dari = null;
if (isset($_GET['error'])) {
    $dari = $_GET['dari'] ?? 'tambah';
    if ($_GET['error'] == 'ukuran')
        $error_edit = 'File tidak boleh melebihi 2 Mb!';
    if ($_GET['error'] == 'format')
        $error_edit = 'File hanya boleh berformat jpg, png, jpeg, atau webp';
    if ($_GET['error'] == 'upload')
        $error_edit = 'Gagal mengupload foto, coba lagi.';
}

if (isset($_POST['tambah_menu'])) {
    $nama_menu = $db_ekantin->real_escape_string($_POST['tambah_nama']);
    $harga_menu = $db_ekantin->real_escape_string($_POST['tambah_harga']);
    $stok_menu = $db_ekantin->real_escape_string($_POST['tambah_stok']);
    $id_toko = $_SESSION['id_toko'];

    $result_user = $db_ekantin->query("SELECT id_users FROM toko WHERE id_toko='$id_toko'");
    $row_user = $result_user->fetch_assoc();
    $id_user = $row_user['id_users'];

    $result_toko = $db_ekantin->query("SELECT username FROM users WHERE id_users='$id_user'");
    $row_toko = $result_toko->fetch_assoc();
    $nama_toko = $row_toko['username'];
    $tipe_produk = $_POST['tipe_pesanan'];

    $nama_baru = null;

    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['foto_produk']['name'];
        $tmp_name = $_FILES['foto_produk']['tmp_name'];
        $file_size = $_FILES['foto_produk']['size'];
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ekstensi_valid = ['jpg', 'png', 'jpeg', 'webp'];
        $folder = "../assets/img_produk/";
        $max_size = 2097152;

        if ($file_size > $max_size) {
            header("Location: produk.php?error=ukuran");
            exit;
        } elseif (!in_array($ekstensi_file, $ekstensi_valid)) {
            header("Location: produk.php?error=format");
            exit;
        } else {
            $nama_filter = preg_replace('/[^a-zA-Z0-9\s]/', '', $nama_menu);
            $nama_bersih = str_replace(' ', '_', $nama_filter);
            $nama_baru = $id_toko . '_' . $nama_bersih . '_' . $nama_toko . '.' . $ekstensi_file;
            $upload_path = $folder . $nama_baru;

            if (!move_uploaded_file($tmp_name, $upload_path)) {
                header("Location: produk.php?error=upload");
                exit;
            }
        }
    }

    $nama_foto_baru = $db_ekantin->real_escape_string($nama_baru);
    $sql = "INSERT INTO produk_kantin (id_toko, nama_menu, harga, stok, file_foto, tipe_produk)
            VALUES ('$id_toko', '$nama_menu', '$harga_menu', '$stok_menu', '$nama_foto_baru', '$tipe_produk')";

    $db_ekantin->query($sql);
    header("Location: produk.php");
    exit;
}

if (isset($_POST['edit_menu'])) {
    include 'handlers/produk_edit_menu.php';
}

$keyword = '';
if (isset($_POST['cari_user']) && isset($_POST['name_id'])) {
    $keyword = $db_ekantin->real_escape_string(trim($_POST['name_id']));
}

$filter_tipe = '';
if (isset($_POST['filter_makanan'])) {
    $filter_tipe = 'makanan';
} else if (isset($_POST['filter_minuman'])) {
    $filter_tipe = 'minuman';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "background": "#f7f8f9",
                        "primary": "#004900",
                        "second-primary": "#f9f9fb",
                        "input": "#f3f3f5",
                        "text-1": "#191c1c",
                        "text-2": "#4e5a48",
                        "text-3": "#5e6659",
                        "submit": "#005300"
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex w-full min-h-screen relative">

        <?php include 'navbar.php'; ?>
<<<<<<< HEAD
        
        <!-- PERBAIKAN: Ganti h-screen → min-h-screen, hapus overflow tersembunyi -->
        <main class="lg:ml-80 flex-grow w-full flex flex-col p-4 md:p-8 bg-surface pt-24 lg:pt-8 min-h-screen">
            <div class="containerDash w-full flex flex-col max-w-7xl mx-auto">

                <!-- Bagian Search -->
                <div class="w-full mb-6">
                    <form method="POST" class="w-full">
                        <div class="flex flex-col sm:flex-row gap-3 w-full items-center">
                            <div class="flex flex-1 items-center gap-3 bg-input rounded-xl px-4 h-12 w-full focus-within:ring-2 focus-within:ring-primary transition-all">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text" name="name_id" placeholder="Search Menu" class="w-full bg-transparent border-none outline-none text-text-1 placeholder-gray-500 text-sm focus:ring-0 p-0">
                            </div>
                            <button type="submit" name="cari_user" class="h-12 w-full sm:w-auto px-8 bg-submit text-white rounded-xl text-sm font-bold hover:bg-primary transition-colors whitespace-nowrap">
                                Search Menu
                            </button>
=======

        <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
            <div class="w-full max-w-7xl mx-auto flex flex-col gap-6">

                <div class="flex flex-col sm:flex-row gap-3 w-full">
                    <form method="POST" class="flex flex-1 gap-3">
                        <div
                            class="flex flex-1 items-center gap-3 bg-input rounded-xl px-4 h-12 focus-within:ring-2 focus-within:ring-primary transition-all">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="name_id" placeholder="Search Menu"
                                value="<?= htmlspecialchars($keyword) ?>"
                                class="w-full h-12 bg-transparent border-none outline-none text-text-1 placeholder-gray-500 text-sm focus:ring-0 p-0">
>>>>>>> 36b5672aa88fdf5c113d414364d65afafc023c0e
                        </div>
                        <button type="submit" name="cari_user"
                            class="h-12 px-6 bg-submit text-white rounded-xl text-sm font-bold hover:bg-primary transition-colors whitespace-nowrap">
                            Search
                        </button>
                    </form>

                    <button onclick="searchEdit()"
                        class="h-10 md:h-12 px-6 bg-primary text-white rounded-xl text-sm font-bold hover:bg-submit transition-colors whitespace-nowrap flex items-center gap-2 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Menu
                    </button>
                </div>

                <div class="flex gap-3 overflow-x-auto pb-1">
                    <?php
                    $aktif = 'px-6 py-2 rounded-full font-bold text-sm bg-submit text-white transition-colors whitespace-nowrap flex-shrink-0';
                    $nonaktif = 'px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors whitespace-nowrap flex-shrink-0';
                    ?>

                    <form method="POST" class="flex gap-3">
                        <button type="submit" name="filter_semua"
                            class="<?= $filter_tipe == '' ? $aktif : $nonaktif ?>">
                            Semua
                        </button>
                        <button type="submit" name="filter_makanan"
                            class="<?= $filter_tipe == 'makanan' ? $aktif : $nonaktif ?>">
                            Makanan
                        </button>
                        <button type="submit" name="filter_minuman"
                            class="<?= $filter_tipe == 'minuman' ? $aktif : $nonaktif ?>">
                            Minuman
                        </button>
                    </form>
                </div>

<<<<<<< HEAD
<<<<<<< HEAD
                <!-- Bagian Filter Tombol -->
                <div class="w-full mb-6 overflow-x-auto pb-2">
                    <form method="POST" class="w-full">
                        <div class="flex gap-3 w-max">
                            <button type="submit" name="filter_pembeli" class="px-6 py-2 rounded-full font-bold text-sm bg-submit text-white transition-colors">
                                Semua
                            </button>
                            <button type="submit" name="filter_penjual" class="px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors">
                                Makanan
                            </button>
                            <button type="submit" name="filter_semua" class="px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors">
                                Minuman
                            </button>
                        </div>
                    </form>
                </div>

                <!-- PERBAIKAN: Hapus overflow-y-auto & h-full dari grid, biarkan konten mengalir natural -->
                <div class="kotakList grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 pb-8">
                    
                    <!-- Kartu Menu — tinggi gambar fixed, info produk di bawahnya -->
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-40 sm:h-48 bg-gray-200 w-full flex-shrink-0">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Nasi Goreng Spesial</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 25.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 12 porsi</p>
                            <div class="pt-2">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
=======
                <!-- Grid Produk -->
=======
>>>>>>> f25e6e2daa67cbc9015df7eae120f166bc9061bc
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 pb-8">
                    <?php
                    $id_toko = $_SESSION['id_toko'];

<<<<<<< HEAD
                    <div
                        class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="aspect-video bg-gray-200 w-full">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80"
                                alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 flex-1">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Nasi Goreng Spesial</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 25.000</p>
                            <p class="text-text-3 text-xs font-medium">Stok: 12 porsi</p>
                            <div class="mt-auto pt-3">
                                <button
                                    class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
>>>>>>> 36b5672aa88fdf5c113d414364d65afafc023c0e
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

<<<<<<< HEAD
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-40 sm:h-48 bg-gray-200 w-full flex-shrink-0">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Es Teh Manis</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 5.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 50 gelas</p>
                            <div class="pt-2">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
=======
                    <div
                        class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="aspect-video bg-gray-200 w-full">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80"
                                alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 flex-1">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Es Teh Manis</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 5.000</p>
                            <p class="text-text-3 text-xs font-medium">Stok: 50 gelas</p>
                            <div class="mt-auto pt-3">
                                <button onclick="tampilkanMode()"
                                    class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
>>>>>>> 36b5672aa88fdf5c113d414364d65afafc023c0e
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

<<<<<<< HEAD
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-40 sm:h-48 bg-gray-200 w-full flex-shrink-0">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Burger Ayam</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 20.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 8 porsi</p>
                            <div class="pt-2">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
=======
                    <div
                        class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="aspect-video bg-gray-200 w-full">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80"
                                alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 flex-1">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Burger Ayam</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 20.000</p>
                            <p class="text-text-3 text-xs font-medium">Stok: 8 porsi</p>
                            <div class="mt-auto pt-3">
                                <button
                                    class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
>>>>>>> 36b5672aa88fdf5c113d414364d65afafc023c0e
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

<<<<<<< HEAD
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-40 sm:h-48 bg-gray-200 w-full flex-shrink-0">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Es Kopi Susu</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 15.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 20 gelas</p>
                            <div class="pt-2">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
=======
                    <div
                        class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="aspect-video bg-gray-200 w-full">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80"
                                alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 flex-1">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Es Kopi Susu</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 15.000</p>
                            <p class="text-text-3 text-xs font-medium">Stok: 20 gelas</p>
                            <div class="mt-auto pt-3">
                                <button
                                    class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
>>>>>>> 36b5672aa88fdf5c113d414364d65afafc023c0e
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>
<<<<<<< HEAD
                    
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-40 sm:h-48 bg-gray-200 w-full flex-shrink-0">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Ayam Geprek</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 18.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 15 porsi</p>
                            <div class="pt-2">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
=======

                    <div
                        class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="aspect-video bg-gray-200 w-full">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80"
                                alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-1 flex-1">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Ayam Geprek</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 18.000</p>
                            <p class="text-text-3 text-xs font-medium">Stok: 15 porsi</p>
                            <div class="mt-auto pt-3">
                                <button
                                    class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
>>>>>>> 36b5672aa88fdf5c113d414364d65afafc023c0e
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Menu Edit -->
                <div id="modal-edit" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                    <div
                        class="popup-enter bg-white rounded-[24px] w-full max-w-md shadow-2xl overflow-hidden p-6 md:p-8">

                        <div class="flex justify-between items-center mb-6 border-b pb-4">
                            <h2 class="text-primary font-extrabold text-xl">Edit Informasi User</h2>
                            <button onclick="tutupModalEdit()"
                                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>


                    </div>

                </div>

                <div id="search-edit"
                    class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                    <div
                        class="popup-enter bg-white rounded-[24px] w-full max-w-md shadow-2xl overflow-hidden p-6 md:p-8">
                        <div class="flex justify-between items-center mb-6 border-b pb-4">
                            <h2 class="text-primary font-extrabold text-xl">Tambah Menu</h2>
                            <button onclick="tutupSearch()"
                                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <?php if (isset($error_edit)): ?>
                            <div
                                class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                                <?= $error_edit ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="flex flex-col gap-4" enctype="multipart/form-data">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Foto
                                    Menu</label>

                                <label for="input-foto" class="cursor-pointer text-center">
                                    <div id="preview-container"
                                        class="w-full h-[160px] bg-input rounded-[15px] border-2 border-dashed border-gray-300 hover:border-primary transition-all flex flex-col items-center justify-center gap-2 overflow-hidden">

                                        <img id="preview-img" src="" alt=""
                                            class="hidden w-full h-full object-cover rounded-[13px]">

                                        <div id="placeholder" class="flex flex-col items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-sm font-medium text-text-3">Klik untuk upload foto</p>
                                            <p class="text-xs text-gray-400">JPG, PNG, JEPG, WEBP maks. 2MB</p>
                                        </div>
=======
                    $sql = "SELECT * FROM produk_kantin WHERE id_toko='$id_toko'";
                    if ($keyword !== '') {
                        $sql .= " AND nama_menu LIKE '%$keyword%'";
                    }
                    if ($filter_tipe !== '') {
                        $sql .= " AND tipe_produk = '$filter_tipe'";
                    }

                    $result = $db_ekantin->query($sql);
                    $pesan = "";

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $file_foto = htmlspecialchars($row['file_foto']);
                            $nama = htmlspecialchars($row['nama_menu']);
                            $harga = number_format($row['harga'], 0, ',', '.');
                            $harga_asli = $row['harga'];
                            $stok = $row['stok'];
                            $id_produk = $row['id_produk'];
                            $tipe_produk = htmlspecialchars($row['tipe_produk']);
                            $nama_js = addslashes($row['nama_menu']);
                            $tipe_js = addslashes($row['tipe_produk']);
                            $file_foto_js = addslashes($row['file_foto']);

                            $pesan = $tipe_produk == 'makanan' ? 'porsi' : 'gelas';
                            $foto_src = $file_foto ? "../assets/img_produk/$file_foto" : "../assets/img/no-image.png";

                            echo "
                            <div class='bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow'>
                                <div class='h-[140px] bg-gray-200 w-full'>
                                    <img src='$foto_src' alt='$nama' class='w-full h-full object-cover'>
                                </div>
                                <div class='p-3 sm:p-4 flex flex-col gap-1 flex-1'>
                                    <h3 class='font-bold text-text-1 text-sm sm:text-base line-clamp-1'>$nama</h3>
                                    <p class='text-primary font-bold text-sm sm:text-base'>Rp $harga</p>
                                    <p class='text-text-3 text-xs font-medium'>Stok: $stok $pesan</p>
                                    <div class='mt-auto pt-3'>
                                        <button onclick='tampilkanMode($id_produk,&quot;$nama_js&quot;,$harga_asli,$stok,&quot;$tipe_js&quot;,&quot;$file_foto_js&quot;)'
                                            class='w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors'>
                                            Edit Menu
                                        </button>
>>>>>>> f25e6e2daa67cbc9015df7eae120f166bc9061bc
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        if ($keyword !== '') {
                            echo "<div class='col-span-4 text-center text-text-3 py-12 text-sm'>Tidak ada menu dengan nama <strong>\"" . htmlspecialchars($keyword) . "\"</strong>.</div>";
                        } else {
                            echo "<div class='col-span-4 text-center text-text-3 py-12 text-sm'>Belum ada produk. Tambahkan menu pertamamu!</div>";
                        }
                    }
                    ?>
                </div>

            </div>
        </main>
    </div>

    <?php include 'potongan_html/produk_menu_edit.html'; ?>
    <?php include 'potongan_html/produk_tambah_menu.html'; ?>

    <script src="../assets/js/produk.js"></script>

    <?php if (isset($error_edit)): ?>
        <script>
            window.onload = function () {
                <?php if ($dari == 'edit'): ?>
                    document.getElementById('modal-edit').classList.remove('hidden');
                    const errorBox = document.createElement('div');
                    errorBox.className = 'px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium';
                    errorBox.innerText = '<?= $error_edit ?>';
                    const form = document.querySelector('#modal-edit form');
                    form.insertBefore(errorBox, form.firstChild);
                <?php else: ?>
                    document.getElementById('search-edit').classList.remove('hidden');
                <?php endif; ?>
            }
        </script>
    <?php endif; ?>

</body>

</html>