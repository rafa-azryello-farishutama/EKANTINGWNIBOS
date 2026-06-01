<?php
$localhost = "localhost";
$user = "root";
$password = "";
$hostname = "ekantinsmea";

$db_ekantin = mysqli_connect($localhost, $user, $password, $hostname);

if($db_ekantin->connect_error){
    echo "Database tidak terkoneksi";
    die("error!");
}

// Global Security Check: Kick out deactivated users immediately
$check_id = null;
if (isset($_SESSION['pembeli_id_users'])) {
    $check_id = $_SESSION['pembeli_id_users'];
} elseif (isset($_SESSION['penjual_id_users'])) {
    $check_id = $_SESSION['penjual_id_users'];
}

if ($check_id) {
    $check_status = $db_ekantin->query("SELECT status FROM users WHERE id_users = '$check_id'");
    if ($check_status && $check_status->num_rows > 0) {
        $user_row = $check_status->fetch_assoc();
        if ($user_row['status'] === 'nonaktif') {
            session_unset();
            session_destroy();
            echo "<script>alert('Akses Ditolak: Akun Anda telah dinonaktifkan oleh Admin!'); window.location.href='/EKANTIN_SMEA/index.php';</script>";
            exit;
        }
    }
}
?>