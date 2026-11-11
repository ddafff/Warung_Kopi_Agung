<?php // user/us_beranda.php — isi Halaman Menu (data disiapkan di us_index.php) ?>

<!-- Spanduk meja -->
<section class="spanduk-meja">
    <?php if ($nomor_meja): ?>
        <span class="spanduk-meja-label">Meja Anda</span>
        <span class="spanduk-meja-angka"><?= str_pad($nomor_meja, 2, '0', STR_PAD_LEFT) ?></span>
        <p class="spanduk-meja-teks">Selamat menikmati kopi terbaik dan suasana terbaik.</p>
    <?php else: ?>
        <span class="spanduk-meja-label">Selamat datang</span>
        <span class="spanduk-meja-angka"><i class="fa-solid fa-qrcode"></i></span>
        <p class="spanduk-meja-teks">Pindai QR code di meja untuk memesan</p>
    <?php endif; ?>
</section>

<?php if (!isset($_SESSION['user_id'])): ?>
    <a class="kotak-ajakan" href="login.php?kembali=<?= urlencode($kembali) ?>">
        <span><strong>Masuk untuk mulai memesan</strong><small>Tamu hanya bisa melihat menu dan harga</small></span>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
<?php endif; ?>

<!-- Kategori -->
<div class="baris-judul"><h2 class="judul-bagian">Kategori Menu</h2></div>
<nav class="deretan-kategori">
    <?php foreach ($daftar_kategori as $nama_kat => $ikon): ?>
        <a class="chip-kategori <?= $nama_kat == $kategori ? 'aktif' : '' ?>"
           href="us_index.php<?= $nama_kat == 'Semua' ? '' : '?kategori=' . urlencode($nama_kat) ?>">
            <i class="fa-solid <?= $ikon ?>"></i><span><?= $nama_kat ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<!-- Daftar menu -->
<div class="baris-judul">
    <h2 class="judul-bagian">Daftar Menu</h2>
    <span class="teks-kecil"><?= count($daftar_menu) ?> Pilihan</span>
</div>

<div class="daftar-menu">
    <?php foreach ($daftar_menu as $m): ?>
        <?php include 'us_kartu_menu.php'; ?>
    <?php endforeach; ?>
</div>
<?php if (!$daftar_menu): ?>
    <div class="kartu keadaan-kosong"><p class="teks-pendukung">Belum ada menu pada kategori ini.</p></div>
<?php endif; ?>

<!-- Bilah keranjang (hanya pelanggan yang sudah punya isi keranjang) -->
<?php if ($role == 'pelanggan' && $nomor_meja && $jumlah_item > 0): ?>
    <a class="bilah-keranjang" href="checkout.php">
        <span class="bilah-keranjang-kiri"><i class="fa-solid fa-cart-shopping"></i> Lihat Keranjang (<?= $jumlah_item ?>)</span>
        <span><?= rupiah($total_keranjang) ?> <i class="fa-solid fa-chevron-right"></i></span>
    </a>
<?php endif; ?>
