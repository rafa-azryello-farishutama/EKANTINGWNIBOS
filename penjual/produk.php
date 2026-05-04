<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['tambah_menu'])) {
    $nama_menu  = $db_ekantin->real_escape_string($_POST['tambah_nama']);
    $harga_menu = $db_ekantin->real_escape_string($_POST['tambah_harga']);
    $stok_menu  = $db_ekantin->real_escape_string($_POST['tambah_stok']);
    $id_toko    = $_SESSION['id_toko'];

    $result_user = $db_ekantin->query("SELECT id_users FROM toko WHERE id_toko='$id_toko'");
    $row_user    = $result_user->fetch_assoc();
    $id_user     = $row_user['id_users'];

    $result_toko = $db_ekantin->query("SELECT username FROM users WHERE id_users='$id_user'");
    $row_toko    = $result_toko->fetch_assoc();
    $nama_toko   = $row_toko['username'];

    $nama_baru  = null;
    $error_edit = null;

    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['foto_produk']['name'];
        $tmp_name  = $_FILES['foto_produk']['tmp_name'];
        $file_size = $_FILES['foto_produk']['size'];

        $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ekstensi_valid = ['jpg', 'png', 'jpeg', 'webp'];
        $folder         = "../assets/img_produk/";
        $max_size       = 2097152;

        if ($file_size > $max_size) {
            $error_edit = 'File tidak boleh melebihi 2 Mb!';
        } elseif (!in_array($ekstensi_file, $ekstensi_valid)) {
            $error_edit = 'File hanya boleh berformat jpg, png, jpeg, atau webp';
        } else {
            $nama_bersih = str_replace(' ', '_', $nama_menu);
            $nama_baru   = $id_toko . '_' . $nama_bersih . '_' . $nama_toko . '.' . $ekstensi_file;
            $upload_path = $folder . $nama_baru;

            if (!move_uploaded_file($tmp_name, $upload_path)) {
                $error_edit = 'Gagal mengupload foto, coba lagi.';
                $nama_baru  = null;
            }
        }
    }

    if (!isset($error_edit)) {
        $nama_foto_baru = $db_ekantin->real_escape_string($nama_baru);
        $sql = "INSERT INTO produk_kantin (id_toko, nama_menu, harga, stok, file_foto)
                VALUES ('$id_toko', '$nama_menu', '$harga_menu', '$stok_menu', '$nama_foto_baru')";

        $db_ekantin->query($sql);
        header("Location: produk.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="../assets/css/penjual.css">
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

        <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
            <div class="w-full max-w-7xl mx-auto flex flex-col gap-6">

                <!-- Search -->
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
                                class="w-full h-12 bg-transparent border-none outline-none text-text-1 placeholder-gray-500 text-sm focus:ring-0 p-0">
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

                <!-- Filter -->
                <div class="flex gap-3 overflow-x-auto pb-1">
                    <form method="POST" class="flex gap-3">
                        <button type="submit" name="filter_semua"
                            class="px-6 py-2 rounded-full font-bold text-sm bg-submit text-white transition-colors whitespace-nowrap flex-shrink-0">
                            Semua
                        </button>
                        <button type="submit" name="filter_makanan"
                            class="px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors whitespace-nowrap flex-shrink-0">
                            Makanan
                        </button>
                        <button type="submit" name="filter_minuman"
                            class="px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors whitespace-nowrap flex-shrink-0">
                            Minuman
                        </button>
                    </form>
                </div>

                <!-- Grid Produk -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 pb-8">

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
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

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
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

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
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

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
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

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
                                    </div>

                                    <p class="text-xs text-gray-400">(Opsional), Produk dengan foto lebih diminati
                                        pembeli</p>
                                </label>

                                <input type="file" id="input-foto" name="foto_produk" accept="image/*" class="hidden"
                                    onchange="previewFoto(this)">
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Nama
                                    Produk</label>
                                <input type="text" name="tambah_nama" id="tambah_nama"
                                    oninput="this.value = this.value.replace(/[\u{1F300}-\u{1FFFF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '')"
                                    onpaste="event.preventDefault()" maxlength="50" placeholder="Nama Produk"
                                    class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    required>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Harga</label>
                                <input type="number" min="0" step="1"
                                    onkeydown="return event.keyCode !== 69 && event.keyCode !== 188 && event.keyCode !== 190 && event.keyCode !== 109 && event.keyCode !== 189"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" name="tambah_harga"
                                    id="tambah_harga" placeholder="Masukkan Harga satuan produk"
                                    class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    required>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Stok</label>
                                <input type="number" min="0" max="999" step="1" name="tambah_stok" id="tambah_stok"
                                    placeholder="Masukkan Stok awal produk (0-999)" oninput="cekStok(this)"
                                    onpaste="event.preventDefault()"
                                    onkeydown="if(['e','E','+','-','.'].includes(event.key)) event.preventDefault()"
                                    class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    required>
                                <p id="pesan-stok" class="hidden text-xs text-red-500 font-medium ml-1">Stok maksimal
                                    999!</p>
                            </div>

                            <button type="submit" name="tambah_menu"
                                class="mt-4 w-full h-[48px] bg-submit rounded-[15px] text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all">
                                Tambah Menu
                            </button>




                        </form>
                    </div>
                </div>
        </main>
    </div>

    <script>
        function tampilkanMode() {
            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function tutupModalEdit() {
            document.getElementById('modal-edit').classList.add('hidden');
        }

        function searchEdit() {
            document.getElementById('search-edit').classList.remove('hidden');
        }

        function tutupSearch() {
            document.getElementById('search-edit').classList.add('hidden');
            document.getElementById('preview-img').classList.add('hidden');
            document.getElementById('preview-img').src = '';
            document.getElementById('placeholder').classList.remove('hidden');
            document.getElementById('input-foto').value = '';
        }

        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('preview-img').classList.remove('hidden');
                    document.getElementById('placeholder').classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function cekStok(input) {
            const pesan = document.getElementById('pesan-stok');
            input.value = input.value.replace(/[^0-9]/g, '');

            if (parseInt(input.value) > 999) {
                input.value = 999;
                pesan.classList.remove('hidden');
                input.classList.add('border-red-400');
                input.classList.remove('border-gray-200');
            } else {
                pesan.classList.add('hidden');
                input.classList.remove('border-red-400');
                input.classList.add('border-gray-200');
            }
        }
    </script>
</body>

</html>