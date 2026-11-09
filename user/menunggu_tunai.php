<?php
// user/menunggu_tunai.php?id= — pelanggan menunggu kasir mengonfirmasi pembayaran tunai
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'ambil_pesanan.php';      // hasilnya: $pesanan (sudah diperiksa pemiliknya)

if ($pesanan['metode_pembayaran'] != 'cash') {
    header('Location: bayar_qris.php?id=' . $pesanan['id']);
    exit;
}
$lunas = ($pesanan['status_pembayaran'] == 'paid');

$judul = 'Menunggu Pembayaran Tunai';
$kelas_isi = 'kontainer-sempit';
include 'us_header.php';
?>
<!-- #pemantau dibaca JS/User.js: memeriksa status pesanan ini tiap 5 detik -->
<div class="pusat" id="pemantau" data-id="<?= $pesanan['id'] ?>" data-mode="bayar" data-selesai="<?= $lunas ? 1 : 0 ?>">
    <div class="ikon-hero"><i class="fa-solid fa-receipt"></i></div>
    <h1 class="judul-halaman">Menunggu Pembayaran Tunai</h1>
    <p class="teks-pendukung">Silakan bayar di kasir dan sebutkan nomor meja serta username Anda.</p>
</div>

<div class="pesan pesan-sukses <?= $lunas ? '' : 'tersembunyi' ?>" id="kotak-berhasil">
    <i class="fa-solid fa-circle-check"></i>
    <span><strong>Pembayaran berhasil.</strong> Pesanan Anda sudah masuk antrean dapur.</span>
</div>

<section class="kartu kartu-struk" style="margin-top:16px">
    <div class="kartu-struk-atas">
        <span><i class="fa-solid fa-receipt"></i> Rincian Pembayaran</span>
        <span class="lencana" data-status="<?= $pesanan['status_pembayaran'] ?>" data-status-dari="status_pembayaran" data-isi="label_pembayaran"><?= label_pembayaran($pesanan['status_pembayaran']) ?></span>
    </div>
    <div class="kartu-struk-isi">
        <div class="baris-rincian"><span>Meja</span><span class="nilai"><?= str_pad($pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="baris-rincian"><span>Username</span><span class="nilai"><?= htmlspecialchars($pesanan['username']) ?></span></div>
        <div class="baris-rincian"><span>No. Pesanan</span><span class="nilai">No. <?= $pesanan['id'] ?></span></div>
        <hr class="garis-putus">
        <div class="baris-rincian">
            <span>Total Pembayaran<br><small class="teks-kecil">Tunai di Kasir</small></span>
            <span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span>
        </div>
    </div>
</section>

<a class="tombol tombol-utama tombol-penuh" href="status_pesanan.php?id=<?= $pesanan['id'] ?>">Lihat Status Pesanan <i class="fa-solid fa-arrow-right"></i></a>
<p class="pesan-koneksi" id="pesan-koneksi">Status berubah setelah kasir mengonfirmasi pembayaran.</p>
<?php include 'us_footer.php'; ?>
