<?php
// PERBAIKAN: session_start() harus PERTAMA sebelum output apapun
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['pembeli_id_users'])) {
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];

$id_users = (int) $_SESSION['id_users']; // PERBAIKAN: cast ke int, lebih aman dari injection

$error_profil   = null;
$success_profil = null;

/* ──────────────────────────────────────────
   SIMPAN PROFIL
────────────────────────────────────────── */
if (isset($_POST['simpan_profil'])) {
    $username_baru = trim($_POST['edit_username'] ?? '');
    $email_baru    = trim($_POST['edit_email']    ?? '');
    $telepon_baru  = trim($_POST['edit_telepon']  ?? '');

    // Validasi username
    if (strlen($username_baru) < 3 || strlen($username_baru) > 20) {
        $error_profil = "Username harus antara 3–20 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_.]+$/', $username_baru)) {
        $error_profil = "Username hanya boleh huruf, angka, underscore, dan titik.";
    }

    // PERBAIKAN: Validasi format email
    if (!$error_profil && $email_baru !== '' && !filter_var($email_baru, FILTER_VALIDATE_EMAIL)) {
        $error_profil = "Format email tidak valid.";
    }

    // PERBAIKAN: Validasi format telepon (opsional, boleh kosong)
    if (!$error_profil && $telepon_baru !== '' && !preg_match('/^[0-9+\-\s]{7,15}$/', $telepon_baru)) {
        $error_profil = "Format nomor telepon tidak valid.";
    }

    // Cek username duplikat — PERBAIKAN: pakai prepared statement
    if (!$error_profil) {
        $stmt = $db_ekantin->prepare("SELECT id_users FROM users WHERE username = ? AND id_users != ?");
        $stmt->bind_param("si", $username_baru, $id_users);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_profil = "Username sudah digunakan orang lain.";
        }
        $stmt->close();
    }

    // Update — PERBAIKAN: pakai prepared statement
    if (!$error_profil) {
        $stmt = $db_ekantin->prepare("UPDATE users SET username = ?, email = ?, no_telepon = ? WHERE id_users = ?");
        $stmt->bind_param("sssi", $username_baru, $email_baru, $telepon_baru, $id_users);
        if ($stmt->execute()) {
            $success_profil = "Profil berhasil diperbarui.";
        } else {
            $error_profil = "Gagal menyimpan perubahan. Coba lagi.";
        }
        $stmt->close();
    }
}

/* ──────────────────────────────────────────
   LOGOUT
────────────────────────────────────────── */
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

/* ──────────────────────────────────────────
   AMBIL DATA USER — PERBAIKAN: prepared statement + null guard
────────────────────────────────────────── */
$stmt  = $db_ekantin->prepare("SELECT * FROM users WHERE id_users = ?");
$stmt->bind_param("i", $id_users);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// PERBAIKAN: guard jika user tidak ditemukan
if (!$user) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$halaman = basename($_SERVER['PHP_SELF']); // dipindah ke sini, tidak mengganggu session_start
?>
<!DOCTYPE html>
<html lang="id">
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
                <p class="text-text-3 mt-1 text-sm">Kelola informasi profil pribadi Anda</p>
            </header>

            <?php if ($success_profil): ?>
            <div class="px-4 py-3 bg-green-50 border border-green-100 rounded-[15px] text-sm text-green-700 font-medium">
                <?= htmlspecialchars($success_profil) /* PERBAIKAN: escape output */ ?>
            </div>
            <?php endif; ?>

            <?php if ($error_profil): ?>
            <div class="px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-600 font-medium">
                <?= htmlspecialchars($error_profil) ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Kartu Profil -->
                <div class="lg:col-span-1 flex flex-col gap-4">
                    <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden flex flex-col items-center text-center relative pb-6">
                        <div class="w-full h-20 bg-gradient-to-r from-primary to-[#006800]"></div>
                        <div class="w-20 h-20 rounded-full border-4 border-white bg-primary/10 flex items-center justify-center -mt-10 relative z-10">
                            <span class="text-primary font-bold text-2xl">
                                <?= htmlspecialchars(strtoupper(mb_substr($user['username'], 0, 1))) ?>
                            </span>
                        </div>
                        <h3 class="font-bold text-text-1 text-lg mt-3"><?= htmlspecialchars($user['username']) ?></h3>
                        <p class="text-xs text-text-3"><?= htmlspecialchars($user['email'] ?? '-') ?></p>
                        <span class="mt-2 text-xs font-semibold text-blue-700 bg-blue-100 px-3 py-1 rounded-full">
                            Pembeli
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

                <!-- Informasi Akun -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-text-1 text-base">Informasi Akun</h4>
                            <button onclick="bukaEdit()"
                                class="text-xs text-primary font-semibold hover:underline">
                                Edit
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Username</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3">
                                    <?= htmlspecialchars($user['username']) ?>
                                </p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Email</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3">
                                    <?= htmlspecialchars($user['email'] ?? '-') ?>
                                </p>
                            </div>
                            <div class="flex flex-col gap-1 md:col-span-2">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                                <p class="text-sm font-medium text-text-1 bg-input rounded-[10px] px-4 py-3">
                                    <?= htmlspecialchars($user['no_telepon'] ?? '-') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- ── MODAL EDIT PROFIL ── -->
