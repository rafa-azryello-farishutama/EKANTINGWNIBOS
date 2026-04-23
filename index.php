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
<body class="bg-[url('assets/img/fotoBackground3.jpg')] bg-cover bg-center  min-h-screen 
            flex items-center justify-center">

  <div class="flex flex-col h-screen w-screen bg-background gap-[10px] 
              md:w-[500px] md:h-auto md:rounded-[30px] md:p-[20px]
              lg:w-[900px] lg:h-[600px] lg:flex-row lg:p-0
              lg:rounded-[30px] lg:overflow-hidden"> 

    <div class="hidden lg:flex flex-col justify-between w-[45%] h-full bg-primary p-10 lg:rounded-l-[30px]">
            <img src="assets/img/logoBaru1.png" 
                 class="w-[180px] h-auto"/>

            <div>
                <h2 class="text-white font-bold text-3xl leading-tight mb-3">
                    Selamat Datang di e-Kantin
                </h2>
                <p class="text-white/70 text-sm leading-relaxed">
                    Pesan makanan favoritmu dengan mudah dan cepat.
                </p>
            </div>
    </div>

    <div class="flex flex-col flex-1 h-full overflow-y-auto no-scrollbar px-[20px] py-[30px] md:px-[40px] md:py-[40px]">

    <div class="flex justify-center items-center">
      <img src="assets/img/logoBaru1.png" class="w-[256px] h-auto lg:hidden">
    </div>

    <div class="flex flex-col mt-[10px] justify-center text-center gap-[10px]">
      <h2 class="font-headline text-3xl font-bold text-text-1">Welcome Back</h2>
      <p class="font-medium text-text-2">Tolong masukkan informasi penting anda untuk mengakses akun</p>
    </div>

    <div class="flex flex-col gap-[13px] w-full p-[20px]">

      <div class="flex flex-col gap-[2px] w-full h-1/2">
        <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">Username</p>
        <div class="w-full h-[60px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                  <input type="text" name="username" class="border-none bg-input outline-none text-[18px] text-zinc-950 w-full
                        focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Tambahkan Username Anda">
                  <img src="assets/img/Person.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
          </div>
      </div>

      <div class="flex flex-col gap-[2px] w-full h-1/2">
        <p class="font-label text-xs font-semibold uppercase tracking-widest text-on-surface-variant ml-1">Password</p>
        <div class="w-full h-[60px] bg-input rounded-[15px] flex items-center gap-[10px] border-box py-0 px-[15px]">
                  <input type="password" name="password" class="border-none bg-input outline-none text-[18px] text-zinc-950 w-full
                        focus:ring-0 focus:outline-none focus:border-transparent" placeholder="Tambahkan Password Anda">
                  <img src="assets/img/Key.png" class="w-[20px] h-auto opacity-60 flex-shrink-0">
          </div>
      </div>
    </div>

    <div class="flex w-full px-[20px]">
     <button
        type="submit" name="submit"
        class="w-full h-[60px] bg-submit rounded-[15px]
               text-[20px] font-bold tracking-[2px] text-white
               uppercase cursor-pointer mt-[20px]
               hover:opacity-90 active:scale-[0.98] transition-all
               focus:outline-none focus:ring-0 border-none outline-none">SUBMIT
     </button>
    </div>

    <footer class="mt-12 text-center">
      <p class="text-text-1 font-medium text-sm">
          New to e-Kantin? 
          <a class="text-primary font-bold ml-1 hover:underline underline-offset-4 decoration-2" href="#">Register Now</a>
      </p>

      <p class="text-text-3 font-semibold text-base tracking-widest mt-[8px] mb-[8px]">OR</p>

      <p class="text-text-1 font-medium text-sm">
          Lupa Sandi? 
          <a class="text-primary font-bold ml-1 hover:underline underline-offset-4 decoration-2" href="#">Request</a>
      </p>
    </footer>
    </div>
  </div>
    
    
</body>
</html>