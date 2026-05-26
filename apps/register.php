<?php
include "../config/koneksi.php";

$show_popup = false;
$error_register = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $role = "pembeli";
    $tipe = in_array($_POST['tipe'] ?? '', ['kelas10','kelas11','kelas12','guru']) ? $_POST['tipe'] : 'kelas10';
    $username = $_POST['username'];

    if(strlen($username) < 3 || strlen($username) > 20){
        $error_register = "Username harus antara 3-20 karakter.";
    } else if(!preg_match('/^[a-zA-Z0-9_.]+$/', $username)){
        $error_register = "Username hanya boleh huruf, angka, underscore, dan titik.";
    }else if(strpos($username, ' ') !== false){
        $error_register = "Username tidak boleh mengandung spasi.";
    } else {
        $password = $_POST['password'];
        $telepon = $_POST['phone'];
        $email = $_POST['email'];

        $stmtCekUser = $db_ekantin->prepare("SELECT id_users FROM users WHERE username = ?");
        $stmtCekUser->bind_param("s", $username);
        $stmtCekUser->execute();
        $cekUsername = $stmtCekUser->get_result();
        if($cekUsername->num_rows > 0){
            $error_register = "Username sudah digunakan, silakan pilih yang lain.";
        }
        
        $stmtCekEmail = $db_ekantin->prepare("SELECT id_users FROM users WHERE email = ?");
        $stmtCekEmail->bind_param("s", $email);
        $stmtCekEmail->execute();
        $cekEmail = $stmtCekEmail->get_result();
        if($cekEmail->num_rows > 0){
            $error_register = "Email sudah terdaftar, silakan gunakan email lain.";
        }

        if($error_register == ""){
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            $stmtInsert = $db_ekantin->prepare("INSERT INTO users(username, password, role, tipe, no_telepon, email) VALUES(?, ?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("ssssss", $username, $hash_password, $role, $tipe, $telepon, $email);

            if($stmtInsert->execute()){
                $show_popup = true;
            }
        }
    }
}
?>

<?php 
if($show_popup) {
    include "../includes/popup.php"; 
}

if($error_register != "") {
    echo "<script>alert('$error_register');</script>";
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

    <div class="flex flex-col lg:flex-row w-full max-w-[1000px] h-[90vh] lg:h-[650px] lg:max-h-[90vh] bg-white rounded-[30px] shadow-[0_20px_50px_rgba(0,0,0,0.1)] overflow-hidden">
        
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
                            <input type="text" name="username"  pattern="[a-zA-Z0-9_.]{3,20}" oninput="this.value = this.value.replace(/[^a-zA-Z0-9_.]/g, '')"
                                    maxlength="20" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="Username Anda" required>
                            <img src="../assets/img/Person.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Password</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="password" name="password" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="••••••••" required>
                            <img src="../assets/img/Key.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">No. Telepon</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="tel" name="phone" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="0812xxxx" required>
                            <img src="../assets/img/phone1.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Email</p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="email" name="email" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="email@contoh.com" required>
                            <img src="../assets/img/email1.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <!-- Pilihan Tipe Akun -->
                    <div class="flex flex-col gap-2">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Daftar Sebagai</p>
                        <div class="grid grid-cols-2 gap-2">

                            <label class="relative cursor-pointer">
                                <input type="radio" name="tipe" value="kelas10" class="sr-only peer" checked>
                                <div class="flex items-center gap-2 p-3 bg-input rounded-[12px] border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 peer-checked:bg-blue-200 flex items-center justify-center flex-shrink-0 text-sm">📚</div>
                                    <div>
                                        <p class="text-xs font-bold text-text-1">Kelas 10</p>
                                        <p class="text-[10px] text-text-3">Siswa baru</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="tipe" value="kelas11" class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 bg-input rounded-[12px] border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0 text-sm">📖</div>
                                    <div>
                                        <p class="text-xs font-bold text-text-1">Kelas 11</p>
                                        <p class="text-[10px] text-text-3">Siswa lanjut</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="tipe" value="kelas12" class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 bg-input rounded-[12px] border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 text-sm">🎓</div>
                                    <div>
                                        <p class="text-xs font-bold text-text-1">Kelas 12</p>
                                        <p class="text-[10px] text-text-3">Siswa senior</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="tipe" value="guru" class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 bg-input rounded-[12px] border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 text-sm">👨‍🏫</div>
                                    <div>
                                        <p class="text-xs font-bold text-text-1">Guru</p>
                                        <p class="text-[10px] text-text-3">Tenaga pengajar</p>
                                    </div>
                                </div>
                            </label>

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