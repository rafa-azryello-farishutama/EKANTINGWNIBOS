<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['id_users']) || $_SESSION['role'] != 'pembeli'){
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Menu</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background": "#fafbf9",
                        "primary": "#004900",
                        "second-primary": "#f9f9fb",
                        "input": "#f0f4f0",
                        "text-1": "#191c1c",
                        "text-2": "#4e5a48",
                        "text-3": "#5e6659",
                        "submit": "#005300"
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(15px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-white">
<div class="flex min-h-screen relative">

    <?php include 'navbar.php'; ?>

    <main class="lg:ml-80 flex-grow w-full px-4 md:px-8 pb-8 pt-[72px] lg:pt-8">
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-6">

            <header class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0" style="animation-delay: 0.1s;">
                <h2 class="font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Pesan Menu</h2>
                <p class="text-text-3 mt-1 text-sm">Pilih kantin dan menu favoritmu</p>
            </header>

            <!-- Konten pesan akan diisi sesuai kebutuhan -->
            <div class="animate-[fadeInUp_0.5s_ease-out_forwards] opacity-0 bg-white rounded-[20px] p-8 shadow-sm border border-gray-100 text-center text-text-3 text-sm" style="animation-delay: 0.2s;">
                Halaman pemesanan sedang dalam pengembangan.
            </div>

        </div>
    </main>
</div>
</body>
</html>