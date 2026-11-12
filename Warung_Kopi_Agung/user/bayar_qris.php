<?php
// bayar_qris.php?id= — menampilkan QR pembayaran QRIS (Midtrans Sandbox).
require_once '../koneksi.php';
require_once '../fungsi.php';

wajib_peran(['pelanggan']);
$pesanan = muat_pesanan_pelanggan($pdo, $_GET['id'] ?? null);

if ($pesanan['metode_pembayaran'] !== 'qris') {
    alihkan(BASE_URL . '/user/menunggu_tunai.php?id=' . (int) $pesanan['id']);
}
$id        = (int) $pesanan['id'];
$lunas     = ($pesanan['status_pembayaran'] === 'paid');
$batal     = ($pesanan['status_pesanan'] === 'dibatalkan');
$ada_qr    = !empty($pesanan['qris_qr_string']);
$batas_jam = $pesanan['qris_expired_at'] ? date('H:i', strtotime($pesanan['qris_expired_at'])) : '-';

$judul_halaman  = 'Pembayaran QRIS';
$tipe_halaman   = 'tumpuk';
$judul_kepala   = 'Keranjang & Pembayaran';
$url_kembali    = BASE_URL . '/user/us_index.php';
$skrip_tambahan = ['https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'];
include 'us_header.php';
?>
<div class="pusat" id="pemantau" data-id="<?= $id ?>" data-mode="bayar"
     data-selesai-awal="<?= ($lunas || $batal) ? '1' : '0' ?>">
    <h1 class="judul-halaman">Menunggu Pembayaran</h1>
    <p class="teks-pendukung">Silakan selesaikan pembayaran Anda.</p>
</div>

<div class="pesan pesan-sukses <?= $lunas ? '' : 'tersembunyi' ?>" id="kotak-berhasil" role="status">
    <i class="fa-solid fa-circle-check"></i>
    <span><strong>Pembayaran berhasil.</strong> Pesanan Anda sudah masuk antrean dapur.</span>
</div>
<div class="pesan pesan-galat <?= ($batal && !$lunas) ? '' : 'tersembunyi' ?>" id="kotak-batal" role="status">
    <i class="fa-solid fa-circle-xmark"></i>
    <span>Pembayaran kedaluwarsa atau dibatalkan. <a href="us_index.php"><strong>Kembali ke menu</strong></a> untuk memesan lagi.</span>
</div>

<section class="kartu pusat" style="margin-top:16px">
    <div class="batas-waktu"><i class="fa-regular fa-clock"></i> Bayar sebelum <?= e($batas_jam) ?></div>

    <?php if ($ada_qr): ?>
        <div>
            <div class="kotak-qr" id="kotak-qr" data-qr="<?= e($pesanan['qris_qr_string']) ?>"></div>
        </div>
        <p class="teks-pendukung" style="margin-top:12px">Pindai kode QR menggunakan GoPay, OVO, BCA, atau aplikasi perbankan lainnya.</p>
    <?php else: ?>
        <p class="teks-pendukung">Kode QR tidak tersedia untuk pesanan ini.</p>
    <?php endif; ?>

    <hr class="garis-pemisah">
    <div class="baris-rincian"><span>Total Pembayaran</span><span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span></div>
    <span class="kotak-uji"><i class="fa-solid fa-circle-info"></i> Mode uji coba (Midtrans Sandbox)</span>
</section>

<a class="tombol tombol-utama tombol-penuh" href="status_pesanan.php?id=<?= $id ?>">
    <i class="fa-solid fa-rotate"></i> Lihat Status Pesanan
</a>
<p class="pesan-koneksi" id="pesan-koneksi" aria-live="polite"></p>
<?php include 'us_footer.php'; ?>
