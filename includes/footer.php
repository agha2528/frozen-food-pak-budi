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

<!-- Modal kelola stok & harga, dipakai oleh daftar produk dan dashboard -->
<div class="modal fade" id="modalStok" tabindex="-1" aria-labelledby="stokNamaProduk" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-stok">
            <form method="post" action="produk-stok.php" id="formStok">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="stokProdukId" value="">
                <input type="hidden" name="tujuan" id="stokTujuan" value="produk.php">

                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow">Kelola Stok &amp; Harga</p>
                        <h2 class="modal-title" id="stokNamaProduk">Nama produk</h2>
                        <p class="modal-subtitle" id="stokKategoriProduk">Kategori</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="stok-summary">
                        <div class="stok-summary-item">
                            <span>Stok saat ini</span>
                            <strong id="stokSaatIni">0 pack</strong>
                        </div>
                        <div class="stok-summary-item">
                            <span>Harga saat ini</span>
                            <strong id="hargaSaatIni">Rp 0</strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="stokAksi" class="form-label">Jenis perubahan</label>
                            <select class="form-select" id="stokAksi" name="aksi">
                                <option value="tambah">Tambah stok</option>
                                <option value="kurang">Kurangi stok</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label for="stokJumlah" class="form-label">
                                Jumlah <span class="required-mark">*</span>
                            </label>
                            <input type="number" class="form-control" id="stokJumlah"
                                   name="jumlah" min="0" step="1" value="0" required>
                            <div class="form-text">Isi 0 jika hanya mengubah harga.</div>
                        </div>

                        <div class="col-12">
                            <label for="stokHarga" class="form-label">
                                Harga jual <span class="required-mark">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="stokHarga"
                                       name="harga" min="1" step="100" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="stokKeterangan" class="form-label">Keterangan</label>
                            <input type="text" class="form-control" id="stokKeterangan"
                                   name="keterangan" maxlength="150"
                                   placeholder="Contoh: Restock dari supplier">
                        </div>
                    </div>

                    <div class="stok-preview" id="stokPratinjau">
                        Stok setelah disimpan: <strong>0 pack</strong>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand">
                        <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js?v=2"></script>
</body>
</html>
