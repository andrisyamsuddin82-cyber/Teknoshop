<?php
require __DIR__ . "/../inc/bootstrap.php";
wajib_admin();
$ikonOpsi = ["headset", "keyboard", "drive", "briefcase", "box", "tag", "grid"];
if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
    cek_csrf();
    $aksi = post_teks("aksi", 10);
    $id = post_int("id");
    if ($aksi === "hapus") {
        if ((int)nilai("SELECT COUNT(*) FROM produk WHERE kategori_id = ?", [$id]) > 0) set_flash("Kategori masih dipakai produk, tidak dapat dihapus.", "bad");
        else { q("DELETE FROM kategori WHERE id = ?", [$id]); set_flash("Kategori dihapus."); }
    } else {
        $nama = post_teks("nama", 80); $ik = post_teks("ikon", 30); $des = post_teks("deskripsi", 255);
        if ($nama === "") set_flash("Nama kategori wajib diisi.", "bad");
        else {
            if (!in_array($ik, $ikonOpsi, true)) $ik = "box";
            $dasar = slugify($nama); $slug = $dasar; $n = 2;
            while (nilai("SELECT 1 FROM kategori WHERE slug = ? AND id <> ?", [$slug, $id])) $slug = $dasar . "-" . $n++;
            if ($aksi === "ubah" && $id) q("UPDATE kategori SET nama=?, slug=?, ikon=?, deskripsi=? WHERE id=?", [$nama, $slug, $ik, $des ?: null, $id]);
            else q("INSERT INTO kategori (nama, slug, ikon, deskripsi) VALUES (?,?,?,?)", [$nama, $slug, $ik, $des ?: null]);
            set_flash("Kategori disimpan.");
        }
    }
    redirect("admin/kategori.php");
}
admin_awal("Kategori", "kategori");
$edit = get_int("edit") ? ambil("SELECT * FROM kategori WHERE id = ?", [get_int("edit")]) : null;
?>
<div class="dua-kolom">
  <div class="tabel-wrap"><table class="tabel"><thead><tr><th>Kategori</th><th>Slug</th><th>Produk</th><th></th></tr></thead><tbody>
    <?php foreach (kategori_semua() as $k): $n = (int)nilai("SELECT COUNT(*) FROM produk WHERE kategori_id = ?", [$k["id"]]); ?><tr>
      <td data-label="Kategori"><?= ikon($k["ikon"], 18) ?> <b><?= e($k["nama"]) ?></b></td><td data-label="Slug"><?= e($k["slug"]) ?></td><td data-label="Produk"><?= $n ?></td>
      <td class="aksi"><a class="tombol kecil sekunder" href="<?= e(url("admin/kategori.php?edit=" . (int)$k["id"])) ?>">Ubah</a>
        <form method="post" data-konfirmasi="Hapus kategori ini?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$k["id"] ?>"><button class="tombol kecil bahaya-garis" type="submit" <?= $n ? "disabled title='Masih dipakai produk'" : "" ?>>Hapus</button></form></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <form method="post" class="form kartu-form" action="<?= e(url("admin/kategori.php")) ?>">
    <h2><?= $edit ? "Ubah kategori" : "Tambah kategori" ?></h2>
    <?= csrf_field() ?><input type="hidden" name="aksi" value="<?= $edit ? "ubah" : "tambah" ?>"><input type="hidden" name="id" value="<?= (int)($edit["id"] ?? 0) ?>">
    <label>Nama<input type="text" name="nama" value="<?= e($edit["nama"] ?? "") ?>" required maxlength="80"></label>
    <label>Ikon<select name="ikon"><?php foreach ($ikonOpsi as $o): ?><option <?= ($edit["ikon"] ?? "") === $o ? "selected" : "" ?>><?= e($o) ?></option><?php endforeach; ?></select></label>
    <label>Deskripsi<input type="text" name="deskripsi" value="<?= e($edit["deskripsi"] ?? "") ?>" maxlength="255"></label>
    <div class="aksi-baris"><button class="tombol" type="submit">Simpan</button><?php if ($edit): ?><a class="tombol sekunder" href="<?= e(url("admin/kategori.php")) ?>">Batal</a><?php endif; ?></div>
  </form>
</div>
<?php admin_akhir();
