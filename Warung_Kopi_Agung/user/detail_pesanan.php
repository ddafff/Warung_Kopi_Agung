<?php
// detail_pesanan.php?id= — rincian satu pesanan. Hanya baca.
require_once '../koneksi.php';
require_once '../fungsi.php';

wajib_peran(['pelanggan']);
$pesanan = muat_pesanan_pelanggan($pdo, $_GET['id'] ?? null);
$id      = (int) $pesanan['id'];
$item    = muat_item_pesanan($pdo, $id);
$masih_berjalan = in_array($pesanan['status_pesanan'], ['pending', 'dapur'], true);

$judul_halaman = 'Detail Pesanan';
$tipe_halaman  = 'tumpuk';
$judul_kepala  = 'Detail Pesanan';
$url_kembali   = BASE_URL . '/user/riwayat_pesanan.php';
include 'us_header.php';
?>
<section class="kartu">
    <div class="baris-rincian"><span>No. Pesanan</span><span class="nilai">No. <?= $id ?></span></div>
    <div class="baris-rincian"><span>Waktu</span><span class="nilai"><?= e(tanggal_indo($pesanan['created_at'])) ?></span></div>
    <div class="baris-rincian"><span>Meja</span><span class="nilai"><?= str_pad((string) $pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></div>
    <div class="baris-rincian"><span>Metode</span><span class="nilai"><?= e(label_metode($pesanan['metode_pembayaran'])) ?></span></div>
    <hr class="garis-pemisah">
    <div class="kelompok-lencana">
        <span class="lencana" data-status="<?= e($pesanan['status_pembayaran']) ?>"><i class="fa-solid fa-wallet"></i> <?= e(label_pembayaran($pesanan['status_pembayaran'])) ?></span>
        <span class="lencana" data-status="<?= e($pesanan['status_pesanan']) ?>"><i class="fa-solid fa-utensils"></i> <?= e(label_pesanan($pesanan['status_pesanan'])) ?></span>
    </div>
</section>

<section class="kartu">
    <h2 class="kartu-judul">Daftar Menu</h2>
    <?php foreach ($item as $i): $foto = url_foto_menu($i['gambar'] ?? null); ?>
    <div class="baris-item">
        <?php if ($foto): ?>
            <img class="foto-menu" src="<?= e($foto) ?>" alt="<?= e($i['nama_menu']) ?>" loading="lazy">
        <?php else: ?>
            <span class="foto-menu" aria-hidden="true"><i class="fa-solid fa-mug-saucer"></i></span>
        <?php endif; ?>
        <div class="info-menu">
            <span class="nama-menu"><?= e($i['nama_menu']) ?></span>
            <span class="teks-kecil"><?= (int) $i['jumlah'] ?> x <?= rupiah($i['harga_satuan']) ?></span>
        </div>
        <strong><?= rupiah($i['subtotal']) ?></strong>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($pesanan['catatan'])): ?>
        <hr class="garis-pemisah">
        <div class="kotak-catatan"><small class="teks-kecil"><i class="fa-solid fa-pen-to-square"></i> Catatan</small><br><?= e($pesanan['catatan']) ?></div>
    <?php endif; ?>

    <hr class="garis-pemisah">
    <div class="baris-rincian"><strong>Total</strong><span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span></div>
</section>

<?php if ($masih_berjalan): ?>
    <a class="tombol tombol-utama tombol-penuh" href="status_pesanan.php?id=<?= $id ?>"><i class="fa-solid fa-rotate"></i> Pantau Status Pesanan</a>
<?php endif; ?>
<?php include 'us_footer.php'; ?>
