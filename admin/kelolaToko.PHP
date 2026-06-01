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

$error_toko = "";
$sukses_toko = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // ── Aktifkan Toko ──────────────────────────────────────
    if(isset($_POST['aktifkan_toko']) && isset($_POST['id_user_target'])){
        $id_target = intval($_POST['id_user_target']);

        // Cek apakah ada ruang kantin yang tersisa
        $cekRuang = $db_ekantin->query("SELECT id_ruang FROM ruang_kantin WHERE id_toko IS NULL LIMIT 1");
        if($cekRuang->num_rows == 0){
            // Tidak ada ruang kosong
            header("Location: kelolaToko.php?error=ruang_penuh");
            exit;
        }

        // Ambil nomor ruang yang dipilih
        $ruang_dipilih = intval($_POST['ruang_aktif'] ?? 0);
        if($ruang_dipilih <= 0){
            header("Location: kelolaToko.php?error=pilih_ruang");
            exit;
        }

        // Ambil id_toko dari id_users
        $qTokoCek = $db_ekantin->query("SELECT t.id_toko FROM toko t JOIN users u ON u.id_users = t.id_users WHERE u.id_users = '$id_target'");
        if($qTokoCek->num_rows > 0){
            $tokoData = $qTokoCek->fetch_assoc();
            $id_toko_target = $tokoData['id_toko'];

            // Validasi ruang benar-benar kosong
            $cekRuangDipilih = $db_ekantin->query("SELECT id_ruang FROM ruang_kantin WHERE nomor_ruang='$ruang_dipilih' AND id_toko IS NULL");
            if($cekRuangDipilih->num_rows > 0){
                $db_ekantin->query("UPDATE users SET status='aktif' WHERE id_users='$id_target'");
                $db_ekantin->query("UPDATE ruang_kantin SET id_toko='$id_toko_target' WHERE nomor_ruang='$ruang_dipilih'");
            }
        }
        header("Location: kelolaToko.php?sukses=aktif");
        exit;
    }

    // ── Nonaktifkan Toko ───────────────────────────────────
    if(isset($_POST['nonaktifkan_toko']) && isset($_POST['id_user_target'])){
        $id_target = intval($_POST['id_user_target']);
        $qTokoCek = $db_ekantin->query("SELECT t.id_toko FROM toko t JOIN users u ON u.id_users = t.id_users WHERE u.id_users = '$id_target'");
        if($qTokoCek->num_rows > 0){
            $tokoData = $qTokoCek->fetch_assoc();
            $id_toko_target = $tokoData['id_toko'];
            $db_ekantin->query("UPDATE users SET status='nonaktif' WHERE id_users='$id_target'");
            // Kosongkan ruang kantin secara otomatis
            $db_ekantin->query("UPDATE ruang_kantin SET id_toko=NULL WHERE id_toko='$id_toko_target'");
        }
        header("Location: kelolaToko.php?sukses=nonaktif");
        exit;
    }

    // ── Tambah Toko ────────────────────────────────────────
    if(isset($_POST['tambah_toko'])){
        $username  = $_POST['nama'];
        $namaToko  = $_POST['nama_toko'];
        $password  = $_POST['password'];
        $role      = "penjual";
        $email     = $_POST['email'];
        $telepon   = $_POST['telepon'];
        $nomor_ruang_baru = intval($_POST['nomor_ruang'] ?? 0);

        if(strlen($username) < 3 || strlen($username) > 20){
            $error_toko = "Username harus antara 3-20 karakter.";
        } else if(!preg_match('/^[a-zA-Z0-9_.]+$/', $username)){
            $error_toko = "Username hanya boleh huruf, angka, underscore, dan titik.";
        }

        if($error_toko == "" && $nomor_ruang_baru <= 0){
            $error_toko = "Pilih ruang kantin terlebih dahulu.";
        }

        if($error_toko == ""){
            // Validasi ruang benar-benar kosong
            $cekRuangBaru = $db_ekantin->query("SELECT id_ruang FROM ruang_kantin WHERE nomor_ruang='$nomor_ruang_baru' AND id_toko IS NULL");
            if($cekRuangBaru->num_rows == 0)
                $error_toko = "Ruang kantin tersebut sudah terisi atau tidak valid.";
        }

        if($error_toko == ""){
            $cekUsername = $db_ekantin->query("SELECT id_users FROM users WHERE username='$username'");
            if($cekUsername->num_rows > 0)
                $error_toko = "Username sudah digunakan, silakan pilih yang lain.";
        }

        if($error_toko == ""){
            $cekEmail = $db_ekantin->query("SELECT id_users FROM users WHERE email='$email'");
            if($cekEmail->num_rows > 0)
                $error_toko = "Email sudah terdaftar.";
        }

        if($error_toko == ""){
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $jamBuka  = $_POST['jam_buka']  ?? '07:00';
            $jamTutup = $_POST['jam_tutup'] ?? '15:00';

            $cek = "INSERT INTO users(username, password, role, no_telepon, email, status)
                        VALUES('$username','$hash_password','$role','$telepon','$email','aktif')";
            if($db_ekantin->query($cek)){
                $id_user = $db_ekantin->insert_id;
                $db_ekantin->query("INSERT INTO toko(id_users, nama_toko, jam_buka, jam_tutup)
                                VALUES('$id_user','$namaToko', '$jamBuka', '$jamTutup')");
                $id_toko_baru = $db_ekantin->insert_id;
                // Assign ruang kantin
                $db_ekantin->query("UPDATE ruang_kantin SET id_toko='$id_toko_baru' WHERE nomor_ruang='$nomor_ruang_baru' AND id_toko IS NULL");
                header("Location: kelolaToko.php?sukses=tambah");
                exit;
            }
        }
    }
}

