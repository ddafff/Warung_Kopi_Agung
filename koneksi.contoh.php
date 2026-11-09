<?php
// koneksi.contoh.php — SALINAN CONTOH tanpa rahasia. Salin menjadi koneksi.php lalu isi.
date_default_timezone_set('Asia/Jakarta');

$host   = 'localhost';
$dbname = 'warung_kopi_agung';
$user   = 'root';
$pass   = '';

// Alamat situs. Dipakai untuk isi QR code meja.
// Uji dari HP: ganti localhost dengan IP laptop, contoh http://192.168.1.10/Warung_Kopi_Agung
define('BASE_URL', 'http://localhost/Warung_Kopi_Agung');

// Kunci Midtrans SANDBOX (bukan Production)
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-rSKSP9~YM1.-IC4_aclQDOsw');
define('MIDTRANS_URL', 'https://api.sandbox.midtrans.com');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Koneksi database gagal. Periksa pengaturan di koneksi.php');
}
?>
