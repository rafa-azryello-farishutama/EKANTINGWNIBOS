<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if(!isset($_SESSION['penjual_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['penjual_id_users'];
$_SESSION['username'] = $_SESSION['penjual_username'];
$_SESSION['role']     = $_SESSION['penjual_role'];
$_SESSION['id_toko']   = $_SESSION['penjual_id_toko'];
$_SESSION['nama_toko'] = $_SESSION['penjual_nama_toko'];

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

/* ═══════════════════════════════════════
   HANDLE UPLOAD BANNER
═══════════════════════════════════════ */
if (isset($_POST['simpan_banner'])) {
    $id_toko = $_SESSION['id_toko'];

    if (isset($_FILES['foto_banner']) && $_FILES['foto_banner']['error'] === UPLOAD_ERR_OK) {
        $nama_file      = $_FILES['foto_banner']['name'];
        $tmp_name       = $_FILES['foto_banner']['tmp_name'];
        $file_size      = $_FILES['foto_banner']['size'];
        $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ekstensi_valid = ['jpg', 'png', 'jpeg', 'webp'];
        $folder_banner  = "../assets/img_banner/";
        $max_size       = 2097152; // 2MB

        if ($file_size > $max_size) {
            header("Location: produk.php?error=ukuran&dari=banner");
            exit;
        } elseif (!in_array($ekstensi_file, $ekstensi_valid)) {
            header("Location: produk.php?error=format&dari=banner");
            exit;
        } else {
            // Buat folder jika belum ada
            if (!is_dir($folder_banner)) {
                mkdir($folder_banner, 0777, true);
            }

            // Ambil banner lama untuk dihapus
            $res_lama    = $db_ekantin->query("SELECT banner_toko FROM toko WHERE id_toko='$id_toko'");
            $row_lama    = $res_lama->fetch_assoc();
            $banner_lama = $row_lama['banner_toko'] ?? null;

            $nama_banner = "banner_" . $id_toko . "_" . time() . "." . $ekstensi_file;
            $upload_path = $folder_banner . $nama_banner;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                // Hapus file banner lama jika ada
                if ($banner_lama && file_exists($folder_banner . $banner_lama)) {
                    unlink($folder_banner . $banner_lama);
                }
                $nama_banner_esc = $db_ekantin->real_escape_string($nama_banner);
                $db_ekantin->query("UPDATE toko SET banner_toko='$nama_banner_esc' WHERE id_toko='$id_toko'");
            } else {
                header("Location: produk.php?error=upload&dari=banner");
                exit;
            }
        }
    }

    header("Location: produk.php?banner=berhasil");
    exit;
}

/* ═══════════════════════════════════════
   HANDLE HAPUS BANNER
═══════════════════════════════════════ */
if (isset($_POST['hapus_banner'])) {
    $id_toko     = $_SESSION['id_toko'];
    $res_lama    = $db_ekantin->query("SELECT banner_toko FROM toko WHERE id_toko='$id_toko'");
    $row_lama    = $res_lama->fetch_assoc();
    $banner_lama = $row_lama['banner_toko'] ?? null;

    if ($banner_lama && file_exists("../assets/img_banner/" . $banner_lama)) {
        unlink("../assets/img_banner/" . $banner_lama);
    }
    $db_ekantin->query("UPDATE toko SET banner_toko=NULL WHERE id_toko='$id_toko'");
    header("Location: produk.php");
    exit;
}

/* ═══════════════════════════════════════
   HANDLE TAMBAH MENU
═══════════════════════════════════════ */
if (isset($_POST['tambah_menu'])) {
    $nama_menu = $db_ekantin->real_escape_string($_POST['tambah_nama']);
    $harga_menu = $db_ekantin->real_escape_string($_POST['tambah_harga']);
    $stok_menu  = $db_ekantin->real_escape_string($_POST['tambah_stok']);
    $id_toko    = $_SESSION['id_toko'];
    $tipe_produk = $_POST['tipe_pesanan'];

    $nama_baru = null; // Default: tidak ada foto

    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] === UPLOAD_ERR_OK) {
        $nama_file     = $_FILES['foto_produk']['name'];
        $tmp_name      = $_FILES['foto_produk']['tmp_name'];
        $file_size     = $_FILES['foto_produk']['size'];
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ekstensi_valid = ['jpg', 'png', 'jpeg', 'webp'];
        $folder        = "../assets/img_produk/";
        $max_size      = 2097152;

        if ($file_size > $max_size) {
            header("Location: produk.php?error=ukuran");
            exit;
        } elseif (!in_array($ekstensi_file, $ekstensi_valid)) {
            header("Location: produk.php?error=format");
            exit;
        } else {
            // Gunakan id_toko + nama menu + timestamp + random untuk mencegah nama file sama
            $nama_filter = preg_replace('/[^a-zA-Z0-9]/', '', $nama_menu);
            $nama_baru   = $id_toko . '_' . $nama_filter . '_' . time() . '_' . rand(100, 999) . '.' . $ekstensi_file;
            $upload_path = $folder . $nama_baru;

            if (!move_uploaded_file($tmp_name, $upload_path)) {
                header("Location: produk.php?error=upload");
                exit;
            }
        }
    }
    // Jika tidak ada foto, file_foto disimpan sebagai NULL
    if ($nama_baru !== null) {
        $nama_foto_baru = $db_ekantin->real_escape_string($nama_baru);
        $sql = "INSERT INTO produk_kantin (id_toko, nama_menu, harga, stok, file_foto, tipe_produk)
                VALUES ('$id_toko', '$nama_menu', '$harga_menu', '$stok_menu', '$nama_foto_baru', '$tipe_produk')";
    } else {
        $sql = "INSERT INTO produk_kantin (id_toko, nama_menu, harga, stok, file_foto, tipe_produk)
                VALUES ('$id_toko', '$nama_menu', '$harga_menu', '$stok_menu', NULL, '$tipe_produk')";
    }

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
} elseif (isset($_POST['filter_minuman'])) {
    $filter_tipe = 'minuman';
}