// ── Statistik ──────────────────────────────────────────────
$sql    = "SELECT COUNT(*) as total FROM toko t
           JOIN users u ON t.id_users = u.id_users
           WHERE u.status = 'aktif'";
$hasil  = $db_ekantin->query($sql);
$row    = $hasil->fetch_assoc();
$total_aktif = $row['total'];

$qRuangKosong = $db_ekantin->query("SELECT COUNT(*) as kosong FROM ruang_kantin WHERE id_toko IS NULL");
$total_ruang_kosong = ($qRuangKosong && $qRuangKosong->num_rows > 0) ? $qRuangKosong->fetch_assoc()['kosong'] : 10;

// Ambil daftar ruang yang masih kosong (untuk dropdown form tambah toko)
$qRuangTersedia = $db_ekantin->query("SELECT nomor_ruang FROM ruang_kantin WHERE id_toko IS NULL ORDER BY nomor_ruang");
$ruang_tersedia = [];
if($qRuangTersedia){
    while($r = $qRuangTersedia->fetch_assoc()){
        $ruang_tersedia[] = $r['nomor_ruang'];
    }
}
$ada_ruang_kosong = count($ruang_tersedia) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Toko</title>

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
    <style>
        .popup-enter { animation: popupIn 0.2s ease-out both; }
        @keyframes popupIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
