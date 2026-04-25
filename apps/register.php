<?php
include "../config/koneksi.php";

$show_popup = false;
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $role = "pembeli";
    $username = $_POST['username'];
    $password = $_POST['password'];
    $telepon = $_POST['phone'];
    $email = $_POST['email'];
    $alamat = $_POST['address'];

    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    $cek = "INSERT INTO users(username, password, role, no_telepon, email, alamat) 
                VALUES('$username','$hash_password','$role','$telepon','$email','$alamat')";

    if($db_ekantin->query($cek)){
            $id_user = $db_ekantin->insert_id;

            $db_ekantin->query("INSERT INTO pembeli(id_users, nama, no_telepon, email, alamat)
                                    VALUES('$id_user','$username','$telepon','$email','$alamat')");
            $show_popup = true;
        }
}
?>

<?php 
if($show_popup) {
    include "../includes/popup.php"; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-background min-h-screen flex items-center justify-center p-4 md:p-10 relative overflow-hidden">

    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-[10%] -left-[5%] w-[40%] h-[60%] rounded-full bg-primary/5 blur-[100px]"></div>
    </div>

    <div class="flex flex-col lg:flex-row w-full max-w-[1000px] h-[90vh] lg:h-[650px] bg-white rounded-[30px] shadow-[0_20px_50px_rgba(0,0,0,0.1)] overflow-hidden">
        
        <div class="hidden lg:flex w-[40%] bg-primary relative p-12 flex-col justify-between text-white shrink-0">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10">
                <img src="../assets/img/logoBaru1.png" class="w-[160px] h-auto opacity-90"/>
                <p class="mt-4 text-white/60 font-medium">E-Kantin</p>
            </div>
            <div class="relative z-10">
                <h2 class="text-4xl font-bold leading-tight mb-4 font-headline">Daftar Akun Baru</h2>
                <p class="text-white/70 text-sm leading-relaxed">
                    Buat akun dan mulai pesan makanan favoritmu dengan mudah dan cepat.
                </p>
            </div>
        </div>

        <div class="flex-1 flex flex-col h-full overflow-hidden bg-white">
            
            <div class="flex-1 overflow-y-auto no-scrollbar p-8 md:p-12">
                
                <div class="lg:hidden flex justify-center mb-8">
                    <img src="../assets/img/logoBaru1.png" class="w-[160px] h-auto">
                </div>

                <div class="mb-8 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-text-1">Daftar Sekarang</h2>
                    <p class="text-text-2 font-medium mt-1">Lengkapi data Anda untuk mendaftar.</p>
                </div>

                <form method="POST" class="space-y-4">
                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Username</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="text" name="username" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="Username Anda">
                            <img src="../assets/img/Person.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Password</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="password" name="password" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="••••••••">
                            <img src="../assets/img/Key.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">No. Telepon</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="tel" name="phone" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="0812xxxx">
                            <img src="../assets/img/phone1.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Email</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="email" name="email" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="email@contoh.com">
                            <img src="../assets/img/email1.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Alamat</p>
                        <div class="w-full bg-input rounded-[15px] flex items-start gap-3 p-4">
                            <textarea name="address" rows="2" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0 resize-none" placeholder="Masukkan alamat lengkap"></textarea>
                            <img src="../assets/img/address1.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full h-[55px] bg-submit rounded-[15px] text-white font-bold tracking-widest hover:opacity-90 active:scale-[0.98] transition-all shadow-lg shadow-primary/10 uppercase">
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center pb-4">
                    <p class="text-text-1 text-sm font-medium">
                        Sudah punya akun?
                        <a class="text-primary font-bold ml-1 hover:underline underline-offset-4" href="../index.php">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>