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

    document.querySelectorAll('.app-alert').forEach(function (alert) {
        window.setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 6000);
    });
});
