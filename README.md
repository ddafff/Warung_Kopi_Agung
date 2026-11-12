<<<<<<< HEAD
=======
<<<<<<< HEAD
# Warung_Kopi_Agung
=======
>>>>>>> d33efa4
# Warung Kopi Agung — Sistem Pemesanan QR Code

PHP native + MySQL (PDO) + HTML/CSS/JS biasa. Pembayaran QRIS memakai Midtrans **Sandbox**.
Gaya penulisan kode mengikuti proyek referensi: satu berkas per halaman, header/footer terpisah, tanpa fungsi rumit.

## Cara pasang di XAMPP
1. Salin folder ini ke `C:\xampp\htdocs\Warung_Kopi_Agung`. Jalankan Apache dan MySQL.
2. Buat database dan tabel sesuai **Bagian 5 rancangan** (nama tabel dan kolom harus persis sama).
3. Buka `koneksi.php`, isi data database, `BASE_URL` (alamat situs, dipakai untuk isi QR code meja), dan `MIDTRANS_SERVER_KEY` (Server Key **Sandbox**).
   Bila `koneksi.php` Anda sudah terisi, **jangan ditimpa**: cukup tambahkan satu baris `define('BASE_URL', 'http://localhost/Warung_Kopi_Agung');`.
   Untuk QR yang dipindai dari HP, ganti `localhost` dengan IP laptop (contoh `http://192.168.1.10/Warung_Kopi_Agung`).
4. Isi data awal secara manual: nomor meja di tabel `meja`, menu di tabel `menu`.
   Foto menu ditaruh di `gambar/` (di kolom `gambar` tulis nama berkasnya saja). Logo: `gambar/logo.png`.
5. Akun Admin/Superadmin dibuat manual. Buat sandi terenkripsi dengan:
   `php -r "echo password_hash('SandiAnda', PASSWORD_DEFAULT);"` lalu tempel hasilnya di kolom `password`.
6. Buka `http://localhost/Warung_Kopi_Agung/user/us_index.php?meja=1` (QR meja berisi alamat seperti ini).
   Untuk uji dari HP, ganti `localhost` dengan IP laptop.
7. Bila nama folder bukan `Warung_Kopi_Agung`, sesuaikan dua baris `ErrorDocument` di `.htaccess` dan alamat CSS di `halaman_error.php`.

## Peta berkas (untuk menjelaskan saat pengujian)

| Berkas | Tugasnya |
|---|---|
| `koneksi.php` | Koneksi database (`$pdo`) dan kunci Midtrans |
| `fungsi.php` | Fungsi kecil: `rupiah()`, `label_*()`, `tanggal_indo()`, `inisial()` |
| `midtrans.php` | `buat_qris()` membuat QR, `periksa_qris()` menarik status dari Midtrans |
| `pesan.php` | Menampilkan pesan sekali tampil (`$_SESSION['pesan']`) |
| `403.php`, `404.php`, `halaman_error.php` | Halaman kesalahan (satu templat) |
| `CSS/tampilan.css`, `CSS/Admin.css` | Tampilan. Nama kelas berbahasa Indonesia (`kamus_kelas.md`) |
| `JS/User.js`, `JS/Admin.js` | JavaScript pelanggan dan admin/superadmin (Admin.js juga berisi grafik, QR, pratinjau foto) |

**Folder `user/` (pelanggan)**

| Berkas | Tugasnya |
|---|---|
| `us_header.php`, `us_footer.php` | Bagian atas dan bawah semua halaman pelanggan |
| `us_index.php` | Halaman Menu: ambil data (meja dari QR, kategori, menu, keranjang) lalu menampilkan header + isi + footer |
| `us_beranda.php`, `us_kartu_menu.php` | Isi Halaman Menu dan satu kartu menu |
| `login.php`, `daftar.php`, `logout.php` | Masuk (satu pintu untuk semua peran), daftar pelanggan, keluar |
| `cek_login.php` | Dipasang di awal halaman pelanggan: harus login sebagai pelanggan |
| `keranjang.php` | (POST) tambah / kurang menu di keranjang |
| `checkout.php` | Konfirmasi pesanan dan pilih metode bayar |
| `proses_checkout.php` | (POST) simpan pesanan; tunai atau QRIS |
| `menunggu_tunai.php`, `bayar_qris.php` | Halaman menunggu bayar (dipantau otomatis) |
| `status_pesanan.php`, `cek_status.php` | Status pesanan dan data JSON untuk pemantauan |
| `ambil_pesanan.php` | Mengambil pesanan dari `?id=` dan memeriksa pemiliknya (404 / 403) |
| `riwayat_pesanan.php`, `detail_pesanan.php` | Riwayat dan rincian pesanan |

**Folder `admin/` (kasir; Superadmin juga boleh masuk)**

