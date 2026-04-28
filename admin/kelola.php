<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php 
include '../config/koneksi.php';

// TAMBAHAN: Proses Edit Informasi User
if(isset($_POST['edit_user'])){
    $id_edit = $_POST['edit_id'];
    $nama_edit = $db_ekantin->real_escape_string($_POST['edit_nama']);
    $email_edit = $db_ekantin->real_escape_string($_POST['edit_email']);
    $telepon_edit = $db_ekantin->real_escape_string($_POST['edit_telepon']);
    
    $db_ekantin->query("UPDATE users SET username='$nama_edit', email='$email_edit', no_telepon='$telepon_edit' WHERE id_users='$id_edit'");
    header("Location: kelola.php");
    exit;
}

// TAMBAHAN: handle aktifkan/nonaktifkan
if(isset($_POST['aktifkan']) && isset($_POST['id_target'])){
    $id_target = $_POST['id_target'];
    $db_ekantin->query("UPDATE users SET status='aktif' WHERE id_users='$id_target'");
    header("Location: kelola.php");
    exit;
}
if(isset($_POST['nonaktifkan']) && isset($_POST['id_target'])){
    $id_target = $_POST['id_target'];
    $db_ekantin->query("UPDATE users SET status='nonaktif' WHERE id_users='$id_target'");
    header("Location: kelola.php");
    exit;
}

// TAMBAHAN: search by ID → simpan data user yang dicari
$user_dicari = null;
$pesan_tidak_ditemukan = false;
if(isset($_POST['submit_cari']) && !empty($_POST['cari_user'])){
    $cari_id = $db_ekantin->real_escape_string($_POST['cari_user']);
    $qCari = "SELECT * FROM users WHERE id_users='$cari_id' AND role != 'admin'";
    $resCari = $db_ekantin->query($qCari);
    if($resCari && $resCari->num_rows > 0){
        $user_dicari = $resCari->fetch_assoc();
    } else {
        $pesan_tidak_ditemukan = true;
    }
}

$sql = "SELECT * FROM users WHERE status = 'aktif'";
$hasil = $db_ekantin->query($sql);
$total_aktif = $hasil->num_rows;

$perintah = "SELECT * FROM users WHERE status='nonaktif'";
$hasilCari = $db_ekantin->query($perintah);
$total_toko = $hasilCari->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
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

    <style>
        .popup-enter { animation: popupIn 0.25s cubic-bezier(.4,0,.2,1) both; }
        @keyframes popupIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
        <div>
            <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Kelola User</h2>
            <p class="text-text-3 font-body mt-2">Silahkan masukkan ID user untuk mengelola user</p>
        </div>

        <form method="POST" class="flex items-center gap-3 w-full sm:w-auto">
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-[15px] px-4 h-[48px] w-full sm:w-[280px] shadow-sm">
                <input type="number" name="cari_user" placeholder="Masukkan ID user..." 
                    class="border-none outline-none bg-transparent text-[14px] text-zinc-950 w-full focus:ring-0 placeholder-gray-400">
            </div>
            <button type="submit" name="submit_cari"
                class="h-[48px] px-6 bg-submit rounded-[15px] text-white text-sm font-bold tracking-wide hover:opacity-90 active:scale-[0.98] transition-all flex-shrink-0">
                Cari
            </button>
        </form>
        </header>

        <?php if($pesan_tidak_ditemukan): ?>
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
            User dengan ID tersebut tidak ditemukan.
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4 md:gap-6">
            <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Anggota Aktif</p>
                <p class="text-4xl font-extrabold text-primary"><?php echo $total_aktif; ?></p>
                <p class="text-xs text-text-2">status aktif</p>
            </div>

            <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Anggota Tidak Aktif</p>
                <p class="text-4xl font-extrabold text-red-500"><?php echo $total_toko; ?></p>
                <p class="text-xs text-text-2">status tidak aktif</p>
            </div>
        </div>

        <div class="w-full bg-white rounded-[20px] mt-[20px] shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="grid grid-cols-[50px_1fr_100px_150px_130px_100px_80px] bg-primary px-6 py-4 gap-4">
            <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
            <p class="text-xs font-bold uppercase tracking-widest text-white">Nama</p>
            <p class="text-xs font-bold uppercase tracking-widest text-white">Role</p>
            <p class="text-xs font-bold uppercase tracking-widest text-white">Email</p>
            <p class="text-xs font-bold uppercase tracking-widest text-white">Telepon</p>
            <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
            <p class="text-xs font-bold uppercase tracking-widest text-white text-center">Kelola</p>
        </div>

    <div class="overflow-y-auto max-h-[400px]">

        <?php 
        $tulisanStatus = "";
        $query = "SELECT * FROM users WHERE role != 'admin'";
        $result = $db_ekantin->query($query);

        if($result && $result->num_rows > 0){
            while($data = $result->fetch_assoc()){
                $id = $data['id_users'];
                $username = htmlspecialchars($data['username'], ENT_QUOTES);
                $role = $data['role'];
                $email = htmlspecialchars($data['email'], ENT_QUOTES);
                $telepon = htmlspecialchars($data['no_telepon'], ENT_QUOTES);
                $status = $data['status']; 

                if($status == 'aktif'){
                    $tulisanStatus = "<span class='text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full w-fit'>Aktif</span>";
                } else{
                    $tulisanStatus = "<span class='text-xs font-semibold text-red-500 bg-red-100 px-3 py-1 rounded-full w-fit'>Nonaktif</span>";
                }

                echo "
                <div class='grid grid-cols-[50px_1fr_100px_150px_130px_100px_80px] px-6 py-4 gap-4 border-b border-gray-100 items-center hover:bg-gray-50 transition-colors'>
                    <p class='text-sm text-text-2'>$id</p>
                    <p class='text-sm font-medium text-text-1 truncate'>$username</p>
                    <p class='text-sm text-text-2'>$role</p>
                    <p class='text-sm text-text-2 truncate'>$email</p>
                    <p class='text-sm text-text-2 truncate'>$telepon</p>
                    $tulisanStatus
                    
                    <button onclick=\"bukaModalEdit('$id', '$username', '$email', '$telepon')\" 
                        class='text-xs font-bold bg-amber-100 text-amber-700 hover:bg-amber-200 px-3 py-2 rounded-[10px] transition-all text-center'>
                        Edit
                    </button>
                </div>";
            }
        }
        ?>

    </div>

