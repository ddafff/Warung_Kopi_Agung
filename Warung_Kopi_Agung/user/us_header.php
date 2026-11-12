<?php
// us_header.php — kerangka atas halaman pelanggan/umum.
// Variabel yang diisi halaman sebelum memanggil berkas ini:
//   $judul_halaman  : judul tab peramban
//   $tipe_halaman   : 'utama' (menu, riwayat; ada navigasi bawah di HP)
//                     'tumpuk' (checkout, pembayaran, status; ada tombol kembali)
//                     'polos'  (masuk, daftar; tanpa kepala)
//   $judul_kepala   : judul di kepala (untuk 'tumpuk')
//   $url_kembali    : tujuan tombol kembali (untuk 'tumpuk')
//   $menu_aktif     : 'menu' atau 'riwayat'
//   $ada_bilah_bawah: true bila ada bilah keranjang melayang di bawah
mulai_sesi();

$judul_halaman   = $judul_halaman   ?? 'Warung Kopi Agung';
$tipe_halaman    = $tipe_halaman    ?? 'utama';
$judul_kepala    = $judul_kepala    ?? 'Warung Kopi Agung';
$url_kembali     = $url_kembali     ?? (BASE_URL . '/user/us_index.php');
$menu_aktif      = $menu_aktif      ?? '';
$ada_bilah_bawah = $ada_bilah_bawah ?? false;

$peran       = $_SESSION['role'] ?? null;
$sudah_masuk = !empty($_SESSION['user_id']);
$kelas_badan = 'badan-halaman' . ($tipe_halaman === 'utama' ? ' punya-nav-bawah' : '');
$kelas_isi   = ($tipe_halaman === 'utama') ? 'kontainer' : 'kontainer-sempit';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($judul_halaman) ?> — Warung Kopi Agung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/CSS/tampilan.css">
</head>
<body class="<?= $kelas_badan ?>">
<?php if ($tipe_halaman !== 'polos'): ?>
<header class="kepala-situs">
    <div class="kontainer kepala-isi">
        <?php if ($tipe_halaman === 'tumpuk'): ?>
            <div class="merek">
                <a class="tombol-kembali" href="<?= e($url_kembali) ?>" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                <span class="judul-kepala"><?= e($judul_kepala) ?></span>
            </div>
        <?php else: ?>
            <a class="merek" href="<?= BASE_URL ?>/user/us_index.php">
                <?= html_logo() ?>
                <span class="nama-merek">WARUNG KOPI AGUNG</span>
            </a>
        <?php endif; ?>

        <nav class="navigasi-atas" aria-label="Menu utama">
            <?php if ($peran === 'pelanggan'): ?>
                <a class="tautan-nav<?= $menu_aktif === 'menu' ? ' aktif' : '' ?>" href="<?= BASE_URL ?>/user/us_index.php">Menu</a>
                <a class="tautan-nav<?= $menu_aktif === 'riwayat' ? ' aktif' : '' ?>" href="<?= BASE_URL ?>/user/riwayat_pesanan.php">Riwayat</a>
            <?php endif; ?>
            <?php if ($peran === 'admin' || $peran === 'superadmin'): ?>
                <a class="tautan-nav" href="<?= e(halaman_peran($peran)) ?>"><i class="fa-solid fa-gauge"></i> Panel Admin</a>
            <?php endif; ?>
            <?php if ($sudah_masuk): ?>
                <span class="info-pengguna">
                    <i class="fa-solid fa-circle-user"></i>
                    <span class="nama-pengguna"><?= e($_SESSION['nama'] ?? '') ?></span>
                </span>
                <a class="tombol tombol-putih" href="<?= BASE_URL ?>/user/logout.php">Keluar</a>
            <?php else: ?>
                <a class="tombol tombol-putih" href="<?= BASE_URL ?>/user/login.php"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php endif; ?>

<?php if ($tipe_halaman === 'polos'): ?>
<main class="halaman-masuk">
<?php else: ?>
<main class="konten-utama<?= $ada_bilah_bawah ? ' ada-bilah-bawah' : '' ?>">
<div class="<?= $kelas_isi ?>">
<?php cetak_pesan(); ?>
<?php endif; ?>
