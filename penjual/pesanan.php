<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include "../config/koneksi.php";

if(!isset($_SESSION['penjual_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['penjual_id_users'];
$_SESSION['username'] = $_SESSION['penjual_username'];
$_SESSION['role']     = $_SESSION['penjual_role'];
$_SESSION['id_toko']   = $_SESSION['penjual_id_toko'];
$_SESSION['nama_toko'] = $_SESSION['penjual_nama_toko'];

$id_toko = $_SESSION['id_toko'];

date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['update_status'])) {
    $id = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];
    $alasan_tolak = isset($_POST['alasan_tolak']) ? $db_ekantin->real_escape_string($_POST['alasan_tolak']) : '';

    $query_update = "UPDATE pesanan SET status_pesanan = '$status_baru', alasan_tolak = '$alasan_tolak' WHERE id_pesanan = '$id'";
    $db_ekantin->query($query_update);

    // Kembalikan stok jika pesanan ditolak
    if ($status_baru === 'dibatalkan') {
        $qDetail = $db_ekantin->query("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = '$id'");
        while ($det = $qDetail->fetch_assoc()) {
            $db_ekantin->query("UPDATE produk_kantin SET stok = stok + {$det['jumlah']} WHERE id_produk = '{$det['id_produk']}'");
        }
    }

    header("Location: pesanan.php");
    exit;
}

$today = date('Y-m-d'); // Asia/Jakarta sudah di-set di atas

// Pesanan yang masuk HARI INI saja
$qTotal = "SELECT * FROM pesanan WHERE id_toko = '$id_toko' AND DATE(tanggal_pesan) = '$today'";
$hasil = $db_ekantin->query($qTotal);
$jTotal = $hasil->num_rows;

// Semua pesanan yang masih PENDING (backlog keseluruhan)
$qPending = "SELECT * FROM pesanan WHERE status_pesanan = 'pending' AND id_toko = '$id_toko'";
$hTotal = $db_ekantin->query($qPending);
$pTotal = $hTotal->num_rows;

