<?php
// Dimuat di awal setiap halaman.
define("ROOT_PATH", dirname(__DIR__));
require __DIR__ . "/config.php";
date_default_timezone_set(ZONA_WAKTU);
require __DIR__ . "/db.php";
require __DIR__ . "/query.php";
require __DIR__ . "/fungsi.php";
require __DIR__ . "/auth.php";
require __DIR__ . "/data.php";
require __DIR__ . "/keranjang.php";
require __DIR__ . "/pesanan.php";
require __DIR__ . "/komponen.php";
require __DIR__ . "/layout.php";
require __DIR__ . "/layout_admin.php";

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
mulai_sesi();
