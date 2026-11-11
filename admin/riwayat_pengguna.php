<?php
// admin/riwayat_pengguna.php?id= — riwayat pesanan satu pelanggan. Hanya baca.
// Isinya sama dengan halaman Riwayat Pesanan milik pelanggan itu.
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_admin.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'pelanggan'");
$stmt->execute([$id]);
$pelanggan = $stmt->fetch();
if (!$pelanggan) {
    header('Location: ../404.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC");
$stmt->execute([$id]);
$daftar = $stmt->fetchAll();

$judul = 'Riwayat @' . $pelanggan['username'];
$menu_aktif = 'pengguna';
include 'ad_header.php';
?>
<p style="margin:0 0 12px"><a class="tombol tombol-sekunder tombol-kecil" href="daftar_pengguna.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Pengguna</a></p>

<section class="kartu-profil">
    <span class="avatar-inisial avatar-besar"><?= strtoupper(substr($pelanggan['username'], 0, 1)) ?></span>
    <div class="profil-info">
        <h1>@<?= htmlspecialchars($pelanggan['username']) ?></h1>
        <div class="profil-meta">
            <span><?= htmlspecialchars($pelanggan['email']) ?></span>
        </div>
    </div>
    <div class="profil-angka"><strong><?= count($daftar) ?></strong><span class="teks-kecil">Pesanan</span></div>
</section>

<div class="banner-baca-saja">
    <i class="fa-solid fa-lock"></i>
    <div>Riwayat hanya dapat dilihat<small>Data ini tidak bisa diubah dari halaman ini.</small></div>
</div>

<section class="kartu-tabel">
    <div class="pembungkus-tabel">
        <table class="tabel-data">
            <thead>
                <tr><th>Waktu</th><th>Meja</th><th>Menu dan jumlah</th><th>Total</th><th>Metode</th><th>Status pembayaran</th><th>Status pesanan</th></tr>
            </thead>
            <tbody>
                <?php if (!$daftar): ?>
                    <tr><td class="tabel-kosong" colspan="7">Pelanggan ini belum pernah memesan.</td></tr>
                <?php endif; ?>
                <?php foreach ($daftar as $p): ?>
                    <?php
                    $stmt = $pdo->prepare("SELECT nama_menu, jumlah FROM order_items WHERE order_id = ? ORDER BY id");
                    $stmt->execute([$p['id']]);
                    $ringkasan = [];
                    foreach ($stmt->fetchAll() as $i) {
                        $ringkasan[] = $i['nama_menu'] . ' x' . $i['jumlah'];
                    }
                    ?>
                    <tr>
                        <td data-label="Waktu"><span class="waktu-dua-baris"><strong>No. <?= $p['id'] ?></strong><span class="teks-kecil"><?= tanggal_indo($p['created_at']) ?></span></span></td>
                        <td data-label="Meja"><span class="chip-meja">Meja <?= str_pad($p['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></td>
                        <td data-label="Menu"><?= htmlspecialchars(implode(', ', $ringkasan)) ?></td>
                        <td data-label="Total"><strong><?= rupiah($p['total_harga']) ?></strong></td>
                        <td data-label="Metode"><?= label_metode($p['metode_pembayaran']) ?></td>
                        <td data-label="Pembayaran"><span class="lencana" data-status="<?= $p['status_pembayaran'] ?>"><?= label_pembayaran($p['status_pembayaran']) ?></span></td>
                        <td data-label="Pesanan"><span class="lencana" data-status="<?= $p['status_pesanan'] ?>"><?= label_pesanan($p['status_pesanan']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="catatan-bawah-tabel"><span>Total <?= count($daftar) ?> catatan</span></div>
</section>
<?php include 'ad_footer.php'; ?>
