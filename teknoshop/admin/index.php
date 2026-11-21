<?php
require __DIR__ . "/../inc/bootstrap.php";
admin_awal("Dasbor", "dashboard");
$sah = "status IN ('diproses','dikirim','selesai')";
$pendapatan = (int)nilai("SELECT COALESCE(SUM(total),0) FROM pesanan WHERE $sah");
$nPesanan = (int)nilai("SELECT COUNT(*) FROM pesanan");
$perlu = (int)nilai("SELECT COUNT(*) FROM pesanan WHERE status IN ('menunggu_verifikasi','diproses')");
$nPelanggan = (int)nilai("SELECT COUNT(*) FROM pengguna WHERE peran = 'pelanggan'");
$nProduk = (int)nilai("SELECT COUNT(*) FROM produk WHERE aktif = 1");

// Grafik 14 hari sampai tanggal pesanan terakhir (atau hari ini)
$terakhir = nilai("SELECT MAX(dibuat) FROM pesanan");
$akhir = $terakhir ? strtotime(substr($terakhir, 0, 10)) : strtotime(date("Y-m-d"));
$awal = $akhir - 13 * 86400;
$harian = [];
foreach (semua("SELECT DATE(dibuat) AS tgl, SUM(total) AS jml, COUNT(*) AS n FROM pesanan WHERE $sah AND DATE(dibuat) >= ? GROUP BY DATE(dibuat)", [date("Y-m-d", $awal)]) as $r) $harian[$r["tgl"]] = $r;
$data = [];
for ($i = 0; $i < 14; $i++) { $d = date("Y-m-d", $awal + $i * 86400); $data[] = [$d, (int)($harian[$d]["jml"] ?? 0)]; }
$maks = max(1, max(array_column($data, 1)));
$W = 640; $H = 220; $pl = 8; $pb = 28; $lebar = ($W - $pl) / 14;

$laris = semua("SELECT id, nama, terjual, stok FROM produk WHERE aktif = 1 ORDER BY terjual DESC LIMIT 5");
$tipis = semua("SELECT id, nama, stok FROM produk WHERE aktif = 1 AND stok <= 10 ORDER BY stok ASC, id LIMIT 6");
$terbaru = semua("SELECT o.kode, o.total, o.status, o.dibuat, g.nama FROM pesanan o JOIN pengguna g ON g.id = o.pengguna_id ORDER BY o.id DESC LIMIT 6");
?>
<div class="stat-baris">
  <div class="stat"><span>Pendapatan</span><b><?= rupiah($pendapatan) ?></b><small>Pesanan diproses, dikirim, selesai</small></div>
  <div class="stat"><span>Total pesanan</span><b><?= $nPesanan ?></b><small><?= $perlu ?> perlu ditindaklanjuti</small></div>
  <div class="stat"><span>Pelanggan</span><b><?= $nPelanggan ?></b><small>akun terdaftar</small></div>
  <div class="stat"><span>Produk aktif</span><b><?= $nProduk ?></b><small><?= count($tipis) ?> stok menipis</small></div>
</div>

<section class="kartu-form">
  <h2>Penjualan 14 hari terakhir</h2>
  <svg class="grafik" viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Grafik batang penjualan harian">
    <?php foreach ([0, .5, 1] as $g): $y = ($H - $pb) - ($H - $pb - 10) * $g; ?>
      <line x1="<?= $pl ?>" x2="<?= $W ?>" y1="<?= $y ?>" y2="<?= $y ?>" class="grid"/>
    <?php endforeach; ?>
    <?php foreach ($data as $i => [$d, $v]):
        $h = ($H - $pb - 10) * $v / $maks; $x = $pl + $i * $lebar + 6; $y = ($H - $pb) - $h; ?>
      <g><title><?= e(tgl($d)) ?>: <?= e(rupiah($v)) ?></title>
        <rect x="<?= $x ?>" y="<?= $y ?>" width="<?= $lebar - 12 ?>" height="<?= max($h, $v ? 2 : 0) ?>" rx="4" class="batang"/>
        <text x="<?= $x + ($lebar - 12) / 2 ?>" y="<?= $H - 10 ?>" text-anchor="middle" class="sumbu"><?= (int)date("j", strtotime($d)) ?></text></g>
    <?php endforeach; ?>
  </svg>
  <p class="redup kecil">Nilai tertinggi: <?= rupiah($maks) ?> · Tanggal terakhir: <?= e(tgl(date("Y-m-d", $akhir))) ?></p>
</section>

<div class="dua-sama">
  <section class="kartu-form"><h2>Produk terlaris</h2>
    <ol class="peringkat"><?php foreach ($laris as $p): ?><li><a href="<?= e(url("admin/produk_form.php?id=" . (int)$p["id"])) ?>"><?= e($p["nama"]) ?></a><b><?= (int)$p["terjual"] ?> terjual</b></li><?php endforeach; ?></ol></section>
  <section class="kartu-form"><h2>Stok menipis</h2>
    <?php if (!$tipis): ?><p class="redup">Semua stok aman.</p><?php endif; ?>
    <ul class="peringkat"><?php foreach ($tipis as $p): ?><li><a href="<?= e(url("admin/produk_form.php?id=" . (int)$p["id"])) ?>"><?= e($p["nama"]) ?></a><b class="<?= $p["stok"] < 5 ? "merah" : "" ?>"><?= (int)$p["stok"] ?> unit</b></li><?php endforeach; ?></ul></section>
</div>

<section class="kartu-form"><div class="judul-bagian"><h2>Pesanan terbaru</h2><a href="<?= e(url("admin/pesanan.php")) ?>">Semua pesanan →</a></div>
  <div class="tabel-wrap"><table class="tabel"><thead><tr><th>Kode</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($terbaru as $o): ?><tr>
    <td data-label="Kode"><a href="<?= e(url("admin/pesanan_detail.php?kode=" . rawurlencode($o["kode"]))) ?>"><?= e($o["kode"]) ?></a></td>
    <td data-label="Pelanggan"><?= e($o["nama"]) ?></td><td data-label="Tanggal"><?= e(tgl($o["dibuat"], true)) ?></td>
    <td data-label="Total"><?= rupiah($o["total"]) ?></td><td data-label="Status"><?= badge_status($o["status"]) ?></td></tr><?php endforeach; ?>
  </tbody></table></div></section>
<?php admin_akhir();
