<?php
require __DIR__ . "/../inc/bootstrap.php";
admin_awal("Produk", "produk");
$qq = mb_substr(get_teks("q"), 0, 80);
$kat = get_int("kategori");
$where = ["1=1"]; $par = [];
if ($qq !== "") { $where[] = "p.nama LIKE ? ESCAPE '\\'"; $par[] = "%" . addcslashes($qq, "%_\\") . "%"; }
if ($kat) { $where[] = "p.kategori_id = ?"; $par[] = $kat; }
$w = implode(" AND ", $where);
$per = 15;
$total = (int)nilai("SELECT COUNT(*) FROM produk p WHERE $w", $par);
$hal = max(1, min(get_int("hal", 1), max(1, (int)ceil($total / $per))));
$baris = semua("SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE $w ORDER BY p.id DESC LIMIT $per OFFSET " . (($hal - 1) * $per), $par);
?>
<div class="bar-alat">
  <form method="get" class="inline"><input type="search" name="q" value="<?= e($qq) ?>" placeholder="Cari produk" aria-label="Cari produk">
    <select name="kategori" aria-label="Kategori"><option value="">Semua kategori</option><?php foreach (kategori_semua() as $k): ?><option value="<?= (int)$k["id"] ?>" <?= $kat === (int)$k["id"] ? "selected" : "" ?>><?= e($k["nama"]) ?></option><?php endforeach; ?></select>
    <button class="tombol sekunder" type="submit">Cari</button></form>
  <a class="tombol" href="<?= e(url("admin/produk_form.php")) ?>"><?= ikon("plus", 16) ?> Tambah produk</a>
</div>
<div class="tabel-wrap"><table class="tabel">
  <thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Terjual</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($baris as $p): ?><tr>
    <td data-label="Produk"><div class="sel-produk"><img src="<?= e(gambar_url($p["gambar"])) ?>" <?= gambar_fallback_attr($p["slug"]) ?>" alt="" width="44" height="44"><b><?= e($p["nama"]) ?></b></div></td>
    <td data-label="Kategori"><?= e($p["kategori"]) ?></td><td data-label="Harga"><?= rupiah($p["harga"]) ?></td>
    <td data-label="Stok"><span class="<?= $p["stok"] < 5 ? "merah" : "" ?>"><?= (int)$p["stok"] ?></span></td><td data-label="Terjual"><?= (int)$p["terjual"] ?></td>
    <td data-label="Status"><?= $p["aktif"] ? '<span class="badge badge-ok">Aktif</span>' : '<span class="badge badge-bad">Nonaktif</span>' ?><?= $p["unggulan"] ? ' <span class="badge badge-violet">Unggulan</span>' : "" ?></td>
    <td class="aksi"><a class="tombol kecil sekunder" href="<?= e(url("admin/produk_form.php?id=" . (int)$p["id"])) ?>">Ubah</a>
      <form method="post" action="<?= e(url("admin/produk_hapus.php")) ?>" data-konfirmasi="Hapus produk &quot;<?= e($p["nama"]) ?>&quot;?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p["id"] ?>"><button class="tombol kecil bahaya-garis" type="submit">Hapus</button></form></td>
  </tr><?php endforeach; ?>
  <?php if (!$baris): ?><tr><td colspan="7" class="tengah redup">Tidak ada produk.</td></tr><?php endif; ?>
  </tbody></table></div>
<?= paginasi($hal, max(1, (int)ceil($total / $per)), "admin/produk.php", array_filter(["q" => $qq, "kategori" => $kat ?: ""], fn($v) => $v !== "")) ?>
<?php admin_akhir();