<div class="flex min-h-screen relative">
    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8">

        <!-- ── Header ─────────────────────────────────────── -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Kelola Toko</h2>
                <p class="text-text-3 font-body mt-2">Silahkan masukkan ID user untuk mengelola toko</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="toggleModalNonaktif(true)"
                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-[15px] font-bold text-sm shadow-md transition-all active:scale-95 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    Kelola Status Toko
                </button>
                <button onclick="toggleModal(true)"
                    class="bg-primary hover:bg-submit text-white px-6 py-3 rounded-[15px] font-bold text-sm shadow-md transition-all active:scale-95 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Toko
                </button>
            </div>
        </header>

        <!-- ── Toast notifikasi sukses ────────────────────── -->
        <?php if(isset($_GET['sukses'])): ?>
        <div id="toast-sukses"
             class="flex items-center gap-3 mb-6 px-4 py-3 rounded-xl text-sm font-medium
             <?= $_GET['sukses'] == 'nonaktif' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <?php
                if($_GET['sukses'] == 'aktif')    echo 'Toko berhasil diaktifkan.';
                if($_GET['sukses'] == 'nonaktif') echo 'Toko berhasil dinonaktifkan. Ruang kantin otomatis dikosongkan.';
                if($_GET['sukses'] == 'tambah')   echo 'Toko baru berhasil ditambahkan.';
            ?>
            <button onclick="document.getElementById('toast-sukses').remove()"
                class="ml-auto font-bold text-lg leading-none opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
        <div id="toast-error" class="flex items-center gap-3 mb-6 px-4 py-3 rounded-xl text-sm font-medium bg-red-50 border border-red-200 text-red-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?php
                if($_GET['error'] == 'ruang_penuh') echo 'Tidak bisa mengaktifkan toko. Semua ruang kantin sudah terisi!';
                if($_GET['error'] == 'pilih_ruang') echo 'Silakan pilih nomor ruang kantin yang tersedia.';
            ?>
            <button onclick="document.getElementById('toast-error').remove()"
                class="ml-auto font-bold text-lg leading-none opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?>

        <!-- ── Statistik ──────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 md:gap-6 mb-6">
            <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2 relative overflow-hidden group">
                <div class="relative gap-4 z-10">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Toko Aktif</p>
                    <p class="text-4xl font-extrabold text-primary"><?php echo $total_aktif; ?></p>
                    <p class="text-xs text-text-2">dari 10 ruang kantin</p>
                </div>
                <img src="../assets/img/ikonstore.png" alt="Store Icon"
                    class="absolute bottom-0 right-0 translate-y-9 w-[120px] md:w-[140px] opacity-10 pointer-events-none transition-transform duration-300 group-hover:scale-110">
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-sm border <?= $total_ruang_kosong > 0 ? 'border-green-100' : 'border-red-100' ?> flex flex-col gap-2 relative overflow-hidden group">
                <div class="relative gap-4 z-10">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Ruang Kosong</p>
                    <p class="text-4xl font-extrabold <?= $total_ruang_kosong > 0 ? 'text-green-600' : 'text-red-500' ?>"><?php echo $total_ruang_kosong; ?></p>
                    <p class="text-xs text-text-2"><?= $total_ruang_kosong > 0 ? 'ruang tersedia' : 'semua terisi' ?></p>
                </div>
                <img src="../assets/img/ikonstore.png" alt="Store Icon"
                    class="absolute bottom-0 right-0 translate-y-9 w-[120px] md:w-[140px] opacity-10 pointer-events-none transition-transform duration-300 group-hover:scale-110">
            </div>
        </div>

        <!-- ── Tabel Toko ─────────────────────────────────── -->
        <div class="w-full bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-[40px_40px_1fr_100px_80px_130px_100px_80px] bg-primary px-6 py-4 gap-4">
                <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">User</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Nama</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Toko</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Ruang</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Telepon</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
                <p class="text-xs font-bold uppercase tracking-widest text-white">Aksi</p>
            </div>

            <div class="overflow-y-auto max-h-[400px]">
                <?php
                $query  = "SELECT t.*, u.username, u.email, u.no_telepon, u.status,
                                  rk.nomor_ruang
                           FROM toko t
                           JOIN users u ON t.id_users = u.id_users
                           LEFT JOIN ruang_kantin rk ON rk.id_toko = t.id_toko
                           ORDER BY t.id_toko";
                $result = $db_ekantin->query($query);

                if($result && $result->num_rows > 0){
                    while($data = $result->fetch_assoc()){
                        $id_toko   = $data['id_toko'];
                        $id_user   = $data['id_users'];
                        $username  = htmlspecialchars($data['username'], ENT_QUOTES);
                        $toko      = htmlspecialchars($data['nama_toko'], ENT_QUOTES);
                        $status    = $data['status'];
                        $lokasi    = htmlspecialchars($data['lokasi']    ?? 'Belum diatur',        ENT_QUOTES);
                        $deskripsi = htmlspecialchars($data['deskripsi'] ?? 'Tidak ada deskripsi', ENT_QUOTES);
                        $jam_buka  = $data['jam_buka']  ?? '--:--';
                        $jam_tutup = $data['jam_tutup'] ?? '--:--';
                        $nomor_ruang = $data['nomor_ruang'] ? 'Ruang ' . $data['nomor_ruang'] : '-';

                        $tulisanStatus = ($status == 'aktif')
                            ? "<span class='text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full'>Aktif</span>"
                            : "<span class='text-xs font-semibold text-red-500 bg-red-100 px-3 py-1 rounded-full'>Nonaktif</span>";

                        $detailData = json_encode([
                            "id_toko" => $id_toko,
                            "id_user" => $id_user,
                            "username" => $data['username'],
                            "email" => $data['email'],
                            "telepon" => $data['no_telepon'],
                            "toko" => $data['nama_toko'],
                            "ruang" => $nomor_ruang,
                            "status" => ucfirst($status),
                            "jam" => $jam_buka . ' - ' . $jam_tutup,
                            "lokasi" => $data['lokasi'] ?? 'Belum diatur',
                            "deskripsi" => $data['deskripsi'] ?? 'Tidak ada deskripsi'
                        ]);

                        echo "
                        <div class='row-toko grid grid-cols-[40px_40px_1fr_100px_80px_130px_100px_80px] px-6 py-4 gap-4 border-b border-gray-100 items-center hover:bg-gray-50 transition-colors'
                             data-iduser='$id_user' data-status='$status'>
                            <p class='text-sm text-text-2'>$id_toko</p>
                            <p class='text-sm text-text-2'>$id_user</p>
                            <p class='text-sm font-medium text-text-1 truncate'>$username</p>
                            <p class='text-sm font-medium text-text-1 truncate'>$toko</p>
                            <p class='text-sm font-semibold text-primary'>$nomor_ruang</p>
                            <p class='text-sm text-text-2 truncate'>" . htmlspecialchars($data['no_telepon']) . "</p>
                            $tulisanStatus
                            <button data-info='" . htmlspecialchars($detailData, ENT_QUOTES, 'UTF-8') . "' onclick=\"showDetail(this)\"
                                class='text-primary bg-primary/10 hover:bg-primary hover:text-white px-3 py-1.5 rounded-[10px] font-bold text-xs uppercase tracking-wider transition-all border border-primary/20 w-full'>Detail</button>
                        </div>";
                    }
                } else {
                    echo "<div class='px-6 py-8 text-center text-text-3 text-sm'>Belum ada data toko.</div>";
                }
                ?>
            </div>
        </div>

    </main>
