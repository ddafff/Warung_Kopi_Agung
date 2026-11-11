<?php
// superadmin/tambah_meja.php — tambah nomor meja (1 sampai 999, tidak boleh sama). Setelah tersimpan, QR ditampilkan untuk dicetak.
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';
include 'alamat_qr.php';         // hasil: $alamat_qr

$nomor = '';
$error = '';
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor = trim($_POST['nomor_meja'] ?? '');

    if (!ctype_digit($nomor) || (int)$nomor < 1 || (int)$nomor > 999) {
        $error = 'Nomor meja harus berupa angka 1 sampai 999.';
    } else {
        $nomor = (int)$nomor;
        $stmt = $pdo->prepare("SELECT id FROM meja WHERE nomor_meja = ?");
        $stmt->execute([$nomor]);
        if ($stmt->fetch()) {
            $error = 'Nomor meja sudah dipakai';
        } else {
            $sekarang = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO meja (nomor_meja, created_at) VALUES (?, ?)");
            $stmt->execute([$nomor, $sekarang]);
            $berhasil = true;
        }
    }
}

$judul = 'Tambah Meja';
$menu_aktif = 'meja';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Tambah Meja</h1>
        <p class="teks-pendukung">Masukkan nomor meja, lalu cetak QR code dan tempelkan di meja.</p>
    </div>
</div>

<?php if ($berhasil): ?>
    <!-- Meja tersimpan: tampilkan QR untuk dicetak -->
    <section class="kartu pusat">
        <div class="pesan pesan-sukses"><i class="fa-solid fa-circle-check"></i><span>Meja <?= str_pad($nomor, 2, '0', STR_PAD_LEFT) ?> berhasil disimpan.</span></div>
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
                <label class="label-isian" for="nomor_meja"><i class="fa-solid fa-table-cells"></i> Nomor meja</label>
                <input class="isian" type="number" id="nomor_meja" name="nomor_meja" min="1" max="999" value="<?= htmlspecialchars($nomor) ?>" placeholder="Contoh: 7" required>
                <span class="petunjuk-isian">Angka 1 sampai 999, tidak boleh sama dengan meja lain.</span>
            </div>
            <div class="aksi-form">
                <a class="tombol tombol-sekunder" href="kelola_meja.php">Batal</a>
                <button class="tombol tombol-utama" type="submit"><i class="fa-solid fa-check"></i> Simpan</button>
            </div>
        </form>
    </section>
<?php endif; ?>
<?php include 'sa_footer.php'; ?>
