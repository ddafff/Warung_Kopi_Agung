<?php
// login.php — SATU portal masuk untuk semua peran (pelanggan, admin, superadmin).
require_once '../koneksi.php';
require_once '../fungsi.php';
mulai_sesi();

// Sudah masuk: langsung ke halaman perannya
if (!empty($_SESSION['user_id'])) {
    alihkan(halaman_peran($_SESSION['role'] ?? null));
}

$kembali = url_kembali_aman($_POST['kembali'] ?? $_GET['kembali'] ?? null);
$email   = '';
$galat   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $sandi = (string) ($_POST['password'] ?? '');

    $pengguna = null;
    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT id, nama, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $pengguna = $stmt->fetch() ?: null;
    }

    // Selalu memeriksa sandi (walau akun tidak ada) agar waktu balasan tidak membocorkan akun
    $hash = $pengguna['password'] ?? password_hash('sandi-pura-pura', PASSWORD_DEFAULT);
    $cocok = password_verify($sandi, $hash);

    if ($pengguna && $cocok) {
        buat_sesi_login($pengguna);
        if ($pengguna['role'] === 'pelanggan') {
            alihkan($kembali ?? halaman_peran('pelanggan'));
        }
        alihkan(halaman_peran($pengguna['role']));
    }
    $galat = 'Email atau kata sandi salah';
}

$judul_halaman = 'Masuk';
$tipe_halaman  = 'polos';
include 'us_header.php';
?>
<div class="kotak-masuk">
    <div class="merek-tengah">
        <?= html_logo() ?>
        <span class="nama-merek">WARUNG KOPI AGUNG</span>
        <span class="teks-pendukung">Pesan dari meja, tanpa antre</span>
    </div>

    <div class="kartu-formulir">
        <h1 class="judul-halaman">Masuk</h1>
        <p class="teks-pendukung">Silakan masuk untuk melanjutkan pesanan meja Anda.</p>

        <?php cetak_pesan(); ?>
        <?php if ($galat): ?>
            <div class="pesan pesan-galat" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span><?= e($galat) ?></span></div>
        <?php endif; ?>

        <form class="formulir" method="post" action="login.php" autocomplete="on">
            <?= csrf_input() ?>
            <input type="hidden" name="kembali" value="<?= e($kembali ?? '') ?>">

            <div class="kolom-isian">
                <label class="label-isian" for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                <input class="isian" type="email" id="email" name="email" value="<?= e($email) ?>"
                       placeholder="contoh@email.com" required autocomplete="email">
            </div>

            <div class="kolom-isian">
                <label class="label-isian" for="password"><i class="fa-solid fa-lock"></i> Kata sandi</label>
                <div class="isian-dengan-tombol">
                    <input class="isian" type="password" id="password" name="password"
                           placeholder="Masukkan kata sandi" required autocomplete="current-password">
                    <button class="tombol-mata" type="button" data-tombol-mata="password"
                            aria-label="Tampilkan atau sembunyikan kata sandi"><i class="fa-solid fa-eye"></i></button>
                </div>
            </div>

            <button class="tombol tombol-utama tombol-penuh" type="submit">
                Masuk <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <p class="tautan-bawah-formulir">
        Belum punya akun?
        <a href="daftar.php<?= $kembali ? '?kembali=' . urlencode($kembali) : '' ?>">Daftar</a>
    </p>
    <p class="tautan-bawah-formulir"><a href="us_index.php"><i class="fa-solid fa-arrow-left"></i> Lihat menu saja</a></p>
</div>
<?php include 'us_footer.php'; ?>