</div>
    </main>
    </div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="popup-enter bg-white rounded-[24px] w-full max-w-md shadow-2xl overflow-hidden p-6 md:p-8">
        
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-primary font-extrabold text-xl">Edit Informasi User</h2>
            <button onclick="tutupModalEdit()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" class="flex flex-col gap-4">
            <input type="hidden" name="edit_id" id="edit_id">

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Nama Username</label>
                <input type="text" name="edit_nama" id="edit_nama" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Email</label>
                <input type="email" name="edit_email" id="edit_email" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                <input type="text" name="edit_telepon" id="edit_telepon" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20" required>
            </div>

            <button type="submit" name="edit_user" class="mt-4 w-full h-[48px] bg-submit rounded-[15px] text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<?php if($user_dicari): 
    $pu_id      = $user_dicari['id_users'];
    $pu_nama    = htmlspecialchars($user_dicari['username']);
    $pu_role    = ucfirst($user_dicari['role']);
    $pu_email   = htmlspecialchars($user_dicari['email']);
    $pu_telepon = htmlspecialchars($user_dicari['no_telepon']);
    $pu_status  = $user_dicari['status'];
    $pu_badge   = $pu_status == 'aktif'
        ? "<span class='text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full'>Aktif</span>"
        : "<span class='text-xs font-semibold text-red-500 bg-red-100 px-3 py-1 rounded-full'>Nonaktif</span>";
?>
<div id="overlay-popup" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="popup-enter bg-white rounded-[24px] w-full max-w-md shadow-2xl overflow-hidden">
        <div class="bg-primary px-8 py-6 flex items-center justify-between">
            <div>
                <p class="text-white/70 text-xs font-semibold uppercase tracking-widest mb-1">Kelola Pengguna</p>
                <h2 class="text-white font-bold text-xl"><?= $pu_nama ?></h2>
            </div>
            <a href="kelola.php" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        </div>

        <div class="px-8 py-6 flex flex-col gap-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">ID</p>
                    <p class="text-sm font-medium text-text-1"><?= $pu_id ?></p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Role</p>
                    <p class="text-sm font-medium text-text-1"><?= $pu_role ?></p>
                </div>
                <div class="flex flex-col gap-1 col-span-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Email</p>
                    <p class="text-sm font-medium text-text-1"><?= $pu_email ?></p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Telepon</p>
                    <p class="text-sm font-medium text-text-1"><?= $pu_telepon ?></p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Status</p>
                    <?= $pu_badge ?>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4 flex flex-col gap-2">
                <p class="text-xs text-text-3 font-semibold uppercase tracking-widest mb-1">Tindakan</p>
                <form method="POST">
                    <input type="hidden" name="id_target" value="<?= $pu_id ?>">
                    <?php if($pu_status != 'aktif'): ?>
                    <button type="submit" name="aktifkan"
                        class="w-full h-[46px] bg-green-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all mb-2">
                        ✓ Aktifkan Akun
                    </button>
                    <?php endif; ?>
                    <?php if($pu_status == 'aktif'): ?>
                    <button type="submit" name="nonaktifkan"
                        class="w-full h-[46px] bg-red-500 rounded-[12px] text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all">
                        ✕ Nonaktifkan Akun
                    </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    function bukaModalEdit(id, nama, email, telepon) {
        // Mengisi nilai input di modal dengan data baris yang diklik
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_telepon').value = telepon;
        
        // Tampilkan modal
        document.getElementById('modal-edit').classList.remove('hidden');
    }

    function tutupModalEdit() {
        // Sembunyikan modal
        document.getElementById('modal-edit').classList.add('hidden');
    }
</script>

</body>
</html>