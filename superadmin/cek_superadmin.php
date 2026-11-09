<?php
// superadmin/cek_superadmin.php — dipasang di awal setiap halaman superadmin.
// Pemeriksaan login dan 30 menit tidak aktif sama dengan admin; bedanya hanya superadmin yang boleh masuk (admin -> 403).
include '../admin/cek_admin.php';
if ($_SESSION['role'] != 'superadmin') {
    header('Location: ../403.php');
    exit;
}
?>
