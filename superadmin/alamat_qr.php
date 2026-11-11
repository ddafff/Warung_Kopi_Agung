<?php
// superadmin/alamat_qr.php — alamat yang dimasukkan ke QR code meja. Hasil: $alamat_qr (diakhiri "?meja=")
// BASE_URL diatur di koneksi.php. Bila belum ada, dipakai alamat yang sedang dibuka.
if (defined('BASE_URL')) {
    $alamat_qr = BASE_URL;
} else {
    $alamat_qr = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME']));
}
$alamat_qr = rtrim($alamat_qr, '/') . '/user/us_index.php?meja=';
?>
