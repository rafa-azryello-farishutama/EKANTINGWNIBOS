<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "background": "#f7f8f9",
                        "primary": "#004900",
                        "input": "#f3f3f5",
                        "text-1": "#191c1c",
                        "text-2": "#4e5a48",
                        "submit": "#005300"
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center p-4 md:p-10">
    <div class="fixed top-0 left-0 w-full h-full bg-black/50 flex justify-center items-center z-[9999] p-4">
        
        <div class="bg-white rounded-[20px] py-[35px] px-[30px] md:py-[50px] md:px-[50px] flex flex-col items-center gap-[20px] md:gap-[30px] w-full max-w-[320px] md:max-w-[400px] lg:max-w-[450px] shadow-2xl transition-all">
            
            <div class="flex flex-row justify-center items-center gap-[10px]">
                <img src="../assets/img/Garpusendok.png" class="w-[20px] md:w-[28px] h-auto">
                <p class="font-bold text-lg md:text-2xl text-primary tracking-tight">E-Kantin</p>
            </div>

            <div class="flex justify-center items-center">
                <div class="w-24 h-24 md:w-32 md:h-32 bg-primary/10 rounded-full flex justify-center items-center p-3">
                    <div class="w-full h-full rounded-full bg-primary flex justify-center items-center shadow-lg">
                        <img src="../assets/img/centang2.png" alt="Success" class="w-[28px] md:w-[40px] h-auto brightness-0 invert">
                    </div>
                </div>
            </div>

            <div class="flex flex-col text-center justify-center items-center gap-[10px] md:gap-[15px]">
                <h2 class="font-bold text-xl md:text-3xl text-text-1">Pendaftaran Berhasil!</h2>
                <p class="text-sm md:text-lg text-text-2 leading-relaxed px-2">
                    Akun Anda telah berhasil didaftarkan. Silakan masuk untuk mulai memesan makanan sehat.
                </p>
            </div>

            <div class="w-full pt-2 md:pt-4">
                <a href="../index.php" class="w-full h-[50px] md:h-[65px] bg-submit rounded-[15px] text-[15px] md:text-[18px] text-white font-bold tracking-widest hover:opacity-90 active:scale-[0.98] transition-all shadow-lg shadow-primary/10 uppercase flex items-center justify-center gap-2">
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>