</div>

<!-- ════════════════════════════════════════════════════
     MODAL TAMBAH TOKO
════════════════════════════════════════════════════ -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black/50 z-[99] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[24px] shadow-2xl overflow-hidden popup-enter max-h-[90vh] flex flex-col">
        <div class="bg-primary p-6 flex justify-between items-center text-white shrink-0">
            <h3 class="font-bold text-xl">Tambah Toko Baru</h3>
            <button onclick="toggleModal(false)" class="hover:bg-white/20 p-1 rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form method="POST" class="p-8 flex flex-col gap-4 overflow-y-auto">
            <input type="hidden" name="tambah_toko" value="1">

            <?php if($error_toko != ""): ?>
            <div class="px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                <?php echo $error_toko; ?>
            </div>
            <?php endif; ?>

            <?php if(!$ada_ruang_kosong): ?>
            <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-[15px] text-sm text-red-600 font-medium flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Semua 10 ruang kantin sudah terisi. Nonaktifkan toko terlebih dahulu untuk membebaskan ruang.
            </div>
            <?php endif; ?>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Username</label>
                <input type="text" name="nama"
                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9_.]/g, '')" maxlength="20"
                    value="<?= isset($_POST['nama']) && $error_toko != '' ? htmlspecialchars($_POST['nama']) : '' ?>"
                    placeholder="Masukkan username pemilik"
                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Password</label>
                <input type="password" name="password" placeholder="Masukkan password"
                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Nama Toko</label>
                <input type="text" name="nama_toko"
                    value="<?= isset($_POST['nama_toko']) && $error_toko != '' ? htmlspecialchars($_POST['nama_toko']) : '' ?>"
                    placeholder="Masukkan nama toko"
                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Email</label>
                <input type="email" name="email"
                    value="<?= isset($_POST['email']) && $error_toko != '' ? htmlspecialchars($_POST['email']) : '' ?>"
                    placeholder="contoh@mail.com"
                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                <input type="tel" name="telepon"
                    value="<?= isset($_POST['telepon']) && $error_toko != '' ? htmlspecialchars($_POST['telepon']) : '' ?>"
                    placeholder="0812xxxx"
                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
            </div>

            <!-- Pilih Ruang Kantin -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Ruang Kantin</label>
                <?php if($ada_ruang_kosong): ?>
                <select name="nomor_ruang" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                    <option value="">-- Pilih Nomor Ruang --</option>
                    <?php foreach($ruang_tersedia as $nr): ?>
                    <option value="<?= $nr ?>" <?= (isset($_POST['nomor_ruang']) && $_POST['nomor_ruang'] == $nr && $error_toko != '') ? 'selected' : '' ?>>
                        Ruang <?= $nr ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <div class="w-full bg-red-50 border border-red-200 rounded-[12px] p-3 text-sm text-red-500">Tidak ada ruang tersedia</div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Jam Buka</label>
                    <input type="time" name="jam_buka"
                        value="<?= isset($_POST['jam_buka']) && $error_toko != '' ? htmlspecialchars($_POST['jam_buka']) : '07:00' ?>"
                        class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Jam Tutup</label>
                    <input type="time" name="jam_tutup"
                        value="<?= isset($_POST['jam_tutup']) && $error_toko != '' ? htmlspecialchars($_POST['jam_tutup']) : '15:00' ?>"
                        class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" <?= !$ada_ruang_kosong ? 'disabled' : '' ?> required>
                </div>
            </div>

            <button type="submit" <?= !$ada_ruang_kosong ? 'disabled' : '' ?>
                class="w-full <?= $ada_ruang_kosong ? 'bg-submit hover:opacity-90 active:scale-[0.98]' : 'bg-gray-300 cursor-not-allowed' ?> text-white font-bold py-4 rounded-[15px] mt-2 shrink-0 shadow-lg transition-all">
                <?= $ada_ruang_kosong ? 'Simpan Data Toko' : 'Ruang Kantin Penuh' ?>
            </button>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     MODAL DETAIL TOKO
