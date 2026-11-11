<?php
// user/status_pesanan.php?id= — status pesanan, dipantau sampai Selesai atau Dibatalkan
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'ambil_pesanan.php';      // hasilnya: $pesanan (sudah diperiksa pemiliknya)

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id");
$stmt->execute([$pesanan['id']]);
$item = $stmt->fetchAll();

$selesai = ($pesanan['status_pesanan'] == 'selesai' || $pesanan['status_pesanan'] == 'dibatalkan');

$judul = 'Status Pesanan';
$kelas_isi = 'kontainer-sempit';
include 'us_header.php';
?>
<a class="tombol tombol-sekunder tombol-kecil" href="riwayat_pesanan.php"><i class="fa-solid fa-arrow-left"></i> Riwayat</a>
<h1 class="judul-halaman" style="margin-top:12px">Status Pesanan</h1>
<div id="pemantau" data-id="<?= $pesanan['id'] ?>" data-mode="status" data-selesai="<?= $selesai ? 1 : 0 ?>"></div>

<section class="kartu">
    <div class="baris-rincian"><span>No. Pesanan</span><span class="nilai">No. <?= $pesanan['id'] ?></span></div>
    <div class="baris-rincian"><span>Meja</span><span class="nilai"><?= str_pad($pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></div>
    <div class="baris-rincian"><span>Metode</span><span class="nilai"><?= label_metode($pesanan['metode_pembayaran']) ?></span></div>
    <hr class="garis-pemisah">
    <div class="kelompok-lencana">
        <span class="lencana" data-status="<?= $pesanan['status_pembayaran'] ?>" data-status-dari="status_pembayaran">
            <i class="fa-solid fa-wallet"></i> <span data-isi="label_pembayaran"><?= label_pembayaran($pesanan['status_pembayaran']) ?></span>
        </span>
        <span class="lencana" data-status="<?= $pesanan['status_pesanan'] ?>" data-status-dari="status_pesanan">
            <i class="fa-solid fa-utensils"></i> <span data-isi="label_pesanan"><?= label_pesanan($pesanan['status_pesanan']) ?></span>
        </span>
    </div>
</section>

<section class="kartu">
    <h2 class="kartu-judul">Daftar Menu</h2>
    <?php foreach ($item as $i): ?>
        <div class="baris-item">
            <div class="info-menu">
                <span class="nama-menu"><?= htmlspecialchars($i['nama_menu']) ?></span>
                <span class="teks-kecil"><?= $i['jumlah'] ?> x <?= rupiah($i['harga_satuan']) ?></span>
            </div>
            <strong><?= rupiah($i['subtotal']) ?></strong>
        </div>
    <?php endforeach; ?>
    <?php if ($pesanan['catatan']): ?>
        <hr class="garis-pemisah">
        <div class="kotak-catatan"><small class="teks-kecil">Catatan</small><br><?= htmlspecialchars($pesanan['catatan']) ?></div>
    <?php endif; ?>
    <hr class="garis-pemisah">
    <div class="baris-rincian"><strong>Total</strong><span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span></div>
</section>

<p class="pesan-koneksi" id="pesan-koneksi"></p>
<a class="tombol tombol-sekunder tombol-penuh" href="riwayat_pesanan.php">Lihat Riwayat Pesanan</a>
<?php include 'us_footer.php'; ?>