// Pesanan yang SELESAI HARI INI saja
$qSelesai = "SELECT * FROM pesanan WHERE status_pesanan = 'selesai' AND id_toko = '$id_toko' AND DATE(tanggal_pesan) = '$today'";
$hSelesai = $db_ekantin->query($qSelesai);
$sTotal = $hSelesai->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan</title>

    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-background pt-24 lg:pt-8 overflow-x-hidden">

            <header class="mb-8">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Kelola Pesanan</h2>
                <p class="text-text-3 mt-1">Berikut seluruh status pemesanan</p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8">

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-blue-50 flex flex-col gap-2 group hover:-translate-y-1 hover:shadow-md hover:shadow-blue-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Hari Ini</p>
                        <p class="text-4xl font-extrabold text-blue-600"><?php echo $jTotal; ?></p>
                        <p class="text-xs text-text-2">total pesanan masuk</p>
                    </div>
                    <img src="../assets/img/user-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-yellow-50 flex flex-col gap-2 group hover:-translate-y-1 hover:shadow-md hover:shadow-yellow-100/50 transition-all">
                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Pesanan Pending</p>
                        <p class="text-4xl font-extrabold text-yellow-500"><?php echo $pTotal; ?></p>
                        <p class="text-xs text-text-2">menunggu diproses</p>
                    </div>
                    <img src="../assets/img/store-icon.png"
                        class="absolute bottom-0 right-0 w-[120px] opacity-10 pointer-events-none group-hover:scale-110 transition-transform" />
                </div>

                <div
                    class="bg-white rounded-[20px] relative overflow-hidden p-6 shadow-sm border border-green-50 flex flex-col gap-2 group hover:-translate-y-1 hover:shadow-md hover:shadow-green-100/50 transition-all">
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

                $query = "SELECT p.*, u.username, pay.metode_bayar as metode_pembayaran, pay.bukti_bayar as bukti_pembayaran, pay.status_bayar 
              FROM pesanan p 
              JOIN users u ON p.id_users = u.id_users 
              LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
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
                            'pending' => 'text-yellow-700 bg-yellow-100 border border-yellow-200',
                            'diproses' => 'text-blue-700 bg-blue-100 border border-blue-200',
                            'selesai' => 'text-green-700 bg-green-100 border border-green-200',
                            'dibatalkan' => 'text-red-600 bg-red-100 border border-red-200',
                            default => 'text-gray-600 bg-gray-100 border border-gray-200'
                        };

                        $badgePill = match ($status) {
                            'pending' => 'text-yellow-700 bg-yellow-100 border border-yellow-200',
                            'diproses' => 'text-blue-700 bg-blue-100 border border-blue-200',
                            'selesai' => 'text-green-700 bg-green-100 border border-green-200',
                            'dibatalkan' => 'text-red-600 bg-red-100 border border-red-200',
                            default => 'text-gray-600 bg-gray-100 border border-gray-200'
                        };

                        $cardBorderClass = match ($status) {
                            'pending' => 'border-yellow-100 hover:border-yellow-200',
                            'diproses' => 'border-blue-100 hover:border-blue-200',
                            'selesai' => 'border-green-100 hover:border-green-200',
                            'dibatalkan' => 'border-red-100 hover:border-red-200',
                            default => 'border-gray-100 hover:border-primary/30'
                        };
                        if ($status == 'pending') {
                            $tombolAksi = "<span class='text-xs font-bold bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl'>⏳ Pending</span>";
                        } else if ($status == 'diproses') {
                            $tombolAksi = "<span class='text-xs font-bold bg-blue-100 text-blue-700 px-4 py-2 rounded-xl'>🔄 Diproses</span>";
                        } else {
                            $tombolAksi = "";
                        }

                        $username = htmlspecialchars($row['username'], ENT_QUOTES);
                        $harga = "Rp " . number_format($row['total_harga'], 0, ',', '.');
                        $catatan = htmlspecialchars($row['catatan'] ?? '-', ENT_QUOTES);
                        $metode_pembayaran = htmlspecialchars($row['metode_pembayaran'] ?? 'transfer', ENT_QUOTES);
                        $bukti_pembayaran = htmlspecialchars($row['bukti_pembayaran'] ?? '', ENT_QUOTES);

                        $qMenu = $db_ekantin->query("SELECT dp.jumlah, pk.nama_menu FROM detail_pesanan dp JOIN produk_kantin pk ON dp.id_produk = pk.id_produk WHERE dp.id_pesanan = '$id_pesanan'");
                        $listMenu = [];
                        while ($m = $qMenu->fetch_assoc()) {
                            $listMenu[] = $m['nama_menu'] . " x" . $m['jumlah'];
                        }
                        $tampilMenu = implode(", ", $listMenu);

                        echo "
            <div onclick=\"bukaPopup('$username', '$tulisanTanggal', '$status', '$tampilMenu', '$catatan', '$harga','$id_pesanan', '$metode_pembayaran', '$bukti_pembayaran')\"
                class='bg-white rounded-[24px] p-6 shadow-sm border $cardBorderClass cursor-pointer transition-all mb-2 hover:-translate-y-0.5 hover:shadow-md'>
                
                <div class='flex justify-between items-start mb-2'>
                    <div>
                        <div class='flex items-center gap-2'>
                            <p class='text-lg font-bold text-text-1'>#ORD-" . sprintf("%04d", $id_pesanan) . "</p>
                            <p class='text-xs font-semibold px-2 py-0.5 bg-gray-100 rounded-md'>$username</p>
                        </div>
                        <p class='text-xs text-text-3 mt-1'>$tulisanTanggal</p>
                    </div>
                    <span class='text-xs font-bold $badgePill px-4 py-1.5 rounded-full capitalize'>$status</span>
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

            <div class="bg-gradient-to-r from-primary to-[#006800] px-8 py-6 flex items-center justify-between">
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

                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Pembayaran</p>
                        <p id="popup-metode" class="text-xs font-bold text-primary capitalize mt-0.5">-</p>
                    </div>
                    <div id="popup-bukti-wrapper" class="flex gap-2">
                        <a id="popup-bukti-link" href="#" target="_blank" class="text-[11px] font-bold bg-primary text-white hover:bg-submit px-3 py-1.5 rounded-lg transition-all shadow-sm">
                            Lihat Bukti Bayar
                        </a>
                        <!-- Struk hanya ditampilkan saat selesai via JS -->
                        <a id="popup-struk-link" href="#" target="_blank" class="hidden text-[11px] font-bold bg-blue-600 text-white hover:bg-blue-700 px-3 py-1.5 rounded-lg transition-all shadow-sm">
                            📄 Struk
                        </a>
                    </div>
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

        function bukaPopup(nama, waktu, status, items, catatan, total, id_pesanan, metode_bayar, bukti_bayar) {
            document.getElementById('popup-nama').textContent = "#ORD-" + String(id_pesanan).padStart(4, '0') + " (" + nama + ")";
            document.getElementById('popup-waktu').textContent = waktu;
            document.getElementById('popup-items').textContent = items;
            document.getElementById('popup-catatan').textContent = catatan;
            document.getElementById('popup-total').textContent = total;

            const metodeText = metode_bayar === 'qr' ? '📱 QRIS / QR Code' : '🏦 Transfer Bank';
            document.getElementById('popup-metode').textContent = metodeText;

            const linkEl = document.getElementById('popup-bukti-link');
            if (bukti_bayar) {
                linkEl.href = "../assets/uploads_bukti/" + bukti_bayar;
                linkEl.style.display = 'inline-block';
            } else {
                linkEl.style.display = 'none';
            }

            document.getElementById('popup-struk-link').href = "../apps/struk.php?id_pesanan=" + id_pesanan;

            const aksiMap = {
                pending: `
        <form method="POST" class="flex flex-col gap-2.5" id="form-pesanan">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="status_baru" id="status-baru-input" value="diproses">
            
            <div id="alasan-tolak-section" class="hidden flex flex-col gap-1.5 border-t pt-3">
                <label class="text-[10px] font-semibold uppercase tracking-widest text-text-3">Alasan Penolakan</label>
                <textarea name="alasan_tolak" id="alasan-tolak-input" rows="2" placeholder="Sebutkan alasan penolakan..." class="w-full border-gray-200 bg-input focus:bg-white focus:ring-primary focus:border-primary text-sm rounded-xl resize-none"></textarea>
            </div>

            <button type="submit" id="btn-proses" style="background:#2563eb;" class="w-full h-[46px] rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Proses Pesanan
            </button>
            <button type="button" id="btn-tolak-init" onclick="showTolakSection()" class="w-full h-[46px] bg-red-50 text-red-600 border border-red-200 rounded-[12px] text-sm font-bold hover:bg-red-100 transition-all">
                Tolak Pesanan
            </button>
            <button type="submit" id="btn-tolak-submit" onclick="return setStatusBatal()" class="hidden w-full h-[46px] bg-red-600 rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Kirim & Tolak Pesanan
            </button>
            <button type="button" id="btn-batal-tolak" onclick="hideTolakSection()" class="hidden w-full h-[36px] text-text-3 text-xs font-semibold hover:underline">
                Kembali
            </button>
        </form>`,
                diproses: `
        <form method="POST">
            <input type="hidden" name="id_pesanan" value="${id_pesanan}">
            <input type="hidden" name="status_baru" value="selesai">
            <button type="submit" name="update_status" style="background:#16a34a;" class="w-full h-[46px] rounded-[12px] text-white text-sm font-bold hover:opacity-90 transition-all">
                Tandai Selesai
            </button>
        </form>`,
                selesai: `<div class="py-2 text-center text-green-600 font-bold bg-green-50 rounded-xl">✅ Pesanan Selesai</div>`
            };

            document.getElementById('popup-aksi').innerHTML = aksiMap[status] || '';

            // Struk hanya tampil jika status diproses atau selesai
            const strukEl = document.getElementById('popup-struk-link');
            if (status === 'diproses' || status === 'selesai') {
                strukEl.href = "../apps/struk.php?id_pesanan=" + id_pesanan;
                strukEl.classList.remove('hidden');
            } else {
                strukEl.classList.add('hidden');
            }

            document.getElementById('overlay-popup').classList.remove('hidden');
        }

        function tutupPopup() {
            document.getElementById('overlay-popup').classList.add('hidden');
        }

        function showTolakSection() {
            document.getElementById('alasan-tolak-section').classList.remove('hidden');
            document.getElementById('btn-proses').classList.add('hidden');
            document.getElementById('btn-tolak-init').classList.add('hidden');
            document.getElementById('btn-tolak-submit').classList.remove('hidden');
            document.getElementById('btn-batal-tolak').classList.remove('hidden');
            document.getElementById('alasan-tolak-input').setAttribute('required', 'true');
            document.getElementById('status-baru-input').value = 'dibatalkan';
        }

        function hideTolakSection() {
            document.getElementById('alasan-tolak-section').classList.add('hidden');
            document.getElementById('btn-proses').classList.remove('hidden');
            document.getElementById('btn-tolak-init').classList.remove('hidden');
            document.getElementById('btn-tolak-submit').classList.add('hidden');
            document.getElementById('btn-batal-tolak').classList.add('hidden');
            document.getElementById('alasan-tolak-input').removeAttribute('required');
            document.getElementById('status-baru-input').value = 'diproses';
        }

        function setStatusBatal() {
            const input = document.getElementById('alasan-tolak-input');
            if (!input.value.trim()) {
                alert('Harap masukkan alasan penolakan.');
                return false;
            }
            return true;
        }
    </script>

</body>

</html>