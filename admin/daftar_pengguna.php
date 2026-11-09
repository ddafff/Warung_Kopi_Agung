<?php
// admin/daftar_pengguna.php — daftar akun PELANGGAN (sandi tidak pernah ditampilkan)
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_admin.php';

$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT * FROM users WHERE role = 'pelanggan'";
$param = [];
if ($cari != '') {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $param = ['%' . $cari . '%', '%' . $cari . '%'];
}
$stmt = $pdo->prepare($sql . " ORDER BY username ASC LIMIT 200");
$stmt->execute($param);
$daftar = $stmt->fetchAll();

$judul = 'Daftar Pengguna';
$menu_aktif = 'pengguna';
include 'ad_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Daftar Pengguna</h1>
        <p class="teks-pendukung">Daftar pelanggan terdaftar. Data ini hanya bisa dilihat.</p>
    </div>
</div>

<section class="kartu-tabel">
    <div class="alat-tabel">
        <form class="formulir-cari" method="GET" style="width:100%">
            <div class="kolom-cari">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input class="isian" type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari username atau email">
            </div>
            <button class="tombol tombol-sekunder tombol-kecil" type="submit">Cari</button>
            <?php if ($cari != ''): ?><a class="tombol tombol-kecil" href="daftar_pengguna.php">Hapus pencarian</a><?php endif; ?>
        </form>
    </div>

    <div class="pembungkus-tabel">
        <table class="tabel-data">
            <thead><tr><th>Username</th><th>Email</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if (!$daftar): ?>
                    <tr><td class="tabel-kosong" colspan="3">Pelanggan tidak ditemukan.</td></tr>
                <?php endif; ?>
                <?php foreach ($daftar as $u): ?>
                    <tr>
                        <td data-label="Username"><span class="sel-pengguna"><span class="avatar-inisial"><?= strtoupper(substr($u['username'], 0, 1)) ?></span> @<?= htmlspecialchars($u['username']) ?></span></td>
                        <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="sel-aksi">
                            <a class="tombol tombol-sekunder tombol-kecil" href="riwayat_pengguna.php?id=<?= $u['id'] ?>"><i class="fa-solid fa-clock-rotate-left"></i> Lihat Riwayat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="catatan-bawah-tabel"><span>Menampilkan <?= count($daftar) ?> pelanggan</span></div>
</section>
<?php include 'ad_footer.php'; ?>