| Berkas | Tugasnya |
|---|---|
| `ad_header.php`, `ad_footer.php` | Kerangka halaman admin (menu samping) |
| `cek_admin.php`, `cek_admin_json.php` | Pemeriksaan akses; keluar otomatis setelah 30 menit tidak aktif |
| `pesanan_masuk.php`, `data_pesanan.php` | Tabel pesanan dan data JSON-nya |
| `konfirmasi_tunai.php` | (POST) kasir mengonfirmasi bayar tunai |
| `antrean_dapur.php`, `data_antrean.php` | Antrean dapur dan data JSON-nya |
| `selesai_pesanan.php` | (POST) tandai pesanan selesai |
| `cetak_struk.php` | Struk (hanya pesanan yang sudah dibayar) |
| `daftar_pengguna.php`, `riwayat_pengguna.php` | Daftar pelanggan dan riwayat pesanannya (hanya baca) |


**Folder `superadmin/` (pemilik; hanya Superadmin)**

| Berkas | Tugasnya |
|---|---|
| `sa_header.php`, `sa_footer.php` | Kerangka halaman superadmin. Memakai kerangka admin sehingga **Superadmin mewarisi semua menu Admin** (`admin/ad_menu.php`) |
| `cek_superadmin.php` | Pemeriksaan akses: hanya Superadmin (Admin -> 403), keluar otomatis 30 menit |
| `periode.php`, `periode_tab.php` | Rentang waktu harian / mingguan / bulanan dan tombol pilihannya |
| `dashboard.php` | Dashboard Analitik: Menu Terlaris, Jam Ramai, Tren Penjualan (Chart.js) |
| `laporan_penjualan.php` | Total penjualan dan rincian pesanan yang sudah dibayar, tombol Cetak |
| `kelola_meja.php`, `tambah_meja.php`, `edit_meja.php`, `alamat_qr.php` | Daftar meja + QR, tambah, edit, dan alamat yang dimasukkan ke QR |
| `kelola_menu.php`, `tambah_menu.php`, `edit_menu.php` | Daftar menu (cari, kategori, saklar), tambah, edit |
| `hapus_menu.php` | (POST) hapus menu: ditolak bila pernah dipesan, bila belum -> konfirmasi Ya |
| `ketersediaan_menu.php` | (POST) membalik Tersedia / Tidak tersedia |
| `foto_menu.php` | `simpan_foto_menu()` (JPG/PNG, maks 2 MB, isi diperiksa) dan `hapus_foto_menu()` |

## Alur singkat
- **Tunai:** pelanggan pilih Cash -> pesanan `pending_cash` -> kasir tekan Konfirmasi -> `paid` + `dapur` -> dapur tekan Tandai Selesai -> `selesai`.
- **QRIS:** pelanggan pilih QRIS -> `buat_qris()` -> QR ditampilkan -> halaman memanggil `cek_status.php` tiap 5 detik -> `periksa_qris()` bertanya ke Midtrans -> `paid` + `dapur`.
- Semua perubahan data lewat formulir POST biasa. `fetch` hanya dipakai untuk membaca status (GET).

## Kesesuaian dengan activity diagram Admin

| Diagram | Langkah di diagram | Ada di kode |
|---|---|---|
| 1. Pesanan masuk + cek QRIS | Membuka halaman pesanan masuk | `admin/pesanan_masuk.php` |
| | Mengambil pesanan QRIS yang belum dibayar, lalu menanyakan status ke Midtrans untuk tiap pesanan | `periksa_qris()` di `midtrans.php`, dipanggil dari `admin/data_pesanan.php` |
| | Berhasil -> Sudah dibayar + Diproses dapur | `settlement` / `capture` di `periksa_qris()` |
| | Menunggu -> tidak ada perubahan | status `pending` dilewati (`continue`) |
| | Kedaluwarsa / dibatalkan -> Dibatalkan, tidak tampil di daftar kasir | `expire` / `cancel` / `deny`; kueri `data_pesanan.php` memakai `status_pesanan <> 'dibatalkan'` |
| | Menampilkan daftar dengan tab Belum dibayar, Menunggu bayar tunai, Sudah dibayar | tab di `pesanan_masuk.php`, diisi `tampilkanPesanan()` di `JS/Admin.js` |
| 2. Konfirmasi tunai | Memilih tab Menunggu bayar tunai, mencari meja / username | `pilihTab()` dan `cariPesanan()` di `JS/Admin.js` (kueri `LIKE` di `data_pesanan.php`) |
| | Menekan Konfirmasi -> Sudah dibayar + Diproses dapur | `admin/konfirmasi_tunai.php` |
| 3. Antrean dapur | Menampilkan pesanan Sudah dibayar, paling awal di atas | `admin/antrean_dapur.php` + `admin/data_antrean.php` (`ORDER BY waktu_masuk ASC`) |
| | Menandai Selesai, sistem menyimpan status Selesai | `admin/selesai_pesanan.php` |
| 4. Cetak struk | Memilih pesanan yang sudah dibayar, menekan Cetak | tombol Cetak hanya muncul pada pesanan Sudah dibayar |
| | Struk berisi nomor pesanan, meja, menu, total, cara bayar, waktu | `admin/cetak_struk.php` (lalu `window.print()`) |

