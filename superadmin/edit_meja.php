<?php
// superadmin/edit_meja.php?id= — ubah nomor meja. Setelah tersimpan, QR baru ditampilkan.
// Riwayat pesanan tidak berubah (orders.nomor_meja hanyalah salinan angka).
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';
include 'alamat_qr.php';         // hasil: $alamat_qr

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM meja WHERE id = ?");
$stmt->execute([$id]);
$meja = $stmt->fetch();
if (!$meja) {
    header('Location: ../404.php');
    exit;
}

$nomor = $meja['nomor_meja'];
$error = '';
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor = trim($_POST['nomor_meja'] ?? '');

    if (!ctype_digit($nomor) || (int)$nomor < 1 || (int)$nomor > 999) {
        $error = 'Nomor meja harus berupa angka 1 sampai 999.';
    } else {
        $nomor = (int)$nomor;
        // Nomor boleh sama dengan miliknya sendiri, tidak boleh sama dengan meja lain
        $stmt = $pdo->prepare("SELECT id FROM meja WHERE nomor_meja = ? AND id <> ?");
        $stmt->execute([$nomor, $id]);
        if ($stmt->fetch()) {
            $error = 'Nomor meja sudah dipakai';
        } else {
            $stmt = $pdo->prepare("UPDATE meja SET nomor_meja = ? WHERE id = ?");
            $stmt->execute([$nomor, $id]);
            $berhasil = true;
        }
    }
}

$judul = 'Edit Meja';
$menu_aktif = 'meja';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Edit Meja</h1>
        <p class="teks-pendukung">Meja <?= str_pad($meja['nomor_meja'], 2, '0', STR_PAD_LEFT) ?> &bull; ubah nomor meja lalu cetak QR baru.</p>
    </div>
</div>

<?php if ($berhasil): ?>
    <section class="kartu pusat">
        <div class="pesan pesan-sukses"><i class="fa-solid fa-circle-check"></i><span>Perubahan disimpan. Nomor meja sekarang <?= str_pad($nomor, 2, '0', STR_PAD_LEFT) ?>.</span></div>
        <div class="pesan pesan-peringatan"><i class="fa-solid fa-triangle-exclamation"></i><span>QR lama tidak berlaku lagi. Cetak QR baru dan ganti yang lama di meja.</span></div>
        <div class="area-cetak-qr">
            <h2 class="judul-bagian">QR Meja <?= str_pad($nomor, 2, '0', STR_PAD_LEFT) ?></h2>
            <div class="kotak-qr" data-qr="<?= htmlspecialchars($alamat_qr . $nomor) ?>" data-ukuran="220"></div>
        </div>
        <div class="aksi-struk" style="margin:16px auto 0">
            <button class="tombol tombol-utama" type="button" onclick="cetakQR()"><i class="fa-solid fa-print"></i> Cetak QR</button>
            <a class="tombol tombol-sekunder" href="kelola_meja.php">Kembali ke daftar meja</a>
        </div>
    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php else: ?>
    <section class="kartu">
        <?php if ($error): ?>
            <div class="pesan pesan-galat"><i class="fa-solid fa-circle-exclamation"></i><span><?= $error ?></span></div>
        <?php endif; ?>
        <form class="formulir" method="POST">
            <div class="kolom-isian">
                <label class="label-isian" for="nomor_meja"><i class="fa-solid fa-table-cells"></i> Nomor meja baru</label>
                <input class="isian" type="number" id="nomor_meja" name="nomor_meja" min="1" max="999" value="<?= htmlspecialchars($nomor) ?>" required>
                <span class="petunjuk-isian">QR code berubah mengikuti nomor baru. QR lama tidak berlaku lagi.</span>
            </div>
            <div class="aksi-form">
                <a class="tombol tombol-sekunder" href="kelola_meja.php">Batal</a>
                <button class="tombol tombol-utama" type="submit"><i class="fa-solid fa-check"></i> Simpan perubahan</button>
            </div>
        </form>
    </section>
<?php endif; ?>
<?php include 'sa_footer.php'; ?>
