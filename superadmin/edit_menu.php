<?php
// superadmin/edit_menu.php?id= — ubah nama, harga, kategori, dan (boleh) foto menu.
// Perubahan harga hanya berlaku untuk pesanan berikutnya (pesanan lama menyimpan salinan harganya sendiri).
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';
include 'foto_menu.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch();
if (!$menu) {
    header('Location: ../404.php');
    exit;
}

$daftar_kategori = ['Kopi', 'Non Kopi', 'Makanan', 'Snack'];
$nama     = $menu['nama'];
$harga    = $menu['harga'];
$kategori = $menu['kategori'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $harga    = preg_replace('/[^0-9]/', '', $_POST['harga'] ?? '');
    $kategori = $_POST['kategori'] ?? '';

    // Nama boleh sama dengan miliknya sendiri, tidak boleh sama dengan menu lain
    $stmt = $pdo->prepare("SELECT id FROM menu WHERE nama = ? AND id <> ?");
    $stmt->execute([$nama, $id]);

    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 40) {
        $error = 'Nama menu wajib diisi (2 sampai 40 karakter).';
    } elseif ($harga == '' || (int)$harga < 1 || (int)$harga > 10000000) {
        $error = 'Harga harus berupa angka lebih dari 0.';
    } elseif (!in_array($kategori, $daftar_kategori)) {
        $error = 'Pilih kategori menu.';
    } elseif ($stmt->fetch()) {
        $error = 'Nama menu sudah ada. Harap gunakan nama pembeda.';
    } else {
        $gambar = $menu['gambar'];
        $foto_baru = (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) != UPLOAD_ERR_NO_FILE);

        if ($foto_baru) {                              // foto diganti (boleh dikosongkan)
            $foto = simpan_foto_menu($_FILES['foto']);
            if ($foto['error'] != '') {
                $error = $foto['error'];
            } else {
                $gambar = $foto['nama'];
            }
        }

        if ($error == '') {
            $stmt = $pdo->prepare("UPDATE menu SET nama = ?, harga = ?, kategori = ?, gambar = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$nama, (int)$harga, $kategori, $gambar, date('Y-m-d H:i:s'), $id]);
            if ($foto_baru) {
                hapus_foto_menu($menu['gambar']);      // foto lama tidak dipakai lagi
            }

            $_SESSION['pesan'] = 'Perubahan menu "' . $nama . '" disimpan.';
            $_SESSION['jenis'] = 'sukses';
            header('Location: kelola_menu.php');
            exit;
        }
    }
}

$judul = 'Edit Menu';
$menu_aktif = 'menu';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Edit Menu</h1>
        <p class="teks-pendukung">Perubahan harga hanya berlaku untuk pesanan berikutnya.</p>
    </div>
</div>

<section class="kartu">
    <?php if ($error): ?>
        <div class="pesan pesan-galat"><i class="fa-solid fa-circle-exclamation"></i><span><?= $error ?></span></div>
    <?php endif; ?>

    <form class="formulir" method="POST" enctype="multipart/form-data">
        <div class="kolom-isian">
            <label class="label-isian" for="nama">Nama menu *</label>
            <input class="isian" type="text" id="nama" name="nama" maxlength="40" value="<?= htmlspecialchars($nama) ?>" required>
        </div>
        <div class="kolom-isian">
            <label class="label-isian" for="harga">Harga (Rp) *</label>
            <input class="isian" type="text" id="harga" name="harga" inputmode="numeric" value="<?= htmlspecialchars($harga) ?>" required>
        </div>
        <div class="kolom-isian">
            <label class="label-isian" for="kategori">Kategori *</label>
            <select class="isian" id="kategori" name="kategori">
                <?php foreach ($daftar_kategori as $k): ?>
                    <option value="<?= $k ?>" <?= $k == $kategori ? 'selected' : '' ?>><?= $k ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="kolom-isian">
            <label class="label-isian" for="foto">Foto menu (kosongkan bila tidak diganti; JPG atau PNG, maksimal 2 MB)</label>
            <?php if ($menu['gambar'] && file_exists('../gambar/' . $menu['gambar'])): ?>
                <img class="pratinjau-foto" id="foto-sekarang" src="../gambar/<?= htmlspecialchars($menu['gambar']) ?>" alt="Foto sekarang">
            <?php endif; ?>
            <input class="isian" type="file" id="foto" name="foto" accept="image/jpeg,image/png" onchange="previewFoto(this)">
            <img class="pratinjau-foto tersembunyi" id="pratinjau-foto" alt="Pratinjau foto baru">
        </div>
        <div class="aksi-form">
            <a class="tombol tombol-sekunder" href="kelola_menu.php">Batal</a>
            <button class="tombol tombol-utama" type="submit"><i class="fa-solid fa-check"></i> Simpan Perubahan</button>
        </div>
    </form>
</section>
<?php include 'sa_footer.php'; ?>
