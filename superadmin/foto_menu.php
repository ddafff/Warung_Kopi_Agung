<?php
// superadmin/foto_menu.php — fungsi simpan_foto_menu() untuk tambah_menu.php dan edit_menu.php
// Aturan: JPG atau PNG, maksimal 2 MB, isi berkas diperiksa dengan getimagesize(), nama diacak menu_<angka>.<jpg|png>
// Hasil: ['nama' => 'menu_1234.jpg', 'error' => ''] atau ['nama' => '', 'error' => 'pesan kesalahan']
function simpan_foto_menu($berkas) {
    if ($berkas['error'] == UPLOAD_ERR_INI_SIZE || $berkas['error'] == UPLOAD_ERR_FORM_SIZE) {
        return ['nama' => '', 'error' => 'Ukuran foto maksimal 2 MB.'];
    }
    if ($berkas['error'] != UPLOAD_ERR_OK) {
        return ['nama' => '', 'error' => 'Foto gagal diunggah, coba lagi.'];
    }
    if ($berkas['size'] > 2 * 1024 * 1024) {
        return ['nama' => '', 'error' => 'Ukuran foto maksimal 2 MB.'];
    }

    // Periksa isi berkas, bukan hanya namanya
    $info = @getimagesize($berkas['tmp_name']);
    if (!$info || ($info[2] != IMAGETYPE_JPEG && $info[2] != IMAGETYPE_PNG)) {
        return ['nama' => '', 'error' => 'Foto harus berformat JPG atau PNG.'];
    }

    $ekstensi = ($info[2] == IMAGETYPE_PNG) ? 'png' : 'jpg';
    $nama = 'menu_' . time() . mt_rand(100, 999) . '.' . $ekstensi;
    if (!move_uploaded_file($berkas['tmp_name'], '../gambar/' . $nama)) {
        return ['nama' => '', 'error' => 'Foto gagal disimpan di folder gambar.'];
    }
    return ['nama' => $nama, 'error' => ''];
}

// Menghapus berkas foto lama (hanya nama berkas, tanpa folder)
function hapus_foto_menu($nama) {
    if ($nama && is_file('../gambar/' . basename($nama))) {
        unlink('../gambar/' . basename($nama));
    }
}
?>