════════════════════════════════════════════════════ -->
<div id="modalDetail" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white w-full max-w-lg rounded-[24px] shadow-2xl overflow-hidden popup-enter">
        <div class="bg-primary p-6 flex justify-between items-center text-white">
            <h3 id="detailNamaToko" class="font-bold text-xl">Detail Toko</h3>
            <button onclick="closeDetail()" class="hover:bg-white/20 p-1 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-8 space-y-6 overflow-y-auto max-h-[75vh]">
            
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">ID Toko / User</p>
                    <p class="text-sm font-semibold text-text-1 mt-1">#<span id="detailIdToko"></span> / #<span id="detailIdUser"></span></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Status / Ruang</p>
                    <p class="text-sm font-semibold text-text-1 mt-1"><span id="detailStatus" class="text-primary"></span> &bull; <span id="detailRuang"></span></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Pemilik (Username)</p>
                    <p id="detailUsername" class="text-sm font-semibold text-text-1 mt-1"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Email</p>
                    <p id="detailEmail" class="text-sm font-semibold text-text-1 mt-1"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Telepon</p>
                    <p id="detailTelepon" class="text-sm font-semibold text-text-1 mt-1"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Jam Operasional</p>
                    <p id="detailJam" class="text-sm font-semibold text-text-1 mt-1"></p>
                </div>
            </div>
            
            <hr class="border-gray-100 border-dashed">
            
            <div>
                <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Lokasi</p>
                <p id="detailLokasi" class="text-sm text-text-1 mt-1"></p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase text-text-3 tracking-widest">Deskripsi Toko</p>
                <p id="detailDeskripsi" class="text-sm text-text-2 mt-2 leading-relaxed italic"></p>
            </div>
            <button onclick="closeDetail()"
                class="w-full bg-input text-text-1 font-bold py-3 rounded-[15px] hover:bg-gray-200 transition-all">Tutup</button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     MODAL KELOLA STATUS TOKO
