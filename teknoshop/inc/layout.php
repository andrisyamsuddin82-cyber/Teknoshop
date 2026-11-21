<?php
// Kerangka halaman pelanggan. Pakai: tampil_awal("Judul"); ... tampil_akhir();

function tampil_awal(string $judul, string $deskripsi = "", string $aktif = ""): void
{
    $u = pengguna();
    $kat = kategori_semua();
    $jml = kr_jumlah();
    $judulPenuh = ($judul !== "" ? $judul . " | " : "") . NAMA_TOKO;
    ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judulPenuh) ?></title>
<meta name="description" content="<?= e($deskripsi ?: TAGLINE) ?>">
<link rel="icon" type="image/svg+xml" href="<?= e(url("assets/img/favicon.svg")) ?>">
<link rel="stylesheet" href="<?= e(aset("assets/css/style.css")) ?>">
<script src="<?= e(aset("assets/js/app.js")) ?>" defer></script>
</head>
<body>
<a class="lewati" href="#konten">Lewati ke konten</a>
<header class="kepala">
  <div class="atas wadah">
    <span><?= ikon("truck", 16) ?> Gratis ongkir Reguler untuk belanja di atas <?= e(rupiah(GRATIS_ONGKIR_MIN)) ?></span>
    <span class="atas-kanan">Kode <b>HEMAT10</b> diskon 10%</span>
  </div>
  <div class="utama wadah">
    <button class="menu-tombol ikon-tombol" type="button" aria-label="Buka menu" aria-expanded="false" data-menu>
      <?= ikon("menu", 22) ?>
    </button>
    <a class="logo" href="<?= e(url("index.php")) ?>"><img src="<?= e(url("assets/img/logo.svg")) ?>" alt="" width="34" height="34"><span><?= e(NAMA_TOKO) ?></span></a>
    <form class="cari" action="<?= e(url("katalog.php")) ?>" method="get" role="search">
      <input type="search" name="q" value="<?= e(get_teks("q")) ?>" placeholder="Cari produk…" aria-label="Cari produk">
      <button type="submit" aria-label="Cari"><?= ikon("search", 18) ?></button>
    </form>
    <nav class="aksi-kepala" aria-label="Akun">
      <?php if ($u): ?>
        <a href="<?= e(url("wishlist.php")) ?>" class="ikon-link" title="Wishlist"><?= ikon("heart", 22) ?><span class="sr">Wishlist</span></a>
      <?php endif; ?>
      <a href="<?= e(url("keranjang.php")) ?>" class="ikon-link" title="Keranjang"><?= ikon("cart", 22) ?><span class="sr">Keranjang</span>
        <?php if ($jml): ?><span class="lencana"><?= (int)$jml ?></span><?php endif; ?></a>
      <?php if ($u): ?>
        <details class="akun-menu">
          <summary><?= ikon("user", 20) ?><span class="nama"><?= e(explode(" ", $u["nama"])[0]) ?></span></summary>
          <div class="menu-turun">
            <a href="<?= e(url("akun.php")) ?>">Profil saya</a>
            <a href="<?= e(url("pesanan.php")) ?>">Pesanan saya</a>
            <a href="<?= e(url("wishlist.php")) ?>">Wishlist</a>
            <?php if ($u["peran"] === "admin"): ?><a href="<?= e(url("admin/index.php")) ?>">Panel admin</a><?php endif; ?>
            <form method="post" action="<?= e(url("logout.php")) ?>"><?= csrf_field() ?><button type="submit">Keluar</button></form>
          </div>
        </details>
      <?php else: ?>
        <a class="tombol kecil" href="<?= e(url("login.php")) ?>">Masuk</a>
      <?php endif; ?>
    </nav>
  </div>
  <nav class="menu-kat" id="menu-kat" aria-label="Kategori">
    <div class="wadah">
      <a href="<?= e(url("index.php")) ?>" class="<?= $aktif === "beranda" ? "aktif" : "" ?>">Beranda</a>
      <a href="<?= e(url("katalog.php")) ?>" class="<?= $aktif === "katalog" ? "aktif" : "" ?>">Semua Produk</a>
      <?php foreach ($kat as $k): ?>
        <a href="<?= e(url("katalog.php?kategori=" . rawurlencode($k["slug"]))) ?>" class="<?= $aktif === "kat-" . $k["slug"] ? "aktif" : "" ?>"><?= e($k["nama"]) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
</header>
<main id="konten" class="wadah konten">
<?= tampil_flash() ?>
<?php
}

function tampil_akhir(): void
{
    ?>
</main>
<footer class="kaki">
  <div class="wadah kaki-grid">
    <div>
      <a class="logo" href="<?= e(url("index.php")) ?>"><img src="<?= e(url("assets/img/logo.svg")) ?>" alt="" width="34" height="34"><span><?= e(NAMA_TOKO) ?></span></a>
      <p><?= e(TAGLINE) ?>. Produk fiktif untuk keperluan proyek perkuliahan.</p>
    </div>
    <div><h4>Belanja</h4>
      <a href="<?= e(url("katalog.php")) ?>">Semua produk</a>
      <?php foreach (kategori_semua() as $k): ?><a href="<?= e(url("katalog.php?kategori=" . rawurlencode($k["slug"]))) ?>"><?= e($k["nama"]) ?></a><?php endforeach; ?>
    </div>
    <div><h4>Akun</h4>
      <a href="<?= e(url("akun.php")) ?>">Profil</a><a href="<?= e(url("pesanan.php")) ?>">Pesanan saya</a><a href="<?= e(url("wishlist.php")) ?>">Wishlist</a><a href="<?= e(url("keranjang.php")) ?>">Keranjang</a>
    </div>
    <div><h4>Pembayaran &amp; kurir</h4>
      <p>Transfer bank, e-wallet, COD<br>Kurir Hemat, Reguler, Ekspres</p>
    </div>
  </div>
  <div class="kaki-bawah wadah">© <?= date("Y") ?> <?= e(NAMA_TOKO) ?> · Proyek Pemrograman Web, PTIK FT UNM</div>
</footer>
</body>
</html>
<?php
}
