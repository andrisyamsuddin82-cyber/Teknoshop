<?php
require __DIR__ . "/../inc/bootstrap.php";
wajib_admin();
$o = pesanan_kode(get_teks("kode") ?: post_teks("kode", 40));
if (!$o) { http_response_code(404); exit("Pesanan tidak ditemukan."); }
if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
    cek_csrf();
    $gal = pesanan_ubah_status($o, post_teks("status", 30), "admin", post_teks("catatan", 255), post_teks("resi", 50));
    set_flash($gal ?? "Status pesanan diperbarui.", $gal ? "bad" : "ok");
    redirect("admin/pesanan_detail.php?kode=" . rawurlencode($o["kode"]));
}
$item = pesanan_item((int)$o["id"]);
$riwayat = pesanan_riwayat((int)$o["id"]);
$opsi = TRANSISI_ADMIN[$o["status"]] ?? [];
$metode = METODE_BAYAR[$o["metode_bayar"]]["nama"] ?? $o["metode_bayar"];
$kurir = KURIR[$o["kurir"]]["nama"] ?? $o["kurir"];
admin_awal("Pesanan " . $o["kode"], "pesanan");
?>
<div class="judul-bagian"><h2><?= badge_status($o["status"]) ?></h2>
  <a class="tombol sekunder kecil" href="<?= e(url("invoice.php?kode=" . rawurlencode($o["kode"]))) ?>" target="_blank" rel="noopener"><?= ikon("print", 16) ?> Invoice</a></div>
<div class="dua-kolom">
  <div>
    <section class="kartu-form"><h2>Produk dipesan</h2>
      <div class="tabel-wrap"><table class="tabel"><thead><tr><th>Produk</th><th>Harga</th><th>Jml</th><th>Subtotal</th></tr></thead><tbody>
      <?php foreach ($item as $it): ?><tr><td data-label="Produk"><?= e($it["nama"]) ?></td><td data-label="Harga"><?= rupiah($it["harga"]) ?></td><td data-label="Jml"><?= (int)$it["jumlah"] ?></td><td data-label="Subtotal"><?= rupiah($it["harga"] * $it["jumlah"]) ?></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <dl class="rincian"><dt>Subtotal</dt><dd><?= rupiah($o["subtotal"]) ?></dd>
        <?php if ($o["diskon"]): ?><dt>Diskon <?= e($o["kupon_kode"]) ?></dt><dd class="hemat">-<?= rupiah($o["diskon"]) ?></dd><?php endif; ?>
        <dt>Ongkir <?= e($kurir) ?></dt><dd><?= $o["ongkir"] ? rupiah($o["ongkir"]) : "Gratis" ?></dd><dt class="total">Total</dt><dd class="total"><?= rupiah($o["total"]) ?></dd></dl>
    </section>
    <section class="kartu-form"><h2>Riwayat status</h2>
      <ol class="linimasa"><?php foreach (array_reverse($riwayat) as $i => $r): ?><li class="<?= $i === 0 ? "kini" : "" ?>"><b><?= e(status_info($r["status"])["label"]) ?></b><span><?= e($r["catatan"]) ?></span><time><?= e(tgl($r["dibuat"], true)) ?></time></li><?php endforeach; ?></ol>
    </section>
  </div>
  <aside>
    <section class="kartu-form"><h2>Ubah status</h2>
      <?php if (!$opsi): ?><p class="redup">Pesanan berstatus akhir, tidak ada perubahan yang tersedia.</p>
      <?php else: ?>
      <form method="post" class="form" action="<?= e(url("admin/pesanan_detail.php")) ?>"><?= csrf_field() ?><input type="hidden" name="kode" value="<?= e($o["kode"]) ?>">
        <label>Status baru<select name="status" data-status><?php foreach ($opsi as $s): ?><option value="<?= e($s) ?>"><?= e(status_info($s)["label"]) ?></option><?php endforeach; ?></select></label>
        <label data-resi>Nomor resi <small class="redup">(wajib saat dikirim)</small><input type="text" name="resi" maxlength="50" value="<?= e($o["resi"]) ?>"></label>
        <label>Catatan (opsional)<input type="text" name="catatan" maxlength="255"></label>
        <button class="tombol blok" type="submit">Simpan perubahan</button>
        <p class="info-kecil">Pembatalan mengembalikan stok produk dan kuota kupon.</p>
      </form>
      <?php endif; ?>
    </section>
    <section class="kartu-form"><h2>Pelanggan</h2>
      <p><b><?= e($o["pelanggan_nama"]) ?></b><br><?= e($o["pelanggan_email"]) ?></p>
      <h3>Dikirim ke</h3><p><b><?= e($o["nama_penerima"]) ?></b><br><?= e($o["telepon"]) ?><br><?= nl2br(e($o["alamat"])) ?></p>
      <?php if ($o["catatan"]): ?><p class="redup">Catatan: <?= e($o["catatan"]) ?></p><?php endif; ?>
      <p>Pembayaran: <?= e($metode) ?></p><?php if ($o["resi"]): ?><p>Resi: <b><?= e($o["resi"]) ?></b></p><?php endif; ?>
    </section>
  </aside>
</div>
<?php admin_akhir();