════════════════════════════════════════════════════ -->
<div id="modalNonaktif" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-[24px] shadow-2xl overflow-hidden popup-enter">
        <div class="bg-red-500 p-6 flex justify-between items-center text-white">
            <h3 class="font-bold text-xl">Kelola Status Toko</h3>
            <button onclick="toggleModalNonaktif(false)" class="hover:bg-white/20 p-1 rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" onsubmit="return validateStatusForm(event)" class="p-8 flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">ID User Toko</label>
                <input type="number" id="inputIdStatus" name="id_user_target" placeholder="Masukkan ID User Toko" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-red-500/20" required>
                <p class="text-xs text-text-3 mt-1">Lihat ID User pada tabel daftar toko.</p>
                <p id="statusError" class="text-xs text-red-500 mt-1 hidden font-bold">ID User tidak ditemukan!</p>
            </div>

            <!-- Dropdown Ruang (hanya muncul saat mengaktifkan toko) -->
            <div id="ruangAktifContainer" class="flex-col gap-1 hidden">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Pilih Ruang Kantin</label>
                <select name="ruang_aktif" id="selectRuangAktif" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-green-500/20">
                    <option value="">-- Pilih Ruang --</option>
                    <?php foreach($ruang_tersedia as $nr): ?>
                    <option value="<?= $nr ?>">Ruang <?= $nr ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(!$ada_ruang_kosong): ?>
                <p class="text-xs text-red-500 font-bold mt-1">⚠ Semua ruang penuh! Tidak bisa mengaktifkan toko.</p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-4" id="statusButtonsContainer">
                <button type="submit" id="btnAktifkan" name="aktifkan_toko"
                    onclick="return konfirmasiAktifkan()"
                    class="hidden w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-[15px] shadow-md transition-all active:scale-95">
                    Aktifkan
                </button>
                <button type="submit" id="btnNonaktifkan" name="nonaktifkan_toko" onclick="return confirm('Nonaktifkan toko ini? Ruang kantin akan otomatis dikosongkan.')" class="hidden w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-[15px] shadow-md transition-all active:scale-95">
                    Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if($error_toko != ""): ?>
<script>window.onload = function(){ toggleModal(true); }</script>
<?php endif; ?>

