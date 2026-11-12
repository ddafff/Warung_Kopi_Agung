<?php
// user/us_header.php — bagian atas halaman pelanggan (header hijau)
// Variabel dari halaman: $judul (judul tab), $menu_aktif, $kelas_isi (lebar isi, boleh kosong)
$role       = $_SESSION['role'] ?? '';
$judul      = $judul ?? 'Warung Kopi Agung';
$menu_aktif = $menu_aktif ?? '';
$kelas_isi  = $kelas_isi ?? 'kontainer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul ?> - Warung Kopi Agung</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/tampilan.css">
</head>
<body class="badan-halaman punya-nav-bawah">

<header class="kepala-situs">
    <div class="kontainer kepala-isi">
        <a class="merek" href="us_index.php">
<<<<<<< HEAD
            <?php if (file_exists('../gambar/logo.png')): ?>
                <img class="logo-bulat" src="../gambar/logo.png" alt="Logo">
=======
            <?php if (file_exists('../gambar/logo_warder.jpeg')): ?>
                <img class="logo-bulat" src="../gambar/logo_warder.jpeg" alt="Logo">
>>>>>>> d33efa4
            <?php else: ?>
                <span class="logo-bulat"><i class="fa-solid fa-mug-hot"></i></span>
            <?php endif; ?>
            <span class="nama-merek">WARUNG KOPI AGUNG</span>
        </a>

        <nav class="navigasi-atas">
            <?php if ($role == 'pelanggan'): ?>
                <a class="tautan-nav <?= $menu_aktif == 'menu' ? 'aktif' : '' ?>" href="us_index.php">Menu</a>
                <a class="tautan-nav <?= $menu_aktif == 'riwayat' ? 'aktif' : '' ?>" href="riwayat_pesanan.php">Riwayat</a>
            <?php endif; ?>
            <?php if ($role == 'admin'): ?>
                <a class="tautan-nav" href="../admin/pesanan_masuk.php"><i class="fa-solid fa-gauge"></i> Panel Admin</a>
            <?php endif; ?>
            <?php if ($role == 'superadmin'): ?>
                <a class="tautan-nav" href="../superadmin/dashboard.php"><i class="fa-solid fa-gauge"></i> Panel Admin</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="info-pengguna"><i class="fa-solid fa-circle-user"></i>
                    <span class="nama-pengguna"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span></span>
                <a class="tombol tombol-putih" href="logout.php">Keluar</a>
            <?php else: ?>
                <a class="tombol tombol-putih" href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="konten-utama">
<div class="<?= $kelas_isi ?>">
<?php include '../pesan.php'; ?>
