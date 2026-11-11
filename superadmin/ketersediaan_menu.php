<?php
// superadmin/ketersediaan_menu.php (POST) — membalik status Tersedia <-> Tidak tersedia.
// Halaman pemesan langsung mengikuti, karena membaca kolom yang sama di tabel menu.
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../404.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch();
if (!$menu) {
    header('Location: ../404.php');
    exit;
}

$baru = $menu['tersedia'] ? 0 : 1;
$stmt = $pdo->prepare("UPDATE menu SET tersedia = ?, updated_at = ? WHERE id = ?");
$stmt->execute([$baru, date('Y-m-d H:i:s'), $id]);

$_SESSION['pesan'] = $menu['nama'] . ' sekarang ' . ($baru ? 'Tersedia' : 'Tidak tersedia') . '.';
$_SESSION['jenis'] = 'sukses';

// Kembali ke daftar dengan filter yang sama
header('Location: kelola_menu.php?kategori=' . urlencode($_POST['kategori'] ?? 'Semua') . '&cari=' . urlencode($_POST['cari'] ?? ''));
exit;
?>
