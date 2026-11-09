<?php
// superadmin/hapus_menu.php (POST) — hapus menu.
//  1) Menu pernah dipesan  -> ditolak, sarankan "Tidak tersedia"
//  2) Menu belum pernah    -> minta konfirmasi "Hapus menu ini?" -> bila Ya, menu dan foto dihapus
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';
include 'foto_menu.php';

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

// Apakah menu ini pernah dipesan?
$stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE menu_id = ?");
$stmt->execute([$id]);
$pernah_dipesan = ($stmt->fetchColumn() > 0);

if ($pernah_dipesan) {
    $_SESSION['pesan'] = 'Menu pernah dipesan, tidak bisa dihapus. Ubah menjadi Tidak tersedia';
    $_SESSION['jenis'] = 'galat';
    header('Location: kelola_menu.php');
    exit;
}

// Sudah dikonfirmasi (tombol Ya) -> hapus
if (isset($_POST['ya'])) {
    $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
    $stmt->execute([$id]);
    hapus_foto_menu($menu['gambar']);

    $_SESSION['pesan'] = 'Menu "' . $menu['nama'] . '" sudah dihapus.';
    $_SESSION['jenis'] = 'sukses';
    header('Location: kelola_menu.php');
    exit;
}

// Belum dikonfirmasi -> tampilkan pertanyaan
$judul = 'Hapus Menu';
$menu_aktif = 'menu';
include 'sa_header.php';
?>
<section class="kartu konfirmasi-hapus pusat">
    <div class="ikon-hero"><i class="fa-solid fa-trash"></i></div>
    <h1 class="judul-halaman">Hapus menu ini?</h1>
    <p class="teks-pendukung"><strong><?= htmlspecialchars($menu['nama']) ?></strong> belum pernah dipesan dan akan dihapus permanen.</p>
    <form method="POST" class="aksi-form" style="justify-content:center; margin-top:16px">
        <input type="hidden" name="id" value="<?= $menu['id'] ?>">
        <a class="tombol tombol-sekunder" href="kelola_menu.php">Batal</a>
        <button class="tombol tombol-bahaya" type="submit" name="ya" value="1"><i class="fa-solid fa-trash"></i> Ya, hapus</button>
    </form>
</section>
<?php include 'sa_footer.php'; ?>
