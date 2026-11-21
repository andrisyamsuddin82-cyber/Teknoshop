<?php
require __DIR__ . "/../inc/bootstrap.php";
wajib_admin();
$st = get_teks("status");
if (!isset(STATUS_PESANAN[$st])) $st = "";
$qq = mb_substr(get_teks("q"), 0, 60);
$where = ["1=1"]; $par = [];
if ($st !== "") { $where[] = "o.status = ?"; $par[] = $st; }
if ($qq !== "") {
    $like = "%" . addcslashes($qq, "%_\\") . "%";
    $where[] = "(o.kode LIKE ? ESCAPE '\\' OR g.nama LIKE ? ESCAPE '\\' OR o.nama_penerima LIKE ? ESCAPE '\\')";
    array_push($par, $like, $like, $like);
}
$w = implode(" AND ", $where);
$dasar = "FROM pesanan o JOIN pengguna g ON g.id = o.pengguna_id WHERE $w";

if (get_teks("export") === "csv") {
    $rows = semua("SELECT o.kode, o.dibuat, g.nama AS pelanggan, o.status, o.kurir, o.metode_bayar, o.subtotal, o.diskon, o.ongkir, o.total, o.resi $dasar ORDER BY o.id DESC", $par);
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=pesanan-" . date("Ymd") . ".csv");
    $out = fopen("php://output", "w");
    fwrite($out, "\xEF\xBB\xBF");
    $aman = fn($v) => is_string($v) && $v !== "" && strpos("=+-@\t\r", $v[0]) !== false ? "'" . $v : $v;
    fputcsv($out, ["Kode", "Tanggal", "Pelanggan", "Status", "Kurir", "Pembayaran", "Subtotal", "Diskon", "Ongkir", "Total", "Resi"]);
    foreach ($rows as $r) fputcsv($out, array_map($aman, array_values($r)));
    exit;
}
$per = 15;
$total = (int)nilai("SELECT COUNT(*) $dasar", $par);
$hal = max(1, min(get_int("hal", 1), max(1, (int)ceil($total / $per))));
$baris = semua("SELECT o.*, g.nama AS pelanggan $dasar ORDER BY o.id DESC LIMIT $per OFFSET " . (($hal - 1) * $per), $par);
$param = array_filter(["status" => $st, "q" => $qq], fn($v) => $v !== "");
admin_awal("Pesanan", "pesanan");
?>
<div class="bar-alat">
  <form method="get" class="inline"><input type="search" name="q" value="<?= e($qq) ?>" placeholder="Kode atau nama" aria-label="Cari pesanan">
    <select name="status" aria-label="Status"><option value="">Semua status</option><?php foreach (STATUS_PESANAN as $k => $v): ?><option value="<?= e($k) ?>" <?= $st === $k ? "selected" : "" ?>><?= e($v["label"]) ?></option><?php endforeach; ?></select>
    <button class="tombol sekunder" type="submit">Terapkan</button></form>
  <a class="tombol sekunder" href="<?= e(url("admin/pesanan.php?" . http_build_query($param + ["export" => "csv"]))) ?>"><?= ikon("download", 16) ?> Ekspor CSV</a>
</div>
<div class="tabel-wrap"><table class="tabel"><thead><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($baris as $o): ?><tr>
    <td data-label="Kode"><b><?= e($o["kode"]) ?></b></td><td data-label="Tanggal"><?= e(tgl($o["dibuat"], true)) ?></td><td data-label="Pelanggan"><?= e($o["pelanggan"]) ?></td>
    <td data-label="Total"><?= rupiah($o["total"]) ?></td><td data-label="Status"><?= badge_status($o["status"]) ?></td>
    <td><a class="tombol kecil sekunder" href="<?= e(url("admin/pesanan_detail.php?kode=" . rawurlencode($o["kode"]))) ?>">Kelola</a></td></tr><?php endforeach; ?>
  <?php if (!$baris): ?><tr><td colspan="6" class="tengah redup">Tidak ada pesanan.</td></tr><?php endif; ?>
</tbody></table></div>
<?= paginasi($hal, max(1, (int)ceil($total / $per)), "admin/pesanan.php", $param) ?>
<?php admin_akhir();