/* ═══════════════════════════════════════
   Ambil data toko + banner
═══════════════════════════════════════ */
$id_toko          = $_SESSION['id_toko'];
$res_toko         = $db_ekantin->query("SELECT * FROM toko WHERE id_toko='$id_toko'");
$data_toko        = $res_toko->fetch_assoc();
$banner_toko      = $data_toko['banner_toko'] ?? null;
$banner_src       = $banner_toko ? "../assets/img_banner/" . htmlspecialchars($banner_toko) : null;
$nama_toko_display = htmlspecialchars($data_toko['nama_toko'] ?? 'Toko Saya');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>

    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* Banner sizing */
        .banner-wrap      { height: 140px; }
        .banner-title     { font-size: 1.1rem; }
        .banner-deco      { font-size: 4rem; }
        @media (min-width: 640px) {
            .banner-wrap  { height: 180px; }
            .banner-title { font-size: 1.5rem; }
            .banner-deco  { font-size: 6rem; }
        }

        /* Drop zone drag-over state */
        #drop-zone.dragover {
            border-color: #004900;
            background: #f0f4f0;
        }
    </style>
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
<div class="flex w-full min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-7xl mx-auto flex flex-col gap-6">

            <!-- Notif banner berhasil -->
            <?php if (isset($_GET['banner']) && $_GET['banner'] == 'berhasil'): ?>
            <div id="notif-banner"
                 class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium animate-[fadeInUp_0.4s_ease-out_forwards]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Banner berhasil diperbarui! Pembeli sekarang bisa melihat banner barumu.
                <button onclick="document.getElementById('notif-banner').remove()"
                        class="ml-auto text-green-500 hover:text-green-700 font-bold text-lg leading-none">&times;</button>
            </div>
            <?php endif; ?>

            <!-- ═══════════════════════════════════════════ -->
            <!--  BANNER SECTION                             -->
            <!-- ═══════════════════════════════════════════ -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.05s;">
                <div class="relative rounded-2xl overflow-hidden banner-wrap">

                    <?php if ($banner_src): ?>
                        <!-- Banner custom dari penjual (image) -->
                        <img src="<?= $banner_src ?>" alt="Banner Toko"
                             class="w-full h-full object-cover">
                        <!-- Overlay agar tombol tetap terbaca -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>

                        <!-- Label aktif -->
                        <div class="absolute bottom-3 left-3 z-10">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg text-white"
                                  style="background:rgba(0,0,0,0.45); backdrop-filter:blur(4px);">
                                ✓ Banner aktif · terlihat oleh pembeli
                            </span>
                        </div>
                    <?php else: ?>
                        <!-- Fallback: gradient default saat belum upload -->
                        <div class="absolute inset-0 bg-gradient-to-br from-primary to-[#00a800]"></div>
                        <div class="absolute inset-0 flex items-center px-4 sm:px-8">
                            <div class="relative z-10 max-w-[65%] sm:max-w-none">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1.5 sm:mb-3"
                                      style="background:rgba(255,255,255,0.2);color:#fff;">🏪 Toko Saya</span>
                                <h3 class="text-white font-extrabold leading-tight banner-title"><?= $nama_toko_display ?></h3>
                                <p class="text-green-200 text-xs mt-1">Upload banner agar tampil menarik di halaman pembeli.</p>
                            </div>
                            <div class="absolute right-3 sm:right-6 bottom-0 opacity-20 leading-none banner-deco select-none">🏪</div>
                        </div>
                    <?php endif; ?>

                    <!-- Tombol kontrol banner (kanan atas) -->
                    <div class="absolute top-3 right-3 flex gap-2 z-20">
                        <?php if ($banner_src): ?>
                        <form method="POST" onsubmit="return confirm('Hapus banner ini? Tampilan akan kembali ke default.')">
                            <input type="hidden" name="hapus_banner" value="1">
                            <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold
                                       bg-red-500/90 hover:bg-red-600 text-white backdrop-blur-sm transition-all shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </form>
                        <?php endif; ?>

                        <button onclick="bukaModalBanner()"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold
                                   bg-white/90 hover:bg-white text-primary backdrop-blur-sm transition-all shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?= $banner_src ? 'Ganti Banner' : 'Upload Banner' ?>
                        </button>
                    </div>

                </div>
            </div>
            <!-- ═══ END BANNER SECTION ═══ -->

            <!-- Search + Tambah Menu -->
            <div class="flex flex-col sm:flex-row gap-3 w-full animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
                <form method="POST" class="flex flex-1 gap-3">
                    <div class="flex flex-1 items-center gap-3 bg-input rounded-xl px-4 h-12 focus-within:ring-2 focus-within:ring-primary transition-all">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="name_id" placeholder="Search Menu"
                            value="<?= htmlspecialchars($keyword) ?>"
                            class="w-full h-12 bg-transparent border-none outline-none text-text-1 placeholder-gray-500 text-sm focus:ring-0 p-0">
                    </div>
                    <button type="submit" name="cari_user"
                        class="h-12 px-6 bg-submit text-white rounded-xl text-sm font-bold hover:bg-primary transition-colors whitespace-nowrap">
                        Search
                    </button>
                </form>

                <button onclick="searchEdit()"
                    class="h-12 px-6 bg-primary text-white rounded-xl text-sm font-bold hover:bg-submit transition-colors whitespace-nowrap flex items-center gap-2 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Menu
                </button>
            </div>

            <!-- Filter Tipe -->
            <div class="flex gap-3 overflow-x-auto pb-1 animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.2s;">
                <?php
                $aktif    = 'px-6 py-2 rounded-full font-bold text-sm bg-submit text-white shadow-md shadow-submit/30 transition-all hover:-translate-y-0.5 whitespace-nowrap flex-shrink-0';
                $nonaktif = 'px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-all hover:-translate-y-0.5 whitespace-nowrap flex-shrink-0';
                ?>
                <form method="POST" class="flex gap-3">
                    <button type="submit" name="filter_semua"
                        class="<?= $filter_tipe == '' ? $aktif : $nonaktif ?>">Semua</button>
                    <button type="submit" name="filter_makanan"
                        class="<?= $filter_tipe == 'makanan' ? $aktif : $nonaktif ?>">Makanan</button>
                    <button type="submit" name="filter_minuman"
                        class="<?= $filter_tipe == 'minuman' ? $aktif : $nonaktif ?>">Minuman</button>
                </form>
            </div>

            <!-- Grid Produk -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 pb-8 animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.3s;">
                <?php
                $sql = "SELECT * FROM produk_kantin WHERE id_toko='$id_toko'";
                if ($keyword !== '') {
                    $sql .= " AND nama_menu LIKE '%$keyword%'";
                }
                if ($filter_tipe !== '') {
                    $sql .= " AND tipe_produk = '$filter_tipe'";
                }
                $sql .= " ORDER BY FIELD(status_menu, 'aktif', 'nonaktif'), id_produk ASC";

                $result = $db_ekantin->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $file_foto    = htmlspecialchars($row['file_foto']);
                        $nama         = htmlspecialchars($row['nama_menu']);
                        $harga        = number_format($row['harga'], 0, ',', '.');
                        $harga_asli   = $row['harga'];
                        $stok         = $row['stok'];
                        $id_produk    = $row['id_produk'];
                        $tipe_produk  = htmlspecialchars($row['tipe_produk']);
                        $nama_js      = addslashes($row['nama_menu']);
                        $tipe_js      = addslashes($row['tipe_produk']);
                        $file_foto_js = addslashes($row['file_foto']);
                        $pesan        = $tipe_produk == 'makanan' ? 'porsi' : 'gelas';
                        $foto_src     = $file_foto ? "../assets/img_produk/$file_foto" : "../assets/img/no-image.png";
                        $status_menu  = $row['status_menu'];

                        echo "
                        <div class='bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group'>
                            <div class='h-[140px] bg-gray-200 w-full overflow-hidden relative'>
                                <img src='$foto_src' alt='$nama' class='w-full h-full object-cover group-hover:scale-110 transition-transform duration-500'>
                                " . ($status_menu === 'nonaktif' ? "<span class='absolute top-2 left-2 text-[10px] font-bold bg-red-500 text-white px-2 py-0.5 rounded shadow-sm z-10'>Nonaktif</span>" : "") . "
                            </div>
                            <div class='p-3 sm:p-4 flex flex-col gap-1 flex-1'>
                                <h3 class='font-bold text-text-1 text-sm sm:text-base line-clamp-1'>$nama</h3>
                                <p class='text-primary font-bold text-sm sm:text-base'>Rp $harga</p>
                                <p class='text-text-3 text-xs font-medium'>Stok: $stok $pesan</p>
                                <div class='mt-auto pt-3'>
                                    <button onclick='tampilkanMode($id_produk,&quot;$nama_js&quot;,$harga_asli,$stok,&quot;$tipe_js&quot;,&quot;$file_foto_js&quot;,&quot;$status_menu&quot;)'
                                        class='w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors'>
                                        Edit Menu
                                    </button>
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

