<?php
// halaman_error.php — templat satu-satunya untuk 403.php dan 404.php
// Variabel dari pemanggil: $kode, $judul, $isi
session_start();

// Tombol Kembali menyesuaikan peran
$kembali = 'user/us_index.php';
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')      $kembali = 'admin/pesanan_masuk.php';
if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin') $kembali = 'superadmin/dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $kode ?> <?= $judul ?> - Warung Kopi Agung</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Warung_Kopi_Agung/CSS/tampilan.css">
</head>
<body class="badan-halaman">
    <main class="halaman-error">
        <p class="angka-error"><?= $kode ?></p>
        <h1 class="judul-halaman"><?= $judul ?></h1>
        <p class="teks-pendukung"><?= $isi ?></p>
        <a class="tombol tombol-utama" href="/Warung_Kopi_Agung/<?= $kembali ?>">Kembali</a>
    </main>
</body>
</html>
