<?php
require __DIR__ . "/../inc/bootstrap.php";
admin_awal("Pelanggan", "pelanggan");
$rows = semua("SELECT g.id, g.nama, g.email, g.telepon, g.dibuat, g.peran,
    (SELECT COUNT(*) FROM pesanan o WHERE o.pengguna_id = g.id) AS n,
    (SELECT COALESCE(SUM(o.total),0) FROM pesanan o WHERE o.pengguna_id = g.id AND o.status IN ('diproses','dikirim','selesai')) AS belanja
    FROM pengguna g ORDER BY g.id");
?>
<div class="tabel-wrap"><table class="tabel"><thead><tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Bergabung</th><th>Pesanan</th><th>Total belanja</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
  <td data-label="Nama"><b><?= e($r["nama"]) ?></b><?= $r["peran"] === "admin" ? ' <span class="badge badge-violet">Admin</span>' : "" ?></td>
  <td data-label="Email"><?= e($r["email"]) ?></td><td data-label="Telepon"><?= e($r["telepon"] ?: "-") ?></td><td data-label="Bergabung"><?= e(tgl($r["dibuat"])) ?></td>
  <td data-label="Pesanan"><?= (int)$r["n"] ?></td><td data-label="Total belanja"><?= rupiah($r["belanja"]) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php admin_akhir();
