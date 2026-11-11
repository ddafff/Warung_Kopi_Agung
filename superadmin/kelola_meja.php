<?php
// superadmin/kelola_meja.php — daftar meja + QR code kecil. Tombol: Tambah Meja, Edit, Lihat QR.
// QR digambar di peramban oleh JS/Admin.js (qrcodejs). Isi QR: alamat Halaman Menu dengan ?meja=N
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';
include 'alamat_qr.php';         // hasil: $alamat_qr

$daftar = $pdo->query("SELECT * FROM meja ORDER BY nomor_meja")->fetchAll();

$judul = 'Kelola Meja';
$menu_aktif = 'meja';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Kelola Meja dan Kode QR</h1>
        <p class="teks-pendukung"><?= count($daftar) ?> meja terdaftar. Tempelkan QR di meja agar pelanggan bisa memesan.</p>
    </div>
    <div class="aksi-kepala">
        <a class="tombol tombol-utama" href="tambah_meja.php"><i class="fa-solid fa-plus"></i> Tambah Meja</a>
    </div>
</div>

<?php if (!$daftar): ?>
    <section class="kartu keadaan-kosong">
        <div class="ikon-hero"><i class="fa-solid fa-table-cells"></i></div>
        <h2 class="judul-bagian">Belum ada meja</h2>
        <p class="teks-pendukung">Tekan Tambah Meja untuk membuat nomor meja dan QR code-nya.</p>
    </section>
<?php endif; ?>

<div class="grid-meja">
    <?php foreach ($daftar as $m): ?>
        <?php $nomor = str_pad($m['nomor_meja'], 2, '0', STR_PAD_LEFT); ?>
        <article class="kartu-meja-kelola">
            <div class="kotak-qr-kecil" data-qr="<?= htmlspecialchars($alamat_qr . $m['nomor_meja']) ?>" data-ukuran="96"></div>
            <div class="info-meja-kelola">
                <h3>Meja <?= $nomor ?></h3>
                <div class="aksi-meja-kelola">
                    <a class="tombol tombol-sekunder tombol-kecil" href="edit_meja.php?id=<?= $m['id'] ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                    <button class="tombol tombol-sekunder tombol-kecil" type="button"
                            onclick="lihatQR('<?= $nomor ?>', '<?= htmlspecialchars($alamat_qr . $m['nomor_meja']) ?>')"><i class="fa-solid fa-qrcode"></i> Lihat QR</button>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<!-- Jendela QR besar (dibuka tombol Lihat QR) -->
<div class="modal-qr" id="modal-qr">
    <div class="isi-modal-qr area-cetak-qr">
        <h2 class="judul-bagian" id="judul-qr">QR Meja</h2>
        <div class="kotak-qr" id="qr-besar"></div>
        <p class="teks-kecil">Pindai untuk membuka menu dan memesan dari meja ini.</p>
        <div class="aksi-struk sembunyi-cetak">
            <button class="tombol tombol-utama" type="button" onclick="cetakQR()"><i class="fa-solid fa-print"></i> Cetak</button>
            <button class="tombol tombol-sekunder" type="button" onclick="tutupQR()">Tutup</button>
        </div>
    </div>
</div>

<!-- Pustaka QR (harus dimuat sebelum JS/Admin.js) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php include 'sa_footer.php'; ?>
