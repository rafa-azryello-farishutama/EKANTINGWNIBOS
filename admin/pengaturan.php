<?php $halaman=basename($_SERVER['PHP_SELF']); ?>

<?php 
session_start();
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

            <form method="POST" class="flex justify-center items-center gap-5">
                    <button type="submit" name="logout" class="bg-red-500 hover:bg-red-900 text-white px-6 py-3 rounded-[15px] font-bold text-sm shadow-md transition-all active:scale-95 flex items-center gap-2">
                    Log Out
                </button>
                </form>
            </div>

        </main>
    </div>
    
</body>
</html>