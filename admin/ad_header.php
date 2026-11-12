<?php
// admin/ad_header.php — bagian atas halaman admin (header hijau + menu samping)
// Juga dipakai halaman superadmin lewat superadmin/sa_header.php
// Variabel dari halaman: $judul (judul tab), $menu_aktif ('pesanan', 'antrean', 'pengguna')
$judul      = $judul ?? 'Panel Admin';
$menu_aktif = $menu_aktif ?? '';
$role       = $_SESSION['role'];

// Alamat folder admin dan superadmin dari halaman ini (halaman superadmin mengisinya di sa_header.php)
$ke_admin = $ke_admin ?? '';
$ke_sa    = $ke_sa ?? '../superadmin/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul ?> - Admin Warung Kopi Agung</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/tampilan.css">
    <link rel="stylesheet" href="../CSS/Admin.css">
</head>
<body class="badan-halaman">

<header class="kepala-situs">
    <div class="kontainer kepala-isi">
        <div class="merek">
            <button class="tombol-menu" type="button" onclick="bukaMenu()"><i class="fa-solid fa-bars"></i></button>
            <a class="merek" href="<?= $ke_admin ?>pesanan_masuk.php">
                <?php if (file_exists('../gambar/logo_warder.jpeg')): ?>
                    <img class="logo-bulat" src="../gambar/logo_warder.jpeg" alt="Logo">
                <?php else: ?>
                    <span class="logo-bulat"><i class="fa-solid fa-mug-hot"></i></span>
                <?php endif; ?>
                <span class="nama-merek">WARUNG KOPI AGUNG</span>
            </a>
        </div>
        <div class="info-pengguna">
            <span class="penanda-peran"><i class="fa-solid fa-user-shield"></i> <?= $role == 'superadmin' ? 'Pemilik' : 'Kasir' ?></span>
            <span class="nama-pengguna"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        </div>
    </div>
</header>

<div class="rangka-admin">
    <div class="latar-menu-samping" id="latar-menu" onclick="tutupMenu()"></div>

    <?php include 'ad_menu.php'; ?>

    <main class="konten-admin">
    <div class="kontainer">
    <?php include '../pesan.php'; ?>
