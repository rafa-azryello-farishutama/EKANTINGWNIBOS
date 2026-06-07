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

        if($tipe === 'guru' && $error_register == "") {
            $kode_input = trim($_POST['kode_guru'] ?? '');
            
            // Buat tabel jika belum ada (sekadar fallback)
            $db_ekantin->query("CREATE TABLE IF NOT EXISTS pengaturan (kunci VARCHAR(100) PRIMARY KEY, nilai TEXT)");
            $resKode = $db_ekantin->query("SELECT nilai FROM pengaturan WHERE kunci='kode_guru'");
            $dataKode = $resKode->fetch_assoc();
            $kode_valid = $dataKode ? $dataKode['nilai'] : 'GURU2025';
            
            if (strtoupper($kode_input) !== strtoupper($kode_valid)) {
                $error_register = "Kode Guru yang Anda masukkan salah!";
            }
        }

        if($error_register == ""){
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            $stmtInsert = $db_ekantin->prepare("INSERT INTO users(username, password, role, tipe, no_telepon, email, status) VALUES(?, ?, ?, ?, ?, ?, 'pending')");
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-background min-h-screen flex items-center justify-center p-4 md:p-10 relative overflow-hidden">

    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-[10%] -left-[5%] w-[40%] h-[60%] rounded-full bg-primary/5 blur-[100px]"></div>
    </div>

    <div class="flex flex-col lg:flex-row w-full max-w-[1000px] h-[90vh] lg:h-[650px] lg:max-h-[90vh] bg-white rounded-[30px] shadow-[0_20px_50px_rgba(0,0,0,0.1)] overflow-hidden">
        
        <div class="hidden lg:flex w-[40%] relative p-12 flex-col justify-between text-white shrink-0 overflow-hidden bg-primary">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="../assets/img/fotoBackground3.jpg" class="w-full h-full object-cover opacity-40 mix-blend-overlay" alt="Background">
                <div class="absolute inset-0 bg-primary/80"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/60 to-transparent"></div>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-[-10%] right-[-10%] w-64 h-64 bg-white/20 rounded-full blur-3xl z-0"></div>
            <div class="absolute bottom-[20%] left-[-20%] w-80 h-80 bg-white/20 rounded-full blur-3xl z-0"></div>

            <div class="relative z-10 animate-[fadeInDown_0.6s_ease-out_forwards]">
                <div class="bg-white/10 backdrop-blur-md w-fit p-4 rounded-[20px] border border-white/20 mb-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)]">
                    <img src="../assets/img/logoBaru1.png" class="w-[140px] h-auto drop-shadow-lg"/>
                </div>
                <p class="text-white/80 font-bold tracking-widest uppercase text-[10px]">Aplikasi Kantin Digital</p>
            </div>
            
            <div class="relative z-10 animate-[fadeInUp_0.6s_ease-out_forwards]" style="animation-delay: 0.2s; opacity: 0;">
                <h2 class="text-4xl lg:text-[2.5rem] font-extrabold leading-[1.1] mb-5 font-headline drop-shadow-sm">Mulai Pesan,<br><span class="text-amber-300">Tanpa Antre!</span></h2>
                <p class="text-white/80 text-[13px] leading-relaxed max-w-sm mb-8 font-medium">
                    Buat akun sekarang dan rasakan kemudahan menikmati makanan favoritmu di sekolah.
                </p>
                
                <div class="flex items-center gap-3 p-3 bg-black/20 backdrop-blur-md border border-white/10 rounded-2xl w-fit">
                    <div class="flex -space-x-3">
                        <div class="w-9 h-9 rounded-full bg-blue-100 border-2 border-primary flex items-center justify-center text-sm shadow-md">🧑‍🎓</div>
                        <div class="w-9 h-9 rounded-full bg-amber-100 border-2 border-primary flex items-center justify-center text-sm shadow-md z-10">👨‍🏫</div>
                        <div class="w-9 h-9 rounded-full bg-green-100 border-2 border-primary flex items-center justify-center text-sm shadow-md z-20">👩‍🎓</div>
                    </div>
                    <p class="text-[10px] text-white font-semibold leading-tight pr-2">Bergabung bersama<br><span class="text-amber-300">Warga E-Kantin</span> lainnya.</p>
                </div>
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
                            <input type="tel" name="phone" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="0812xxxx" required>
                            <img src="../assets/img/phone1.png" class="w-5 h-5 opacity-40">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-3 ml-1">Email <span class="normal-case font-normal text-red-500">(wajib .com)</span></p>
                        <div class="w-full h-[55px] bg-input rounded-[15px] flex items-center gap-3 px-4">
                            <input type="email" name="email" pattern=".*\.com$" title="Harap masukkan email yang valid dengan akhiran .com" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0" placeholder="email@contoh.com" required>
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

                    <div id="kode-guru-container" class="hidden flex-col gap-1 mt-2 p-4 bg-amber-50 border border-amber-200 rounded-[15px]">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-amber-700 ml-1">Kode Pendaftaran Guru</p>
                        <div class="w-full h-[55px] bg-white rounded-[12px] flex items-center px-4 border border-amber-100">
                            <input type="text" name="kode_guru" class="border-none bg-transparent outline-none text-[15px] text-text-1 w-full focus:ring-0 uppercase font-mono tracking-wider" placeholder="Masukkan Kode">
                        </div>
                        <p class="text-[10px] text-amber-600 ml-1 mt-1">Dapatkan kode ini dari pihak admin sekolah.</p>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipeRadios = document.querySelectorAll('input[name="tipe"]');
        const kodeGuruContainer = document.getElementById('kode-guru-container');
        const kodeGuruInput = document.querySelector('input[name="kode_guru"]');

        function updateKodeGuruVisibility() {
            const selectedTipe = document.querySelector('input[name="tipe"]:checked');
            if(selectedTipe && selectedTipe.value === 'guru') {
                kodeGuruContainer.classList.remove('hidden');
                kodeGuruContainer.classList.add('flex');
                kodeGuruInput.required = true;
            } else {
                kodeGuruContainer.classList.add('hidden');
                kodeGuruContainer.classList.remove('flex');
                kodeGuruInput.required = false;
                kodeGuruInput.value = '';
            }
        }

        tipeRadios.forEach(radio => {
            radio.addEventListener('change', updateKodeGuruVisibility);
        });

        // Initialize state on load
        updateKodeGuruVisibility();
    });
</script>
</html>