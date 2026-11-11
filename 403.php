<?php
// 403.php — Akses ditolak
http_response_code(403);
$kode  = 403;
$judul = 'Akses ditolak';
$isi   = 'Anda tidak memiliki izin untuk membuka halaman ini.';
include 'halaman_error.php';
?>
