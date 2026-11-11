<?php
// user/login.php — SATU halaman masuk untuk semua peran (pelanggan, admin, superadmin)
session_start();
require_once '../koneksi.php';

// Halaman tujuan setelah login (hanya nama berkas biasa, contoh: checkout.php)
$kembali = $_POST['kembali'] ?? $_GET['kembali'] ?? '';
if (!preg_match('/^[a-z_]+\.php(\?[a-zA-Z0-9_=%+&]+)?$/', $kembali)) {
    $kembali = '';
}

// Halaman utama tiap peran
$halaman_peran = [
    'pelanggan'  => 'us_index.php',
    'admin'      => '../admin/pesanan_masuk.php',
    'superadmin' => '../superadmin/dashboard.php',
];

// Sudah login -> langsung ke halaman perannya
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $halaman_peran[$_SESSION['role']]);
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $akun = $stmt->fetch();

    if ($akun && password_verify($password, $akun['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $akun['id'];
        $_SESSION['username'] = $akun['username'];
        $_SESSION['role']     = $akun['role'];
        $_SESSION['aktivitas_terakhir'] = time();

        // Pelanggan kembali ke halaman asal; admin/superadmin ke panelnya
        if ($akun['role'] == 'pelanggan' && $kembali != '') {
            header('Location: ' . $kembali);
        } else {
            header('Location: ' . $halaman_peran[$akun['role']]);
        }
        exit;
    }
    $error = 'Email atau kata sandi salah';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Warung Kopi Agung</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/tampilan.css">
</head>
<body class="badan-halaman">
<main class="halaman-masuk">
    <div class="kotak-masuk">
        <div class="merek-tengah">
            <span class="logo-bulat"><i class="fa-solid fa-mug-hot"></i></span>
            <span class="nama-merek">WARUNG KOPI AGUNG</span>
            <span class="teks-pendukung">Pesan dari meja, tanpa antre</span>
        </div>

        <div class="kartu-formulir">
            <h1 class="judul-halaman">Masuk</h1>
            <p class="teks-pendukung">Silakan masuk untuk melanjutkan pesanan meja Anda.</p>

            <?php include '../pesan.php'; ?>
            <?php if ($error): ?>
                <div class="pesan pesan-galat"><i class="fa-solid fa-circle-exclamation"></i><span><?= $error ?></span></div>
            <?php endif; ?>

            <form class="formulir" method="POST">
                <input type="hidden" name="kembali" value="<?= htmlspecialchars($kembali) ?>">

                <div class="kolom-isian">
                    <label class="label-isian" for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                    <input class="isian" type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="contoh@email.com" required>
                </div>

                <div class="kolom-isian">
                    <label class="label-isian" for="password"><i class="fa-solid fa-lock"></i> Kata sandi</label>
                    <div class="isian-dengan-tombol">
                        <input class="isian" type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                        <button class="tombol-mata" type="button" onclick="togglePassword()"><i class="fa-solid fa-eye" id="ikonMata"></i></button>
                    </div>
                </div>

                <button class="tombol tombol-utama tombol-penuh" type="submit">Masuk <i class="fa-solid fa-arrow-right"></i></button>
            </form>
        </div>

        <p class="tautan-bawah-formulir">Belum punya akun? <a href="daftar.php<?= $kembali ? '?kembali=' . urlencode($kembali) : '' ?>">Daftar</a></p>
        <p class="tautan-bawah-formulir"><a href="us_index.php"><i class="fa-solid fa-arrow-left"></i> Lihat menu saja</a></p>
    </div>
</main>
<script src="../JS/User.js"></script>
</body>
</html>
