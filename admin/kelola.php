<?php 
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if (!isset($_SESSION['id_users']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// 0. PROSES NAIKKAN KELAS
if (isset($_POST['naikkan_kelas'])) {
    // Kelas 12 → soft delete: set tipe='lulus', status='nonaktif', catat tanggal_lulus
    $db_ekantin->query("UPDATE users SET tipe='lulus', status='nonaktif', tanggal_lulus=NOW() WHERE tipe='kelas12' AND role='pembeli'");
    // Naikkan kelas11 → kelas12
    $db_ekantin->query("UPDATE users SET tipe='kelas12' WHERE tipe='kelas11' AND role='pembeli'");
    // Naikkan kelas10 → kelas11
    $db_ekantin->query("UPDATE users SET tipe='kelas11' WHERE tipe='kelas10' AND role='pembeli'");

    header("Location: kelola.php?naik=1");
    exit;
}

// AUTO-CLEANUP: Hapus user 'lulus' yang sudah >1 tahun beserta seluruh datanya
$resLulus = $db_ekantin->query("SELECT id_users FROM users WHERE tipe='lulus' AND tanggal_lulus IS NOT NULL AND tanggal_lulus < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
if ($resLulus && $resLulus->num_rows > 0) {
    while ($rowL = $resLulus->fetch_assoc()) {
        $idL = $rowL['id_users'];
        $db_ekantin->query("DELETE FROM keranjang WHERE id_users = '$idL'");
        $resPL = $db_ekantin->query("SELECT id_pesanan FROM pesanan WHERE id_users = '$idL'");
        if ($resPL && $resPL->num_rows > 0) {
            while ($rowPL = $resPL->fetch_assoc()) {
                $idPesananL = $rowPL['id_pesanan'];
                $db_ekantin->query("DELETE FROM detail_pesanan WHERE id_pesanan = '$idPesananL'");
                $db_ekantin->query("DELETE FROM pembayaran WHERE id_pesanan = '$idPesananL'");
            }
        }
        $db_ekantin->query("DELETE FROM pesanan WHERE id_users = '$idL'");
        $db_ekantin->query("DELETE FROM users WHERE id_users = '$idL'");
    }
}



// 1. PROSES EDIT INFORMASI USER
if(isset($_POST['edit_user'])){
    $id_edit      = $_POST['edit_id'];
    $nama_edit    = $db_ekantin->real_escape_string($_POST['edit_nama']);
    $email_edit   = $db_ekantin->real_escape_string($_POST['edit_email']);
    $telepon_edit = $db_ekantin->real_escape_string($_POST['edit_telepon']);

    // Validasi
    if(strlen($nama_edit) < 3 || strlen($nama_edit) > 20){
        $error_edit = "Username harus antara 3-20 karakter.";
    } else if(!preg_match('/^[a-zA-Z0-9_.]+$/', $nama_edit)){
        $error_edit = "Username hanya boleh huruf, angka, underscore, dan titik.";
    } else if(strpos($nama_edit, ' ') !== false){
        $error_edit = "Username tidak boleh mengandung spasi.";
    }

    // Cek duplikasi
    $cekUsername = $db_ekantin->query("SELECT id_users FROM users WHERE username='$nama_edit' AND id_users != '$id_edit'");
    if($cekUsername->num_rows > 0) $error_edit = "Username sudah digunakan.";
    
    $cekEmail = $db_ekantin->query("SELECT id_users FROM users WHERE email='$email_edit' AND id_users != '$id_edit'");
    if($cekEmail->num_rows > 0) $error_edit = "Email sudah digunakan.";

    if(!isset($error_edit)){
        $updatePassword = "";
        if(!empty($_POST['edit_password'])){
            $hash_baru = password_hash($_POST['edit_password'], PASSWORD_DEFAULT);
            $updatePassword = ", password='$hash_baru'";
        }
        $db_ekantin->query("UPDATE users SET username='$nama_edit', email='$email_edit', no_telepon='$telepon_edit'$updatePassword WHERE id_users='$id_edit'");
        header("Location: kelola.php?success=1");
        exit;
    }
}

// 2. PROSES UPDATE STATUS (AKTIF/NONAKTIF)
if(isset($_POST['update_status'])){
    $id_target = $_POST['id_target'];
    $status_baru = $_POST['status_aksi'];
    $db_ekantin->query("UPDATE users SET status='$status_baru' WHERE id_users='$id_target'");
    header("Location: kelola.php?success=1");
    exit;
}

// 3. SEARCH BY ID
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

// Data Stats
$total_aktif = $db_ekantin->query("SELECT id_users FROM users WHERE status = 'aktif' AND role != 'admin'")->num_rows;
$total_nonaktif = $db_ekantin->query("SELECT id_users FROM users WHERE status = 'nonaktif' AND role != 'admin'")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <p class="text-text-3 font-body mt-2">Cari ID user untuk mengedit informasi atau status</p>
                </div>

                <form method="POST" class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-[15px] px-4 h-[48px] w-full sm:w-[280px] shadow-sm focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <input type="number" name="cari_user" placeholder="Masukkan ID user..." required
                            class="border-none outline-none bg-transparent text-[14px] text-zinc-950 w-full focus:ring-0 placeholder-gray-400">
                    </div>
                    <button type="submit" name="submit_cari"
                        class="h-[48px] px-6 bg-primary rounded-[15px] text-white text-sm font-bold tracking-wide hover:opacity-90 active:scale-[0.98] transition-all flex-shrink-0">
                        Cari User
                    </button>
                </form>
            </header>

            <?php if($pesan_tidak_ditemukan): ?>
                <div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                    User dengan ID tersebut tidak ditemukan atau merupakan Admin.
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['naik']) && $_GET['naik'] == '1'): ?>
                <div id="notif-naik" class="mb-6 px-5 py-4 bg-green-50 border border-green-200 rounded-[15px] flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-green-700">Kelas berhasil dinaikkan! 🎓</p>
                        <p class="text-xs text-green-600">Kelas 10→11, Kelas 11→12. Siswa kelas 12 ditandai <strong>Lulus</strong> — data akan terhapus otomatis setelah 1 tahun.</p>
                    </div>
                    <button onclick="document.getElementById('notif-naik').remove()" class="ml-auto text-green-400 hover:text-green-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-4 md:gap-6">
                <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2 relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Anggota Aktif</p>
                        <p class="text-4xl font-extrabold text-primary"><?= $total_aktif ?></p>
                    </div>
                    <img src="../assets/img/gambarBesar.png" class="absolute bottom-0 right-0 translate-y-9 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform">
                </div>
                <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2 relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Anggota Nonaktif</p>
                        <p class="text-4xl font-extrabold text-red-500"><?= $total_nonaktif ?></p>
                    </div>
                    <img src="../assets/img/inactive.png" class="absolute bottom-0 right-0 translate-y-9 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform">
                </div>
            </div>

            <div class="w-full bg-white rounded-[20px] mt-[20px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="grid grid-cols-[50px_1fr_100px_150px_130px_100px_80px] bg-primary px-6 py-4 gap-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Nama</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Role</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Tipe</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Telepon</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white text-center">Aksi</p>
                </div>

                <div class="overflow-y-auto max-h-[400px]">
                    <?php 
                    // PERBAIKAN URUTAN: Menggunakan ASC agar ID terkecil (ID Pertama) muncul paling atas
                    $result = $db_ekantin->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id_users ASC");
                    while($data = $result->fetch_assoc()):
                        if ($data['status'] == 'aktif') {
                            $status_badge = "<span class='text-[10px] font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full uppercase'>Aktif</span>";
                        } else {
                            if (isset($data['tipe']) && $data['tipe'] == 'lulus') {
                                $tgl_lulus = $data['tanggal_lulus'] ?? null;
                                $info_hapus = "";
                                if ($tgl_lulus) {
                                    $tgl_hapus = date('d M Y', strtotime($tgl_lulus . ' + 1 year'));
                                    $info_hapus = "<div class='text-[9px] text-red-500 font-semibold mt-1 leading-tight'>Dihapus:<br>$tgl_hapus</div>";
                                }
                                $status_badge = "<div class='flex flex-col items-start'><span class='text-[10px] font-bold px-2 py-1 rounded-full uppercase' style='background-color: #f3f4f6; color: #6b7280;'>Lulus</span>$info_hapus</div>";
                            } else {
                                $status_badge = "<span class='text-[10px] font-bold text-red-500 bg-red-100 px-2 py-1 rounded-full uppercase'>Nonaktif</span>";
                            }
                        }
                        $tipe_val = $data['tipe'] ?? '-';
                        $tipe_colors = [
                            'kelas10' => 'bg-blue-100 text-blue-700',
                            'kelas11' => 'bg-purple-100 text-purple-700',
                            'kelas12' => 'bg-green-100 text-green-700',
                            'guru'    => 'bg-amber-100 text-amber-700',
                            'lulus'   => 'bg-gray-100 text-gray-500',
                        ];
                        $tipe_labels = [
                            'kelas10' => 'Kelas 10',
                            'kelas11' => 'Kelas 11',
                            'kelas12' => 'Kelas 12',
                            'guru'    => 'Guru',
                            'lulus'   => 'Lulus',
                        ];
                        $tipe_css   = $tipe_colors[$tipe_val] ?? 'bg-gray-100 text-gray-500';
                        $tipe_label = $tipe_labels[$tipe_val] ?? htmlspecialchars($tipe_val);
                    ?>
                        <div class="grid grid-cols-[50px_1fr_100px_150px_130px_100px_80px] px-6 py-4 gap-4 border-b border-gray-100 items-center hover:bg-gray-50 transition-colors">
                            <p class="text-sm text-text-2 truncate"><?= $data['id_users'] ?></p>
                            <p class="text-sm font-medium text-text-1 truncate"><?= htmlspecialchars($data['username']) ?></p>
                            <p class="text-sm text-text-2 italic truncate"><?= $data['role'] ?></p>
                            <div class="truncate"><span class="text-[10px] font-bold px-2 py-1 rounded-full uppercase <?= $tipe_css ?>"><?= $tipe_label ?></span></div>
                            <p class="text-sm text-text-2 truncate"><?= htmlspecialchars($data['no_telepon']) ?></p>
                            <div class="truncate"><?= $status_badge ?></div>
                            <button onclick="bukaModalEdit('<?= $data['id_users'] ?>', '<?= addslashes($data['username']) ?>', '<?= addslashes($data['email']) ?>', '<?= addslashes($data['no_telepon']) ?>', '<?= $data['status'] ?>')" 
                                class="text-[11px] font-bold bg-amber-100 text-amber-700 hover:bg-amber-200 px-3 py-2 rounded-[10px] transition-all text-center block w-full truncate">
                                Kelola
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-edit" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="popup-enter bg-white rounded-[24px] w-full max-w-md shadow-2xl overflow-hidden shadow-primary/10 flex flex-col max-h-[90vh]">
            <div class="bg-primary p-6 flex justify-between items-center text-white flex-shrink-0">
                <div>
                    <h2 class="font-extrabold text-xl tracking-tight">Kelola Pengguna</h2>
                    <p class="text-xs text-white/70">ID User: <span id="display_id">0</span></p>
                </div>
                <button onclick="tutupModalEdit()" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 md:p-8 overflow-y-auto">
                <form method="POST" class="flex flex-col gap-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-text-3">Username</label>
                            <input type="text" name="edit_nama" id="edit_nama" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:ring-2 focus:ring-primary/20 outline-none" required>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-text-3">Email</label>
                            <input type="email" name="edit_email" id="edit_email" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:ring-2 focus:ring-primary/20 outline-none" required>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                            <input type="text" name="edit_telepon" id="edit_telepon" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:ring-2 focus:ring-primary/20 outline-none" required>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-text-3">Password Baru <span class="normal-case font-normal text-gray-400">(Opsional)</span></label>
                            <input type="password" name="edit_password" placeholder="••••••••" class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                    </div>

                    <button type="submit" name="edit_user" class="mt-2 w-full h-[48px] bg-primary rounded-[12px] text-white text-sm font-bold hover:opacity-95 transition-all active:scale-[0.98]">
                        Simpan Perubahan
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-text-3 mb-3">Status Kontrol</p>
                    <form method="POST">
                        <input type="hidden" name="id_target" id="status_id">
                        <input type="hidden" name="status_aksi" id="status_aksi_val">
                        
                        <div id="btn-aktifkan" class="hidden">
                            <button type="submit" name="update_status" onclick="document.getElementById('status_aksi_val').value='aktif'"
                                class="w-full h-[46px] bg-green-600 rounded-[12px] text-white text-sm font-bold hover:bg-green-700 transition-all flex items-center justify-center gap-2">
                                <span>✓</span> Aktifkan Kembali Akun
                            </button>
                        </div>
                        
                        <div id="btn-nonaktifkan" class="hidden">
                            <button type="submit" name="update_status" onclick="document.getElementById('status_aksi_val').value='nonaktif'"
                                class="w-full h-[46px] bg-red-50 text-red-600 rounded-[12px] text-sm font-bold hover:bg-red-100 transition-all flex items-center justify-center gap-2 border border-red-200">
                                <span>✕</span> Nonaktifkan Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bukaModalEdit(id, nama, email, telepon, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('display_id').innerText = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_telepon').value = telepon;
            document.getElementById('status_id').value = id;

            // Kontrol tombol status
            if(status === 'aktif') {
                document.getElementById('btn-nonaktifkan').classList.remove('hidden');
                document.getElementById('btn-aktifkan').classList.add('hidden');
            } else {
                document.getElementById('btn-aktifkan').classList.remove('hidden');
                document.getElementById('btn-nonaktifkan').classList.add('hidden');
            }

            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function tutupModalEdit() {
            document.getElementById('modal-edit').classList.add('hidden');
        }
    </script>

    <?php if($user_dicari): ?>
    <script>
        window.onload = function() {
            bukaModalEdit(
                '<?= $user_dicari['id_users'] ?>', 
                '<?= addslashes($user_dicari['username']) ?>', 
                '<?= addslashes($user_dicari['email']) ?>', 
                '<?= addslashes($user_dicari['no_telepon']) ?>', 
                '<?= $user_dicari['status'] ?>'
            );
        }
    </script>
    <?php endif; ?>

    <?php if(isset($error_edit)): ?>
    <script>
        alert("<?= $error_edit ?>");
        window.onload = function() {
            bukaModalEdit('<?= $_POST['edit_id'] ?>', '<?= $_POST['edit_nama'] ?>', '<?= $_POST['edit_email'] ?>', '<?= $_POST['edit_telepon'] ?>', 'aktif');
        }
    </script>
    <?php endif; ?>
</body>
</html>