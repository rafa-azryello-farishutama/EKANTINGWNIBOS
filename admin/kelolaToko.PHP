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

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['nama'];
    $namaToko = $_POST['nama_toko'];
    $password = $_POST['password'];
    $role = "penjual";
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];

    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    $cek = "INSERT INTO users(username, password, role, no_telepon, email, status) 
                VALUES('$username','$hash_password','$role','$telepon','$email','aktif')";

    if($db_ekantin->query($cek)){
        $id_user = $db_ekantin->insert_id;

        $db_ekantin->query("INSERT INTO penjual(id_users, nama_toko)
                        VALUES('$id_user','$namaToko')");
    }
}

$sql = "SELECT COUNT(*) as total FROM penjual p 
        JOIN users u ON p.id_users = u.id_users 
        WHERE u.status = 'aktif'";
$hasil = $db_ekantin->query($sql);
$row = $hasil->fetch_assoc();
$total_aktif = $row['total'];
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
        .popup-enter { animation: popupIn 0.2s ease-out both; }
        @keyframes popupIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8">
            <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
                <div>
                    <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Kelola Toko</h2>
                    <p class="text-text-3 font-body mt-2">Silahkan masukkan ID user untuk mengelola toko</p>
                </div>
                
                <button onclick="toggleModal(true)" class="bg-primary hover:bg-submit text-white px-6 py-3 rounded-[15px] font-bold text-sm shadow-md transition-all active:scale-95 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Toko
                </button>
            </header>

            <div class="grid grid-cols-1 gap-4 md:gap-6">
                <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Toko Aktif</p>
                    <p class="text-4xl font-extrabold text-primary"><?php echo $total_aktif; ?></p>
                    <p class="text-xs text-text-2">status aktif</p>
                </div>
            </div>

            <div class="w-full bg-white rounded-[20px] mt-[20px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="grid grid-cols-[40px_40px_1fr_100px_150px_130px_100px] bg-primary px-6 py-4 gap-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-white">ID</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">User</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Nama</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Toko</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Email</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Telepon</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-white">Status</p>
                </div>

                <div class="overflow-y-auto max-h-[400px]">
                    <?php 
                    // JOIN tabel penjual dan users agar data sinkron
                    $query = "SELECT p.kode_penjual, p.id_users, u.username, p.nama_toko, 
                                     u.email, u.no_telepon, u.status 
                              FROM penjual p
                              JOIN users u ON p.id_users = u.id_users";
                    $result = $db_ekantin->query($query);

                    if($result && $result->num_rows > 0){
                        while($data = $result->fetch_assoc()){
                            $kode_penjual = $data['kode_penjual'];
                            $id           = $data['id_users'];
                            $username     = htmlspecialchars($data['username'], ENT_QUOTES);    
                            $toko         = htmlspecialchars($data['nama_toko'], ENT_QUOTES);   
                            $email        = htmlspecialchars($data['email'], ENT_QUOTES);       // dari tabel users
                            $telepon      = htmlspecialchars($data['no_telepon'], ENT_QUOTES);  // dari tabel users
                            $status       = $data['status'];                                    // dari tabel users

                            if($status == 'aktif'){
                                $tulisanStatus = "<span class='text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full w-fit'>Aktif</span>";
                            } else {
                                $tulisanStatus = "<span class='text-xs font-semibold text-red-500 bg-red-100 px-3 py-1 rounded-full w-fit'>Nonaktif</span>";
                            }

                            echo "
                            <div class='grid grid-cols-[40px_40px_1fr_100px_150px_130px_100px] px-6 py-4 gap-4 border-b border-gray-100 items-center hover:bg-gray-50 transition-colors'>
                                <p class='text-sm text-text-2'>$kode_penjual</p>
                                <p class='text-sm text-text-2'>$id</p>
                                <p class='text-sm font-medium text-text-1 truncate'>$username</p>
                                <p class='text-sm font-medium text-text-1 truncate'>$toko</p>
                                <p class='text-sm text-text-2 truncate'>$email</p>
                                <p class='text-sm text-text-2 truncate'>$telepon</p>
                                $tulisanStatus
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

    <div id="modalTambah" class="hidden fixed inset-0 bg-black/50 z-[99] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-[24px] shadow-2xl overflow-hidden popup-enter">
            <div class="bg-primary p-6 flex justify-between items-center text-white">
                <h3 class="font-bold text-xl">Tambah Toko Baru</h3>
                <button onclick="toggleModal(false)" class="hover:bg-white/20 p-1 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form method="POST" class="p-8 flex flex-col gap-5">
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Username</label>
                    <input type="text" name="nama" oninput="this.value = this.value.replace(/\s/g, '')" placeholder="Masukkan username pemilik" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Nama Toko</label>
                    <input type="text" name="nama_toko" placeholder="Masukkan nama toko" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">Email</label>
                    <input type="email" name="email" placeholder="contoh@mail.com" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-text-3">No. Telepon</label>
                    <input type="tel" name="telepon" placeholder="0812xxxx" class="w-full bg-input border-none rounded-[12px] p-3 text-sm focus:ring-2 focus:ring-primary/20" required>
                </div>

                <button type="submit" class="w-full bg-submit text-white font-bold py-4 rounded-[15px] mt-2 shadow-lg shadow-submit/20 hover:opacity-90 active:scale-[0.98] transition-all">
                    Simpan Data Toko
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(show) {
            const modal = document.getElementById('modalTambah');
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }
        window.onclick = function(event) {
            const modal = document.getElementById('modalTambah');
            if (event.target == modal) {
                toggleModal(false);
            }
        }
    </script>
</body>
</html>