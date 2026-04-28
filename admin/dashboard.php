<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css">
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
<body class="bg-emerlad-900 text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">
        <?php include 'navbar.php'; ?>

        <main class="lg:ml-80 flex-grow p-4 md:p-8 bg-surface pt-24 lg:pt-8">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
            <div>
                <h2 class="font-headline font-extrabold text-3xl md:text-4xl tracking-tight text-primary">Halo Sang Administrator!</h2>
                <p class="text-on-surface-variant font-body mt-2">Inilah yang sekarang terjadi di E-Kantin.</p>
            </div>
        </header>

        <div class="grid grid-cols-2 gap-4 md:gap-6">

    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Anggota Aktif</p>
        <p class="text-4xl font-extrabold text-primary">128</p>
        <p class="text-xs text-text-2">pengguna terdaftar</p>
    </div>

    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-gray-100 flex flex-col gap-2">
        <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Toko Aktif</p>
        <p class="text-4xl font-extrabold text-primary">12</p>
        <p class="text-xs text-text-2">toko berjalan</p>
    </div>

</div>
    </main>
    </div>

</body>
</html>