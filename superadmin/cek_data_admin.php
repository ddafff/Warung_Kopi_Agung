<?php
// superadmin/cek_data_admin.php — validasi data admin sebelum disimpan (dipakai tambah_admin.php dan edit_admin.php).
// Variabel dari halaman pemanggil: $username, $email, $password, $mode ('tambah'|'edit'), $id_admin (0 saat tambah, ID admin saat edit)

$error = '';

if (!preg_match('/^[A-Za-z0-9_. ]{3,30}$/', $username)) {
    $error = 'Username 3-30 karakter, hanya huruf, angka, titik, dan garis bawah.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Format email tidak valid.';
} elseif (($mode == 'tambah' || $password != '') && strlen($password) < 8) {
    $error = 'Kata sandi minimal 8 karakter.';
} else {
    // Cek duplikasi — $id_admin dikecualikan agar username/email milik admin yang diedit tidak dianggap duplikat
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE (username = ? OR email = ?) AND id <> ?");
    $stmt->execute([$username, $email, $id_admin]);
    $ada = $stmt->fetch();
    if ($ada && $ada['username'] === $username) {
        $error = 'Username sudah dipakai, coba yang lain.';
    } elseif ($ada) {
        $error = 'Email sudah terdaftar pada akun lain.';
    }
}
?>