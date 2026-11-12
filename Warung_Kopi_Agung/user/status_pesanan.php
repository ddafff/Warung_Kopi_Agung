<?php
// status_pesanan.php?id= — status pesanan; memantau sampai Selesai atau Dibatalkan.
require_once '../koneksi.php';
require_once '../fungsi.php';

wajib_peran(['pelanggan']);
$pesanan = muat_pesanan_pelanggan($pdo, $_GET['id'] ?? null);
$id      = (int) $pesanan['id'];
$item    = muat_item_pesanan($pdo, $id);
$selesai = in_array($pesanan['status_pesanan'], ['selesai', 'dibatalkan'], true);

$judul_halaman = 'Status Pesanan';
$tipe_halaman  = 'tumpuk';
$judul_kepala  = 'Status Pesanan';
$url_kembali   = BASE_URL . '/user/riwayat_pesanan.php';
include 'us_header.php';
?>
<div id="pemantau" data-id="<?= $id ?>" data-mode="status" data-selesai-awal="<?= $selesai ? '1' : '0' ?>"></div>

<section class="kartu">
    <div class="baris-rincian"><span>No. Pesanan</span><span class="nilai">No. <?= $id ?></span></div>
    <div class="baris-rincian"><span>Meja</span><span class="nilai"><?= str_pad((string) $pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></div>
    <div class="baris-rincian"><span>Metode</span><span class="nilai"><?= e(label_metode($pesanan['metode_pembayaran'])) ?></span></div>
    <hr class="garis-pemisah">
    <div class="kelompok-lencana">
        <span class="lencana" data-status="<?= e($pesanan['status_pembayaran']) ?>" data-status-dari="status_pembayaran">
            <i class="fa-solid fa-wallet"></i>
            <span data-isi="label_pembayaran"><?= e(label_pembayaran($pesanan['status_pembayaran'])) ?></span>
        </span>
        <span class="lencana" data-status="<?= e($pesanan['status_pesanan']) ?>" data-status-dari="status_pesanan">
            <i class="fa-solid fa-utensils"></i>
            <span data-isi="label_pesanan"><?= e(label_pesanan($pesanan['status_pesanan'])) ?></span>
        </span>
    </div>
</section>

<section class="kartu">
    <h2 class="kartu-judul">Daftar Menu</h2>
    <?php foreach ($item as $i): ?>
    <div class="baris-item">
        <div class="info-menu">
            <span class="nama-menu"><?= e($i['nama_menu']) ?></span>
            <span class="teks-kecil"><?= (int) $i['jumlah'] ?> x <?= rupiah($i['harga_satuan']) ?></span>
        </div>
        <strong><?= rupiah($i['subtotal']) ?></strong>
    </div>
    <?php endforeach; ?>
    <?php if (!empty($pesanan['catatan'])): ?>
        <hr class="garis-pemisah">
        <div class="kotak-catatan"><small class="teks-kecil">Catatan</small><br><?= e($pesanan['catatan']) ?></div>
    <?php endif; ?>
    <hr class="garis-pemisah">
    <div class="baris-rincian"><strong>Total</strong><span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span></div>
</section>

<p class="pesan-koneksi" id="pesan-koneksi" aria-live="polite"></p>
<a class="tombol tombol-sekunder tombol-penuh" href="riwayat_pesanan.php">Lihat Riwayat Pesanan</a>
<?php include 'us_footer.php'; ?>
