<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'penjual'){
    header("Location: ../index.php");
    exit;
}

$id_toko = $_SESSION['id_toko'];

date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['update_status'])) {
    $id = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];

    $query_update = "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = '$id'";
    $db_ekantin->query($query_update);

    header("Location: pesanan.php");
    exit;
}

$qTotal = "SELECT * FROM pesanan WHERE id_toko = '$id_toko'";
$hasil = $db_ekantin->query($qTotal);
$jTotal = $hasil->num_rows;

$qPending = "SELECT * FROM pesanan WHERE status_pesanan = 'pending' AND id_toko = '$id_toko'";
$hTotal = $db_ekantin->query($qPending);
$pTotal = $hTotal->num_rows;

$qSelesai = "SELECT * FROM pesanan WHERE status_pesanan =  'selesai' AND id_toko = '$id_toko'";
$hSelesai = $db_ekantin->query($qSelesai);
$sTotal = $hSelesai->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="assets/css/style.css">
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

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8 overflow-x-hidden">

            <header class="mb-8">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Kelola Pesanan</h2>
                <p class="text-text-3 mt-1">Berikut seluruh status pemesanan</p>
            </header>

            <div class="grid grid-cols-2 gap-4 md:gap-6 mb-8">

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Hari Ini</p>
                        <p class="text-4xl font-extrabold text-primary"><?php echo $jTotal; ?></p>
                        <p class="text-xs text-text-2">total pesanan masuk</p>
                    </div>
                    <img src="../assets/img/user-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Pending</p>
                        <p class="text-4xl font-extrabold text-yellow-500"><?php echo $pTotal; ?></p>
                        <p class="text-xs text-text-2">menunggu diproses</p>
                    </div>
                    <img src="../assets/img/store-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-gray-100 flex flex-col gap-2 group col-span-2 md:col-span-1 md:col-start-1 md:translate-x-1/2">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Selesai Hari Ini</p>
                        <p class="text-4xl font-extrabold text-green-600"><?php echo $sTotal; ?></p>
                        <p class="text-xs text-text-2">pesanan selesai</p>
                    </div>
                    <img src="../assets/img/revenue-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

            </div>

            <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-1">
                <form method="GET" id="form-filter">
                    <input type="hidden" name="filter_status" id="input-filter" value="semua">
                </form>
                <button onclick="filterPesanan('semua')" id="btn-semua"
                    class="px-5 py-2 rounded-full text-sm font-semibold whitespace-nowrap bg-primary text-white transition-all">
                    Semua
                </button>
                <button onclick="filterPesanan('pending')" id="btn-pending"
                    class="px-5 py-2 rounded-full text-sm font-semibold whitespace-nowrap bg-white border border-gray-200 text-text-2 hover:bg-gray-50 transition-all">
                    Pending
                </button>
                <button onclick="filterPesanan('diproses')" id="btn-diproses"
                    class="px-5 py-2 rounded-full text-sm font-semibold whitespace-nowrap bg-white border border-gray-200 text-text-2 hover:bg-gray-50 transition-all">
                    Diproses
                </button>
                <button onclick="filterPesanan('selesai')" id="btn-selesai"
                    class="px-5 py-2 rounded-full text-sm font-semibold whitespace-nowrap bg-white border border-gray-200 text-text-2 hover:bg-gray-50 transition-all">
                    Selesai
                </button>
            </div>

            <div class="flex flex-col gap-3">
                <?php
                $id_toko = $_SESSION['id_toko'];
                $filter = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'semua';
                $where_status = ($filter !== 'semua') ? " AND p.status_pesanan = '$filter'" : "";

                $query = "SELECT p.*, u.username FROM pesanan p 
              JOIN users u ON p.id_users = u.id_users 
              WHERE p.id_toko = '$id_toko' $where_status 
              ORDER BY p.tanggal_pesan DESC";

                $pesanan = $db_ekantin->query($query);

                if ($pesanan && $pesanan->num_rows > 0) {
                    while ($row = $pesanan->fetch_assoc()) {
                        $waktu_pesan = strtotime($row['tanggal_pesan']);
                        $selisih_detik = time() - $waktu_pesan;

                        if ($selisih_detik < 0)
                            $selisih_detik = 0;

                        $menit = floor($selisih_detik / 60);

                        if ($menit < 1) {
                            $waktu_lalu = "Baru saja";
                        } elseif ($menit < 60) {
                            $waktu_lalu = $menit . " menit yang lalu";
                        } else {
                            $jam = floor($menit / 60);
                            $waktu_lalu = $jam . " jam yang lalu";
                        }

                        $tulisanTanggal = date('d M Y, H:i', $waktu_pesan);

                        $id_pesanan = $row['id_pesanan'];
                        $status = $row['status_pesanan'];

    
                        $badgeClass = match ($status) {
                            'pending' => 'text-yellow-700 bg-yellow-100',
                            'diproses' => 'text-blue-700 bg-blue-100',
                            'selesai' => 'text-green-700 bg-green-100',
                            'dibatalkan' => 'text-red-600 bg-red-100',
                            default => 'text-gray-600 bg-gray-100'
                        };

                
                        $tombolAksi = '';
                        if ($status == 'pending') {
                            $tombolAksi = "<button class='text-sm font-bold bg-green-100 text-green-700 px-6 py-2 rounded-xl hover:bg-green-200 transition-all'>Proses</button>";
                        } else if ($status == 'diproses') {
                            $tombolAksi = "<button class='text-sm font-bold bg-blue-100 text-blue-700 px-6 py-2 rounded-xl hover:bg-blue-200 transition-all'>Selesai</button>";
                        } else {
                            $tombolAksi = "";
                        }

                        $username = htmlspecialchars($row['username'], ENT_QUOTES);
                        $harga = "Rp " . number_format($row['total_harga'], 0, ',', '.');
                        $catatan = htmlspecialchars($row['catatan'] ?? '-', ENT_QUOTES);

                        $qMenu = $db_ekantin->query("SELECT dp.jumlah, pk.nama_menu FROM detail_pesanan dp JOIN produk_kantin pk ON dp.id_produk = pk.id_produk WHERE dp.id_pesanan = '$id_pesanan'");
                        $listMenu = [];
                        while ($m = $qMenu->fetch_assoc()) {
                            $listMenu[] = $m['nama_menu'] . " x" . $m['jumlah'];
                        }
                        $tampilMenu = implode(", ", $listMenu);

                        echo "
            <div onclick=\"bukaPopup('$username', '$tulisanTanggal', '$status', '$tampilMenu', '$catatan', '$harga','$id_pesanan')\"
                class='bg-white rounded-[24px] p-6 shadow-sm border border-gray-100 cursor-pointer hover:border-primary/30 transition-all mb-2'>
                
                <div class='flex justify-between items-start mb-2'>
                    <div>
                        <p class='text-lg font-bold text-text-1'>$username</p>
                        <p class='text-xs text-text-3'>$tulisanTanggal</p>
                    </div>
                    <span class='text-xs font-bold text-orange-700 bg-orange-100 px-4 py-1.5 rounded-full capitalize'>$status</span>
                </div>

                <p class='text-sm text-text-2 mb-4'>$tampilMenu</p>

                <div class='flex justify-between items-center'>
                    <p class='text-xl font-extrabold text-text-1'>$harga</p>
                    $tombolAksi
                </div>
            </div>";
                    }
                } else {
                    echo "<div class='text-center py-10 text-text-3'>Belum ada pesanan masuk.</div>";
                }
                ?>
            </div>

        </main>
    </div>

    <div id="overlay-popup" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        onclick="tutupPopup()">
        <div class="bg-white rounded-[24px] w-full max-w-md shadow-2xl overflow-hidden"
            onclick="event.stopPropagation()">

            <div class="bg-primary px-8 py-6 flex items-center justify-between">
                <div>
                    <p class="text-white/60 text-xs uppercase tracking-widest mb-1">Detail Pesanan</p>
                    <h2 id="popup-nama" class="text-white font-bold text-xl">-</h2>
                </div>
                <button onclick="tutupPopup()"
                    class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-8 py-6 flex flex-col gap-5">

                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Waktu Pesan</p>
                        <p id="popup-waktu" class="text-sm font-medium text-text-1 mt-1">-</p>
                    </div>
                    <div id="popup-badge"></div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3 mb-2">Item Pesanan</p>
                    <p id="popup-items" class="text-sm text-text-2 bg-input rounded-[12px] px-4 py-3">-</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3 mb-1">Catatan</p>
                    <p id="popup-catatan" class="text-sm text-text-2 italic">-</p>
                </div>

                <div class="flex justify-between items-center border-t border-gray-100 pt-4">
                    <p class="text-sm text-text-3">Total Harga</p>
                    <p id="popup-total" class="text-lg font-bold text-text-1">-</p>
                </div>

                <div id="popup-aksi"></div>

            </div>
        </div>
    </div>

    <script>
        function filterPesanan(status) {
            document.getElementById('input-filter').value = status;
            document.getElementById('form-filter').submit();
        }

        function setActiveButton() {
            const params = new URLSearchParams(window.location.search);
            const aktif = params.get('filter_status') || 'semua';

            const buttons = ['semua', 'pending', 'diproses', 'selesai'];
            buttons.forEach(btn => {
                const el = document.getElementById('btn-' + btn);
                if (btn === aktif) {
                    el.classList.add('bg-primary', 'text-white');
                    el.classList.remove('bg-white', 'border', 'border-gray-200', 'text-text-2');
                } else {
                    el.classList.remove('bg-primary', 'text-white');
                    el.classList.add('bg-white', 'border', 'border-gray-200', 'text-text-2');
                }
            });
        }

        setActiveButton();

        function bukaPopup(nama, waktu, status, items, catatan, total, id_pesanan) {
            document.getElementById('popup-nama').textContent = nama;
            document.getElementById('popup-waktu').textContent = waktu;
            document.getElementById('popup-items').textContent = items;
            document.getElementById('popup-catatan').textContent = catatan;
            document.getElementById('popup-total').textContent = total;

            const aksiMap = {
                pending: `
        <form method="POST">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="status_baru" value="diproses">
            <button type="submit" name="update_status" class="w-full h-[46px] bg-green-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Proses Pesanan
            </button>
        </form>`,
                diproses: `
        <form method="POST">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="status_baru" value="selesai">
            <button type="submit" name="update_status" class="w-full h-[46px] bg-blue-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Tandai Selesai
            </button>
        </form>`,
                selesai: `<div class="py-2 text-center text-green-600 font-bold bg-green-50 rounded-xl">Pesanan Selesai</div>`
            };

            document.getElementById('popup-aksi').innerHTML = aksiMap[status];
            document.getElementById('overlay-popup').classList.remove('hidden');
        }

        function tutupPopup() {
            document.getElementById('overlay-popup').classList.add('hidden');
        }
    </script>

</body>

</html>