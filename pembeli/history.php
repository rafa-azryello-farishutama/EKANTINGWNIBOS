<?php
session_start();
$halaman = basename($_SERVER['PHP_SELF']);
include '../config/koneksi.php';

if(!isset($_SESSION['pembeli_id_users'])){
    header("Location: ../index.php");
    exit;
}
$_SESSION['id_users'] = $_SESSION['pembeli_id_users'];
$_SESSION['username'] = $_SESSION['pembeli_username'];
$_SESSION['role']     = $_SESSION['pembeli_role'];

$id_users = (int) $_SESSION['id_users'];

// Update notifikasi pesanan terakhir dilihat
$q_hash = $db_ekantin->query("SELECT GROUP_CONCAT(CONCAT(id_pesanan, '-', status_pesanan)) as hash FROM pesanan WHERE id_users='$id_users'");
if ($q_hash) {
    $_SESSION['last_seen_orders_hash'] = md5($q_hash->fetch_assoc()['hash'] ?? '');
}
// Proses pembatalan pesanan jika ada
if (isset($_POST['batalkan_pesanan'])) {
    $id_batal = (int) $_POST['id_pesanan'];
    
    // Cek apakah pesanan masih pending dan milik user ini
    $qCek = $db_ekantin->prepare("SELECT status_pesanan FROM pesanan WHERE id_pesanan = ? AND id_users = ?");
    $qCek->bind_param("ii", $id_batal, $id_users);
    $qCek->execute();
    $resCek = $qCek->get_result();
    
    if ($resCek->num_rows > 0) {
        $rowCek = $resCek->fetch_assoc();
        if ($rowCek['status_pesanan'] === 'pending') {
            // Update status jadi dibatalkan
            $qBatal = $db_ekantin->prepare("UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE id_pesanan = ?");
            $qBatal->bind_param("i", $id_batal);
            if ($qBatal->execute()) {
                // Kembalikan stok
                $qDetail = $db_ekantin->prepare("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = ?");
                $qDetail->bind_param("i", $id_batal);
                $qDetail->execute();
                $resDetail = $qDetail->get_result();
                
                $qKembaliStok = $db_ekantin->prepare("UPDATE produk_kantin SET stok = stok + ? WHERE id_produk = ?");
                while ($det = $resDetail->fetch_assoc()) {
                    $qKembaliStok->bind_param("ii", $det['jumlah'], $det['id_produk']);
                    $qKembaliStok->execute();
                }
                
                $_SESSION['pesan_sukses'] = "Pesanan berhasil dibatalkan.";
            }
        } else {
            $_SESSION['pesan_error'] = "Pesanan tidak dapat dibatalkan karena sudah diproses oleh kantin.";
        }
    }
    header("Location: history.php");
    exit;
}

// Ambil filter (jika ada)
$filter = $_GET['filter'] ?? 'semua';
$where_status = "";
if ($filter !== 'semua') {
    // Validasi filter agar tidak disusupi SQL injection
    $allowed_filters = ['pending', 'diproses', 'selesai', 'dibatalkan'];
    if (in_array($filter, $allowed_filters)) {
        $where_status = " AND p.status_pesanan = '$filter'";
    } else {
        $filter = 'semua';
    }
}

// Ambil data pesanan
$sql_pesanan = "SELECT p.*, t.nama_toko, pay.metode_bayar as metode_pembayaran, pay.bukti_bayar as bukti_pembayaran, pay.status_bayar,
                (SELECT COUNT(*) FROM review r WHERE r.id_pesanan = p.id_pesanan) as is_reviewed
                FROM pesanan p 
                JOIN toko t ON p.id_toko = t.id_toko 
                LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
                WHERE p.id_users = ? $where_status
                ORDER BY p.tanggal_pesan DESC";

$stmt_pesanan = $db_ekantin->prepare($sql_pesanan);
$stmt_pesanan->bind_param("i", $id_users);
$stmt_pesanan->execute();
$hasil_pesanan = $stmt_pesanan->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .star-icon {
            color: #e5e7eb !important;
            cursor: pointer;
            transition: color 0.15s ease-in-out;
        }
        .star-icon.active {
            color: #facc15 !important;
        }
        .star-icon:hover,
        .star-icon:hover ~ .star-icon {
            color: #e5e7eb !important; /* Reset sibling hover if hovered from left */
        }
        /* Custom hover effect to light up stars on hover */
        .rating-container:hover .star-icon {
            color: #facc15 !important;
        }
        .rating-container .star-icon:hover ~ .star-icon {
            color: #e5e7eb !important;
        }
    </style>
