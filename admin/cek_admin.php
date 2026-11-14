<?php
// admin/cek_admin.php — dipasang di awal setiap halaman admin.
// Belum login -> halaman masuk. Bukan admin/superadmin -> 403. Akun dihapus -> keluar. Tidak aktif 30 menit -> keluar otomatis.
// (session_start() sudah dipanggil di halaman yang memuat berkas ini)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit;
}
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin') {
    header('Location: ../403.php');
    exit;
}
// Akun yang sudah dihapus oleh Super Admin langsung kehilangan akses (tidak menunggu sesi habis)
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['aktivitas_terakhir']);
    $_SESSION['pesan'] = 'Akun Anda sudah tidak terdaftar. Silakan hubungi pemilik.';
    $_SESSION['jenis'] = 'galat';
    header('Location: ../user/login.php');
    exit;
}
if (isset($_SESSION['aktivitas_terakhir']) && time() - $_SESSION['aktivitas_terakhir'] > 1800) {
    unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['aktivitas_terakhir']);
    $_SESSION['pesan'] = 'Anda keluar otomatis karena lama tidak aktif. Silakan masuk lagi.';
    $_SESSION['jenis'] = 'info';
    header('Location: ../user/login.php');
    exit;
}
$_SESSION['aktivitas_terakhir'] = time();
?>
