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
?>