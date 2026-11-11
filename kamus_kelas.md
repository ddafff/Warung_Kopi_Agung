# Kamus Kelas — Desain Google Stitch → Bahasa Indonesia

Desain Stitch memakai **Tailwind CSS** (banyak kelas pendek seperti `flex items-center gap-space-sm`).
Di proyek ini semuanya diganti menjadi **kelas bernama jelas dalam bahasa Indonesia** yang didefinisikan sekali di `CSS/tampilan.css`.
Tailwind CDN, Material Symbols, dan `tailwind.config` **tidak dipakai lagi** (ikon memakai Font Awesome, sesuai rancangan).

## 1. Komponen (Stitch → kelas Indonesia)

| Bagian di desain Stitch | Kelas baru | Keterangan |
|---|---|---|
| `<header class="fixed top-0 ...">` | `kepala-situs`, `kepala-isi` | Header hijau, menempel di atas |
| logo + "WARUNG KOPI AGUNG" | `merek`, `logo-bulat`, `nama-merek` | |
| tombol panah kembali | `tombol tombol-sekunder tombol-kecil` | Tombol "Kembali" ditaruh di isi halaman |
| menu atas | `navigasi-atas`, `tautan-nav`, `info-pengguna`, `nama-pengguna` | |
| `<nav class="fixed bottom-0 ...">` | `nav-bawah`, `nav-bawah-isi`, `nav-bawah-tautan` | Hanya tampil di HP |
| `<main class="pt-16 pb-24 ...">` | `konten-utama` (+ `ada-bilah-bawah`) | |
| pembungkus lebar | `kontainer` (1100px), `kontainer-sempit` (640px) | |
| Hero "Meja Anda 02" | `spanduk-meja`, `spanduk-meja-label`, `spanduk-meja-angka`, `spanduk-meja-teks` | |
| callout "Silakan login untuk memesan" | `kotak-ajakan` | Mode tamu |
| `category-btn` / `category-chip` | `chip-kategori` (+ `aktif`), pembungkus `deretan-kategori` | |
| daftar kartu menu | `daftar-menu` (grid) | |
| kartu satu menu | `kartu-menu` (+ `kartu-menu-nonaktif`) | |
| gambar 64×64 | `foto-menu` | Kalau foto kosong, tampil ikon cangkir |
| nama / harga / label | `info-menu`, `nama-menu`, `harga`, `status-ketersediaan` (+ `tidak-tersedia`) | |
| `btn-add-item` (tombol +) | `tombol-tambah` (+ `tombol-nonaktif`) | Hijau terang / abu-abu |
| `#cart-bar` | `bilah-keranjang`, `bilah-keranjang-kiri` | |
| toast `#loginToast` | `toast-info` (+ `tampil`) | |
| kartu putih | `kartu`, `kartu-judul` | |
| baris "label … nilai" | `baris-rincian`, `nilai` | |
| garis pemisah `h-px` | `garis-pemisah`, `garis-putus` | |
| badge status | `lencana` + atribut `data-status` | Warna otomatis menurut status |
| Daftar/Masuk: kartu form | `kartu-formulir`, `kotak-masuk`, `merek-tengah`, `halaman-masuk` | |
| label + input | `formulir`, `kolom-isian`, `label-isian`, `isian`, `isian-teks` | |
| mata sandi `#togglePassword` | `isian-dengan-tombol`, `tombol-mata` | |
| kotak error merah | `pesan` + `pesan-galat` | Juga `pesan-info`, `pesan-sukses`, `pesan-peringatan` |
| Checkout: kartu meja | `kartu-meja`, `nomor-meja-besar` | |
| Checkout: baris pesanan | `daftar-pesanan`, `baris-pesanan`, `pengatur-jumlah`, `tombol-jumlah` (+ `tambah`), `angka-jumlah` | |
| Checkout: catatan | `isian-teks`, `hitungan-catatan` | |
| Checkout: rincian total | `rincian-biaya`, `baris-total`, `harga-total` | |
| Checkout: kartu QRIS / Cash | `pilihan-metode`, `kartu-metode`, `kartu-metode-isi`, `ikon-metode`, `kartu-metode-teks`, `tanda-pilih` | Dipilih lewat `input:checked`, tanpa JS |
| Checkout: bar bawah | `bilah-bayar`, `bilah-bayar-isi`, `ringkasan` | |
| Tunai: tiket struk | `kartu-struk`, `kartu-struk-atas`, `kartu-struk-isi`, `angka-total` | |
| Tunai: ikon besar | `ikon-hero` | |
| QRIS: kotak QR | `kotak-qr` | Digambar `qrcodejs` |
| QRIS: "Bayar sebelum 14:35" | `batas-waktu` | |
| QRIS: "Mode uji coba" | `kotak-uji` | |
| teks "Menghubungkan ulang…" | `pesan-koneksi` | |
| Riwayat: kartu transaksi | `daftar-riwayat`, `kartu-riwayat`, `waktu-riwayat`, `kotak-menu-ringkas`, `kartu-riwayat-bawah` | |
| Riwayat: kosong | `keadaan-kosong` | |
| Detail: item & catatan | `baris-item`, `kotak-catatan`, `kelompok-lencana` | |
| 403 / 404 | `halaman-error`, `angka-error` | |
| tombol | `tombol` + `tombol-utama` / `tombol-sekunder` / `tombol-putih` / `tombol-bahaya` / `tombol-penuh` | |
| judul & teks | `judul-halaman`, `judul-bagian`, `baris-judul`, `teks-pendukung`, `teks-kecil` | |
| bantu | `tersembunyi`, `pusat`, `sembunyi-visual` | |

