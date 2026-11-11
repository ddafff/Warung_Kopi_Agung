<?php
// admin/cek_admin.php — dipasang di awal setiap halaman admin.
// Belum login -> halaman masuk. Bukan admin/superadmin -> 403. Tidak aktif 30 menit -> keluar otomatis.
// (session_start() sudah dipanggil di halaman yang memuat berkas ini)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit;
}
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin') {
    header('Location: ../403.php');
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