</head>

<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
    <div class="flex min-h-screen relative">

        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
            <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

                <!-- Header -->
                <header class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
                    <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Riwayat Pesanan</h2>
                    <p class="text-text-3 mt-1 text-sm">Semua pesanan yang pernah kamu buat</p>
                </header>

                <?php if(isset($_SESSION['pesan_sukses'])): ?>
                    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold animate-[fadeInUp_0.3s_ease-out_forwards]">
                        <?= $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['pesan_error'])): ?>
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold animate-[fadeInUp_0.3s_ease-out_forwards]">
                        <?= $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Tab -->
                <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 flex items-center gap-2 flex-wrap"
                    style="animation-delay: 0.15s;">
                    <?php 
                    $tabs = ['semua' => 'Semua', 'pending' => 'Pending', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
                    foreach($tabs as $key => $label): 
                        $isActive = ($filter === $key);
                        $classActive = "bg-primary text-white shadow-sm";
                        $classInactive = "bg-white border border-gray-100 text-text-2 hover:border-primary/30 hover:text-primary";
                    ?>
                        <a href="history.php?filter=<?= $key ?>"
                           class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 <?= $isActive ? $classActive : $classInactive ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Daftar Riwayat -->
                <div class="flex flex-col gap-4">

                    <?php if (empty($hasil_pesanan)): ?>
                        <div class="text-center py-10 opacity-0 animate-[fadeInUp_0.5s_ease-out_forwards]" style="animation-delay: 0.2s;">
                            <div class="w-20 h-20 bg-input rounded-full flex items-center justify-center text-4xl mx-auto mb-4">📜</div>
                            <p class="text-text-3 font-semibold">Tidak ada pesanan.</p>
                        </div>
                    <?php else: ?>

                        <?php 
                        $delay = 0.2;
                        foreach ($hasil_pesanan as $pesanan): 
                            $status = strtolower($pesanan['status_pesanan']);
                            $id_pesanan = $pesanan['id_pesanan'];
                            
                            // Ambil detail item
                            $sql_detail = "SELECT dp.jumlah, pk.nama_menu, IF(dp.harga_satuan > 0, dp.harga_satuan, pk.harga) AS harga 
                                           FROM detail_pesanan dp 
                                           JOIN produk_kantin pk ON dp.id_produk = pk.id_produk 
                                           WHERE dp.id_pesanan = ?";
                            $stmt_detail = $db_ekantin->prepare($sql_detail);
                            $stmt_detail->bind_param("i", $id_pesanan);
                            $stmt_detail->execute();
                            $items = $stmt_detail->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            // Setup UI berdasarkan status
                            if ($status == 'selesai') {
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                $iconColor = "text-green-600"; $iconBg = "bg-green-50"; $badgeClass = "bg-green-100 text-green-700";
                            } elseif ($status == 'diproses') {
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                $iconColor = "text-blue-500"; $iconBg = "bg-blue-50"; $badgeClass = "bg-blue-100 text-blue-700";
                            } elseif ($status == 'dibatalkan') {
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                $iconColor = "text-red-500"; $iconBg = "bg-red-50"; $badgeClass = "bg-red-100 text-red-600";
                            } else {
                                // pending
                                $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                $iconColor = "text-yellow-500"; $iconBg = "bg-yellow-50"; $badgeClass = "bg-yellow-100 text-yellow-700";
                            }
                            
                            $waktu_pesan = date('d M Y · H:i', strtotime($pesanan['tanggal_pesan']));
                        ?>

                        <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-200"
                            style="animation-delay: <?= $delay ?>s;">
                            
                            <!-- Card Header -->
                            <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-gray-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl <?= $iconBg ?> flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 <?= $iconColor ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <?= $icon ?>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-text-1 text-sm">#ORD-<?= sprintf("%04d", $id_pesanan) ?></p>
                                        <p class="text-xs text-text-3 mt-0.5"><?= $waktu_pesan ?> WIB</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold px-3 py-1 rounded-full capitalize <?= $badgeClass ?>"><?= $status ?></span>
                            </div>
                            
                            <!-- Card Body (Items) -->
                            <div class="px-5 py-3 flex flex-col gap-1.5">
                                <p class="text-xs font-semibold text-text-3 uppercase tracking-wider mb-1"><?= htmlspecialchars($pesanan['nama_toko']) ?></p>
                                
                                <?php 
                                $total_qty = 0;
                                foreach($items as $item): 
                                    $total_qty += $item['jumlah'];
                                    // Beri efek coret kalau dibatalkan
                                    $strike = ($status === 'dibatalkan') ? 'line-through opacity-50' : '';
                                ?>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-text-2 <?= $strike ?>"><?= htmlspecialchars($item['nama_menu']) ?> <span class="text-text-3">×<?= $item['jumlah'] ?></span></span>
                                    <span class="font-semibold text-text-1 <?= $strike ?>">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (!empty($pesanan['catatan'])): ?>
                                    <div class="mt-2 text-xs italic text-text-3 bg-gray-50 p-2 rounded-lg">
                                        Catatan: "<?= htmlspecialchars($pesanan['catatan']) ?>"
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($pesanan['metode_pembayaran'])): ?>
                                    <div class="mt-2 flex items-center justify-between text-xs text-text-3 bg-gray-50/50 p-2 rounded-lg border border-gray-100">
                                        <span>Metode: <strong class="text-primary capitalize"><?= $pesanan['metode_pembayaran'] === 'qr' ? 'QRIS / QR Code' : 'Transfer Bank' ?></strong></span>
                                        <?php if (!empty($pesanan['bukti_pembayaran'])): ?>
                                            <a href="../assets/uploads_bukti/<?= htmlspecialchars($pesanan['bukti_pembayaran']) ?>" target="_blank" class="font-bold text-primary hover:underline">Lihat Bukti Bayar</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($status === 'dibatalkan' && !empty($pesanan['alasan_tolak'])): ?>
                                    <div class="mt-2 text-xs font-semibold text-red-600 bg-red-50 border border-red-100 p-2.5 rounded-lg">
                                        Alasan Ditolak: "<?= htmlspecialchars($pesanan['alasan_tolak']) ?>"
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="px-5 pb-4 pt-3 flex items-center justify-between border-t border-gray-50">
                                <span class="text-xs text-text-3"><?= $total_qty ?> item</span>
                                <div class="flex items-center gap-3">
                                    <a href="../apps/struk.php?id_pesanan=<?= $id_pesanan ?>" class="text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                        📄 Struk
                                    </a>
                                    
                                    <?php if ($status === 'selesai' && $pesanan['is_reviewed'] == 0): ?>
                                    <button type="button" onclick="bukaModalReview(<?= $id_pesanan ?>)" class="text-xs font-bold text-yellow-600 bg-yellow-100 hover:bg-yellow-200 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                        ⭐ Beri Ulasan
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($status === 'selesai' && $pesanan['is_reviewed'] > 0): ?>
                                    <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg">
                                        Sudah Diulas
                                    </span>
                                    <?php endif; ?>

                                    <?php if ($status === 'pending'): ?>
                                    <!-- Tombol Batalkan Pesanan (Hanya jika pending) -->
                                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                        <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                        <button type="submit" name="batalkan_pesanan" class="text-xs font-bold text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                            Batalkan
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <span class="font-extrabold text-base <?= ($status === 'dibatalkan') ? 'text-text-3 line-through' : 'text-primary' ?>">
                                        Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>

                        </div>
                        <?php 
                        $delay += 0.05;
                        endforeach; 
                        ?>
                    <?php endif; ?>

                </div>

            </div>
        </main>
    </div>

    <!-- Modal Review -->
    <div id="modal-review" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-0">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="tutupModalReview()"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-md sm:max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-white flex-shrink-0">
                <div>
                    <h2 class="text-text-1 font-extrabold text-xl">Beri Ulasan</h2>
                    <p class="text-xs text-text-3 font-medium mt-1">Bagaimana rasa makanannya?</p>
                </div>
                <button type="button" onclick="tutupModalReview()"
                    class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="overflow-y-auto p-5 flex flex-col gap-6" id="modal-review-body">
                <!-- Konten dinamis akan dimuat di sini -->
                <div class="flex justify-center p-8">
                    <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
            
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                <button type="button" id="btn-submit-review" onclick="submitReview()"
                    class="w-full h-12 bg-primary rounded-xl text-white text-sm font-bold hover:bg-submit active:scale-[0.98] transition-all shadow-lg shadow-primary/30">
                    Kirim Ulasan
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/navbar.js"></script>
    <script>
        let currentReviewItems = [];
        let currentOrderId = null;

        function bukaModalReview(id_pesanan) {
            currentOrderId = id_pesanan;
            document.getElementById('modal-review').classList.remove('hidden');
            document.getElementById('modal-review').classList.add('flex');
            document.getElementById('modal-review-body').innerHTML = `
                <div class="flex justify-center p-8">
                    <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>`;
            
            // Fetch order items to review
            fetch('ajax_get_review_items.php?id_pesanan=' + id_pesanan)
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        currentReviewItems = data.items;
                        renderReviewForm();
                    } else {
                        document.getElementById('modal-review-body').innerHTML = `<p class="text-red-500 text-center font-bold">Gagal memuat data pesanan.</p>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('modal-review-body').innerHTML = `<p class="text-red-500 text-center font-bold">Terjadi kesalahan sistem.</p>`;
                });
        }

        function tutupModalReview() {
            document.getElementById('modal-review').classList.add('hidden');
            document.getElementById('modal-review').classList.remove('flex');
            currentReviewItems = [];
            currentOrderId = null;
        }

        function renderReviewForm() {
            let html = '';
            currentReviewItems.forEach((item, index) => {
                html += `
                <div class="flex flex-col gap-3 pb-5 ${index !== currentReviewItems.length - 1 ? 'border-b border-gray-100' : ''}">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                            <img src="${item.foto ? '../assets/img_produk/'+item.foto : '../assets/img/no-image.png'}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="font-bold text-sm text-text-1">${item.nama_menu}</p>
                            <p class="text-xs text-text-3">Jumlah: ${item.jumlah}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-bold text-text-3 uppercase tracking-wider">Rating</label>
                        <div class="flex gap-2 rating-container" data-id="${item.id_produk}">
                            ${[1,2,3,4,5].map(star => `
                                <svg xmlns="http://www.w3.org/2000/svg" data-val="${star}" onclick="setRating(${item.id_produk}, ${star})"
                                    class="w-8 h-8 star-icon" 
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            `).join('')}
                        </div>
                        <input type="hidden" id="rating-${item.id_produk}" value="0">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold text-text-3 uppercase tracking-wider">Ulasan (Opsional)</label>
                        <textarea id="komen-${item.id_produk}" rows="2" placeholder="Bagikan pendapatmu..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all resize-none"></textarea>
                    </div>
                </div>
                `;
            });
            document.getElementById('modal-review-body').innerHTML = html;
        }

        function setRating(id_produk, rating) {
            document.getElementById('rating-' + id_produk).value = rating;
            const container = document.querySelector(`.rating-container[data-id="${id_produk}"]`);
            const stars = container.querySelectorAll('.star-icon');
            
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        function submitReview() {
            const btn = document.getElementById('btn-submit-review');
            const originalText = btn.innerHTML;
            
            let reviews = [];
            let isValid = true;
            
            currentReviewItems.forEach(item => {
                const rating = document.getElementById('rating-' + item.id_produk).value;
                const komen = document.getElementById('komen-' + item.id_produk).value;
                
                if (rating == 0) {
                    isValid = false;
                }
                
                reviews.push({
                    id_produk: item.id_produk,
                    rating: rating,
                    komentar: komen
                });
            });
            
            if (!isValid) {
                alert("Harap berikan rating bintang untuk semua produk.");
                return;
            }
            
            btn.innerHTML = `<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mx-auto"></div>`;
            btn.disabled = true;
            
            fetch('proses_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_pesanan: currentOrderId,
                    reviews: reviews
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Ulasan berhasil dikirim!');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal mengirim ulasan');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>

</html>