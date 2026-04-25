<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Kantin | Lupa Password</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
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

<body class="bg-background min-h-screen flex flex-col items-center justify-center overflow-y-scroll">

    <div class="absolute top-4 left-1/2 -translate-x-1/2 md:top-8 md:left-10 md:translate-x-0 lg:left-12 z-20">
        <img src="../assets/img/logoBaru1.png" alt="Logo e-Kantin" class="w-[140px] md:w-[160px] lg:w-[180px] h-auto">
    </div>

    <div class="w-full max-w-[1440px] px-6 py-12 flex items-center justify-center">

        <div class="w-full max-w-lg">

            <div class="text-center mb-8 mt-16 md:mt-4">
                <h2 class="text-3xl font-extrabold text-primary mb-2 tracking-tight">Lupa Password?</h2>
                <p class="text-text-2 font-medium text-base">Ikuti langkah berikut untuk mereset password Anda</p>
            </div>

            <div class="bg-white rounded-[2rem] p-8 sm:p-10 shadow-[0_32px_64px_-16px_rgba(0,73,0,0.08)] border border-gray-100 flex flex-col gap-6">

                <div class="flex flex-col gap-[6px]">
                    <p class="text-xs font-semibold uppercase tracking-widest text-text-3">Panduan Reset Password</p>
                    <p class="text-sm text-text-2">Anda tidak dapat mereset password sendiri. Silakan hubungi admin dengan membawa informasi berikut.</p>
                </div>

                <div class="flex flex-col gap-4">

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">1</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Siapkan Identitas Diri</p>
                            <p class="text-sm text-text-2 leading-relaxed">Siapkan identitas diri Anda seperti <span class="font-semibold text-primary">Kartu Pelajar / KTM</span> atau dokumen resmi lainnya yang memuat nama lengkap Anda.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">2</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Ingat Username Anda</p>
                            <p class="text-sm text-text-2 leading-relaxed">Pastikan Anda mengingat <span class="font-semibold text-primary">username</span> akun yang terdaftar. Admin akan memverifikasi username Anda sebelum mereset password.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">3</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Temui Admin e-Kantin</p>
                            <p class="text-sm text-text-2 leading-relaxed">Datang langsung ke <span class="font-semibold text-primary">ruang admin / kantin</span> dan sampaikan bahwa Anda ingin mereset password akun e-Kantin Anda.</p>
                        </div>
                    </div>

                    <div class="flex flex-row items-start gap-4 p-4 bg-[#f3f3f5] rounded-[15px]">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-[1px]">
                            <span class="text-white text-xs font-bold">4</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-semibold text-text-1">Password Direset oleh Admin</p>
                            <p class="text-sm text-text-2 leading-relaxed">Setelah identitas diverifikasi, admin akan mereset password Anda. Anda akan mendapatkan <span class="font-semibold text-primary">password sementara</span> dan disarankan untuk segera menggantinya.</p>
                        </div>
                    </div>

                </div>

                <div class="flex flex-col gap-3 pt-2 border-t border-gray-100">
                    <p class="text-xs text-text-3 text-center">Butuh bantuan lebih lanjut? Hubungi admin melalui:</p>
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
                <a href="../index.php" class="w-full h-[50px] bg-submit rounded-[15px] text-[16px] font-bold tracking-[2px] text-white uppercase cursor-pointer hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center">
                Kembali ke Login
                </a>
            </div>

        </div>
    </div>

</body>
</html>