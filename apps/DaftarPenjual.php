<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Kantin | Daftar sebagai Penjual</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background":     "#f7f8f9",
                        "primary":        "#004900",
                        "second-primary": "#f9f9fb",
                        "input":          "#f3f3f5",
                        "text-1":         "#191c1c",
                        "text-2":         "#4e5a48",
                        "text-3":         "#5e6659",
                        "submit":         "#005300"
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-background min-h-screen flex flex-col items-center justify-center overflow-y-scroll">

    <div class="absolute top-4 left-1/2 -translate-x-1/2 md:top-8 md:left-10 md:translate-x-0 lg:left-12 z-20">
        <img src="../assets/img/logoBaru1.png" alt="Logo e-Kantin" class="w-[140px] md:w-[160px] lg:w-[180px] h-auto">
    </div>

    <div class="w-full max-w-[1440px] px-6 py-12 flex items-center justify-center">
        <div class="w-full max-w-lg">

            <div class="text-center mb-8 mt-16 md:mt-4">
                <h2 class="text-3xl font-extrabold text-primary mb-2 tracking-tight">Daftar sebagai Penjual</h2>
                <p class="text-text-2 font-medium text-base">Pendaftaran dilakukan langsung bersama admin e-Kantin</p>
            </div>

            <div class="bg-white rounded-[2rem] p-8 sm:p-10 shadow-[0_32px_64px_-16px_rgba(0,73,0,0.08)] border border-gray-100 flex flex-col gap-6">

                <div class="flex flex-col gap-[6px]">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Panduan Pendaftaran Penjual</p>
                    <p class="text-sm text-text-2">Akun penjual dibuat oleh admin. Ikuti langkah berikut agar proses pendaftaran berjalan lancar.</p>
                </div>

                <div class="flex flex-col gap-4">

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">1</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Siapkan Data Toko</p>
                            <p class="text-sm text-text-2 leading-relaxed">Siapkan informasi toko Anda: <span class="font-semibold text-primary">nama toko, lokasi, dan jam operasional</span> (jam buka & tutup). Data ini akan dimasukkan admin saat pembuatan akun.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">2</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Tentukan Username & Password</p>
                            <p class="text-sm text-text-2 leading-relaxed">Pilih <span class="font-semibold text-primary">username</span> yang unik (3–20 karakter, boleh huruf, angka, titik, underscore) dan <span class="font-semibold text-primary">password</span> yang mudah Anda ingat. Sampaikan ke admin saat pendaftaran.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">3</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Siapkan Email & Nomor Telepon</p>
                            <p class="text-sm text-text-2 leading-relaxed">Pastikan <span class="font-semibold text-primary">email</span> yang Anda daftarkan belum pernah digunakan di e-Kantin, dan siapkan <span class="font-semibold text-primary">nomor telepon aktif</span> untuk keperluan verifikasi.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">4</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Temui Admin e-Kantin</p>
                            <p class="text-sm text-text-2 leading-relaxed">Datang langsung ke <span class="font-semibold text-primary">ruang admin / kantin</span> dengan membawa semua data di atas. Admin akan membuatkan akun penjual untuk Anda.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">5</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Login & Mulai Berjualan</p>
                            <p class="text-sm text-text-2 leading-relaxed">Setelah akun dibuat, Anda bisa langsung <span class="font-semibold text-primary">login</span> menggunakan username dan password yang tadi didaftarkan, lalu mulai tambahkan menu toko Anda.</p>
                        </div>
                    </div>

                </div>

                <!-- Kontak admin -->
                <div class="flex flex-col gap-3 pt-2 border-t border-gray-100">
                    <p class="text-xs text-text-3 text-center">Butuh informasi lebih lanjut? Hubungi admin melalui:</p>
                    <div class="flex flex-row justify-center gap-6">
                        <div class="flex flex-col items-center gap-1">
                            <p class="text-xs font-semibold text-text-2">WhatsApp</p>
                            <p class="text-xs text-primary font-bold">+62 857-8510-1487</p>
                        </div>
                        <div class="w-[1px] bg-gray-200"></div>
                        <div class="flex flex-col items-center gap-1">
                            <p class="text-xs font-semibold text-text-2">Lokasi</p>
                            <p class="text-xs text-primary font-bold">Ruang Admin Kantin</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-6">
                <a href="../index.php"
                   class="w-full h-[50px] bg-submit rounded-[15px] text-[16px] font-bold tracking-[2px] text-white uppercase cursor-pointer hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center">
                    Kembali ke Login
                </a>
            </div>

        </div>
    </div>

</body>
</html>