## 1b. Komponen Admin (CSS/Admin.css)

| Bagian di desain Stitch | Kelas baru | Keterangan |
|---|---|---|
| `<header>` + menu atas admin | `kepala-situs` (dari tampilan.css) + `menu-samping`, `menu-samping-tautan` (+ `aktif`), `menu-samping-judul`, `menu-samping-pemisah`, `menu-samping-bawah` | Di HP menjadi laci; dibuka `tombol-menu`, latar gelap `latar-menu-samping` |
| tombol hamburger | `tombol-menu` | Hanya tampil di layar sempit |
| kerangka halaman | `rangka-admin`, `konten-admin` | |
| label peran "Kasir/Pemilik" | `penanda-peran` | |
| judul halaman + tombol kanan | `kepala-halaman`, `aksi-kepala` | |
| jam "12:36 WIB" | `kotak-jam` (isi diperbarui oleh `data-jam-langsung`) | |
| tombol Perbarui | `tombol-perbarui` | |
| kartu putih berisi tabel | `kartu-tabel`, `alat-tabel`, `pembungkus-tabel` | |
| chip filter + angka | `tab-filter`, `tab-filter-tombol` (+ `aktif`), `tab-filter-angka` | |
| kolom cari | `kolom-cari`, `formulir-cari` | |
| tabel | `tabel-data`, `sel-aksi`, `sel-kanan`, `tabel-kosong` | Di layar sempit tiap baris menjadi kartu (label dari `data-label`) |
| "Meja 02" | `chip-meja` | |
| ikon + username | `sel-pengguna`, `avatar-inisial` (+ `avatar-besar`) | |
| ikon + Tunai/QRIS | `pil-metode` | |
| teks kecil di bawah lencana | `status-kecil` | |
| catatan di bawah tabel | `catatan-bawah-tabel`, `pesan-peringatan-kecil` | |
| Antrean: kartu pesanan | `grid-antrean`, `kartu-antrean` (+ `terlama`), `kartu-antrean-garis`, `kartu-antrean-isi`, `kartu-antrean-atas`, `kartu-antrean-bawah` | |
| Antrean: "#008", "Meja 04" | `nomor-antrean`, `meja-antrean`, `tanda-terlama` | |
| Antrean: jam + "Menunggu: 10 mnt" | `waktu-antrean`, `waktu-tunggu` (+ `lama` bila 15 menit atau lebih) | |
| Antrean: daftar menu & catatan | `daftar-item-antrean`, `item-antrean`, `catatan-antrean` | |
| Struk | `area-pratinjau`, `area-struk`, `struk-kepala`, `struk-nama`, `struk-pembagi`, `struk-baris`, `struk-item-nama`, `struk-item-rinci`, `struk-total`, `struk-penutup`, `aksi-struk` | Cetak: `@media print`, kertas 80 mm |
| Riwayat Pengguna: kartu profil | `kartu-profil`, `profil-info`, `profil-meta`, `profil-angka` | |
| banner "Riwayat hanya dapat dilihat" | `banner-baca-saja` | |
| baris waktu dua tingkat | `waktu-dua-baris` | |

Penanda JavaScript Admin: `#daftar-pesanan` / `#antrean` (daerah yang dipantau, membawa `data-csrf`, `data-url-data`), `#isi-tabel`, `#ringkasan-tabel`, `#peringatan-tabel`, `#pesan-koneksi`, `#jumlah-antrean`, `#tombol-perbarui`, `#tombol-bunyi`, `#kolom-cari`, `data-angka`, `data-tab`, `data-konfirmasi`, `data-cetak`, `data-jam-langsung`.

