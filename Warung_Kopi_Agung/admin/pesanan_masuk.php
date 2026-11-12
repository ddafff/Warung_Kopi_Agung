<?php
// SEMENTARA (Fase 1). Halaman sebenarnya dibuat di Fase 2.
require_once '../koneksi.php';
require_once '../fungsi.php';
wajib_peran(['admin', 'superadmin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Masuk — Warung Kopi Agung</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/CSS/tampilan.css">
</head>
<body class="badan-halaman">
<main class="konten-utama"><div class="kontainer-sempit">
    <div class="kartu">
        <h1 class="judul-halaman">Pesanan Masuk</h1>
        <p class="teks-pendukung">Halaman ini dibuat pada tahap berikutnya.</p>
        <p><a class="tombol tombol-sekunder" href="<?= BASE_URL ?>/user/logout.php">Keluar</a></p>
    </div>
</div></main>
</body>
</html>
