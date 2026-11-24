/**
 * Interaksi ringan untuk aplikasi FrozenStock.
 * - Mengisi data pada modal konfirmasi hapus.
 * - Menutup notifikasi otomatis setelah beberapa detik.
 */
document.addEventListener('DOMContentLoaded', function () {
    const modalHapus = document.getElementById('konfirmasiHapus');

    if (modalHapus) {
        modalHapus.addEventListener('show.bs.modal', function (event) {
            const tombol = event.relatedTarget;

            if (!tombol) {
                return;
            }

            const form = modalHapus.querySelector('#formHapus');
            const inputId = modalHapus.querySelector('#hapusId');
            const nama = modalHapus.querySelector('#konfirmasiNama');

            form.action = tombol.getAttribute('data-hapus-url') || '';
            inputId.value = tombol.getAttribute('data-hapus-id') || '';
            nama.textContent = '“' + (tombol.getAttribute('data-hapus-nama') || 'data ini') + '”';
        });
    }

    const modalStok = document.getElementById('modalStok');

    if (modalStok) {
        const form = modalStok.querySelector('#formStok');
        const inputId = modalStok.querySelector('#stokProdukId');
        const inputNama = modalStok.querySelector('#stokNamaProduk');
        const inputKategori = modalStok.querySelector('#stokKategoriProduk');
        const inputAksi = modalStok.querySelector('#stokAksi');
        const inputJumlah = modalStok.querySelector('#stokJumlah');
        const inputHarga = modalStok.querySelector('#stokHarga');
        const inputKeterangan = modalStok.querySelector('#stokKeterangan');
        const inputTujuan = modalStok.querySelector('#stokTujuan');
        const stokSaatIni = modalStok.querySelector('#stokSaatIni');
        const hargaSaatIni = modalStok.querySelector('#hargaSaatIni');
        const pratinjau = modalStok.querySelector('#stokPratinjau');
        const formatRupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        });

        let stokAwal = 0;
        let satuan = 'pack';

        function perbaruiPratinjau() {
            const jumlah = parseInt(inputJumlah.value, 10) || 0;
            const hasil = inputAksi.value === 'kurang' ? stokAwal - jumlah : stokAwal + jumlah;
            const teks = pratinjau.querySelector('strong');

            pratinjau.classList.toggle('stok-minus', hasil < 0);
            teks.textContent = hasil < 0 ? 'Stok tidak boleh minus' : hasil + ' ' + satuan;
        }

        modalStok.addEventListener('show.bs.modal', function (event) {
            const tombol = event.relatedTarget;

            if (!tombol) {
                return;
            }

            stokAwal = parseInt(tombol.getAttribute('data-stok-jumlah') || '0', 10) || 0;
            satuan = tombol.getAttribute('data-stok-satuan') || 'pack';

            inputId.value = tombol.getAttribute('data-stok-id') || '';
            inputNama.textContent = tombol.getAttribute('data-stok-nama') || 'Nama produk';
            inputKategori.textContent = tombol.getAttribute('data-stok-kategori') || '';
            stokSaatIni.textContent = stokAwal + ' ' + satuan;
            hargaSaatIni.textContent = formatRupiah.format(
                Number(tombol.getAttribute('data-stok-harga') || 0)
            );

            inputAksi.value = 'tambah';
            inputJumlah.value = '0';
            inputHarga.value = Math.round(Number(tombol.getAttribute('data-stok-harga') || 0));
            inputKeterangan.value = '';
            inputTujuan.value = tombol.getAttribute('data-stok-tujuan') || 'produk.php';
            pratinjau.classList.remove('stok-minus');

            perbaruiPratinjau();
        });

        inputAksi.addEventListener('change', perbaruiPratinjau);
        inputJumlah.addEventListener('input', perbaruiPratinjau);
        inputHarga.addEventListener('input', function () {
            if (Number(inputHarga.value) < 1) {
                inputHarga.value = 1;
            }
        });
    }

    document.querySelectorAll('.app-alert').forEach(function (alert) {
        window.setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 6000);
    });
});
