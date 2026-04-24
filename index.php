<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Kantin | Login</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

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

<body class="bg-background min-h-screen relative flex-col items-center justify-center relative overflow-hidden">

    <div class="absolute top-4 left-1/2 mb-[20px] -translate-x-1/2 md:top-8 md:left-10 md:translate-x-0 lg:left-12 z-20">
        <img src="assets/img/logoBaru1.png" alt="Logo e-Kantin" class="w-[140px] md:w-[160px] lg:w-[180px] h-auto">
    </div>

    <div class="w-full max-w-[1440px] px-6 py-12 flex items-center justify-center relative z-10">
        
        <div class="w-full max-w-md">
            
            <div class="text-center mb-10 mt-12 md:mt-0">
                <h2 class="text-4xl font-extrabold text-primary mb-2 tracking-tight">Selamat Datang</h2>
                <p class="text-text-2 font-medium text-lg">di e-Kantin</p>
            </div>

            <div class="bg-white rounded-[2rem] p-8 sm:p-10 shadow-[0_32px_64px_-16px_rgba(0,73,0,0.08)] border border-gray-100 flex flex-col gap-6">

                <div class="text-center">
                    <p class="font-medium text-text-3 text-sm">Tolong masukkan informasi penting Anda untuk mengakses akun</p>
                </div>

                <div class="flex flex-col gap-[15px] w-full mt-2">
                    <div class="flex flex-col gap-[4px] w-full">
                        <p class="font-label text-xs font-semibold uppercase tracking-widest text-text-2 ml-1">Username</p>
                        <div class="w-full h-[50px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                            <input type="text" name="username" class="border-none bg-transparent outline-none text-[16px] text-zinc-950 w-full focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Tambahkan Username Anda">
                            <img src="assets/img/Person.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        </div>
                    </div>

                    <div class="flex flex-col gap-[4px] w-full">
                        <p class="font-label text-xs font-semibold uppercase tracking-widest text-text-2 ml-1">Password</p>
                        <div class="w-full h-[50px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                            <input type="password" name="password" class="border-none bg-transparent outline-none text-[16px] text-zinc-950 w-full focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Tambahkan Password Anda">
                            <img src="assets/img/Key.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        </div>
                    </div>
                </div>

                <div class="flex justify-center w-full mt-4">
                    <button type="submit" name="submit" class="w-full h-[50px] bg-submit rounded-[15px] text-[18px] font-bold tracking-[2px] text-white uppercase cursor-pointer hover:opacity-90 active:scale-[0.98] transition-all focus:outline-none focus:ring-0 border-none outline-none">
                        MASUK
                    </button>
                </div>

            </div>

            <div class="mt-8 text-center flex flex-col gap-3">
                <p class="text-text-1 font-medium text-sm">
                    Tidak punya Akun? <a class="text-primary font-bold ml-1 hover:underline underline-offset-4 decoration-2" href="apps/register.php">Daftar Sekarang</a>
                </p>
                <p class="text-text-1 font-medium text-sm">
                    Lupa Sandi? <a class="text-primary font-bold ml-1 hover:underline underline-offset-4 decoration-2" href="#">Request</a>
                </p>
            </div>

        </div>
    </div>
    
</body>
</html>