<script>
    const adaRuangKosong = <?= $ada_ruang_kosong ? 'true' : 'false' ?>;

    /* ── Modal Tambah ─────────────────────────── */
    function toggleModal(show) {
        document.getElementById('modalTambah').classList.toggle('hidden', !show);
    }

    function konfirmasiAktifkan() {
        const selectRuang = document.getElementById('selectRuangAktif');
        if (!adaRuangKosong) {
            alert('Semua ruang kantin sudah penuh! Nonaktifkan toko lain terlebih dahulu.');
            return false;
        }
        if (!selectRuang.value) {
            alert('Pilih nomor ruang kantin terlebih dahulu.');
            return false;
        }
        return confirm('Aktifkan toko ini di Ruang ' + selectRuang.value + '?');
    }

    /* ── Modal Kelola Status ──────────────────── */
    function toggleModalNonaktif(show) {
        const modal = document.getElementById('modalNonaktif');
        modal.classList.toggle('hidden', !show);
        if (!show) resetStatusModal();
    }

    function resetStatusModal() {
        document.getElementById('inputIdStatus').value = '';
        document.getElementById('statusError').classList.add('hidden');
        document.getElementById('btnAktifkan').classList.add('hidden');
        document.getElementById('btnNonaktifkan').classList.add('hidden');
    }

    function validateStatusForm(event) {
        let id = document.getElementById('inputIdStatus').value;
        let row = document.querySelector(`.row-toko[data-iduser="${id}"]`);
        if (!row) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    document.getElementById('inputIdStatus').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            let id = this.value;
            let row = document.querySelector(`.row-toko[data-iduser="${id}"]`);
            if (row) {
                let status = row.getAttribute('data-status');
                if (status === 'aktif') {
                    document.getElementById('btnNonaktifkan').click();
                } else {
                    document.getElementById('btnAktifkan').click();
                }
            }
        }
    });

    document.getElementById('inputIdStatus').addEventListener('input', function() {
        let id = this.value;
        let statusError = document.getElementById('statusError');
        let btnAktifkan = document.getElementById('btnAktifkan');
        let btnNonaktifkan = document.getElementById('btnNonaktifkan');
        let container = document.getElementById('statusButtonsContainer');
        
        if (!id) {
            statusError.classList.add('hidden');
            btnAktifkan.classList.add('hidden');
            btnNonaktifkan.classList.add('hidden');
            container.classList.remove('grid-cols-1');
            container.classList.add('grid-cols-2');
            return;
        }

        let row = document.querySelector(`.row-toko[data-iduser="${id}"]`);
        if (row) {
            statusError.classList.add('hidden');
            let status = row.getAttribute('data-status');

            container.classList.remove('grid-cols-2');
            container.classList.add('grid-cols-1');

            if (status === 'aktif') {
                btnAktifkan.classList.add('hidden');
                btnNonaktifkan.classList.remove('hidden');
                document.getElementById('ruangAktifContainer').classList.add('hidden');
            } else {
                btnAktifkan.classList.remove('hidden');
                btnNonaktifkan.classList.add('hidden');
                // Tampilkan dropdown ruang hanya saat akan mengaktifkan
                document.getElementById('ruangAktifContainer').classList.remove('hidden');
                document.getElementById('ruangAktifContainer').classList.add('flex');
            }
        } else {
            statusError.classList.remove('hidden');
            btnAktifkan.classList.add('hidden');
            btnNonaktifkan.classList.add('hidden');
            document.getElementById('ruangAktifContainer').classList.add('hidden');
            container.classList.remove('grid-cols-1');
            container.classList.add('grid-cols-2');
        }
    });

    /* ── Modal Detail ─────────────────────────── */
    function showDetail(btnElement) {
        let info = JSON.parse(btnElement.getAttribute('data-info'));
        
        document.getElementById('detailNamaToko').innerText  = info.toko;
        document.getElementById('detailIdToko').innerText    = info.id_toko;
        document.getElementById('detailIdUser').innerText    = info.id_user;
        document.getElementById('detailUsername').innerText  = info.username;
        document.getElementById('detailEmail').innerText     = info.email;
        document.getElementById('detailTelepon').innerText   = info.telepon;
        document.getElementById('detailStatus').innerText    = info.status;
        document.getElementById('detailRuang').innerText     = info.ruang;
        document.getElementById('detailJam').innerText       = info.jam;
        document.getElementById('detailLokasi').innerText    = info.lokasi;
        document.getElementById('detailDeskripsi').innerText = info.deskripsi;
        
        document.getElementById('modalDetail').classList.remove('hidden');
    }
    function closeDetail() {
        document.getElementById('modalDetail').classList.add('hidden');
    }

    /* ── Klik backdrop untuk tutup modal ─────── */
    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('modalTambah'))   toggleModal(false);
        if (e.target === document.getElementById('modalDetail'))   closeDetail();
        if (e.target === document.getElementById('modalNonaktif')) toggleModalNonaktif(false);
    });
    /* ── Logika input ID User ─────────────────── */
    document.getElementById('inputIdStatus').addEventListener('input', function() {
        const id            = this.value.trim();
        const statusError   = document.getElementById('statusError');
        const statusInfo    = document.getElementById('statusInfo');
        const statusInfoTxt = document.getElementById('statusInfoText');
        const btnAktifkan   = document.getElementById('btnAktifkan');
        const btnNonaktifkan= document.getElementById('btnNonaktifkan');

        // Reset semua
        statusError.classList.add('hidden');
        statusInfo.classList.add('hidden');
        btnAktifkan.classList.add('hidden');
        btnNonaktifkan.classList.add('hidden');

        if (!id) return; // Input kosong → tidak perlu cek

        const row = document.querySelector(`.row-toko[data-iduser="${id}"]`);

        if (!row) {
            // ID tidak ditemukan
            statusError.classList.remove('hidden');
            return;
        }

        // ID ditemukan — baca status
        const status = row.getAttribute('data-status');
        const namaToko = row.querySelector('.text-text-1.truncate:nth-child(4)')?.innerText || 'toko ini';

        if (status === 'aktif') {
            // Toko aktif → hanya tampilkan tombol Nonaktifkan
            statusInfoTxt.innerText = `Toko ditemukan · Status saat ini: Aktif`;
            statusInfo.classList.remove('hidden');
            statusInfo.className = statusInfo.className.replace('bg-blue-50 border-blue-200 text-blue-700', '');
            statusInfo.classList.add('bg-green-50', 'border-green-200', 'text-green-700');
            btnNonaktifkan.classList.remove('hidden');
        } else {
            // Toko nonaktif → hanya tampilkan tombol Aktifkan
            statusInfoTxt.innerText = `Toko ditemukan · Status saat ini: Nonaktif`;
            statusInfo.classList.remove('hidden');
            statusInfo.className = statusInfo.className.replace('bg-green-50 border-green-200 text-green-700', '');
            statusInfo.classList.add('bg-orange-50', 'border-orange-200', 'text-orange-700');
            btnAktifkan.classList.remove('hidden');
        }
    });
</script>

</body>
</html>