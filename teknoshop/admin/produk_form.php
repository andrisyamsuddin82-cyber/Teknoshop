<?php
require __DIR__ . "/../inc/bootstrap.php";
wajib_admin();
$id = get_int("id");
$p = $id ? ambil("SELECT * FROM produk WHERE id = ?", [$id]) : null;
if ($id && !$p) { http_response_code(404); exit("Produk tidak ditemukan."); }
$v = $p ?: ["nama" => "", "kategori_id" => 0, "deskripsi" => "", "harga" => "", "harga_coret" => "", "stok" => 0, "unggulan" => 0, "aktif" => 1, "gambar" => null];
$galat = [];
if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
    cek_csrf();
    $v = array_merge($v, [
        "nama" => post_teks("nama", 150), "kategori_id" => post_int("kategori_id"), "deskripsi" => post_teks("deskripsi", 5000),
        "harga" => post_int("harga", -1), "harga_coret" => post_teks("harga_coret", 12), "stok" => post_int("stok", -1),
        "unggulan" => isset($_POST["unggulan"]) ? 1 : 0, "aktif" => isset($_POST["aktif"]) ? 1 : 0,
    ]);
    if ($v["nama"] === "") $galat[] = "Nama produk wajib diisi.";
    if (!nilai("SELECT 1 FROM kategori WHERE id = ?", [$v["kategori_id"]])) $galat[] = "Pilih kategori.";
    if ($v["harga"] < 1) $galat[] = "Harga harus lebih dari 0.";
    if ($v["stok"] < 0) $galat[] = "Stok tidak boleh negatif.";
    $coret = null;
    if ($v["harga_coret"] !== "") {
        if (!ctype_digit($v["harga_coret"]) || (int)$v["harga_coret"] <= $v["harga"]) $galat[] = "Harga coret harus angka dan lebih besar dari harga jual.";
        else $coret = (int)$v["harga_coret"];
    }
    if ($v["deskripsi"] === "") $galat[] = "Deskripsi wajib diisi.";
    [$gbr, $galGbr] = simpan_gambar($_FILES["gambar"] ?? []);
    if ($galGbr) $galat[] = $galGbr;
    if (!$galat) {
        // slug unik
        $dasar = slugify($v["nama"]); $slug = $dasar; $n = 2;
        while (nilai("SELECT 1 FROM produk WHERE slug = ? AND id <> ?", [$slug, $id])) $slug = $dasar . "-" . $n++;
        if ($p) {
            $gambar = $gbr ?: $p["gambar"];
            q("UPDATE produk SET kategori_id=?, nama=?, slug=?, deskripsi=?, harga=?, harga_coret=?, stok=?, gambar=?, unggulan=?, aktif=? WHERE id=?",
                [$v["kategori_id"], $v["nama"], $slug, $v["deskripsi"], $v["harga"], $coret, $v["stok"], $gambar, $v["unggulan"], $v["aktif"], $id]);
            if ($gbr) hapus_gambar($p["gambar"]);
            set_flash("Produk diperbarui.");
        } else {
            q("INSERT INTO produk (kategori_id, nama, slug, deskripsi, harga, harga_coret, stok, gambar, unggulan, aktif, terjual, dibuat) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                [$v["kategori_id"], $v["nama"], $slug, $v["deskripsi"], $v["harga"], $coret, $v["stok"], $gbr, $v["unggulan"], $v["aktif"], 0, date("Y-m-d H:i:s")]);
            set_flash("Produk ditambahkan.");
        }
        redirect("admin/produk.php");
    }
    if ($gbr) hapus_gambar($gbr);
}
admin_awal($p ? "Ubah produk" : "Tambah produk", "produk");
?>
<?php foreach ($galat as $g): ?><div class="alert alert-bad" role="alert"><?= e($g) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="form kartu-form lebar" action="<?= e(url("admin/produk_form.php" . ($id ? "?id=$id" : ""))) ?>">
  <?= csrf_field() ?>
  <label>Nama produk<input type="text" name="nama" value="<?= e($v["nama"]) ?>" required maxlength="150"></label>
  <label>Kategori<select name="kategori_id" required><option value="">Pilih kategori</option><?php foreach (kategori_semua() as $k): ?><option value="<?= (int)$k["id"] ?>" <?= (int)$v["kategori_id"] === (int)$k["id"] ? "selected" : "" ?>><?= e($k["nama"]) ?></option><?php endforeach; ?></select></label>
  <div class="tiga">
    <label>Harga jual (Rp)<input type="number" name="harga" min="1" value="<?= e($v["harga"]) ?>" required></label>
    <label>Harga coret (opsional)<input type="number" name="harga_coret" min="0" value="<?= e($v["harga_coret"]) ?>"></label>
    <label>Stok<input type="number" name="stok" min="0" value="<?= e($v["stok"]) ?>" required></label>
  </div>
  <label>Deskripsi <small class="redup">(awali baris dengan "- " untuk poin)</small><textarea name="deskripsi" rows="8" required maxlength="5000"><?= e($v["deskripsi"]) ?></textarea></label>
  <label>Gambar (JPG/PNG/WebP, maks 2 MB)<input type="file" name="gambar" accept="image/jpeg,image/png,image/webp"></label>
  <?php if ($p && $p["gambar"]): ?><img class="pratinjau" src="<?= e(gambar_url($p["gambar"])) ?>" <?= gambar_fallback_attr($p["slug"]) ?>" alt="Gambar saat ini" width="120" height="120"><?php endif; ?>
  <label class="centang"><input type="checkbox" name="aktif" <?= $v["aktif"] ? "checked" : "" ?>> Tampilkan di toko</label>
  <label class="centang"><input type="checkbox" name="unggulan" <?= $v["unggulan"] ? "checked" : "" ?>> Produk unggulan (tampil di beranda)</label>
  <div class="aksi-baris"><button class="tombol" type="submit">Simpan</button><a class="tombol sekunder" href="<?= e(url("admin/produk.php")) ?>">Batal</a></div>
</form>
<?php admin_akhir();
