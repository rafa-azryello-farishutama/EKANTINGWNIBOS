 <div class="bg-primary lg:hidden fixed top-0 left-0 right-0 h-16 flex items-center justify-between px-6 z-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-background p-2">
                <img src="../assets/img/Garpusendok.png" class="w-full h-auto object-contain" alt="Logo">
                </div>
                <span class="font-headline font-bold text-background text-[20px]">E-Kantin</span>
            </div>

            <a href="#mobile-sidebar" class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 transition-all">
              <img src="../assets/img/menu.png" class="w-6 h-6 object-contain invert" alt="Menu">
            </a>

        </div><!--Mobile Navigation-->

        <aside class="hidden lg:flex flex-col top-0 left-0 w-80 h-full fixed py-8 bg-input rounded-r-xl z-40">
            <div class="flex items-center justify-center mb-4">
                <img src="../assets/img/logoBaru1.png" class="w-[200px] h-auto">
            </div>

            <nav class="flex-grow space-y-1 px-6">

                <a href="dashboard.php" class="<?= $halaman == 'dashboard.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'dashboard.php' ? '../assets/img/dashboard.png' : '../assets/img/dashboardHitam.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'dashboard.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Dashboard</span>
                </a>

                <a href="pesan.php" class="<?= $halaman == 'pesan.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'pesan.php' ? '../assets/img/menuBook_white.png' : '../assets/img/menuBook_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'pesan.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Pesan</span>
                </a>

                <a href="keranjang.php" class="<?= $halaman == 'keranjang.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'keranjang.php' ? '../assets/img/keranjang_white.png' : '../assets/img/keranjang_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'keranjang.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Keranjang</span>
                </a>

                <a href="history.php" class="<?= $halaman == 'history.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'history.php' ? '../assets/img/history_white.png' : '../assets/img/history_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'history.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Riwayat</span>
                </a>

                <a href="profil.php" class="<?= $halaman == 'profil.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'profil.php' ? '../assets/img/akun_white.png' : '../assets/img/akun_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'profil.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Akun</span>
                </a>
            </nav>

        </aside>