## 1c. Komponen Superadmin (CSS/Admin.css)

| Bagian di desain Stitch | Kelas baru | Keterangan |
|---|---|---|
| tombol periode Harian / Mingguan / Bulanan | `tab-filter`, `tab-filter-tombol` (+ `aktif`) | Sama dengan tab Pesanan Masuk |
| tiga kotak analitik sejajar | `grid-analitik`, `kotak-analitik`, `kanvas-grafik` | Satu kolom di layar sempit |
| daftar "Menu Terlaris" | `baris-terlaris`, `baris-terlaris-atas`, `nomor-terlaris`, `nama-terlaris`, `porsi-terlaris`, `bar-terlaris`, `isi-bar-terlaris` | |
| kartu total laporan | `grid-ringkasan`, `kartu-ringkas`, `angka-ringkas` | |
| kartu satu meja + QR | `grid-meja`, `kartu-meja-kelola`, `kotak-qr-kecil`, `info-meja-kelola`, `aksi-meja-kelola` | |
| jendela QR besar | `modal-qr` (+ `terbuka`), `isi-modal-qr`, `kotak-qr` | |
| bagian yang ikut tercetak | `area-cetak-qr` (dengan `body.cetak-qr`), `sembunyi-cetak` | Aturan `@media print` |
| saklar Tersedia | `saklar`, `saklar-geser` | Digeser = formulir terkirim |
| foto kecil di tabel menu | `foto-tabel` | |
| tombol Edit + Hapus | `aksi-baris`, `tombol-bahaya` | |
| formulir menu / meja | `formulir`, `kolom-isian`, `aksi-form`, `pratinjau-foto` | |
| konfirmasi hapus | `konfirmasi-hapus` | |

Penanda JavaScript tambahan: `#grafik-jam` dan `#grafik-tren` (`data-label`, `data-nilai`), `data-qr` + `data-ukuran` (QR yang digambar otomatis), `#modal-qr`, `#judul-qr`, `#qr-besar`, `#foto` dan `#pratinjau-foto`.

## 2. Warna dan ukuran (token Tailwind → variabel CSS)

| Token Stitch | Variabel baru | Nilai |
|---|---|---|
| `primary-container` / tombol utama | `--hijau-tua` | `#0B7A0B` |
| `primary` | `--hijau-gelap` | `#005F03` |
| `secondary-fixed` (tombol +) | `--hijau-terang` | `#3DBE5C` |
| `secondary-container` | `--hijau-muda` | `#E3F6E6` |
| nonaktif | `--abu-nonaktif` | `#BDBDBD` |
| `surface-container-low` | `--latar-sekunder` | `#F4F6F4` |
| `surface-container-lowest` | `--latar-kartu` | `#FFFFFF` |
| `on-surface` | `--teks` | `#141B2B` |
| `on-surface-variant` | `--teks-redup` | `#5B6558` |
| `error` / `error-container` | `--merah` / `--merah-muda` | |
| badge kuning "Diproses dapur" | `--kuning` / `--kuning-muda` | |
| `rounded-xl` | `--sudut` | `12px` |
| font `Plus Jakarta Sans` | tetap | dimuat dari Google Fonts |

## 3. Penanda untuk JavaScript

| Atribut / id | Dipakai oleh | Fungsi |
|---|---|---|
| `#pemantau` (`data-id`, `data-mode`, `data-selesai`) | `User.js` | Penanda halaman pelanggan yang memantau status |
| `data-isi="label_pembayaran"` / `"label_pesanan"` | `User.js` | Teks yang diganti otomatis |
| `data-status-dari="status_pembayaran"` / `"status_pesanan"` | `User.js` | Lencana yang warnanya ikut berubah |
| `#kotak-berhasil`, `#kotak-batal`, `#pesan-koneksi` | `User.js` | Kotak hasil dan pesan koneksi |
| `#kotak-qr` (`data-qr`) | `User.js` | Tempat gambar QR |
| `#isi-tabel`, `#angka-semua`, `#angka-belum_dibayar`, `#angka-menunggu_tunai`, `#angka-sudah_dibayar` | `Admin.js` | Isi tabel dan angka pada tab Pesanan Masuk |
| `#antrean`, `#jumlah-antrean` | `Admin.js` | Tempat kartu Antrean Dapur |
| `#ringkasan-tabel`, `#peringatan-tabel` | `Admin.js` | Teks di bawah tabel |
| `data-jam` | `Admin.js` | Jam di kepala halaman |
