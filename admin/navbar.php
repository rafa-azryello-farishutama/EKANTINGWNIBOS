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

                <a href="kelola.php" class="<?= $halaman == 'kelola.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'kelola.php' ? '../assets/img/person_white.png' : '../assets/img/person_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'pesan.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Anggota</span>
                </a>

                <a href="kelolaToko.php" class="<?= $halaman == 'kelolaToko.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'kelolaToko.php' ? '../assets/img/store_white.png' : '../assets/img/store_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'kelolaToko.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Toko</span>
                </a>

                <a href="history.php" class="<?= $halaman == 'history.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'history.php' ? '../assets/img/history_white.png' : '../assets/img/history_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'history.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Riwayat</span>
                </a>

                <a href="pengaturan.php" class="<?= $halaman == 'pengaturan.php' ? 'bg-primary text-white' : 'text-text-2 hover:bg-primary/10' ?> 
                rounded-xl flex items-center px-8 py-4 transition-all duration-200 cursor-pointer">
                <img src="<?= $halaman == 'pengaturan.php' ? '../assets/img/akun_white.png' : '../assets/img/akun_black.png' ?>" 
                    class="w-[25px] h-auto mr-3 <?= $halaman != 'pengaturan.php' ? 'opacity-40' : '' ?>">
                <span class="font-medium text-sm">Pengaturan</span>
                </a>
            </nav>

            <!-- Tombol Naikkan Kelas -->
            <div class="px-6 pb-6 mt-4">
                <div class="border-t border-gray-200 pt-4">
                    <button onclick="document.getElementById('modal-naik-kelas').classList.remove('hidden')"
                        class="w-full flex items-center gap-3 px-5 py-3.5 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-all duration-200 group">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-amber-700">Naikkan Kelas</p>
                            <p class="text-[10px] text-amber-500 leading-tight">10→11→12→Lulus</p>
                        </div>
                    </button>
                </div>
            </div>

        </aside>

<!-- Modal Konfirmasi Naikkan Kelas -->
<div id="modal-naik-kelas" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('modal-naik-kelas').classList.add('hidden')"></div>

    <!-- Panel -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 overflow-hidden">
        <!-- Header peringatan -->
        <div style="background: linear-gradient(135deg, #ef4444, #f97316);" class="px-6 pt-6 pb-5 text-white">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="font-extrabold text-lg">Naikkan Kelas Siswa</h3>
            </div>
            <p class="text-white/80 text-sm leading-relaxed">Aksi ini akan memproses seluruh siswa aktif sekaligus.</p>
        </div>

        <!-- Body -->
        <div class="px-6 py-5">
            <p class="text-text-1 text-sm font-medium mb-4">Yang akan terjadi:</p>
            <div class="space-y-2.5 mb-5">
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                    <span class="text-lg">📚</span>
                    <p class="text-sm text-blue-700 font-medium">Kelas 10 → naik ke Kelas 11</p>
                </div>
                <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl">
                    <span class="text-lg">📖</span>
                    <p class="text-sm text-purple-700 font-medium">Kelas 11 → naik ke Kelas 12</p>
                </div>
                <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100">
                    <span class="text-lg">🎓</span>
                    <div>
                        <p class="text-sm text-amber-700 font-medium">Kelas 12 → <strong>Lulus</strong> (akun dinonaktifkan)</p>
                        <p class="text-[11px] text-amber-500 mt-0.5">Data otomatis terhapus setelah 1 tahun</p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-5 text-center">⚠️ Aksi ini tidak dapat dibatalkan</p>

            <div class="flex gap-3">
                <button onclick="document.getElementById('modal-naik-kelas').classList.add('hidden')"
                    class="flex-1 h-11 rounded-xl border border-gray-200 text-text-2 text-sm font-bold hover:bg-gray-50 transition-all">
                    Batal
                </button>
                <form method="POST" action="kelola.php" class="flex-1">
                    <button type="submit" name="naikkan_kelas"
                        style="background: linear-gradient(135deg, #ef4444, #f97316); box-shadow: 0 4px 15px rgba(239,68,68,0.3);"
                        class="w-full h-11 rounded-xl text-white text-sm font-bold hover:opacity-90 active:scale-[0.98] transition-all">
                        Ya, Naikkan Kelas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>