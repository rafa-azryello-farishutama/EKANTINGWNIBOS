<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php'; 

if (!isset($_SESSION['id_users']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// === Auto-create tabel pengaturan jika belum ada ===
$db_ekantin->query("
    CREATE TABLE IF NOT EXISTS pengaturan (
        kunci VARCHAR(100) PRIMARY KEY,
        nilai TEXT
    )
");
// Isi default kode_guru jika belum ada
$db_ekantin->query("INSERT IGNORE INTO pengaturan (kunci, nilai) VALUES ('kode_guru', 'GURU2025')");

$notif_kode = "";
$kode_success = false;
$kode_error = false;

// === Handle ubah kode guru ===
if (isset($_POST['ubah_kode_guru'])) {
    $kode_baru = trim($_POST['kode_guru_baru']);
    if (strlen($kode_baru) < 4) {
        $notif_kode = "Kode guru minimal 4 karakter.";
        $kode_error = true;
    } else {
        $kode_escaped = $db_ekantin->real_escape_string($kode_baru);
        $db_ekantin->query("UPDATE pengaturan SET nilai='$kode_escaped' WHERE kunci='kode_guru'");
        $notif_kode = "Kode guru berhasil diperbarui!";
        $kode_success = true;
    }
}

// Ambil kode guru saat ini
$res_kode = $db_ekantin->query("SELECT nilai FROM pengaturan WHERE kunci='kode_guru'");
$kode_guru_saat_ini = $res_kode->fetch_assoc()['nilai'] ?? 'GURU2025';

$error = false;
$correct = false;
$notif = "";

if(isset($_POST['submit'])){
    $id           = $_SESSION['id_users'];
    $passwordLama = $_POST['password_lama'];
    $passwordBaru = $_POST['password_baru'];
    $konfirmasi   = $_POST['konfirmasi'];

    $cek  = $db_ekantin->query("SELECT * FROM users WHERE id_users='$id'");
    $data = $cek->fetch_assoc();

    if (!password_verify($passwordLama, $data['password'])) {
        $notif = "Password lama salah!";
        $error = true;
    } elseif ($passwordBaru != $konfirmasi) {
        $notif = "Konfirmasi password tidak cocok!";
        $error = true;
    } else {
        $passwordHashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
        
        $update = $db_ekantin->query("UPDATE users SET password='$passwordHashBaru' WHERE id_users='$id'");
        
        if($update){
            $notif = "Password berhasil diubah!";
            $correct = true;
        } else {
            $notif = "Gagal mengupdate database.";
            $error = true;
        }
    }
}

$id     = $_SESSION['id_users'];
$result = $db_ekantin->query("SELECT * FROM users WHERE id_users='$id'");
$data   = $result->fetch_assoc();

$username = $data['username'];
$email    = $data['email'];
$telepon  = $data['no_telepon'];
$role     = $data['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">

    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?> 

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8">
            <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
                <div>
                    <h2 class="font-headline font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Informasi Pribadi</h2>
                </div>
            </header>

            <div class="grid grid-col-1 gap-4 md:gap-6">
                <div class="bg-white rounded-[20px] p-8 shadow-sm border border-gray-100">
                    <div class="grid grid-cols-[120px_20px_1fr] gap-y-6 items-center">
        
                    <p class="text-sm font-extrabold text-primary">Username</p>
                    <p class="text-sm font-extrabold text-primary">:</p>
                    <div class="bg-primary rounded-[12px] p-2 flex justify-center">
                        <p class="text-xs font-medium text-white"><?php echo $username; ?></p>
                    </div>

                    <p class="text-sm font-extrabold text-primary">Role</p>
                    <p class="text-sm font-extrabold text-primary">:</p>
                    <div class="bg-primary rounded-[12px] p-2 flex justify-center">
                        <p class="text-xs font-medium text-white"><?php echo $role; ?></p>
                    </div>

                    <p class="text-sm font-extrabold text-primary">Email</p>
                    <p class="text-sm font-extrabold text-primary">:</p>
                    <div class="bg-primary rounded-[12px] p-2 flex justify-center">
                        <p class="text-xs font-medium text-white"><?php echo $email; ?></p>
                    </div>

                    <p class="text-sm font-extrabold text-primary">No Telepon</p>
                    <p class="text-sm font-extrabold text-primary">:</p>
                    <div class="bg-primary rounded-[12px] p-2 flex justify-center">
                    <p class="text-xs font-medium text-white"><?php echo $telepon; ?></p>
                    </div>

                </div>
            </div>

            <div class="bg-white rounded-[20px] p-8 shadow-sm border border-gray-100">
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4">
                <div>
                    <h2 class="font-bold text-xl tracking-tight text-primary">Ganti Password</h2>
                </div>
                </header>

                <?php if($error): ?>
                    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                    <?php echo $notif; ?>
                    </div>
                <?php endif; ?>

                <?php if($correct): ?>
                    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-100 rounded-[15px] text-sm text-green-500 font-medium">
                    <?php echo $notif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Password Lama</label>
                    <input type="password" name="password_lama" placeholder="Masukkan password lama" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                    </div>

                    <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Password Baru</label>
                    <input type="password" name="password_baru" placeholder="Masukkan password baru" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                    </div>

                    <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Konfirmasi</label>
                    <input type="password" name="konfirmasi" placeholder="Konfirmasi password baru" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                    </div>

                    <button type="submit" name="submit" class="w-full bg-submit text-white font-bold py-2 rounded-[15px] mt-2 shadow-lg shadow-submit/20 hover:opacity-90 active:scale-[0.98] transition-all">
                        Ganti Password
                    </button>
                </form>
            </div>

            <!-- Kode Guru -->
            <div class="bg-white rounded-[20px] p-8 shadow-sm border border-gray-100">
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4">
                    <div>
                        <h2 class="font-bold text-xl tracking-tight text-primary">🔑 Kode Pendaftaran Guru</h2>
                        <p class="text-xs text-text-3 mt-1">Kode ini wajib dimasukkan oleh guru saat mendaftar. Bagikan secara offline kepada guru.</p>
                    </div>
                </header>

                <?php if($kode_error): ?>
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium"><?= $notif_kode ?></div>
                <?php endif; ?>
                <?php if($kode_success): ?>
                    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-100 rounded-[15px] text-sm text-green-600 font-medium"><?= $notif_kode ?></div>
                <?php endif; ?>

                <div class="mb-5 flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-[15px] px-4 py-3">
                    <span class="text-2xl">🔐</span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Kode Aktif Saat Ini</p>
                        <p class="text-xl font-extrabold tracking-[0.2em] text-amber-700 font-mono" id="kode-display"><?= htmlspecialchars($kode_guru_saat_ini) ?></p>
                    </div>
                    <button type="button" onclick="toggleKode()" class="ml-auto text-xs text-amber-600 hover:text-amber-800 font-bold border border-amber-300 px-3 py-1.5 rounded-lg transition-all" id="toggle-btn">Sembunyikan</button>
                </div>

                <form method="POST" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Kode Baru</label>
                        <input type="text" name="kode_guru_baru" placeholder="Minimal 4 karakter, contoh: GURU2025" 
                               class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20 font-mono tracking-wider uppercase"
                               maxlength="30" oninput="this.value = this.value.toUpperCase()" required>
                        <p class="text-[10px] text-text-3 ml-1">Gunakan kombinasi huruf dan angka agar lebih aman.</p>
                    </div>
                    <button type="submit" name="ubah_kode_guru" class="w-full bg-submit text-white font-bold py-2.5 rounded-[15px] shadow-lg shadow-submit/20 hover:opacity-90 active:scale-[0.98] transition-all">
                        Perbarui Kode Guru
                    </button>
                </form>
            </div>

            <form method="POST" class="flex justify-center items-center gap-5">
                    <button type="submit" name="logout" class="bg-red-500 hover:bg-red-900 text-white px-6 py-3 rounded-[15px] font-bold text-sm shadow-md transition-all active:scale-95 flex items-center gap-2">
                    Log Out
                </button>
                </form>
            </div>

        </main>
    </div>
    
    <script>
        // Set original code
        const kodeDisplay = document.getElementById('kode-display');
        if (kodeDisplay) {
            kodeDisplay.dataset.kode = kodeDisplay.textContent;
        }

        function toggleKode() {
            const display = document.getElementById('kode-display');
            const btn = document.getElementById('toggle-btn');
            
            if (btn.textContent === 'Sembunyikan') {
                display.textContent = '••••••••';
                btn.textContent = 'Tampilkan';
            } else {
                display.textContent = display.dataset.kode;
                btn.textContent = 'Sembunyikan';
            }
        }
    </script>
</body>
</html>