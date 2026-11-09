// JS/User.js — JavaScript halaman pelanggan (JS biasa, tanpa jQuery)

var intervalPantau = 5000;   // memeriksa status tiap 5 detik (5000 milidetik)

// ---------- Tampil / sembunyi kata sandi (halaman masuk dan daftar) ----------
function togglePassword() {
    var input = document.getElementById('password');
    var ikon = document.getElementById('ikonMata');

    if (input.type === 'password') {
        input.type = 'text';
        ikon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        ikon.className = 'fa-solid fa-eye';
    }
}

// ---------- Pemberitahuan kecil di bawah layar ----------
function tampilkanToast(pesan) {
    var kotak = document.getElementById('toast-info');
    document.getElementById('toast-teks').textContent = pesan;
    kotak.classList.add('tampil');
    setTimeout(function () {
        kotak.classList.remove('tampil');
    }, 2800);
}

// ---------- Hitungan huruf pada kolom catatan (halaman checkout) ----------
function hitungCatatan() {
    var catatan = document.getElementById('catatan');
    document.getElementById('hitung-catatan').textContent = catatan.value.length;
}

// ---------- Gambar kode QR (halaman bayar QRIS) ----------
var kotakQR = document.getElementById('kotak-qr');
if (kotakQR) {
    new QRCode(kotakQR, {
        text: kotakQR.getAttribute('data-qr'),
        width: 220,
        height: 220
    });
}

// ---------- Pantau status pesanan ----------
// Halaman yang memantau punya elemen <div id="pemantau" data-id="..." data-mode="..." data-selesai="...">
//   data-mode "bayar"  : berhenti saat sudah dibayar atau dibatalkan
//   data-mode "status" : berhenti saat pesanan selesai atau dibatalkan
var pemantau = document.getElementById('pemantau');
if (pemantau && pemantau.getAttribute('data-selesai') != '1') {
    pantauStatus();
}

function pantauStatus() {
    var id = pemantau.getAttribute('data-id');
    var mode = pemantau.getAttribute('data-mode');
    var pesanKoneksi = document.getElementById('pesan-koneksi');
    var teksAwal = pesanKoneksi.textContent;

    function periksa() {
        // Tab sedang tidak dilihat: lewati dulu, coba lagi nanti
        if (document.hidden) {
            setTimeout(periksa, intervalPantau);
            return;
        }

        fetch('cek_status.php?id=' + id)
            .then(function (respons) {
                if (respons.status == 403) {          // sesi habis -> ke halaman masuk
                    window.location.href = 'login.php';
                    return null;
                }
                return respons.json();
            })
            .then(function (data) {
                if (data === null) return;
                pesanKoneksi.textContent = teksAwal;
                tampilkanStatus(data);

                // Berhenti bila sudah selesai
                var berhenti = (mode == 'bayar')
                    ? (data.status_pembayaran == 'paid' || data.status_pesanan == 'dibatalkan')
                    : data.selesai;
                if (!berhenti) {
                    setTimeout(periksa, intervalPantau);
                }
            })
            .catch(function () {
                pesanKoneksi.textContent = 'Menghubungkan ulang…';
                setTimeout(periksa, intervalPantau);
            });
    }

    periksa();   // pemeriksaan pertama langsung dijalankan
}

// Perbarui teks dan warna lencana, lalu tampilkan kotak berhasil / batal
function tampilkanStatus(data) {
    // <span data-isi="label_pembayaran"> -> isinya diganti teks terbaru
    document.querySelectorAll('[data-isi]').forEach(function (el) {
        el.textContent = data[el.getAttribute('data-isi')];
    });
    // <span data-status-dari="status_pesanan"> -> warna lencana mengikuti status (diatur CSS lewat data-status)
    document.querySelectorAll('[data-status-dari]').forEach(function (el) {
        el.setAttribute('data-status', data[el.getAttribute('data-status-dari')]);
    });

    var berhasil = document.getElementById('kotak-berhasil');
    var batal = document.getElementById('kotak-batal');
    if (berhasil && data.status_pembayaran == 'paid') {
        berhasil.classList.remove('tersembunyi');
    }
    if (batal && data.status_pesanan == 'dibatalkan' && data.status_pembayaran != 'paid') {
        batal.classList.remove('tersembunyi');
    }
}
