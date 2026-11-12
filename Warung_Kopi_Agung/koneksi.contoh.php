<?php
// SALINAN CONTOH (boleh di-commit). Salin menjadi koneksi.php lalu isi data asli.
// Berkas koneksi.php TIDAK boleh di-commit (sudah ada di .gitignore).

date_default_timezone_set('Asia/Jakarta');

// Alamat dasar situs, tanpa garis miring di akhir.
// Untuk uji dari HP gunakan IP jaringan lokal, contoh: http://192.168.1.10/Warung_Kopi_Agung
define('BASE_URL', 'http://localhost/Warung_Kopi_Agung');

// Pemantauan status otomatis (milidetik)
define('INTERVAL_PANTAU', 5000);

// Midtrans Sandbox (JANGAN pakai kunci Production)
define('MIDTRANS_SERVER_KEY', 'ISI_SERVER_KEY_SANDBOX');
define('MIDTRANS_BASE_URL', 'https://api.sandbox.midtrans.com');

// Basis data
$db_host = 'localhost';
$db_nama = 'warung_kopi_agung';
$db_user = 'root';
$db_pass = '';

// Galat tidak ditampilkan ke pengguna, hanya dicatat ke logs/app.log
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/app.log');
error_reporting(E_ALL);

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_nama;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE         => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] Koneksi basis data gagal: ' . $e->getMessage() . "\n", 3, __DIR__ . '/logs/app.log');
    http_response_code(500);
    echo 'Terjadi kesalahan, silakan coba lagi.';
    exit;
}
