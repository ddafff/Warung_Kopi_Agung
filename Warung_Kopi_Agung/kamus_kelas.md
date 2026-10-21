# Kamus Kelas — Desain Google Stitch → Bahasa Indonesia

Desain Stitch memakai **Tailwind CSS** (banyak kelas pendek seperti `flex items-center gap-space-sm`).
Di proyek ini semuanya diganti menjadi **kelas bernama jelas dalam bahasa Indonesia** yang didefinisikan sekali di `CSS/tampilan.css`.
Tailwind CDN, Material Symbols, dan `tailwind.config` **tidak dipakai lagi** (ikon memakai Font Awesome, sesuai rancangan).

## 1. Komponen (Stitch → kelas Indonesia)

| Bagian di desain Stitch | Kelas baru | Keterangan |
|---|---|---|
| `<header class="fixed top-0 ...">` | `kepala-situs`, `kepala-isi` | Header hijau, menempel di atas |
| logo + "WARUNG KOPI AGUNG" | `merek`, `logo-bulat`, `nama-merek` | |
| tombol panah kembali | `tombol-kembali` | Hanya di halaman bertipe `tumpuk` |
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
| Riwayat: kartu transaksi | `daftar-riwayat`, `kartu-riwayat`, `kartu-riwayat-atas`, `waktu-riwayat`, `kotak-menu-ringkas`, `kartu-riwayat-bawah` | |
| Riwayat: kosong | `keadaan-kosong` | |
| Detail: item & catatan | `baris-item`, `kotak-catatan`, `kelompok-lencana` | |
| 403 / 404 | `halaman-error`, `angka-error` | |
| tombol | `tombol` + `tombol-utama` / `tombol-sekunder` / `tombol-putih` / `tombol-bahaya` / `tombol-penuh` | |
| judul & teks | `judul-halaman`, `judul-bagian`, `baris-judul`, `teks-pendukung`, `teks-kecil` | |
| bantu | `tersembunyi`, `pusat`, `sembunyi-visual` | |

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

## 3. Penanda untuk JavaScript (`JS/User.js`)

| Atribut / id | Fungsi |
|---|---|
| `#pemantau` | Penanda halaman yang memantau status. `data-id` = nomor pesanan, `data-mode` = `bayar` atau `status` |
| `data-isi="label_pembayaran"` / `"label_pesanan"` | Teks yang diganti otomatis |
| `data-status-dari="status_pembayaran"` / `"status_pesanan"` | Lencana yang warnanya diganti otomatis |
| `#kotak-berhasil`, `#kotak-batal` | Kotak "Pembayaran berhasil" dan "Pembayaran dibatalkan" |
| `#pesan-koneksi` | Pesan "Menghubungkan ulang…" |
| `#kotak-qr` (`data-qr`) | Tempat gambar QR |
| `data-pesan-toast` | Tombol + nonaktif yang menampilkan pesan kecil |
| `data-tombol-mata` | Tombol tampil/sembunyi kata sandi |