<div id="modal-edit"
     class="<?= ($error_profil ? '' : 'hidden') ?> fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <!--
        PERBAIKAN: Jika ada error_profil, modal langsung terbuka kembali
        supaya user tidak kehilangan konteks kesalahan.
    -->
    <div class="popup-enter bg-white rounded-[24px] w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">

        <div class="flex justify-between items-center p-5 border-b flex-shrink-0">
            <h2 class="text-primary font-extrabold text-xl">Edit Profil</h2>
            <button onclick="tutupEdit()"
                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all"
                aria-label="Tutup modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5 flex flex-col gap-4">

            <!-- Error dari server ditampilkan di dalam modal -->
            <div id="error-box"
                 class="<?= ($error_profil ? '' : 'hidden') ?> px-4 py-3 bg-red-50 border border-red-100 rounded-[15px] text-sm text-red-500 font-medium">
                <?= htmlspecialchars($error_profil ?? '') ?>
            </div>

            <form method="POST" id="form-edit" class="flex flex-col gap-4">

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3" for="edit_username">
                        Username
                    </label>
                    <input type="text" id="edit_username" name="edit_username"
                        value="<?= htmlspecialchars($_POST['edit_username'] ?? $user['username']) ?>"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9_.]/g, '')"
                        onpaste="event.preventDefault()"
                        maxlength="20" minlength="3" required
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <span class="text-[11px] text-text-3">3–20 karakter, boleh huruf, angka, titik, underscore.</span>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3" for="edit_email">
                        Email
                    </label>
                    <input type="email" id="edit_email" name="edit_email"
                        value="<?= htmlspecialchars($_POST['edit_email'] ?? ($user['email'] ?? '')) ?>"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3" for="edit_telepon">
                        No. Telepon
                    </label>
                    <input type="tel" id="edit_telepon" name="edit_telepon"
                        value="<?= htmlspecialchars($_POST['edit_telepon'] ?? ($user['no_telepon'] ?? '')) ?>"
                        placeholder="Contoh: 08123456789"
                        class="border border-gray-200 rounded-[12px] p-3 text-sm bg-input focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <button type="submit" name="simpan_profil"
                    class="w-full h-[48px] bg-submit rounded-[15px] text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all">
                    Simpan Perubahan
                </button>

            </form>
        </div>
    </div>
</div>

<script>
    // Nilai asli dari server (untuk reset saat tutup modal)
    const nilaiAwal = {
        username: <?= json_encode($user['username']) ?>,
        email:    <?= json_encode($user['email']      ?? '') ?>,
        telepon:  <?= json_encode($user['no_telepon'] ?? '') ?>,
    };

    function bukaEdit() {
        document.getElementById('modal-edit').classList.remove('hidden');
    }

    function tutupEdit() {
        document.getElementById('modal-edit').classList.add('hidden');

        // Reset ke nilai awal
        const f = document.getElementById('form-edit');
        f.edit_username.value = nilaiAwal.username;
        f.edit_email.value    = nilaiAwal.email;
        f.edit_telepon.value  = nilaiAwal.telepon;

        // Sembunyikan error
        const errBox = document.getElementById('error-box');
        errBox.classList.add('hidden');
        errBox.textContent = '';
    }

    // Validasi sisi klien sebelum submit
    document.getElementById('form-edit').addEventListener('submit', function (e) {
        const username = this.edit_username.value.trim();
        const email    = this.edit_email.value.trim();
        const errBox   = document.getElementById('error-box');
        let pesan = '';

        if (username.length < 3 || username.length > 20) {
            pesan = 'Username harus antara 3–20 karakter.';
        } else if (!/^[a-zA-Z0-9_.]+$/.test(username)) {
            pesan = 'Username hanya boleh huruf, angka, underscore, dan titik.';
        } else if (email !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            // PERBAIKAN: validasi email di sisi klien juga
            pesan = 'Format email tidak valid.';
        }

        if (pesan) {
            e.preventDefault();
            errBox.textContent = pesan;
            errBox.classList.remove('hidden');
        }
    });
</script>

</body>
</html>