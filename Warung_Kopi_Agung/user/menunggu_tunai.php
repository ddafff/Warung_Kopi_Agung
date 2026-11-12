<?php
// menunggu_tunai.php?id= — pelanggan menunggu kasir mengonfirmasi pembayaran tunai.
require_once '../koneksi.php';
require_once '../fungsi.php';

wajib_peran(['pelanggan']);
$pesanan = muat_pesanan_pelanggan($pdo, $_GET['id'] ?? null);

if ($pesanan['metode_pembayaran'] !== 'cash') {
    alihkan(BASE_URL . '/user/bayar_qris.php?id=' . (int) $pesanan['id']);
}
$id    = (int) $pesanan['id'];
$lunas = ($pesanan['status_pembayaran'] === 'paid');

$judul_halaman = 'Menunggu Pembayaran Tunai';
$tipe_halaman  = 'tumpuk';
$judul_kepala  = 'Keranjang & Pembayaran';
$url_kembali   = BASE_URL . '/user/us_index.php';
include 'us_header.php';
?>
<div class="pusat" id="pemantau" data-id="<?= $id ?>" data-mode="bayar" data-selesai-awal="<?= $lunas ? '1' : '0' ?>">
    <div class="ikon-hero"><i class="fa-solid fa-receipt"></i></div>
    <h1 class="judul-halaman">Menunggu Pembayaran Tunai</h1>
    <p class="teks-pendukung">Silakan bayar di kasir dan sebutkan nomor meja serta username Anda.</p>
</div>

<div class="pesan pesan-sukses <?= $lunas ? '' : 'tersembunyi' ?>" id="kotak-berhasil" role="status">
    <i class="fa-solid fa-circle-check"></i>
    <span><strong>Pembayaran berhasil.</strong> Pesanan Anda sudah masuk antrean dapur.</span>
</div>

<section class="kartu kartu-struk" style="margin-top:16px">
    <div class="kartu-struk-atas">
        <span><i class="fa-solid fa-receipt"></i> Rincian Pembayaran</span>
        <span class="lencana" data-status="<?= e($pesanan['status_pembayaran']) ?>" data-status-dari="status_pembayaran" data-isi="label_pembayaran">
            <?= e(label_pembayaran($pesanan['status_pembayaran'])) ?>
        </span>
    </div>
    <div class="kartu-struk-isi">
        <div class="baris-rincian"><span>Meja</span><span class="nilai"><?= str_pad((string) $pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="baris-rincian"><span>Username</span><span class="nilai"><?= e($pesanan['username']) ?></span></div>
        <div class="baris-rincian"><span>No. Pesanan</span><span class="nilai">No. <?= $id ?></span></div>
        <hr class="garis-putus">
        <div class="baris-rincian">
            <span>Total Pembayaran<br><small class="teks-kecil">Tunai di Kasir</small></span>
            <span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span>
        </div>
    </div>
</section>

<a class="tombol tombol-utama tombol-penuh" href="status_pesanan.php?id=<?= $id ?>">
    Lihat Status Pesanan <i class="fa-solid fa-arrow-right"></i>
</a>
<p class="pesan-koneksi" id="pesan-koneksi" aria-live="polite">Status berubah setelah kasir mengonfirmasi pembayaran.</p>
<?php include 'us_footer.php'; ?>
