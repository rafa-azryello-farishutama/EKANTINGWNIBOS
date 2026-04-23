<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="assets/css/style.css">

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "background": "#f9f9fb",
                        "primary": "#004900",
                        "second-primary": "#eef1f0",
                        "input": "#f3f3f5",
                        "textstual1": "#004800",
                        "textstual2": "#404a3b",
                        "textstual3": "#5e6659",
                        "submit": "#005300"
                    }
                }
            }
        }
    </script>
</head>
<body class="h-screen bg-background">
    <div class="flex flex-col h-full overflow-y-scroll">

        <div class="flex flex-row items-center w-full flex-shrink-0 h-[60px] px-[15px] gap-[14px] bg-white shadow-sm">
            <img src="../assets/img/Person.png" class="w-[20px] h-auto">
            <p class="font-headline text-[20px] font-bold text-textstual1">E-Kantin</p>
        </div>

        <div class="flex justify-center w-full flex-1 bg-second-primary p-[20px]">
            <div class="flex flex-col bg-white rounded-[30px] w-full h-full p-[20px] gap-[8px]">

                <div class="flex flex-col mt-[10px] text-left gap-[10px]">
                <h2 class="font-headline text-4xl font-bold text-text-1">Register Akun Pembeli</h2>
                <p class="font-medium text-base text-textstual2">Silahkan isi data diri Anda untuk memulai pengalaman e-kantin</p>
                </div>

                <div class="flex flex-col gap-[15px] w-full p-[10px] mt-[25px]">
                    <div class="flex flex-col gap-[2px] w-full h-1/2">
                    <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">Username</p>
                     <div class="w-full h-[50px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                        <img src="../assets/img/person1.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        <input type="text" name="username" class="border-none bg-input outline-none text-[15px] text-zinc-950 w-full
                            focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Tambahkan Username Anda">
                    </div>
                    </div>

                    <div class="flex flex-col gap-[2px] w-full h-1/2">
                    <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">Password</p>
                     <div class="w-full h-[60px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                        <img src="../assets/img/password1.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        <input type="password" name="password" class="border-none bg-input outline-none text-[15px] text-zinc-950 w-full
                            focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Tambahkan Password Anda">
                    </div>
                    </div>

                    <div class="flex flex-col gap-[2px] w-full h-1/2">
                    <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">No. Telepon</p>
                     <div class="w-full h-[60px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                        <img src="../assets/img/phone1.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        <input type="text" name="phone" class="border-none bg-input outline-none text-[15px] text-zinc-950 w-full
                            focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Masukkan Nomor Telepon Anda">
                    </div>
                    </div>

                    <div class="flex flex-col gap-[2px] w-full h-1/2">
                    <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">Alamat</p>
                     <div class="w-full h-[80px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                        <img src="../assets/img/address1.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        <input type="text" name="address" class="border-none bg-input outline-none text-[15px] text-zinc-950 w-full
                            focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Masukkan Alamat Anda">
                    </div>
                    </div>

                    <div class="flex flex-col gap-[2px] w-full h-1/2">
                    <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">Email</p>
                     <div class="w-full h-[60px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                        <img src="../assets/img/email1.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
                        <input type="text" name="address" class="border-none bg-input outline-none text-[15px] text-zinc-950 w-full
                            focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Masukkan Email Anda">
                    </div>
                    </div>
                </div>

                <div class="flex w-full px-[20px] justify-center">
                    <button
                    type="submit" name="submit"
                    class="w-full h-[60px] bg-submit rounded-[15px]
                    text-[15px] font-medium tracking-[1px] text-white
                    uppercase cursor-pointer mt-[20px]
                    hover:opacity-90 active:scale-[0.98] transition-all
                    focus:outline-none focus:ring-0 border-none outline-none">REGISTER
                    </button>
                </div>

                <div class="mt-12 flex flex-col items-center text-center">
                    <p class="text-text-1 font-medium text-sm">
                    Sudah punya akun?
                    <a class="text-primary font-bold ml-1 hover:underline underline-offset-4 decoration-2" href="../index.php">Kembali ke Awal</a>
                    </p>
                </div>
                
                <footer class="flex flex-row justify-center gap-[12px] mt-[30px]">
                    <p class="text-textstual1 text-bold">Privacy</p>
                    <p class="text-textstual1 text-bold">Support</p>
                </footer>
            </div>
        </div>
    </div>
</body>
</html>