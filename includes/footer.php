        </main>

        <footer class="app-footer">
            <span>&copy; <?= date('Y') ?> FrozenStock &middot; Aplikasi Pencatatan Produk Frozen Food</span>
            <span>Dibuat dengan PHP Native &amp; MySQL</span>
        </footer>
    </div>
</div>

<!-- Modal konfirmasi hapus, dipakai oleh semua tombol hapus -->
<div class="modal fade" id="konfirmasiHapus" tabindex="-1" aria-labelledby="judulKonfirmasiHapus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-konfirmasi">
            <div class="modal-body">
                <span class="modal-icon-danger">
                    <svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg>
                </span>
                <h2 class="modal-title" id="judulKonfirmasiHapus">Hapus data ini?</h2>
                <p class="modal-text">
                    Data <strong id="konfirmasiNama">ini</strong> akan dihapus secara permanen
                    dan tidak bisa dikembalikan.
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" id="formHapus" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="hapusId" value="">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg>
                        Ya, hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