Catatan: setelah menekan Konfirmasi, halaman dimuat ulang. Tab dan kata cari terakhir disimpan di `sessionStorage` (`JS/Admin.js`), jadi kasir tetap berada di tab Menunggu bayar tunai.

## Kesesuaian dengan activity diagram Superadmin

Kegiatan Admin (pesanan masuk + cek QRIS, konfirmasi tunai, antrean dapur, cetak struk) diwarisi: Superadmin membuka halaman `admin/` yang sama.

| Diagram | Langkah di diagram | Ada di kode |
|---|---|---|
| Dashboard analitik | Memilih periode harian / mingguan / bulanan | `periode.php` (`?periode=`), tombol di `periode_tab.php` |
| | Mengambil pesanan Sudah dibayar pada periode itu | `$kondisi_laporan` di `periode.php` |
| | Data tidak ada -> "Belum ada data" | `dashboard.php` (`$ada_data`) |
| | Menghitung menu terlaris, jam ramai, tren penjualan (tiga kotak sejajar) | tiga kueri di `dashboard.php`; kotak `grid-analitik`; grafik oleh `gambarGrafik()` di `JS/Admin.js` |
| Tambah nomor meja | Mengisi nomor; nomor sudah dipakai -> pesan, kembali mengisi | `tambah_meja.php` ("Nomor meja sudah dipakai") |
| | Menyimpan nomor dan membuat QR code, lalu dicetak | `tambah_meja.php` + `gambarSemuaQR()` dan `cetakQR()` |
| Edit nomor meja | Mengisi nomor baru; sudah dipakai -> pesan | `edit_meja.php` (nomor boleh sama dengan miliknya sendiri) |
| | Menyimpan perubahan dan membuat QR baru, mengganti yang lama | `edit_meja.php` (peringatan "QR lama tidak berlaku lagi") |
| Laporan penjualan | Memilih periode, mengambil pesanan Sudah dibayar | `laporan_penjualan.php` |
| | Data ada -> tabel rinci dan total; tidak ada -> "Belum ada data" | `laporan_penjualan.php` |
| Tambah menu | Mengisi nama, harga, kategori, foto; data lengkap? | `tambah_menu.php` (pesan kesalahan lalu kembali ke formulir) |
| | Menyimpan menu baru (status Tersedia), menampilkan daftar terbaru | `tambah_menu.php` -> `kelola_menu.php` |
| Edit menu | Form berisi data menu saat ini; nama belum dipakai? | `edit_menu.php` |
| | Menyimpan perubahan atau menampilkan pesan kesalahan | `edit_menu.php` |
| Hapus menu | Menu pernah dipesan -> ditolak, sarankan Tidak tersedia | `hapus_menu.php` |
| | Belum pernah dipesan -> "Hapus menu ini?", tekan Ya, menu dihapus | `hapus_menu.php` (tombol `ya`, foto ikut dihapus) |
| Atur ketersediaan | Mengubah status; tampilan menu pemesan ikut berubah | `ketersediaan_menu.php` (pelanggan membaca kolom `tersedia` yang sama) |

## Uji pembayaran
- **QRIS:** bayar memakai simulator QRIS Sandbox Midtrans.
- **Tunai:** masuk sebagai admin, buka Pesanan Masuk, tekan Konfirmasi.

## Keputusan (ikut rancangan, berbeda dari gambar Stitch)
- Masuk memakai **email** dan kata sandi, bukan nomor HP. Header hijau.
- Admin dan Superadmin memakai menu samping. Fitur gambar Stitch yang tidak ada di rancangan dan diagram tidak dibuat (Tambah Pengguna, Ekspor/Unduh Excel, pajak, barcode, kapasitas kursi dan area meja, kartu "total pengunjung", dan lain-lain).
- Hapus menu: diagram menulis cabang "Ya" dan "Tidak" tertukar. Kode mengikuti aturan rancangan: menu yang pernah dipesan **tidak boleh** dihapus, menu yang belum pernah dipesan baru ditanya "Hapus menu ini?".
- Kode sengaja dibuat sederhana: tanpa token CSRF dan tanpa fungsi bantu rumit. Keamanan dasar tetap ada: kueri memakai `prepare()`, teks ditampilkan dengan `htmlspecialchars()`, sandi memakai `password_hash()`, dan akses dibatasi per peran.
<<<<<<< HEAD
=======
>>>>>>> 643be15 (sisa satu fitur, next gweh push)
>>>>>>> d33efa4
