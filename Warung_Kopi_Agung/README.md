# Warung Kopi Agung — Sistem Pemesanan QR Code (Fase 1: Sisi Pelanggan)

PHP native + MySQL (PDO) + HTML/CSS/JS biasa. Pembayaran QRIS memakai Midtrans **Sandbox**.

## Isi Fase 1
`koneksi.php`, `koneksi.contoh.php`, `fungsi.php`, `midtrans.php`, `403.php`, `404.php`, `.htaccess`, `.gitignore`,
`CSS/tampilan.css`, `JS/User.js`, dan folder `user/` (menu, daftar, masuk, keranjang, checkout, tunai, QRIS, status, riwayat, detail).
`admin/pesanan_masuk.php` dan `superadmin/dashboard.php` hanya halaman sementara agar login tiap peran bisa diuji.

## Cara pasang di XAMPP
1. Salin folder ini ke `C:\xampp\htdocs\Warung_Kopi_Agung`. Jalankan Apache dan MySQL.
2. Pastikan ekstensi PHP `pdo_mysql`, `mbstring`, dan `curl` aktif (bawaan XAMPP).
3. Buat database dan tabel sendiri sesuai **Bagian 5 rancangan** (nama tabel dan kolom harus persis sama).
4. Buka `koneksi.php`, lalu isi: `BASE_URL`, data database, dan `MIDTRANS_SERVER_KEY` (Server Key **Sandbox**).
   Untuk uji dari HP, ganti `localhost` pada `BASE_URL` dengan IP laptop, misalnya `http://192.168.1.10/Warung_Kopi_Agung`.
5. Di `.htaccess`, sesuaikan `/Warung_Kopi_Agung` pada baris `ErrorDocument` bila nama folder berbeda.
6. Isi data awal secara manual: nomor meja di tabel `meja` dan menu di tabel `menu`.
   Foto menu: taruh di folder `gambar/`, tulis **nama berkasnya saja** di kolom `gambar`. Logo: `gambar/logo.png`.
7. Akun Admin dan Superadmin dibuat manual. Buat sandi terenkripsi dengan:
   `php -r "echo password_hash('SandiAnda', PASSWORD_DEFAULT);"` lalu tempel hasilnya di kolom `password`.
8. Alamat QR meja: `BASE_URL/user/us_index.php?meja=N` (nomor N harus ada di tabel `meja`).

## Uji pembayaran QRIS (Sandbox)
Pilih QRIS di checkout, lalu bayar memakai **simulator pembayaran QRIS Sandbox Midtrans** (lihat dokumentasi Sandbox Midtrans).
Halaman pelanggan memeriksa status tiap 5 detik. Server menanyakan status ke Midtrans (Get Status API), tanpa webhook.

## Uji pembayaran tunai (sebelum Fase 2)
Pesanan tunai baru berubah setelah kasir mengonfirmasi (Fase 2). Untuk uji sementara, ubah lewat phpMyAdmin:
`status_pembayaran = 'paid'` dan `status_pesanan = 'dapur'` pada baris pesanannya.

## Keputusan yang diambil (ikut rancangan, berbeda dari gambar Stitch)
- Masuk memakai **email** dan kata sandi (Bagian 13 poin 1), bukan nomor HP.
- Header hijau dan satu CSS dasar responsif (Bagian 11).
- Tidak ada "Lupa kata sandi", "Biaya Layanan", "pajak", tab "Saya", atau teks yang tidak ada di rancangan.
- Metode bayar di checkout tidak terpilih sejak awal; bila kosong muncul pesan "Pilih metode pembayaran".
- Daftar menu bisa difilter lewat `?kategori=`; nomor meja tersimpan di sesi.

Kamus nama kelas ada di `kamus_kelas.md`.
