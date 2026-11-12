<?php
// fungsi.php — semua fungsi bantu Warung Kopi Agung (satu berkas).
// Fungsi "murni" (rupiah, label_*, validasi_*, hitung_total, boleh_akses, boleh_hapus_menu)
// tidak menyentuh $_SESSION, basis data, atau jaringan, sehingga mudah diuji.

require_once __DIR__ . '/koneksi.php';

/* ============================================================
   SESI, ALIH HALAMAN, KELUARAN
   ============================================================ */

// Memulai sesi. Satu sesi dipakai di folder user/, admin/, dan superadmin/.
function mulai_sesi(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
    // Admin dan Superadmin keluar otomatis setelah 30 menit tidak aktif
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
        $sekarang = time();
        if (isset($_SESSION['aktivitas_terakhir']) && ($sekarang - $_SESSION['aktivitas_terakhir']) > 1800) {
            keluarkan_pengguna();
            set_pesan('info', 'Anda keluar otomatis karena lama tidak aktif. Silakan masuk lagi.');
        } else {
            $_SESSION['aktivitas_terakhir'] = $sekarang;
        }
    }
}

// Menghapus data login. Nomor meja sengaja dipertahankan.
function keluarkan_pengguna(): void
{
    foreach (['user_id', 'nama', 'role', 'keranjang', 'aktivitas_terakhir', 'catatan_checkout', 'metode_checkout'] as $kunci) {
        unset($_SESSION[$kunci]);
    }
    session_regenerate_id(true);
}

// Membungkus teks agar aman ditampilkan di HTML
function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

// Pindah halaman lalu berhenti
function alihkan(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Mencatat kejadian ke logs/app.log
function tulis_log(string $pesan): void
{
    @error_log('[' . date('Y-m-d H:i:s') . '] ' . $pesan . "\n", 3, __DIR__ . '/logs/app.log');
}

// Balasan JSON dengan status HTTP asli
function kirim_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ============================================================
   PESAN SEKALI TAMPIL (flash)
   ============================================================ */

function set_pesan(string $jenis, string $teks): void
{
    $_SESSION['pesan'][] = ['jenis' => $jenis, 'teks' => $teks];
}

// Mengambil lalu menghapus semua pesan
function ambil_pesan(): array
{
    $pesan = $_SESSION['pesan'] ?? [];
    unset($_SESSION['pesan']);
    return $pesan;
}

/* ============================================================
   CSRF
   ============================================================ */

function buat_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . e(buat_csrf()) . '">';
}

function cek_csrf(?string $token): bool
{
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

// Berkas penangan POST yang dibuka lewat GET menghasilkan 404
function wajib_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        tampilkan_error(404);
    }
}

// Semua POST wajib membawa token yang benar (kalau salah: 403)
function wajib_csrf(): void
{
    if (!cek_csrf($_POST['csrf'] ?? null)) {
        tampilkan_error(403);
    }
}

/* ============================================================
   LOGIN DAN PERAN
   ============================================================ */

// Tujuan "kembali" hanya boleh berupa alamat di situs ini (cegah pengalihan ke situs lain)
function url_kembali_aman(?string $url): ?string
{
    if (!is_string($url) || $url === '') {
        return null;
    }
    $dasar = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    if ($url[0] !== '/' || str_starts_with($url, '//') || str_contains($url, '\\')
        || preg_match('/[\r\n]/', $url)) {
        return null;
    }
    if ($dasar !== '' && !str_starts_with($url, $dasar . '/')) {
        return null;
    }
    return $url;
}

function wajib_login(): void
{
    mulai_sesi();
    if (empty($_SESSION['user_id'])) {
        $kembali = urlencode($_SERVER['REQUEST_URI'] ?? '');
        alihkan(BASE_URL . '/user/login.php?kembali=' . $kembali);
    }
}

function wajib_peran(array $peran_boleh): void
{
    wajib_login();
    if (!boleh_akses($_SESSION['role'] ?? '', $peran_boleh)) {
        tampilkan_error(403);
    }
}

// Alamat halaman utama tiap peran
function halaman_peran(?string $peran): string
{
    switch ($peran) {
        case 'admin':      return BASE_URL . '/admin/pesanan_masuk.php';
        case 'superadmin': return BASE_URL . '/superadmin/dashboard.php';
        default:           return BASE_URL . '/user/us_index.php';
    }
}

