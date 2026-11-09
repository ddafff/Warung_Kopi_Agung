<?php
// user/bayar_qris.php?id= — menampilkan kode QR pembayaran QRIS (Midtrans Sandbox)
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'ambil_pesanan.php';      // hasilnya: $pesanan (sudah diperiksa pemiliknya)

if ($pesanan['metode_pembayaran'] != 'qris') {
    header('Location: menunggu_tunai.php?id=' . $pesanan['id']);
    exit;
}
$lunas = ($pesanan['status_pembayaran'] == 'paid');
$batal = ($pesanan['status_pesanan'] == 'dibatalkan');
$waktu_kedaluwarsa = $pesanan['qris_kedaluwarsa'] ?? $pesanan['qris_expired_at'] ?? null;
$jam_batas = $waktu_kedaluwarsa ? date('H:i', strtotime($waktu_kedaluwarsa)) : '-';

// Dapatkan URL gambar QR untuk simulator Sandbox
$url_simulator = '';
if (!empty($pesanan['qris_url']) && str_starts_with($pesanan['qris_url'], 'http')) {
    $url_simulator = $pesanan['qris_url'];
} elseif (!empty($pesanan['midtrans_order_id'])) {
    require_once '../midtrans.php';
    $status_midtrans = kirim_midtrans('GET', '/v2/' . $pesanan['midtrans_order_id'] . '/status');
    if (!empty($status_midtrans['transaction_id'])) {
        $url_simulator = 'https://api.sandbox.midtrans.com/v2/qris/' . $status_midtrans['transaction_id'] . '/qr-code';
        $stmt_update = $pdo->prepare("UPDATE orders SET qris_url = ? WHERE id = ?");
        $stmt_update->execute([$url_simulator, $pesanan['id']]);
        $pesanan['qris_url'] = $url_simulator;
    }
}

$judul = 'Pembayaran QRIS';
$kelas_isi = 'kontainer-sempit';
include 'us_header.php';
?>
<div class="pusat" id="pemantau" data-id="<?= $pesanan['id'] ?>" data-mode="bayar"
    data-selesai="<?= ($lunas || $batal) ? 1 : 0 ?>">
    <h1 class="judul-halaman">Menunggu Pembayaran</h1>
    <p class="teks-pendukung">Silakan selesaikan pembayaran Anda.</p>
</div>

<div class="pesan pesan-sukses <?= $lunas ? '' : 'tersembunyi' ?>" id="kotak-berhasil">
    <i class="fa-solid fa-circle-check"></i>
    <span><strong>Pembayaran berhasil.</strong> Pesanan Anda sudah masuk antrean dapur.</span>
</div>
<div class="pesan pesan-galat <?= ($batal && !$lunas) ? '' : 'tersembunyi' ?>" id="kotak-batal">
    <i class="fa-solid fa-circle-xmark"></i>
    <span>Pembayaran kedaluwarsa atau dibatalkan. <a href="us_index.php"><strong>Kembali ke menu</strong></a> untuk
        memesan lagi.</span>
</div>

<section class="kartu pusat" style="margin-top:16px">
    <div class="batas-waktu"><i class="fa-regular fa-clock"></i> Bayar sebelum <?= $jam_batas ?></div>

    <?php if (!empty($pesanan['qris_url']) && str_starts_with($pesanan['qris_url'], 'http')): ?>
        <div style="margin:16px auto;max-width:240px">
            <img src="<?= htmlspecialchars($pesanan['qris_url']) ?>" alt="Kode QR QRIS" width="220" height="220"
                style="display:block;margin:0 auto;border-radius:12px;background:#fff;padding:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border:1px solid #e5e7eb">
        </div>
        <p class="teks-pendukung" style="margin-top:12px">Pindai kode QR menggunakan GoPay, OVO, BCA, atau aplikasi
            perbankan lainnya.</p>
    <?php elseif (!empty($pesanan['qris_url']) || !empty($pesanan['qris_qr_string'])): ?>
        <?php $qr_data = $pesanan['qris_url'] ?? $pesanan['qris_qr_string']; ?>
        <div>
            <div class="kotak-qr" id="kotak-qr" data-qr="<?= htmlspecialchars($qr_data) ?>"></div>
        </div>
        <p class="teks-pendukung" style="margin-top:12px">Pindai kode QR menggunakan GoPay, OVO, BCA, atau aplikasi
            perbankan lainnya.</p>
    <?php else: ?>
        <p class="teks-pendukung">Kode QR tidak tersedia untuk pesanan ini.</p>
    <?php endif; ?>

    <hr class="garis-pemisah">
    <div class="baris-rincian"><span>Total Pembayaran</span><span
            class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span></div>
    <span class="kotak-uji"><i class="fa-solid fa-circle-info"></i> Mode uji coba (Midtrans Sandbox)</span>


</section>

<a class="tombol tombol-utama tombol-penuh" href="status_pesanan.php?id=<?= $pesanan['id'] ?>"><i
        class="fa-solid fa-rotate"></i> Lihat Status Pesanan</a>
<p class="pesan-koneksi" id="pesan-koneksi"></p>

<!-- Pustaka untuk menggambar QR (harus dimuat sebelum JS/User.js) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php include 'us_footer.php'; ?>