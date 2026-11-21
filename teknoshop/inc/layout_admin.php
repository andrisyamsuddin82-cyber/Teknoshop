<?php
// Kerangka panel admin. Pakai: admin_awal("Judul", "menu"); ... admin_akhir();

function admin_awal(string $judul, string $aktif = ""): void
{
    wajib_admin();
    $u = pengguna();
    $menu = [
        "dashboard" => ["Dasbor", "admin/index.php", "chart"],
        "produk" => ["Produk", "admin/produk.php", "box"],
        "kategori" => ["Kategori", "admin/kategori.php", "grid"],
        "pesanan" => ["Pesanan", "admin/pesanan.php", "truck"],
        "pelanggan" => ["Pelanggan", "admin/pelanggan.php", "users"],
        "kupon" => ["Kupon", "admin/kupon.php", "tag"],
    ];
    ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> | Admin <?= e(NAMA_TOKO) ?></title>
<meta name="robots" content="noindex">
<link rel="icon" type="image/svg+xml" href="<?= e(url("assets/img/favicon.svg")) ?>">
<link rel="stylesheet" href="<?= e(aset("assets/css/style.css")) ?>">
<script src="<?= e(aset("assets/js/app.js")) ?>" defer></script>
</head>
<body class="admin">
<div class="admin-bingkai">
  <aside class="admin-sisi" id="admin-sisi">
    <a class="logo" href="<?= e(url("admin/index.php")) ?>"><img src="<?= e(url("assets/img/logo.svg")) ?>" alt="" width="34" height="34"><span>Admin</span></a>
    <nav>
      <?php foreach ($menu as $k => [$t, $h, $i]): ?>
        <a href="<?= e(url($h)) ?>" class="<?= $aktif === $k ? "aktif" : "" ?>"><?= ikon($i, 18) ?> <?= e($t) ?></a>
      <?php endforeach; ?>
      <hr>
      <a href="<?= e(url("index.php")) ?>"><?= ikon("home", 18) ?> Lihat toko</a>
      <form method="post" action="<?= e(url("logout.php")) ?>"><?= csrf_field() ?><button type="submit"><?= ikon("logout", 18) ?> Keluar</button></form>
    </nav>
  </aside>
  <div class="admin-isi">
    <header class="admin-atas">
      <button class="menu-tombol ikon-tombol" type="button" aria-label="Menu" aria-expanded="false" data-menu-admin><?= ikon("menu", 22) ?></button>
      <h1><?= e($judul) ?></h1>
      <span class="admin-user"><?= e($u["nama"]) ?></span>
    </header>
    <main class="admin-main">
<?= tampil_flash() ?>
<?php
}

function admin_akhir(): void
{
    echo "    </main>\n  </div>\n</div>\n</body>\n</html>\n";
}