/* ============================================================
   HALAMAN KESALAHAN (hanya 403 dan 404, satu templat)
   ============================================================ */

function tampilkan_error(int $kode, ?string $pesan = null): void
{
    mulai_sesi();
    $kode = ($kode === 403) ? 403 : 404;
    http_response_code($kode);

    $judul = ($kode === 403) ? 'Akses ditolak' : 'Halaman tidak ditemukan';
    $isi   = $pesan ?? (($kode === 403)
        ? 'Anda tidak memiliki izin untuk membuka halaman ini.'
        : 'Halaman yang Anda cari tidak ada.');
    $kembali = halaman_peran($_SESSION['role'] ?? null);
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $kode ?> <?= e($judul) ?> — Warung Kopi Agung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/CSS/tampilan.css">
</head>
<body class="badan-halaman">
<main class="halaman-error">
    <p class="angka-error"><?= $kode ?></p>
    <h1 class="judul-halaman"><?= e($judul) ?></h1>
    <p class="teks-pendukung"><?= e($isi) ?></p>
    <a class="tombol tombol-utama" href="<?= e($kembali) ?>">Kembali</a>
</main>
</body>
</html>
    <?php
    exit;
}

/* ============================================================
   FUNGSI MURNI (aman diuji tanpa sesi / basis data)
   ============================================================ */

function rupiah($angka): string
{
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}

function label_pembayaran(string $nilai): string
{
    $peta = [
        'unpaid'       => 'Belum dibayar',
        'pending_cash' => 'Menunggu bayar tunai',
        'paid'         => 'Sudah dibayar',
    ];
    return $peta[$nilai] ?? $nilai;
}

function label_pesanan(string $nilai): string
{
    $peta = [
        'pending'    => 'Menunggu',
        'dapur'      => 'Diproses dapur',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
    return $peta[$nilai] ?? $nilai;
}

function label_metode(string $nilai): string
{
    $peta = ['cash' => 'Tunai', 'qris' => 'QRIS'];
    return $peta[$nilai] ?? $nilai;
}

function label_ketersediaan($nilai): string
{
    return ((int) $nilai === 1) ? 'Tersedia' : 'Tidak tersedia';
}

// Jumlah item: bilangan bulat 0 sampai 99 (0 = hapus). Selain itu: null
function validasi_jumlah($nilai): ?int
{
    if (is_int($nilai)) {
        $angka = $nilai;
    } elseif (is_string($nilai) && preg_match('/^\d{1,3}$/', trim($nilai))) {
        $angka = (int) trim($nilai);
    } else {
        return null;
    }
    return ($angka >= 0 && $angka <= 99) ? $angka : null;
}

// Catatan opsional, maksimal 200 karakter
function validasi_catatan($nilai): array
{
    $teks = trim((string) $nilai);
    $teks = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $teks) ?? '';
    if (mb_strlen($teks, 'UTF-8') > 200) {
        return ['valid' => false, 'nilai' => $teks, 'pesan' => 'Catatan terlalu panjang (maksimal 200 karakter).'];
    }
    return ['valid' => true, 'nilai' => $teks, 'pesan' => ''];
}

// $baris = [ ['harga' => 15000, 'jumlah' => 2], ... ]
function hitung_total(array $baris): int
{
    $total = 0;
    foreach ($baris as $b) {
        $total += ((int) $b['harga']) * ((int) $b['jumlah']);
    }
    return $total;
}

function boleh_akses(string $peran, array $peran_boleh): bool
{
    return in_array($peran, $peran_boleh, true);
}

// Menu yang pernah dipesan tidak boleh dihapus
function boleh_hapus_menu(int $jumlah_pernah_dipesan): bool
{
    return $jumlah_pernah_dipesan === 0;
}

// Contoh: 2026-10-28 14:15:00 -> "28 Okt 2026, 14:15 WIB"
function tanggal_indo(?string $waktu): string
{
    if (!$waktu) {
        return '-';
    }
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($waktu);
    if ($t === false) {
        return '-';
    }
    return date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y, H:i', $t) . ' WIB';
}

