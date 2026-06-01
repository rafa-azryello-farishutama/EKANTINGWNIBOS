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

$id_users = $_SESSION['id_users'];
$id_toko  = $_SESSION['id_toko'];

$qUser = $db_ekantin->query("SELECT * FROM users WHERE id_users='$id_users'");
$user  = $qUser->fetch_assoc();

$qToko = $db_ekantin->query("SELECT t.*, rk.nomor_ruang FROM toko t LEFT JOIN ruang_kantin rk ON t.id_toko = rk.id_toko WHERE t.id_toko='$id_toko'");
$toko  = $qToko->fetch_assoc();

if(!$user || !$toko) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$error_profil   = null;
$success_profil = null;

if(isset($_POST['simpan_profil'])){
    $username_baru  = $db_ekantin->real_escape_string($_POST['edit_username']);
    $email_baru     = $db_ekantin->real_escape_string($_POST['edit_email']);
    $telepon_baru   = $db_ekantin->real_escape_string($_POST['edit_telepon']);
    $nama_toko_baru = $db_ekantin->real_escape_string($_POST['edit_nama_toko']);
    $lokasi_baru    = $db_ekantin->real_escape_string($_POST['edit_lokasi']);
    $deskripsi_baru = $db_ekantin->real_escape_string($_POST['edit_deskripsi']);
    $jam_buka_baru  = $db_ekantin->real_escape_string($_POST['edit_jam_buka']);
    $jam_tutup_baru = $db_ekantin->real_escape_string($_POST['edit_jam_tutup']);
    $jam_tutup_baru = $db_ekantin->real_escape_string($_POST['edit_jam_tutup']);

    $detik_buka = strtotime($jam_buka_baru);
    $detik_tutup = strtotime($jam_tutup_baru);

    if(strlen($username_baru) < 3 || strlen($username_baru) > 20){
        $error_profil = "Username harus antara 3-20 karakter.";
    } elseif(!preg_match('/^[a-zA-Z0-9_.]+$/', $username_baru)){
        $error_profil = "Username hanya boleh huruf, angka, underscore, dan titik.";
    } elseif($detik_tutup < $detik_buka){
        $error_profil = "Jam tutup tidak boleh lebih awal dari jam buka.";
    } else {
        $cekUsername = $db_ekantin->query("SELECT id_users FROM users WHERE username='$username_baru' AND id_users != '$id_users'");
        if($cekUsername->num_rows > 0){
            $error_profil = "Username sudah digunakan orang lain.";
        }
    }

    if(!$error_profil){
        $foto_profil_baru = $user['foto_profil'] ?? null; // default old

        if (isset($_POST['hapus_foto']) && $_POST['hapus_foto'] == '1') {
            if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil'])) {
                unlink('../assets/img/profil/' . $user['foto_profil']);
            }
            $foto_profil_baru = null;
        } else if (isset($_FILES['edit_foto_profil']) && $_FILES['edit_foto_profil']['error'] == 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['edit_foto_profil']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_size = $_FILES['edit_foto_profil']['size'];
            $file_tmp = $_FILES['edit_foto_profil']['tmp_name'];

            if (in_array($file_ext, $allowed_ext)) {
                if ($file_size <= 2000000) { // 2MB max
                    $new_file_name = 'profil_' . $id_users . '_' . time() . '.' . $file_ext;
                    $upload_path = '../assets/img/profil/' . $new_file_name;
                    
                    if (!is_dir('../assets/img/profil/')) {
                        mkdir('../assets/img/profil/', 0777, true);
                    }
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $foto_profil_baru = $new_file_name;
                        
                        if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil'])) {
                            unlink('../assets/img/profil/' . $user['foto_profil']);
                        }
                    } else {
                        $error_profil = "Gagal mengupload foto profil.";
                    }
                } else {
                    $error_profil = "Ukuran foto profil maksimal 2MB.";
                }
            } else {
                $error_profil = "Ekstensi file foto profil tidak diizinkan.";
            }
        }

        if (!$error_profil) {
            $db_ekantin->query("UPDATE users SET username='$username_baru', email='$email_baru', no_telepon='$telepon_baru', foto_profil='$foto_profil_baru' WHERE id_users='$id_users'");
            $db_ekantin->query("UPDATE toko SET nama_toko='$nama_toko_baru', lokasi='$lokasi_baru', deskripsi='$deskripsi_baru', jam_buka='$jam_buka_baru', jam_tutup='$jam_tutup_baru' WHERE id_toko='$id_toko'");
            $_SESSION['nama_toko'] = $nama_toko_baru;
            $success_profil = "Profil berhasil diperbarui.";
            // Update local variables
            $user['foto_profil'] = $foto_profil_baru;
            $user['username'] = $username_baru;
            $user['email'] = $email_baru;
            $user['no_telepon'] = $telepon_baru;
        }
    }
}

