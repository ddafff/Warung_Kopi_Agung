<?php

session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';

$nama = $username = $no_hp = $email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $mode     = 'tambah';
    $id_admin = 0;
    include 'cek_data_admin.php';     // hasil: $error

    if ($error == '') {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'admin', ?)");
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);

        $_SESSION['pesan'] = 'Admin "' . $username . '" berhasil ditambahkan. Admin dapat masuk dengan email dan kata sandi tersebut.';
        $_SESSION['jenis'] = 'sukses';
        header('Location: kelola_admin.php');
        exit;
    }
}

$judul = 'Tambah Admin';
$menu_aktif = 'admin';
$mode = 'tambah';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Tambah Admin</h1>
        <p class="teks-pendukung">Buat akun kasir baru. Akun langsung bisa masuk lewat halaman masuk yang sama.</p>
    </div>
</div>
<?php include 'form_admin.php'; ?>
<?php include 'sa_footer.php'; ?>