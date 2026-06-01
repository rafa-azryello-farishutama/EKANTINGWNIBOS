<?php
session_start();
$halaman = 'unggah_bukti';
include '../config/koneksi.php';

if(!isset($_SESSION['pembeli_id_users'])){
    header("Location: ../index.php");
    exit;
}

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_users   = $_SESSION['pembeli_id_users'];

if (!$id_pesanan) {
    header("Location: pesanan.php");
    exit;
}

$qPesanan = $db_ekantin->prepare("
    SELECT p.*, pb.metode_bayar, pb.status_bayar, t.nama_toko, t.qris_image, t.info_bank, t.info_ewallet 
    FROM pesanan p 
    JOIN pembayaran pb ON p.id_pesanan = pb.id_pesanan 
    JOIN toko t ON p.id_toko = t.id_toko 
    WHERE p.id_pesanan = ? AND p.id_users = ?
");
$qPesanan->bind_param("ii", $id_pesanan, $id_users);
$qPesanan->execute();
$pesanan = $qPesanan->get_result()->fetch_assoc();

if (!$pesanan || $pesanan['status_bayar'] !== 'menunggu_pembayaran') {
    header("Location: pesanan.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bukti_bayar']['tmp_name'];
        $fileName = $_FILES['bukti_bayar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExtension, $ekstensi_valid)) {
            $error = "Format bukti pembayaran salah! Hanya JPG, JPEG, PNG, WEBP.";
        } else {
            $newFileName = time() . '_' . md5(uniqid()) . '.' . $fileExtension;
            $uploadFileDir = '../assets/uploads_bukti/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $stmtUpdate = $db_ekantin->prepare("UPDATE pembayaran SET bukti_bayar = ?, status_bayar = 'sudah_bayar' WHERE id_pesanan = ?");
                $stmtUpdate->bind_param("si", $newFileName, $id_pesanan);
                $stmtUpdate->execute();
                
                $_SESSION['pesan_sukses'] = "Bukti pembayaran berhasil diunggah! Mohon tunggu konfirmasi penjual.";
                header("Location: pesanan.php");
                exit;
            } else {
                $error = "Gagal menyimpan berkas.";
            }
        }
    } else {
        $error = "Bukti pembayaran wajib diunggah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selesaikan Pembayaran</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-2xl mx-auto flex flex-col gap-6">
            
            <header>
                <h2 class="font-extrabold text-3xl tracking-tight text-primary">Pembayaran</h2>
                <p class="text-text-3 mt-1 text-sm">Selesaikan pesanan Anda di <b><?= htmlspecialchars($pesanan['nama_toko']) ?></b></p>
            </header>

            <?php if (isset($error)): ?>
            <div class="px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-600 font-medium">
                <?= $error ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col items-center justify-center mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-text-3 mb-1">Total Transfer (Termasuk Kode Unik)</p>
                    <p class="text-4xl font-extrabold text-primary">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></p>
                    <?php if ($pesanan['kode_unik'] > 0): ?>
                    <p class="text-sm font-semibold text-text-2 mt-2 bg-yellow-50 text-yellow-700 px-3 py-1 rounded-full">
                        Terdapat kode unik <b>Rp <?= $pesanan['kode_unik'] ?></b> di dalam total.
                    </p>
                    <p class="text-xs text-center text-text-3 mt-2 max-w-md">
                        Pastikan Anda mentransfer <b>Tepat Sesuai Nominal di Atas (hingga 3 digit terakhir)</b> agar penjual mudah memverifikasi pesanan Anda.
                    </p>
                    <?php endif; ?>
                </div>

                <div class="bg-input rounded-xl p-4 border border-gray-100 mb-6 flex flex-col items-center">
                    <?php if ($pesanan['metode_bayar'] === 'qr'): ?>
                        <p class="text-xs font-bold text-primary mb-3 uppercase tracking-widest">📱 QRIS Toko</p>
                        <?php if (!empty($pesanan['qris_image']) && file_exists('../assets/img/qris/' . $pesanan['qris_image'])): ?>
                            <img src="../assets/img/qris/<?= htmlspecialchars($pesanan['qris_image']) ?>" alt="QRIS" class="w-48 h-48 object-contain rounded-lg border border-gray-200 bg-white">
                            <p class="text-xs text-text-3 text-center mt-3">Silakan scan kode QR di atas</p>
                        <?php else: ?>
                            <p class="text-sm font-semibold text-red-500">Penjual belum mengunggah kode QRIS.</p>
                            <p class="text-xs text-text-3">Silakan minta QRIS langsung di kantin.</p>
                        <?php endif; ?>
                        
                    <?php elseif ($pesanan['metode_bayar'] === 'transfer'): ?>
                        <p class="text-xs font-bold text-primary mb-1 uppercase tracking-widest">🏦 Informasi Rekening Bank</p>
                        <p class="text-lg font-bold select-all text-text-1 mt-2"><?= htmlspecialchars($pesanan['info_bank'] ?: 'Belum diatur') ?></p>
                        <p class="text-xs text-text-3 mt-1">Silakan transfer ke rekening di atas</p>

                    <?php else: ?>
                        <p class="text-xs font-bold text-primary mb-1 uppercase tracking-widest">🟡 Informasi E-Wallet (<?= strtoupper($pesanan['metode_bayar']) ?>)</p>
                        <p class="text-lg font-bold select-all text-text-1 mt-2"><?= htmlspecialchars($pesanan['info_ewallet'] ?: 'Belum diatur') ?></p>
                        <p class="text-xs text-text-3 mt-1">Silakan transfer ke nomor E-Wallet di atas</p>
                    <?php endif; ?>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
                    <p class="text-sm font-semibold text-blue-800 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Pastikan pesanan Anda telah <b>dikonfirmasi</b> oleh penjual sebelum melakukan transfer. Jika terjadi kendala, mohon tunjukkan bukti transfer secara langsung kepada penjual di kantin.</span>
                    </p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                    <div>
                        <label for="bukti_bayar" class="block text-sm font-bold text-text-1 mb-2">Upload Bukti Pembayaran (Wajib)</label>
                        <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/*" required class="w-full text-sm text-text-2 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer file:transition-all border border-gray-200 rounded-xl p-2 focus:outline-none focus:border-primary">
                    </div>

                    <button type="submit" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-submit active:scale-95 transition-all shadow-md flex justify-center items-center gap-2 mt-2">
                        Kirim Bukti Pembayaran
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </form>

            </div>
        </div>
    </main>
</div>
</body>
</html>
