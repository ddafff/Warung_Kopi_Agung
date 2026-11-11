<?php
// 404.php — Halaman tidak ditemukan (juga dipakai untuk "Meja tidak ditemukan")
http_response_code(404);
$kode  = 404;
$judul = 'Halaman tidak ditemukan';
$isi   = 'Halaman yang Anda cari tidak ada.';
if (isset($_GET['pesan']) && $_GET['pesan'] == 'meja') {
    $isi = 'Meja tidak ditemukan';
}
include 'halaman_error.php';
?>
