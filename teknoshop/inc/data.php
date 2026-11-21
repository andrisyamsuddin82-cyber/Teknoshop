<?php
// Pengambilan data katalog.

function kategori_semua(): array
{
    return semua("SELECT k.*, (SELECT COUNT(*) FROM produk p WHERE p.kategori_id = k.id AND p.aktif = 1) AS jumlah
                  FROM kategori k ORDER BY k.id");
}

const URUTAN_PRODUK = [
    "terbaru"   => ["Terbaru", "p.id DESC"],
    "terlaris"  => ["Terlaris", "p.terjual DESC, p.id DESC"],
    "termurah"  => ["Harga terendah", "p.harga ASC, p.id DESC"],
    "termahal"  => ["Harga tertinggi", "p.harga DESC, p.id DESC"],
];

const SELECT_PRODUK = "SELECT p.*, k.nama AS kategori_nama, k.slug AS kategori_slug,
        (SELECT AVG(u.rating) FROM ulasan u WHERE u.produk_id = p.id) AS rating,
        (SELECT COUNT(*) FROM ulasan u WHERE u.produk_id = p.id) AS jumlah_ulasan
        FROM produk p JOIN kategori k ON k.id = p.kategori_id";

// $f: q, kategori (slug), min, maks, urut, hal, per
function produk_cari(array $f): array
{
    $where = ["p.aktif = 1"];
    $par = [];
    if (($f["q"] ?? "") !== "") {
        $like = "%" . addcslashes($f["q"], "%_\\") . "%";
        $where[] = "(p.nama LIKE ? ESCAPE '\\' OR p.deskripsi LIKE ? ESCAPE '\\')";
        array_push($par, $like, $like);
    }
    if (($f["kategori"] ?? "") !== "") {
        $where[] = "k.slug = ?";
        $par[] = $f["kategori"];
    }
    if (($f["min"] ?? 0) > 0) { $where[] = "p.harga >= ?"; $par[] = (int)$f["min"]; }
    if (($f["maks"] ?? 0) > 0) { $where[] = "p.harga <= ?"; $par[] = (int)$f["maks"]; }
    $w = " WHERE " . implode(" AND ", $where);
    $urut = URUTAN_PRODUK[$f["urut"] ?? ""][1] ?? URUTAN_PRODUK["terbaru"][1];
    $per = max(1, (int)($f["per"] ?? PER_HALAMAN));
    $total = (int)nilai("SELECT COUNT(*) FROM produk p JOIN kategori k ON k.id = p.kategori_id" . $w, $par);
    $hal = max(1, min((int)($f["hal"] ?? 1), max(1, (int)ceil($total / $per))));
    $off = ($hal - 1) * $per;
    $baris = semua(SELECT_PRODUK . $w . " ORDER BY $urut LIMIT " . $per . " OFFSET " . $off, $par);
    return ["baris" => $baris, "total" => $total, "hal" => $hal, "halaman" => max(1, (int)ceil($total / $per))];
}

function produk_daftar(string $kondisi, array $par = [], string $urut = "p.id DESC", int $batas = 8): array
{
    return semua(SELECT_PRODUK . " WHERE p.aktif = 1 AND $kondisi ORDER BY $urut LIMIT " . (int)$batas, $par);
}
function produk_slug(string $slug): ?array
{
    return ambil(SELECT_PRODUK . " WHERE p.slug = ? AND p.aktif = 1", [$slug]);
}
function produk_id(int $id): ?array
{
    return ambil(SELECT_PRODUK . " WHERE p.id = ? AND p.aktif = 1", [$id]);
}
function produk_terkait(array $p, int $n = 4): array
{
    return produk_daftar("p.kategori_id = ? AND p.id <> ?", [$p["kategori_id"], $p["id"]], "p.terjual DESC", $n);
}
function ulasan_produk(int $pid): array
{
    return semua("SELECT u.*, g.nama FROM ulasan u JOIN pengguna g ON g.id = u.pengguna_id
                  WHERE u.produk_id = ? ORDER BY u.id DESC", [$pid]);
}
// Pelanggan boleh mengulas bila sudah punya pesanan selesai berisi produk ini
function boleh_ulas(int $uid, int $pid): bool
{
    return (int)nilai("SELECT COUNT(*) FROM pesanan o JOIN pesanan_item i ON i.pesanan_id = o.id
                       WHERE o.pengguna_id = ? AND o.status = 'selesai' AND i.produk_id = ?", [$uid, $pid]) > 0;
}
function di_wishlist(int $uid, int $pid): bool
{
    return (int)nilai("SELECT COUNT(*) FROM wishlist WHERE pengguna_id = ? AND produk_id = ?", [$uid, $pid]) > 0;
}
function id_wishlist(int $uid): array
{
    return array_map("intval", array_column(semua("SELECT produk_id FROM wishlist WHERE pengguna_id = ?", [$uid]), "produk_id"));
}