<!-- ═══════════════════════════════════════════════════════════════ -->
<!--  MODAL EDIT BANNER (inline — no file terpisah)                 -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="modal-banner" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="tutupModalBanner()"></div>

    <!-- Panel -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto z-10 overflow-hidden">

        <!-- Header modal -->
        <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100">
            <div>
                <h3 class="font-extrabold text-text-1 text-lg">Edit Banner Toko</h3>
                <p class="text-text-3 text-xs mt-0.5">Gambar banner akan tampil di halaman pembeli</p>
            </div>
            <button onclick="tutupModalBanner()"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-input text-text-3 hover:text-text-1 transition-colors text-2xl font-light leading-none">
                &times;
            </button>
        </div>

        <!-- Body form -->
        <form method="POST" enctype="multipart/form-data" class="px-6 py-5 flex flex-col gap-4">
            <input type="hidden" name="simpan_banner" value="1">

            <!-- Tip ukuran -->
            <div class="flex items-start gap-2.5 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Gunakan gambar <strong>landscape</strong> (lebar &gt; tinggi). Rasio <strong>3:1</strong> atau <strong>4:1</strong> sangat disarankan. Format: JPG, PNG, WEBP · Maks. <strong>2 MB</strong>.</span>
            </div>

            <!-- Drop zone -->
            <div id="drop-zone"
                 class="relative border-2 border-dashed border-gray-300 rounded-xl overflow-hidden cursor-pointer transition-all hover:border-primary"
                 onclick="document.getElementById('input-banner').click()"
                 ondragover="event.preventDefault(); this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleBannerDrop(event)">

                <!-- Placeholder -->
                <div id="banner-placeholder" class="flex flex-col items-center justify-center gap-2 py-10 text-text-3 select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm font-semibold">Klik atau drag &amp; drop gambar di sini</p>
                    <p class="text-xs">JPG, PNG, JPEG, WEBP · Maks. 2 MB</p>
                </div>

                <!-- Preview gambar terpilih -->
                <img id="banner-preview-img" src="" alt="Preview Banner"
                     class="w-full object-cover hidden"
                     style="max-height:160px;">
            </div>

            <!-- Hidden file input -->
            <input type="file" id="input-banner" name="foto_banner"
                   accept=".jpg,.jpeg,.png,.webp" class="hidden"
                   onchange="previewBannerFile(this)">

            <!-- Error slot -->
            <div id="banner-error"
                 class="hidden px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-500 font-medium"></div>

            <!-- Tombol -->
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="tutupModalBanner()"
                    class="flex-1 h-11 rounded-xl border border-gray-200 text-text-3 font-bold text-sm hover:bg-input transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn-simpan-banner" disabled
                    class="flex-1 h-11 rounded-xl bg-primary text-white font-bold text-sm
                           hover:bg-submit transition-colors
                           disabled:opacity-40 disabled:cursor-not-allowed
                           flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Simpan Banner
                </button>
            </div>
        </form>
    </div>
