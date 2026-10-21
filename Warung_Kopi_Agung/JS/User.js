/* ============================================================
   User.js — JavaScript halaman pelanggan (JS biasa, tanpa jQuery).
   - Tombol mata (tampilkan/sembunyikan kata sandi)
   - Pemberitahuan kecil (toast)
   - Hitungan huruf catatan
   - Gambar QR QRIS (qrcodejs)
   - pantauStatus(): memantau status pesanan lewat fetch GET tiap 5 detik
   Perubahan data TETAP lewat formulir POST biasa. fetch hanya untuk membaca status.
   ============================================================ */

/* ---------- Pemberitahuan kecil ---------- */
var waktuToast = null;
function tampilkanToast(pesan) {
    var kotak = document.getElementById('toast-info');
    var teks = document.getElementById('toast-teks');
    if (!kotak || !teks) { return; }
    teks.textContent = pesan;
    kotak.classList.add('tampil');
    clearTimeout(waktuToast);
    waktuToast = setTimeout(function () { kotak.classList.remove('tampil'); }, 2800);
}

/* ---------- Pasang perilaku dasar ---------- */
document.addEventListener('DOMContentLoaded', function () {

    // Tombol + yang nonaktif karena belum ada nomor meja
    document.querySelectorAll('[data-pesan-toast]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            tampilkanToast(tombol.getAttribute('data-pesan-toast'));
        });
    });

    // Tombol mata pada kolom kata sandi
    document.querySelectorAll('[data-tombol-mata]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var kolom = document.getElementById(tombol.getAttribute('data-tombol-mata'));
            var ikon = tombol.querySelector('i');
            if (!kolom) { return; }
            var tampil = kolom.type === 'password';
            kolom.type = tampil ? 'text' : 'password';
            if (ikon) { ikon.className = tampil ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'; }
        });
    });

    // Hitungan huruf pada catatan
    var catatan = document.getElementById('catatan');
    var hitung = document.getElementById('hitung-catatan');
    if (catatan && hitung) {
        catatan.addEventListener('input', function () { hitung.textContent = catatan.value.length; });
    }

    // Gambar QR dari teks QRIS
    var kotakQR = document.getElementById('kotak-qr');
    if (kotakQR && typeof QRCode !== 'undefined') {
        new QRCode(kotakQR, {
            text: kotakQR.getAttribute('data-qr'),
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.M
        });
    } else if (kotakQR) {
        kotakQR.textContent = 'Kode QR gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.';
    }

    // Mulai memantau bila halaman punya elemen #pemantau
    var pemantau = document.getElementById('pemantau');
    if (pemantau) { pantauStatus(); }
});

/* ---------- Pemantauan status pesanan ---------- */
// Elemen #pemantau membawa: data-id (nomor pesanan), data-mode ("bayar" atau "status"),
// data-selesai-awal ("1" bila pemantauan tidak perlu dimulai).
//  - mode "bayar"  : berhenti saat pembayaran Sudah dibayar atau pesanan Dibatalkan
//  - mode "status" : berhenti saat pesanan Selesai atau Dibatalkan
function pantauStatus() {
    var pemantau = document.getElementById('pemantau');
    if (!pemantau) { return; }
    if (pemantau.getAttribute('data-selesai-awal') === '1') { return; }

    var idPesanan = pemantau.getAttribute('data-id');
    var mode = pemantau.getAttribute('data-mode') || 'status';
    var interval = window.INTERVAL_PANTAU || 5000;
    var alamat = (window.ALAMAT_DASAR || '') + '/user/cek_status.php?id=' + encodeURIComponent(idPesanan);
    var pesanKoneksi = document.getElementById('pesan-koneksi');
    var teksAwalPesan = pesanKoneksi ? pesanKoneksi.textContent : '';
    var berhenti = false;
    var pewaktu = null;

    function setPesan(teks) { if (pesanKoneksi) { pesanKoneksi.textContent = teks; } }

    // Memperbarui lencana dan teks hanya bila isinya berubah
    function perbarui(data) {
        document.querySelectorAll('[data-isi]').forEach(function (el) {
            var baru = data[el.getAttribute('data-isi')];
            if (baru !== undefined && el.textContent.trim() !== baru) { el.textContent = baru; }
        });
        document.querySelectorAll('[data-status-dari]').forEach(function (el) {
            var baru = data[el.getAttribute('data-status-dari')];
            if (baru !== undefined && el.getAttribute('data-status') !== baru) { el.setAttribute('data-status', baru); }
        });
    }

    function tampilkanHasil(data) {
        var berhasil = document.getElementById('kotak-berhasil');
        var batal = document.getElementById('kotak-batal');
        if (data.status_pembayaran === 'paid' && berhasil) { berhasil.classList.remove('tersembunyi'); }
        if (data.status_pesanan === 'dibatalkan' && data.status_pembayaran !== 'paid' && batal) {
            batal.classList.remove('tersembunyi');
        }
    }

    function sudahSelesai(data) {
        if (mode === 'bayar') {
            return data.status_pembayaran === 'paid' || data.status_pesanan === 'dibatalkan';
        }
        return data.selesai === true;
    }

    function jadwalkan() {
        if (berhenti) { return; }
        clearTimeout(pewaktu);
        pewaktu = setTimeout(ambil, interval);
    }

    function ambil() {
        if (berhenti || document.hidden) { return; }   // tab tersembunyi: jeda dulu
        fetch(alamat, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (respons) {
                if (respons.status === 403) {
                    window.location.href = (window.ALAMAT_DASAR || '') + '/user/login.php?kembali=' +
                        encodeURIComponent(window.location.pathname + window.location.search);
                    return null;
                }
                if (!respons.ok) { throw new Error('status ' + respons.status); }
                return respons.json();
            })
            .then(function (data) {
                if (!data) { return; }
                setPesan(teksAwalPesan);
                if (data.ok) {
                    perbarui(data);
                    tampilkanHasil(data);
                    if (sudahSelesai(data)) { berhenti = true; return; }
                }
                jadwalkan();
            })
            .catch(function () {
                setPesan('Menghubungkan ulang…');
                jadwalkan();
            });
    }

    // Lanjut saat tab terlihat lagi
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && !berhenti) { clearTimeout(pewaktu); ambil(); }
    });

    ambil();   // pemeriksaan pertama langsung dilakukan
}