/* ──────────────────────────────────────────
   GANTI PASSWORD
────────────────────────────────────────── */
$error_password = false;
$success_password = false;
$notif_password = "";

if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi    = $_POST['konfirmasi'] ?? '';

    if (!password_verify($password_lama, $user['password'])) {
        $notif_password = "Password lama salah!";
        $error_password = true;
    } elseif ($password_baru !== $konfirmasi) {
        $notif_password = "Konfirmasi password tidak cocok!";
        $error_password = true;
    } else {
        $passwordHashBaru = password_hash($password_baru, PASSWORD_DEFAULT);
        
        $stmt = $db_ekantin->prepare("UPDATE users SET password = ? WHERE id_users = ?");
        $stmt->bind_param("si", $passwordHashBaru, $id_users);
        if ($stmt->execute()) {
            $notif_password = "Password berhasil diubah!";
            $success_password = true;
            $user['password'] = $passwordHashBaru;
        } else {
            $notif_password = "Gagal mengupdate database.";
            $error_password = true;
        }
        $stmt->close();
    }
}

/* ──────────────────────────────────────────
   SIMPAN PEMBAYARAN
────────────────────────────────────────── */
$error_pembayaran   = null;
$success_pembayaran = null;

if(isset($_POST['simpan_pembayaran'])){
    $info_bank_baru    = $db_ekantin->real_escape_string($_POST['edit_info_bank'] ?? '');
    $info_ewallet_baru = $db_ekantin->real_escape_string($_POST['edit_info_ewallet'] ?? '');
    $qris_image_baru   = $toko['qris_image'] ?? null;

    if(isset($_FILES['edit_qris_image']) && $_FILES['edit_qris_image']['error'] == 0){
        $allowed_ext = ['jpg','jpeg','png'];
        $file_name   = $_FILES['edit_qris_image']['name'];
        $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size   = $_FILES['edit_qris_image']['size'];
        $file_tmp    = $_FILES['edit_qris_image']['tmp_name'];

        if(!in_array($file_ext, $allowed_ext)){
            $error_pembayaran = "Format QRIS hanya JPG, JPEG, PNG.";
        } elseif($file_size > 2000000){
            $error_pembayaran = "Ukuran gambar QRIS maksimal 2MB.";
        } else {
            $new_qris_name = 'qris_' . $id_toko . '_' . time() . '.' . $file_ext;
            $upload_path   = '../assets/img/qris/' . $new_qris_name;
            if(!is_dir('../assets/img/qris/')){
                mkdir('../assets/img/qris/', 0777, true);
            }
            if(move_uploaded_file($file_tmp, $upload_path)){
                if(!empty($toko['qris_image']) && file_exists('../assets/img/qris/' . $toko['qris_image'])){
                    unlink('../assets/img/qris/' . $toko['qris_image']);
                }
                $qris_image_baru = $new_qris_name;
            } else {
                $error_pembayaran = "Gagal mengupload gambar QRIS.";
            }
        }
    }

    if(!$error_pembayaran){
        $db_ekantin->query("UPDATE toko SET qris_image='$qris_image_baru', info_bank='$info_bank_baru', info_ewallet='$info_ewallet_baru' WHERE id_toko='$id_toko'");
        $success_pembayaran = "Informasi pembayaran berhasil diperbarui.";
        $toko['qris_image']   = $qris_image_baru;
        $toko['info_bank']    = $info_bank_baru;
        $toko['info_ewallet'] = $info_ewallet_baru;
    }
}

