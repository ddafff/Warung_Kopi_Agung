<?php
// admin/antrean_dapur.php — pesanan yang sudah dibayar dan sedang diproses (terlama di atas).
// Kartunya diisi oleh JS/Admin.js (mulaiAntrean) dari data_antrean.php, diperbarui tiap 5 detik.
session_start();
require_once '../koneksi.php';
include 'cek_admin.php';

$judul = 'Antrean Dapur';
$menu_aktif = 'antrean';
include 'ad_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Antrean Dapur</h1>
        <p class="teks-pendukung"><span id="jumlah-antrean">0 pesanan sedang disiapkan</span> &bull; Hanya pesanan yang sudah dibayar</p>
    </div>
    <div class="aksi-kepala">
        <span class="kotak-jam"><i class="fa-regular fa-clock"></i> <span data-jam><?= date('H:i') ?> WIB</span></span>
        <button class="tombol tombol-perbarui" type="button" id="tombol-bunyi" onclick="ubahBunyi()">
            <i class="fa-solid fa-volume-xmark" id="ikon-bunyi"></i> <span id="teks-bunyi">Bunyi: Mati</span></button>
        <button class="tombol tombol-perbarui" type="button" onclick="muatAntrean()"><i class="fa-solid fa-rotate"></i> Perbarui Antrean</button>
    </div>
</div>

<p class="pesan-koneksi" id="pesan-koneksi"></p>

<div class="grid-antrean" id="antrean">
    <div class="kartu keadaan-kosong kosong-antrean"><p class="teks-pendukung">Memuat data...</p></div>
</div>
<?php include 'ad_footer.php'; ?>
