<?php
// superadmin/tambah_menu.php — tambah menu baru (nama, harga, kategori, foto). Menu baru otomatis Tersedia.
session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';
include 'foto_menu.php';

$daftar_kategori = ['Kopi', 'Non Kopi', 'Makanan', 'Snack'];
$nama = '';
$harga = '';
$kategori = 'Kopi';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $harga    = preg_replace('/[^0-9]/', '', $_POST['harga'] ?? '');     // "22.000" -> 22000
    $kategori = $_POST['kategori'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM menu WHERE nama = ?");
    $stmt->execute([$nama]);

    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 40) {
        $error = 'Nama menu wajib diisi (2 sampai 40 karakter).';
    } elseif ($harga == '' || (int)$harga < 1 || (int)$harga > 10000000) {
        $error = 'Harga harus berupa angka lebih dari 0.';
    } elseif (!in_array($kategori, $daftar_kategori)) {
        $error = 'Pilih kategori menu.';
    } elseif ($stmt->fetch()) {
        $error = 'Nama menu sudah ada. Harap gunakan nama pembeda.';
    } elseif (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) == UPLOAD_ERR_NO_FILE) {
        $error = 'Foto menu wajib diunggah.';
    } else {
        // Data lengkap -> simpan foto, lalu simpan menu
        $foto = simpan_foto_menu($_FILES['foto']);
        if ($foto['error'] != '') {
            $error = $foto['error'];
        } else {
            $sekarang = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO menu (nama, harga, kategori, gambar, tersedia, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, ?)");
            $stmt->execute([$nama, (int)$harga, $kategori, $foto['nama'], $sekarang, $sekarang]);

            $_SESSION['pesan'] = 'Menu "' . $nama . '" berhasil ditambahkan.';
            $_SESSION['jenis'] = 'sukses';
            header('Location: kelola_menu.php');
            exit;
        }
    }
}

$judul = 'Tambah Menu';
$menu_aktif = 'menu';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Tambah Menu Baru</h1>
        <p class="teks-pendukung">Isi detail menu. Menu baru otomatis berstatus Tersedia.</p>
    </div>
</div>

<section class="kartu">
    <?php if ($error): ?>
        <div class="pesan pesan-galat"><i class="fa-solid fa-circle-exclamation"></i><span><?= $error ?></span></div>
    <?php endif; ?>

    <form class="formulir" method="POST" enctype="multipart/form-data">
        <div class="kolom-isian">
            <label class="label-isian" for="nama">Nama menu *</label>
            <input class="isian" type="text" id="nama" name="nama" maxlength="40" value="<?= htmlspecialchars($nama) ?>" placeholder="Contoh: Kopi Susu Gula Aren" required>
        </div>
        <div class="kolom-isian">
            <label class="label-isian" for="harga">Harga (Rp) *</label>
            <input class="isian" type="text" id="harga" name="harga" inputmode="numeric" value="<?= htmlspecialchars($harga) ?>" placeholder="Contoh: 18000" required>
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
            <label class="label-isian" for="foto">Foto menu * (JPG atau PNG, maksimal 2 MB)</label>
            <input class="isian" type="file" id="foto" name="foto" accept="image/jpeg,image/png" onchange="previewFoto(this)" required>
            <img class="pratinjau-foto tersembunyi" id="pratinjau-foto" alt="Pratinjau foto">
        </div>
        <div class="aksi-form">
            <a class="tombol tombol-sekunder" href="kelola_menu.php">Batal</a>
            <button class="tombol tombol-utama" type="submit"><i class="fa-solid fa-check"></i> Simpan Menu</button>
        </div>
    </form>
</section>
<?php include 'sa_footer.php'; ?>