// Contoh: "Mie Nyemek x1, Kopi Susu x2"
function ringkas_item(array $item): string
{
    $bagian = [];
    foreach ($item as $i) {
        $bagian[] = $i['nama_menu'] . ' x' . $i['jumlah'];
    }
    return implode(', ', $bagian);
}

/* ============================================================
   BANTUAN DATA PESANAN (memakai basis data)
   ============================================================ */

// Urutan pemeriksaan: id angka (404) -> pesanan ada (404) -> milik sendiri (403)
function muat_pesanan_pelanggan(PDO $pdo, $id): array
{
    if (!is_string($id) || !ctype_digit($id)) {
        tampilkan_error(404);
    }
    $stmt = $pdo->prepare(
        'SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?'
    );
    $stmt->execute([(int) $id]);
    $pesanan = $stmt->fetch();
    if (!$pesanan) {
        tampilkan_error(404);
    }
    if ((int) $pesanan['user_id'] !== (int) ($_SESSION['user_id'] ?? 0)) {
        tampilkan_error(403);
    }
    return $pesanan;
}

function muat_item_pesanan(PDO $pdo, int $order_id): array
{
    $stmt = $pdo->prepare(
        'SELECT oi.nama_menu, oi.harga_satuan, oi.jumlah, oi.subtotal, m.gambar
           FROM order_items oi LEFT JOIN menu m ON m.id = oi.menu_id
          WHERE oi.order_id = ? ORDER BY oi.id'
    );
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Mengambil data menu untuk isi keranjang [menu_id => jumlah]. Hasil: [menu_id => baris menu]
function muat_menu_keranjang(PDO $pdo, array $keranjang): array
{
    if (!$keranjang) {
        return [];
    }
    $id = array_map('intval', array_keys($keranjang));
    $tanda = implode(',', array_fill(0, count($id), '?'));
    $stmt = $pdo->prepare("SELECT id, nama, harga, kategori, gambar, tersedia FROM menu WHERE id IN ($tanda)");
    $stmt->execute($id);
    $hasil = [];
    foreach ($stmt->fetchAll() as $baris) {
        $hasil[(int) $baris['id']] = $baris;
    }
    return $hasil;
}

// Alamat foto menu. Kalau kosong atau berkasnya tidak ada, mengembalikan null
function url_foto_menu(?string $nama_berkas): ?string
{
    if (!$nama_berkas) {
        return null;
    }
    $aman = basename($nama_berkas);
    return is_file(__DIR__ . '/gambar/' . $aman) ? BASE_URL . '/gambar/' . rawurlencode($aman) : null;
}

/* ============================================================
   TAMPILAN BERSAMA
   ============================================================ */

// Mencetak pesan sekali tampil (info, sukses, galat, peringatan)
function cetak_pesan(): void
{
    $ikon = [
        'info'       => 'fa-circle-info',
        'sukses'     => 'fa-circle-check',
        'galat'      => 'fa-circle-exclamation',
        'peringatan' => 'fa-triangle-exclamation',
    ];
    foreach (ambil_pesan() as $p) {
        $jenis = isset($ikon[$p['jenis']]) ? $p['jenis'] : 'info';
        echo '<div class="pesan pesan-' . e($jenis) . '" role="alert"><i class="fa-solid ' . $ikon[$jenis] . '"></i><span>'
            . e($p['teks']) . '</span></div>';
    }
}

// Logo bulat. Memakai gambar/logo.png bila ada, kalau tidak memakai ikon cangkir kopi.
function html_logo(string $kelas = 'logo-bulat'): string
{
    foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp'] as $nama) {
        if (is_file(__DIR__ . '/gambar/' . $nama)) {
            return '<img class="' . e($kelas) . '" src="' . e(BASE_URL . '/gambar/' . $nama) . '" alt="Logo Warung Kopi Agung">';
        }
    }
    return '<span class="' . e($kelas) . '" aria-hidden="true"><i class="fa-solid fa-mug-hot"></i></span>';
}

// Mengisi sesi setelah masuk atau daftar. Nomor meja di sesi dipertahankan.
function buat_sesi_login(array $pengguna): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']            = (int) $pengguna['id'];
    $_SESSION['nama']               = $pengguna['nama'];
    $_SESSION['role']               = $pengguna['role'];
    $_SESSION['aktivitas_terakhir'] = time();
}
