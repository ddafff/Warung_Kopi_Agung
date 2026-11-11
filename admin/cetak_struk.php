<?php
// admin/cetak_struk.php?id= — struk pesanan yang sudah dibayar. Tombol Cetak memakai window.print()
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_admin.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$pesanan = $stmt->fetch();
if (!$pesanan) {
    header('Location: ../404.php');
    exit;
}

// Struk hanya untuk pesanan yang sudah dibayar
if ($pesanan['status_pembayaran'] != 'paid') {
    $_SESSION['pesan'] = 'Struk hanya bisa dicetak untuk pesanan yang sudah dibayar.';
    $_SESSION['jenis'] = 'galat';
    header('Location: pesanan_masuk.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id");
$stmt->execute([$id]);
$item = $stmt->fetchAll();

$judul = 'Struk Pesanan No. ' . $id;
$menu_aktif = 'pesanan';
include 'ad_header.php';
?>
<!-- Kertas struk 80 mm (hanya berlaku saat mencetak) -->
<style media="print">@page { size: 80mm auto; margin: 4mm; }</style>

<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Pratinjau Struk</h1>
        <p class="teks-pendukung">Periksa isi struk, lalu tekan Cetak.</p>
    </div>
</div>

<div class="area-pratinjau">
    <div class="area-struk">
        <div class="struk-kepala">
            <h2 class="struk-nama">Warung Kopi Agung</h2>
        </div>
        <hr class="struk-pembagi">
        <div class="struk-baris"><span>No. Pesanan</span><strong>No. <?= $pesanan['id'] ?></strong></div>
        <div class="struk-baris"><span>Meja</span><strong><?= str_pad($pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></strong></div>
        <div class="struk-baris"><span>Waktu</span><span><?= tanggal_indo($pesanan['created_at']) ?></span></div>
        <hr class="struk-pembagi">
        <?php foreach ($item as $i): ?>
            <div class="struk-baris">
                <span class="struk-item-nama">
                    <?= $i['jumlah'] ?>x <?= htmlspecialchars($i['nama_menu']) ?>
                    <span class="struk-item-rinci">@ <?= rupiah($i['harga_satuan']) ?></span>
                </span>
                <span><?= rupiah($i['subtotal']) ?></span>
            </div>
        <?php endforeach; ?>
        <hr class="struk-pembagi">
        <div class="struk-baris struk-total"><span>Total</span><span><?= rupiah($pesanan['total_harga']) ?></span></div>
        <div class="struk-baris"><span>Metode Pembayaran</span><span><?= label_metode($pesanan['metode_pembayaran']) ?> (Sudah dibayar)</span></div>
        <hr class="struk-pembagi">
        <div class="struk-penutup"><strong>Terima kasih</strong><span>Selamat menikmati!</span></div>
    </div>

    <div class="aksi-struk">
        <button class="tombol tombol-utama" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak</button>
        <a class="tombol tombol-sekunder" href="pesanan_masuk.php"><i class="fa-solid fa-xmark"></i> Tutup</a>
    </div>
</div>
<?php include 'ad_footer.php'; ?>