</div>
<!-- ═══ END MODAL BANNER ═══ -->

<?php include 'potongan_html/produk_menu_edit.html'; ?>
<?php include 'potongan_html/produk_tambah_menu.html'; ?>

<script src="../assets/js/produk.js"></script>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!--  JS MODAL BANNER (inline — no file terpisah)                   -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<script>
    /* Buka modal */
    function bukaModalBanner() {
        document.getElementById('modal-banner').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /* Tutup modal & reset state */
    function tutupModalBanner() {
        document.getElementById('modal-banner').classList.add('hidden');
        document.body.style.overflow = '';
        resetPreviewBanner();
    }

    /* Reset preview ke kondisi awal */
    function resetPreviewBanner() {
        const inp  = document.getElementById('input-banner');
        const prev = document.getElementById('banner-preview-img');
        const ph   = document.getElementById('banner-placeholder');
        const btn  = document.getElementById('btn-simpan-banner');
        const err  = document.getElementById('banner-error');

        if (inp)  inp.value = '';
        if (prev) { prev.src = ''; prev.classList.add('hidden'); }
        if (ph)   ph.style.display = 'flex';
        if (btn)  btn.disabled = true;
        if (err)  err.classList.add('hidden');
    }

    /* Dari input file biasa */
    function previewBannerFile(input) {
        if (!input.files || !input.files[0]) return;
        validasiBanner(input.files[0]);
    }

    /* Dari drag & drop */
    function handleBannerDrop(e) {
        e.preventDefault();
        document.getElementById('drop-zone').classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (!file) return;
        // Sync ke input supaya ikut ter-submit form
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('input-banner').files = dt.files;
        validasiBanner(file);
    }

    /* Validasi ukuran & format, lalu tampilkan preview */
    function validasiBanner(file) {
        const errBox = document.getElementById('banner-error');
        const prev   = document.getElementById('banner-preview-img');
        const ph     = document.getElementById('banner-placeholder');
        const btn    = document.getElementById('btn-simpan-banner');
        const valid  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        const maxSz  = 2097152; // 2MB

        errBox.classList.add('hidden');
        errBox.textContent = '';

        if (!valid.includes(file.type)) {
            errBox.textContent = 'Format tidak valid. Gunakan JPG, PNG, JPEG, atau WEBP.';
            errBox.classList.remove('hidden');
            btn.disabled = true;
            return;
        }
        if (file.size > maxSz) {
            errBox.textContent = 'Ukuran file melebihi 2 MB. Silakan kompres gambar terlebih dahulu.';
            errBox.classList.remove('hidden');
            btn.disabled = true;
            return;
        }

        // Tampilkan preview
        const reader = new FileReader();
        reader.onload = function(e) {
            prev.src = e.target.result;
            prev.classList.remove('hidden');
            ph.style.display = 'none';
            btn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    /* Tutup modal dengan tombol Escape */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') tutupModalBanner();
    });
</script>

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