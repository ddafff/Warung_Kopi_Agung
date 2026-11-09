<?php
// user/us_kartu_menu.php — satu kartu menu (dipanggil berulang dari us_beranda.php, data di $m)
$foto = ($m['gambar'] && file_exists('../gambar/' . $m['gambar'])) ? '../gambar/' . $m['gambar'] : '';
?>
<article class="kartu-menu <?= $m['tersedia'] ? '' : 'kartu-menu-nonaktif' ?>">
    <div class="kartu-menu-isi">
        <?php if ($foto): ?>
            <img class="foto-menu" src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($m['nama']) ?>">
        <?php else: ?>
            <span class="foto-menu"><i class="fa-solid fa-mug-saucer"></i></span>
        <?php endif; ?>
        <div class="info-menu">
            <span class="nama-menu"><?= htmlspecialchars($m['nama']) ?></span>
            <span class="harga"><?= rupiah($m['harga']) ?></span>
            <span class="status-ketersediaan <?= $m['tersedia'] ? '' : 'tidak-tersedia' ?>"><?= $m['tersedia'] ? 'Tersedia' : 'Tidak tersedia' ?></span>
        </div>
    </div>

    <?php if ($role == 'admin' || $role == 'superadmin'): ?>
        <!-- Admin dan Superadmin tidak punya tombol + -->
    <?php elseif (!$m['tersedia']): ?>
        <button class="tombol-tambah tombol-nonaktif" type="button" disabled><i class="fa-solid fa-plus"></i></button>
    <?php elseif (!isset($_SESSION['user_id'])): ?>
        <a class="tombol-tambah" href="login.php?kembali=<?= urlencode($kembali) ?>"><i class="fa-solid fa-plus"></i></a>
    <?php elseif (!$nomor_meja): ?>
        <button class="tombol-tambah tombol-nonaktif" type="button" onclick="tampilkanToast('Pindai QR code di meja untuk memesan')"><i class="fa-solid fa-plus"></i></button>
    <?php else: ?>
        <form class="formulir-tambah" method="POST" action="keranjang.php">
            <input type="hidden" name="menu_id" value="<?= $m['id'] ?>">
            <input type="hidden" name="kembali" value="<?= htmlspecialchars($kembali) ?>">
            <button class="tombol-tambah" type="submit"><i class="fa-solid fa-plus"></i></button>
        </form>
    <?php endif; ?>
</article>