if(isset($_POST['logout'])){
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$qStatistik = $db_ekantin->query("SELECT COUNT(*) as total FROM pesanan WHERE id_toko='$id_toko' AND status_pesanan IN ('selesai','diambil','tidak_diambil')");
$statistik  = $qStatistik->fetch_assoc();
$total_pesanan = $statistik['total'] ?? 0;

$qProduk = $db_ekantin->query("SELECT COUNT(*) as total FROM produk_kantin WHERE id_toko='$id_toko'");
$produk  = $qProduk->fetch_assoc();
$total_produk = $produk['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>

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

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-4xl mx-auto flex flex-col gap-6">

            <header>
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Profil</h2>
                <p class="text-text-3 mt-1 text-sm">Kelola informasi toko dan profil pribadi Anda</p>
            </header>

            <?php if($success_profil): ?>
            <div class="px-4 py-3 bg-green-50 border border-green-100 rounded-[15px] text-sm text-green-700 font-medium">
                <?= $success_profil ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-1 flex flex-col gap-4">
                    <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden flex flex-col items-center text-center relative pb-6">
                        <div class="w-full h-20 bg-gradient-to-r from-primary to-[#006800]"></div>
                        <div class="w-20 h-20 rounded-full border-4 border-white bg-primary/10 flex items-center justify-center -mt-10 relative z-10 overflow-hidden">
                            <?php if(!empty($user['foto_profil'])): ?>
                            <img src="../assets/img/profil/<?= htmlspecialchars($user['foto_profil']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                            <span class="text-primary font-bold text-2xl"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-bold text-text-1 text-lg mt-3"><?= htmlspecialchars($toko['nama_toko']) ?></h3>
                        <p class="text-xs text-text-3"><?= htmlspecialchars($user['username']) ?></p>
                        <span class="mt-2 text-xs font-semibold text-green-700 bg-green-100 px-3 py-1 rounded-full">
                            <?= ucfirst($toko['status']) ?>
                        </span>
                        <button onclick="bukaEdit()"
                            class="mt-4 mx-6 w-[calc(100%-48px)] h-[44px] bg-primary text-white text-sm font-bold rounded-[12px] hover:opacity-90 transition-all">
                            Edit Profil
                        </button>
                    </div>

                    <form method="POST">
                        <button type="submit" name="logout"
                            class="w-full h-[44px] bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-[12px] transition-all active:scale-[0.98]">
                            Log Out
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 flex flex-col gap-4">

                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100">
                        <h4 class="font-bold text-text-1 text-base mb-5 pb-4 border-b border-gray-100">Informasi Akun</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Username</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($user['username']) ?></p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Email</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($user['email'] ?? '-') ?></p>
                            </div>
                            <div class="flex flex-col gap-1 md:col-span-2">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($user['no_telepon'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100">
                        <h4 class="font-bold text-text-1 text-base mb-5 pb-4 border-b border-gray-100">Informasi Toko</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Nama Toko</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($toko['nama_toko']) ?></p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Lokasi</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($toko['lokasi'] ?? '-') ?></p>
                            </div>
                            <div class="flex flex-col gap-1 md:col-span-2">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Ruang Kantin</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($toko['nomor_ruang'] ? 'Ruang ' . $toko['nomor_ruang'] : 'Belum diatur') ?></p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Jam Buka</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($toko['jam_buka'] ?? '-') ?></p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Jam Tutup</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3"><?= htmlspecialchars($toko['jam_tutup'] ?? '-') ?></p>
                            </div>
                            <div class="flex flex-col gap-1 md:col-span-2">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Deskripsi</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3 min-h-[60px]"><?= htmlspecialchars($toko['deskripsi'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Pembayaran Toko -->
                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 mt-4">
                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-text-1 text-base">Informasi Pembayaran Toko (Untuk Pembeli)</h4>
                        </div>

                        <?php if(isset($error_pembayaran) && $error_pembayaran): ?>
                            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                            <?php echo $error_pembayaran; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($success_pembayaran) && $success_pembayaran): ?>
                            <div class="mb-6 px-4 py-3 bg-green-50 border border-green-100 rounded-[15px] text-sm text-green-500 font-medium">
                            <?php echo $success_pembayaran; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Upload Gambar QRIS Saat Ini</label>
                                <?php if(!empty($toko['qris_image'])): ?>
                                    <div class="mt-2 mb-2 w-32 h-32 rounded-[12px] border border-gray-200 overflow-hidden relative">
                                        <img src="../assets/img/qris/<?= htmlspecialchars($toko['qris_image']) ?>" class="w-full h-full object-cover">
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="edit_qris_image" name="edit_qris_image" accept="image/png, image/jpeg, image/jpg"
                                    class="w-full bg-input border border-gray-200 rounded-[12px] p-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <span class="text-[11px] text-text-3 mt-1">Abaikan jika tidak ingin mengubah QRIS. Format: JPG, PNG.</span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Info Rekening Bank</label>
                                <input type="text" name="edit_info_bank"
                                    value="<?= htmlspecialchars($toko['info_bank'] ?? '') ?>"
                                    placeholder="Contoh: BCA 123456789 a/n Nama Toko"
                                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20">
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Info E-Wallet (Dana / OVO / GoPay)</label>
                                <input type="text" name="edit_info_ewallet"
                                    value="<?= htmlspecialchars($toko['info_ewallet'] ?? '') ?>"
                                    placeholder="Contoh: DANA 0812345678 a/n Nama Toko"
                                    class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20">
                            </div>

                            <button type="submit" name="simpan_pembayaran" class="w-full bg-submit text-white font-bold py-3 rounded-[15px] mt-2 shadow-lg shadow-submit/20 hover:opacity-90 active:scale-[0.98] transition-all">
                                Simpan Pembayaran
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100">
                        <h4 class="font-bold text-text-1 text-base mb-5 pb-4 border-b border-gray-100">Statistik Toko</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-green-50 border border-green-100 rounded-[15px] p-4 flex flex-col gap-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Selesai</p>
                                <p class="text-3xl font-extrabold text-green-600"><?= $total_pesanan ?></p>
                            </div>
                            <div class="bg-blue-50 border border-blue-100 rounded-[15px] p-4 flex flex-col gap-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Total Produk</p>
                                <p class="text-3xl font-extrabold text-blue-600"><?= $total_produk ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Ganti Password -->
                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 mt-4">
                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-text-1 text-base">Ganti Password</h4>
                        </div>

                        <?php if($error_password): ?>
                            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                            <?php echo $notif_password; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($success_password): ?>
                            <div class="mb-6 px-4 py-3 bg-green-50 border border-green-100 rounded-[15px] text-sm text-green-500 font-medium">
                            <?php echo $notif_password; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Password Lama</label>
                                <input type="password" name="password_lama" placeholder="Masukkan password lama" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Password Baru</label>
                                <input type="password" name="password_baru" placeholder="Masukkan password baru" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi" placeholder="Konfirmasi password baru" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                            </div>

                            <p class="text-xs text-text-3 mt-1 italic">
                                * Lupa password lama? Silakan hubungi admin sekolah untuk mereset password Anda.
                            </p>

                            <button type="submit" name="ganti_password" class="w-full bg-submit text-white font-bold py-3 rounded-[15px] mt-2 shadow-lg shadow-submit/20 hover:opacity-90 active:scale-[0.98] transition-all">
                                Ubah Password
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="popup-enter bg-white rounded-[24px] w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">

        <div class="flex justify-between items-center p-5 border-b flex-shrink-0">
            <h2 class="text-primary font-extrabold text-xl">Edit Profil</h2>
            <button onclick="tutupEdit()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5 flex flex-col gap-4">

            <div id="error-box" class="hidden px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium"></div>

            <form method="POST" id="form-edit" class="flex flex-col gap-4" enctype="multipart/form-data">

                <p class="text-xs font-bold uppercase tracking-widest text-text-3 border-b pb-2">Informasi Akun</p>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3" for="edit_foto_profil">
                        Foto Profil User (Opsional)
                    </label>
                    <input type="file" id="edit_foto_profil" name="edit_foto_profil" accept="image/png, image/jpeg, image/jpg, image/gif"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <span class="text-[11px] text-text-3">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</span>
                    <?php if (!empty($user['foto_profil'])): ?>
                    <div class="flex items-center gap-2 mt-1">
                        <input type="checkbox" id="hapus_foto" name="hapus_foto" value="1" class="w-4 h-4 text-primary rounded focus:ring-primary border-gray-300">
                        <label for="hapus_foto" class="text-xs text-text-2 font-medium">Hapus foto profil saat ini</label>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Username</label>
                    <input type="text" name="edit_username"
                        value="<?= htmlspecialchars($user['username']) ?>"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9_.]/g, '')"
                        onpaste="event.preventDefault()" maxlength="20"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Email</label>
                    <input type="email" name="edit_email"
                        value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                    <input type="tel" name="edit_telepon"
                        value="<?= htmlspecialchars($user['no_telepon'] ?? '') ?>"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <p class="text-xs font-bold uppercase tracking-widest text-text-3 border-b pb-2 mt-2">Informasi Toko</p>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Nama Toko</label>
                    <input type="text" name="edit_nama_toko"
                        value="<?= htmlspecialchars($toko['nama_toko']) ?>"
                        maxlength="100"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Lokasi</label>
                    <input type="text" name="edit_lokasi"
                        value="<?= htmlspecialchars($toko['lokasi'] ?? '') ?>"
                        placeholder="Contoh: Kantin Utama, Stan A1"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Jam Buka</label>
                        <input type="time" name="edit_jam_buka"
                            value="<?= htmlspecialchars($toko['jam_buka'] ?? '') ?>"
                            class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Jam Tutup</label>
                        <input type="time" name="edit_jam_tutup"
                            value="<?= htmlspecialchars($toko['jam_tutup'] ?? '') ?>"
                            class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <div class="flex justify-between items-end">
                        <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Deskripsi Toko</label>
                        <span id="desc-counter" class="text-[10px] text-text-3 font-semibold">0/150</span>
                    </div>
                    <textarea name="edit_deskripsi" id="edit_deskripsi" rows="3" maxlength="150"
                        oninput="document.getElementById('desc-counter').textContent = this.value.length + '/150'"
                        placeholder="Ceritakan sedikit tentang toko Anda..."
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20 resize-none"><?= htmlspecialchars($toko['deskripsi'] ?? '') ?></textarea>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const descArea = document.getElementById('edit_deskripsi');
                        if (descArea) {
                            document.getElementById('desc-counter').textContent = descArea.value.length + '/150';
                        }
                    });
                </script>


                <button type="submit" name="simpan_profil"
                    class="w-full h-[48px] bg-submit rounded-[15px] text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all">
                    Simpan Perubahan
                </button>

            </form>
        </div>
    </div>
