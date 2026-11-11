<?php
// user/daftar.php — pendaftaran pelanggan baru (peran selalu "pelanggan")
session_start();
require_once '../koneksi.php';

$kembali = $_POST['kembali'] ?? $_GET['kembali'] ?? '';
if (!preg_match('/^[a-z_]+\.php(\?[a-zA-Z0-9_=%+&]+)?$/', $kembali)) {
    $kembali = '';
}

if (isset($_SESSION['user_id'])) {
    header('Location: us_index.php');
    exit;
}

$error = '';
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Cek apakah username / email sudah dipakai
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $ada = $stmt->fetch();

    if ($username == '' || $email == '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
        $error = 'Username 3-30 karakter, hanya huruf, angka, titik, dan garis bawah.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi minimal 8 karakter.';
    } elseif ($ada && $ada['username'] == $username) {
        $error = 'Username sudah dipakai, coba yang lain.';
    } elseif ($ada) {
        $error = 'Email sudah terdaftar. Silakan masuk.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'pelanggan', ?)");
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT) , date('Y-m-d H:i:s')]);

        // Langsung login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['role']    = 'pelanggan';
        header('Location: ' . ($kembali != '' ? $kembali : 'us_index.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Warung Kopi Agung</title>
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
        </div>

        <div class="kartu-formulir">
            <h1 class="judul-halaman">Daftar Akun</h1>
            <p class="teks-pendukung">Bergabung dan pesan kopi langsung dari meja Anda.</p>

            <?php if ($error): ?>
                <div class="pesan pesan-galat"><i class="fa-solid fa-circle-exclamation"></i><span><?= $error ?></span></div>
            <?php endif; ?>

            <form class="formulir" method="POST">
                <input type="hidden" name="kembali" value="<?= htmlspecialchars($kembali) ?>">

                <div class="kolom-isian">
                    <label class="label-isian" for="username"><i class="fa-solid fa-at"></i> Username</label>
                    <input class="isian" type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Buat username" required>
                    <span class="petunjuk-isian">Dipakai kasir untuk mengenali pesanan tunai Anda.</span>
                </div>
                <div class="kolom-isian">
                    <label class="label-isian" for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                    <input class="isian" type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="contoh@email.com" required>
                </div>
                <div class="kolom-isian">
                    <label class="label-isian" for="password"><i class="fa-solid fa-lock"></i> Kata sandi</label>
                    <div class="isian-dengan-tombol">
                        <input class="isian" type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
                        <button class="tombol-mata" type="button" onclick="togglePassword()"><i class="fa-solid fa-eye" id="ikonMata"></i></button>
                    </div>
                </div>

                <button class="tombol tombol-utama tombol-penuh" type="submit">Daftar <i class="fa-solid fa-arrow-right"></i></button>
            </form>
        </div>

        <p class="tautan-bawah-formulir">Sudah punya akun? <a href="login.php<?= $kembali ? '?kembali=' . urlencode($kembali) : '' ?>">Masuk</a></p>
    </div>
</main>
<script src="../JS/User.js"></script>
</body>
</html>
