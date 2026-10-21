<?php
// daftar.php — pendaftaran pelanggan baru. Pendaftaran hanya membuat peran "pelanggan".
require_once '../koneksi.php';
require_once '../fungsi.php';
mulai_sesi();

if (!empty($_SESSION['user_id'])) {
    alihkan(halaman_peran($_SESSION['role'] ?? null));
}

$kembali = url_kembali_aman($_POST['kembali'] ?? $_GET['kembali'] ?? null);
$nilai   = ['nama' => '', 'username' => '', 'no_hp' => '', 'email' => ''];
$galat   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf();
    foreach ($nilai as $kunci => $_) {
        $nilai[$kunci] = trim((string) ($_POST[$kunci] ?? ''));
    }
    $sandi = (string) ($_POST['password'] ?? '');

    // Nomor HP: terima 08..., 628..., +628...; simpan dalam bentuk 08...
    $hp = preg_replace('/[\s\-]/', '', $nilai['no_hp']);
    $hp = preg_replace('/^(\+62|62)/', '0', $hp);
    $nilai['no_hp'] = $hp;

    $panjang_nama = mb_strlen($nilai['nama'], 'UTF-8');
    if ($panjang_nama < 2 || $panjang_nama > 100) {
        $galat[] = 'Nama harus 2 sampai 100 karakter.';
    }
    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $nilai['username'])) {
        $galat[] = 'Username 3 sampai 30 karakter, hanya huruf, angka, titik, dan garis bawah.';
    }
    if (!preg_match('/^0[0-9]{8,13}$/', $hp)) {
        $galat[] = 'Nomor HP tidak valid. Contoh: 081234567890.';
    }
    if (!filter_var($nilai['email'], FILTER_VALIDATE_EMAIL) || strlen($nilai['email']) > 150) {
        $galat[] = 'Format email tidak valid.';
    }
    if (strlen($sandi) < 8) {
        $galat[] = 'Kata sandi minimal 8 karakter.';
    }

    if (!$galat) {
        $cek = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $cek->execute([$nilai['username'], $nilai['email']]);
        foreach ($cek->fetchAll() as $ada) {
            if (strcasecmp($ada['username'], $nilai['username']) === 0) {
                $galat[] = 'Username sudah dipakai, coba yang lain.';
            }
            if (strcasecmp($ada['email'], $nilai['email']) === 0) {
                $galat[] = 'Email sudah terdaftar. Silakan masuk.';
            }
        }
        $galat = array_values(array_unique($galat));
    }

    if (!$galat) {
        try {
            $simpan = $pdo->prepare(
                "INSERT INTO users (nama, username, no_hp, email, password, role, created_at)
                 VALUES (?, ?, ?, ?, ?, 'pelanggan', NOW())"
            );
            $simpan->execute([
                $nilai['nama'], $nilai['username'], $hp, $nilai['email'],
                password_hash($sandi, PASSWORD_DEFAULT),
            ]);
            buat_sesi_login([
                'id' => (int) $pdo->lastInsertId(), 'nama' => $nilai['nama'], 'role' => 'pelanggan',
            ]);
            alihkan($kembali ?? halaman_peran('pelanggan'));
        } catch (PDOException $e) {
            tulis_log('Daftar gagal: ' . $e->getMessage());
            $galat[] = ($e->getCode() === '23000')
                ? 'Username atau email sudah terdaftar.'
                : 'Terjadi kesalahan, silakan coba lagi';
        }
    }
}

$judul_halaman = 'Daftar Akun';
$tipe_halaman  = 'polos';
include 'us_header.php';
?>
<div class="kotak-masuk">
    <div class="merek-tengah">
        <?= html_logo() ?>
        <span class="nama-merek">WARUNG KOPI AGUNG</span>
    </div>

    <div class="kartu-formulir">
        <h1 class="judul-halaman">Daftar Akun</h1>
        <p class="teks-pendukung">Bergabung dan pesan kopi langsung dari meja Anda.</p>

        <?php foreach ($galat as $g): ?>
            <div class="pesan pesan-galat" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span><?= e($g) ?></span></div>
        <?php endforeach; ?>

        <form class="formulir" method="post" action="daftar.php" autocomplete="on">
            <?= csrf_input() ?>
            <input type="hidden" name="kembali" value="<?= e($kembali ?? '') ?>">

            <div class="kolom-isian">
                <label class="label-isian" for="nama"><i class="fa-solid fa-user"></i> Nama</label>
                <input class="isian" type="text" id="nama" name="nama" value="<?= e($nilai['nama']) ?>"
                       placeholder="Nama lengkap" maxlength="100" required autocomplete="name">
            </div>
            <div class="kolom-isian">
                <label class="label-isian" for="username"><i class="fa-solid fa-at"></i> Username</label>
                <input class="isian" type="text" id="username" name="username" value="<?= e($nilai['username']) ?>"
                       placeholder="Buat username" maxlength="30" required autocomplete="username">
                <span class="petunjuk-isian">Dipakai kasir untuk mengenali pesanan tunai Anda.</span>
            </div>
            <div class="kolom-isian">
                <label class="label-isian" for="no_hp"><i class="fa-solid fa-phone"></i> Nomor HP</label>
                <input class="isian" type="tel" id="no_hp" name="no_hp" value="<?= e($nilai['no_hp']) ?>"
                       placeholder="081234567890" inputmode="numeric" required autocomplete="tel">
            </div>
            <div class="kolom-isian">
                <label class="label-isian" for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                <input class="isian" type="email" id="email" name="email" value="<?= e($nilai['email']) ?>"
                       placeholder="contoh@email.com" maxlength="150" required autocomplete="email">
            </div>
            <div class="kolom-isian">
                <label class="label-isian" for="password"><i class="fa-solid fa-lock"></i> Kata sandi</label>
                <div class="isian-dengan-tombol">
                    <input class="isian" type="password" id="password" name="password"
                           placeholder="Minimal 8 karakter" minlength="8" required autocomplete="new-password">
                    <button class="tombol-mata" type="button" data-tombol-mata="password"
                            aria-label="Tampilkan atau sembunyikan kata sandi"><i class="fa-solid fa-eye"></i></button>
                </div>
            </div>

            <button class="tombol tombol-utama tombol-penuh" type="submit">
                Daftar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <p class="tautan-bawah-formulir">
        Sudah punya akun?
        <a href="login.php<?= $kembali ? '?kembali=' . urlencode($kembali) : '' ?>">Masuk</a>
    </p>
</div>
<?php include 'us_footer.php'; ?>