</div>

<script>
    const nilaiAwal = {
        username:   <?= json_encode($user['username']) ?>,
        email:      <?= json_encode($user['email'] ?? '') ?>,
        telepon:    <?= json_encode($user['no_telepon'] ?? '') ?>,
        nama_toko:  <?= json_encode($toko['nama_toko']) ?>,
        lokasi:     <?= json_encode($toko['lokasi'] ?? '') ?>,
        jam_buka:   <?= json_encode($toko['jam_buka'] ?? '') ?>,
        jam_tutup:  <?= json_encode($toko['jam_tutup'] ?? '') ?>,
        deskripsi:  <?= json_encode($toko['deskripsi'] ?? '') ?>
    };

    function bukaEdit() {
        document.getElementById('modal-edit').classList.remove('hidden');
    }

    function tutupEdit() {
        document.getElementById('modal-edit').classList.add('hidden');

        const f = document.getElementById('form-edit');
        f.edit_username.value  = nilaiAwal.username;
        f.edit_email.value     = nilaiAwal.email;
        f.edit_telepon.value   = nilaiAwal.telepon;
        f.edit_nama_toko.value = nilaiAwal.nama_toko;
        f.edit_lokasi.value    = nilaiAwal.lokasi;
        f.edit_jam_buka.value  = nilaiAwal.jam_buka;
        f.edit_jam_tutup.value = nilaiAwal.jam_tutup;
        f.edit_deskripsi.value = nilaiAwal.deskripsi;

        const errBox = document.getElementById('error-box');
        errBox.classList.add('hidden');
        errBox.textContent = '';
    }

    document.getElementById('form-edit').addEventListener('submit', function(e) {
        const username  = this.edit_username.value.trim();
        const jamBuka   = this.edit_jam_buka.value;
        const jamTutup  = this.edit_jam_tutup.value;

        const errBox = document.getElementById('error-box');
        let pesan = '';

        if(username.length < 3 || username.length > 20){
            pesan = 'Username harus antara 3-20 karakter.';
        } else if(!/^[a-zA-Z0-9_.]+$/.test(username)){
            pesan = 'Username hanya boleh huruf, angka, underscore, dan titik.';
        } else if(jamBuka && jamTutup && jamTutup < jamBuka){
            pesan = 'Jam tutup tidak boleh lebih awal dari jam buka.';
        }

        if(pesan){
            e.preventDefault(); 
            errBox.textContent = pesan;
            errBox.classList.remove('hidden');
        }
    });
</script>

</body>
</html>