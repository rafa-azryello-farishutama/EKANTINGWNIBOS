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
    
    <link rel="stylesheet" href="../assets/css/penjual.css">
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
    <div class="flex w-full min-h-screen relative">
        
        <?php include 'navbar.php'; ?>
        
        <main class="lg:ml-80 flex-grow w-full flex flex-col p-4 md:p-8 bg-surface pt-24 lg:pt-8 h-screen">
            <div class="containerDash w-full h-full flex flex-col max-w-7xl mx-auto">

                <!-- Bagian Search (Responsive) -->
                <div class="w-full mb-6">
                    <form method="POST" class="w-full">
                        <div class="flex flex-col sm:flex-row gap-3 w-full items-center">
                            <div class="flex flex-1 items-center gap-3 bg-input rounded-xl px-4 h-12 w-full focus-within:ring-2 focus-within:ring-primary transition-all">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input type="text" name="name_id" placeholder="Search Menu" class="w-full bg-transparent border-none outline-none text-text-1 placeholder-gray-500 text-sm focus:ring-0 p-0">
                            </div>
                            <button type="submit" name="cari_user" class="h-12 w-full sm:w-auto px-8 bg-submit text-white rounded-xl text-sm font-bold hover:bg-primary transition-colors whitespace-nowrap">
                                Search Menu
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bagian Filter Tombol (Responsive Scroll) -->
                <div class="w-full mb-6 overflow-x-auto pb-2 scrollbar-hide">
                    <form method="POST" class="w-full">
                        <div class="flex gap-3 w-max">
                            <button type="submit" name="filter_pembeli" class="px-6 py-2 rounded-full font-bold text-sm bg-submit text-white transition-colors">
                                Semua
                            </button>
                            <button type="submit" name="filter_penjual" class="px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors">
                                Makanan
                            </button>
                            <button type="submit" name="filter_semua" class="px-6 py-2 rounded-full font-bold text-sm bg-input text-text-3 hover:bg-gray-200 transition-colors">
                                Minuman
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bagian List Produk (Responsive Grid: 2 Mobile, 3 Tablet, 4 Desktop) -->
                <!-- Menambahkan custom scrollbar class di CSS -->
                <div class="kotakList flex-1 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 overflow-y-auto pb-8 pr-2 content-start">
                    
                    <!-- Kotak Menu 1 -->
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-32 sm:h-40 bg-gray-200 w-full relative">
                             <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex flex-col flex-1 gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Nasi Goreng Spesial</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 25.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 12 porsi</p>
                            <div class="mt-auto pt-3">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Kotak Menu 2 -->
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-32 sm:h-40 bg-gray-200 w-full relative">
                             <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex flex-col flex-1 gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Es Teh Manis</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 5.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 50 gelas</p>
                            <div class="mt-auto pt-3">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Duplikat untuk melihat layout grid -->
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-32 sm:h-40 bg-gray-200 w-full relative">
                             <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex flex-col flex-1 gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Burger Ayam</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 20.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 8 porsi</p>
                            <div class="mt-auto pt-3">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-32 sm:h-40 bg-gray-200 w-full relative">
                             <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex flex-col flex-1 gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Es Kopi Susu</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 15.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 20 gelas</p>
                            <div class="mt-auto pt-3">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-second-primary rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        <div class="h-32 sm:h-40 bg-gray-200 w-full relative">
                            <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80" alt="Makanan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex flex-col flex-1 gap-1 sm:gap-2">
                            <h3 class="font-bold text-text-1 text-sm sm:text-base line-clamp-1">Ayam Geprek</h3>
                            <p class="text-primary font-bold text-sm sm:text-base">Rp 18.000</p>
                            <p class="text-text-3 text-xs sm:text-sm font-medium">Stok: 15 porsi</p>
                            <div class="mt-auto pt-3">
                                <button class="w-full bg-input text-submit border border-submit rounded-xl py-2 text-xs sm:text-sm font-bold hover:bg-submit hover:text-white transition-colors">
                                    Edit Menu
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</body>
</html>