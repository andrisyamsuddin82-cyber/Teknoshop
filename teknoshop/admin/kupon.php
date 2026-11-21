<?php
require __DIR__ . "/../inc/bootstrap.php";
wajib_admin();
if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
    cek_csrf();
    $aksi = post_teks("aksi", 10);
    $id = post_int("id");
    if ($aksi === "hapus") {
        $k = ambil("SELECT * FROM kupon WHERE id = ?", [$id]);
        if ($k && (int)nilai("SELECT COUNT(*) FROM pesanan WHERE kupon_kode = ?", [$k["kode"]]) > 0) { q("UPDATE kupon SET aktif = 0 WHERE id = ?", [$id]); set_flash("Kupon sudah pernah dipakai, jadi dinonaktifkan.", "warn"); }
        elseif ($k) { q("DELETE FROM kupon WHERE id = ?", [$id]); set_flash("Kupon dihapus."); }
    } else {
        $kode = strtoupper(preg_replace('/[^A-Za-z0-9]/', "", post_teks("kode", 30)));
        $jenis = post_teks("jenis", 10) === "nominal" ? "nominal" : "persen";
        $nilai = post_int("nilai"); $min = max(0, post_int("min_belanja"));
        $maks = post_teks("maks_diskon", 12) !== "" ? post_int("maks_diskon") : null;
        $kuota = post_teks("kuota", 8) !== "" ? post_int("kuota") : null;
        $sampai = post_teks("berlaku_sampai", 10) ?: null;
        $aktif = isset($_POST["aktif"]) ? 1 : 0;
        $galat = [];
        if ($kode === "") $galat[] = "Kode wajib diisi (huruf dan angka).";
        if ($nilai < 1 || ($jenis === "persen" && $nilai > 100)) $galat[] = "Nilai kupon tidak valid.";
        if ($sampai && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $galat[] = "Tanggal berlaku tidak valid.";
        if (nilai("SELECT 1 FROM kupon WHERE kode = ? AND id <> ?", [$kode, $id])) $galat[] = "Kode kupon sudah dipakai.";
        if ($galat) set_flash(implode(" ", $galat), "bad");
        else {
            if ($aksi === "ubah" && $id) q("UPDATE kupon SET kode=?, jenis=?, nilai=?, min_belanja=?, maks_diskon=?, kuota=?, berlaku_sampai=?, aktif=? WHERE id=?", [$kode, $jenis, $nilai, $min, $maks, $kuota, $sampai, $aktif, $id]);
            else q("INSERT INTO kupon (kode, jenis, nilai, min_belanja, maks_diskon, kuota, terpakai, berlaku_sampai, aktif) VALUES (?,?,?,?,?,?,?,?,?)", [$kode, $jenis, $nilai, $min, $maks, $kuota, 0, $sampai, $aktif]);
            set_flash("Kupon disimpan.");
        }
    }
    redirect("admin/kupon.php");
}
admin_awal("Kupon", "kupon");
$edit = get_int("edit") ? ambil("SELECT * FROM kupon WHERE id = ?", [get_int("edit")]) : null;
$e0 = $edit ?: ["kode" => "", "jenis" => "persen", "nilai" => "", "min_belanja" => 0, "maks_diskon" => "", "kuota" => "", "berlaku_sampai" => "", "aktif" => 1, "id" => 0];
?>
<div class="dua-kolom">
  <div class="tabel-wrap"><table class="tabel"><thead><tr><th>Kode</th><th>Potongan</th><th>Min. belanja</th><th>Terpakai</th><th>Berlaku</th><th></th></tr></thead><tbody>
  <?php foreach (semua("SELECT * FROM kupon ORDER BY id") as $k): ?><tr>
    <td data-label="Kode"><b><?= e($k["kode"]) ?></b> <?= $k["aktif"] ? "" : '<span class="badge badge-bad">Nonaktif</span>' ?></td>
    <td data-label="Potongan"><?= $k["jenis"] === "persen" ? (int)$k["nilai"] . "%" . ($k["maks_diskon"] ? " (maks " . rupiah($k["maks_diskon"]) . ")" : "") : rupiah($k["nilai"]) ?></td>
    <td data-label="Min. belanja"><?= rupiah($k["min_belanja"]) ?></td><td data-label="Terpakai"><?= (int)$k["terpakai"] ?><?= $k["kuota"] !== null ? " / " . (int)$k["kuota"] : "" ?></td>
    <td data-label="Berlaku"><?= $k["berlaku_sampai"] ? e(tgl($k["berlaku_sampai"])) : "Tanpa batas" ?></td>
    <td class="aksi"><a class="tombol kecil sekunder" href="<?= e(url("admin/kupon.php?edit=" . (int)$k["id"])) ?>">Ubah</a>
      <form method="post" data-konfirmasi="Hapus kupon ini?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$k["id"] ?>"><button class="tombol kecil bahaya-garis" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <form method="post" class="form kartu-form" action="<?= e(url("admin/kupon.php")) ?>">
    <h2><?= $edit ? "Ubah kupon" : "Tambah kupon" ?></h2>
    <?= csrf_field() ?><input type="hidden" name="aksi" value="<?= $edit ? "ubah" : "tambah" ?>"><input type="hidden" name="id" value="<?= (int)$e0["id"] ?>">
    <label>Kode<input type="text" name="kode" value="<?= e($e0["kode"]) ?>" required maxlength="30"></label>
    <label>Jenis<select name="jenis"><option value="persen" <?= $e0["jenis"] === "persen" ? "selected" : "" ?>>Persen (%)</option><option value="nominal" <?= $e0["jenis"] === "nominal" ? "selected" : "" ?>>Nominal (Rp)</option></select></label>
    <label>Nilai<input type="number" name="nilai" min="1" value="<?= e($e0["nilai"]) ?>" required></label>
    <label>Minimal belanja (Rp)<input type="number" name="min_belanja" min="0" value="<?= e($e0["min_belanja"]) ?>"></label>
    <label>Maks. diskon (untuk persen, opsional)<input type="number" name="maks_diskon" min="0" value="<?= e($e0["maks_diskon"]) ?>"></label>
    <label>Kuota (kosong = tanpa batas)<input type="number" name="kuota" min="0" value="<?= e($e0["kuota"]) ?>"></label>
    <label>Berlaku sampai<input type="date" name="berlaku_sampai" value="<?= e($e0["berlaku_sampai"]) ?>"></label>
    <label class="centang"><input type="checkbox" name="aktif" <?= $e0["aktif"] ? "checked" : "" ?>> Aktif</label>
    <div class="aksi-baris"><button class="tombol" type="submit">Simpan</button><?php if ($edit): ?><a class="tombol sekunder" href="<?= e(url("admin/kupon.php")) ?>">Batal</a><?php endif; ?></div>
  </form>
</div>
<?php admin_akhir();
