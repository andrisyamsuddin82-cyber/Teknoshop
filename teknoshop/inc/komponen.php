<?php
// Komponen tampilan yang dipakai di banyak halaman.

function ikon(string $n, int $u = 20): string
{
    static $p = [
        "cart" => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
        "heart" => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>',
        "search" => '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/>',
        "user" => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        "box" => '<path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.7z"/><path d="M3.3 7L12 12l8.7-5M12 22V12"/>',
        "truck" => '<path d="M1 3h15v13H1zM16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        "shield" => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        "tag" => '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1"/>',
        "headset" => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
        "keyboard" => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h.01M12 12h.01M16 12h.01M7 16h10"/>',
        "drive" => '<path d="M22 12H2M5.5 5.1L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1zM6 16h.01M10 16h.01"/>',
        "briefcase" => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        "plus" => '<path d="M12 5v14M5 12h14"/>',
        "minus" => '<path d="M5 12h14"/>',
        "trash" => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>',
        "check" => '<path d="M20 6L9 17l-5-5"/>',
        "menu" => '<path d="M3 12h18M3 6h18M3 18h18"/>',
        "home" => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        "grid" => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        "users" => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        "chart" => '<path d="M18 20V10M12 20V4M6 20v-6"/>',
        "edit" => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4z"/>',
        "logout" => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        "print" => '<path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        "download" => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        "alert" => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>',
        "star" => '<path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
    ];
    $d = $p[$n] ?? $p["box"];
    return '<svg class="ikon" width="' . $u . '" height="' . $u . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}

function bintang(float $r): string
{
    $o = '<span class="bintang" role="img" aria-label="Rating ' . number_format($r, 1, ",", ".") . ' dari 5">';
    for ($i = 1; $i <= 5; $i++) $o .= '<span class="' . ($r >= $i - 0.25 ? "on" : "off") . '">★</span>';
    return $o . "</span>";
}

function badge_status(string $s): string
{
    $i = status_info($s);
    return '<span class="badge badge-' . e($i["kelas"]) . '">' . e($i["label"]) . "</span>";
}

function kartu_produk(array $p, array $wish = []): string
{
    $habis = (int)$p["stok"] < 1;
    $href = url("produk.php?slug=" . rawurlencode($p["slug"]));
    $diskon = !empty($p["harga_coret"]) && $p["harga_coret"] > $p["harga"] ? (int)round(100 - $p["harga"] * 100 / $p["harga_coret"]) : 0;
    $h = '<article class="kartu-produk">';
    $h .= '<a class="gambar" href="' . e($href) . '"><img src="' . e(gambar_url($p["gambar"])) . '" ' . gambar_fallback_attr($p["slug"]) . ' alt="' . e($p["nama"]) . '" loading="lazy" width="400" height="400">';
    if ($diskon) $h .= '<span class="pita">-' . $diskon . '%</span>';
    if ($habis) $h .= '<span class="pita habis">Stok habis</span>';
    $h .= "</a>";
    if (sudah_login()) {
        $on = in_array((int)$p["id"], $wish, true);
        $h .= '<form method="post" action="' . e(url("wishlist_aksi.php")) . '" class="wish">' . csrf_field()
            . '<input type="hidden" name="produk_id" value="' . (int)$p["id"] . '">'
            . '<input type="hidden" name="kembali" value="' . e(($_SERVER["REQUEST_URI"] ?? "/")) . '">'
            . '<button type="submit" class="ikon-tombol' . ($on ? " aktif" : "") . '" aria-label="' . ($on ? "Hapus dari wishlist" : "Tambah ke wishlist") . '">' . ikon("heart", 18) . "</button></form>";
    }
    $h .= '<div class="isi"><span class="kat">' . e($p["kategori_nama"] ?? "") . "</span>";
    $h .= '<h3><a href="' . e($href) . '">' . e($p["nama"]) . "</a></h3>";
    $h .= '<div class="rating">' . bintang((float)($p["rating"] ?? 0)) . ' <small>' . ((int)($p["jumlah_ulasan"] ?? 0) ? "(" . (int)$p["jumlah_ulasan"] . ")" : "Baru") . "</small></div>";
    $h .= '<div class="harga"><strong>' . rupiah($p["harga"]) . "</strong>";
    if ($diskon) $h .= ' <s>' . rupiah($p["harga_coret"]) . "</s>";
    $h .= "</div></div></article>";
    return $h;
}

function paginasi(int $hal, int $total, string $dasar, array $param): string
{
    if ($total <= 1) return "";
    $link = function (int $n, string $t, string $kelas = "") use ($dasar, $param) {
        $param["hal"] = $n;
        return '<a class="' . $kelas . '" href="' . e(url($dasar) . "?" . http_build_query($param)) . '">' . $t . "</a>";
    };
    $o = '<nav class="paginasi" aria-label="Halaman">';
    if ($hal > 1) $o .= $link($hal - 1, "‹ Sebelumnya");
    for ($i = 1; $i <= $total; $i++) {
        if ($i === 1 || $i === $total || abs($i - $hal) <= 1) $o .= $i === $hal ? '<span class="aktif" aria-current="page">' . $i . "</span>" : $link($i, (string)$i);
        elseif (abs($i - $hal) === 2) $o .= "<span>…</span>";
    }
    if ($hal < $total) $o .= $link($hal + 1, "Berikutnya ›");
    return $o . "</nav>";
}

function tampil_flash(): string
{
    $o = "";
    foreach (ambil_flash() as [$j, $t]) $o .= '<div class="alert alert-' . e($j) . '" role="status">' . e($t) . "</div>";
    return $o;
}
