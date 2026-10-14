<?php
// koneksi.php (tidak berubah, hanya ditambah charset)
$host     = "localhost";
$username = "root";
$password = "";
$database = "bank_sampah_rifky";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($koneksi, "utf8mb4");
?>
