<?php
// admin/pesanan_masuk.php — daftar pesanan untuk kasir.
// Tabelnya diisi oleh JS/Admin.js (mulaiPesanan) dari data_pesanan.php, diperbarui tiap 5 detik.
session_start();
require_once '../koneksi.php';
include 'cek_admin.php';

$judul = 'Pesanan Masuk';
$menu_aktif = 'pesanan';
include 'ad_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Pesanan Masuk</h1>
        <p class="teks-pendukung">Konfirmasi pembayaran tunai dan cetak struk pesanan.</p>
    </div>
    <div class="aksi-kepala">
        <span class="kotak-jam"><i class="fa-regular fa-clock"></i> <span data-jam><?= date('H:i') ?> WIB</span></span>
        <button class="tombol tombol-perbarui" type="button" onclick="muatPesanan()"><i class="fa-solid fa-rotate"></i> Perbarui</button>
    </div>
</div>

<section class="kartu-tabel">
    <div class="alat-tabel">
        <nav class="tab-filter">
            <button class="tab-filter-tombol aktif" type="button" data-tab="semua" onclick="pilihTab('semua', this)">
                <span>Semua</span> <span class="tab-filter-angka" id="angka-semua">0</span></button>
            <button class="tab-filter-tombol" type="button" data-tab="belum_dibayar" onclick="pilihTab('belum_dibayar', this)">
                <span>Belum dibayar</span> <span class="tab-filter-angka" id="angka-belum_dibayar">0</span></button>
            <button class="tab-filter-tombol" type="button" data-tab="menunggu_tunai" onclick="pilihTab('menunggu_tunai', this)">
                <span>Menunggu bayar tunai</span> <span class="tab-filter-angka" id="angka-menunggu_tunai">0</span></button>
            <button class="tab-filter-tombol" type="button" data-tab="sudah_dibayar" onclick="pilihTab('sudah_dibayar', this)">
                <span>Sudah dibayar</span> <span class="tab-filter-angka" id="angka-sudah_dibayar">0</span></button>
        </nav>
        <div class="kolom-cari">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input class="isian" type="search" id="kolom-cari" placeholder="Cari meja atau username" oninput="cariPesanan(this.value)">
        </div>
    </div>

    <div class="pembungkus-tabel">
        <table class="tabel-data">
            <thead>
                <tr><th>No.</th><th>Meja</th><th>Username</th><th>Metode</th><th>Total</th><th>Status</th><th>Waktu</th><th>Aksi</th></tr>
            </thead>
            <tbody id="isi-tabel">
                <tr><td class="tabel-kosong" colspan="8">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="catatan-bawah-tabel">
        <span id="ringkasan-tabel"></span>
        <span id="peringatan-tabel" class="pesan-peringatan-kecil"></span>
        <span>Pembaruan otomatis tiap 5 detik</span>
    </div>
</section>
<?php include 'ad_footer.php'; ?>
