<?php
// Keranjang belanja (sesi) dan perhitungan total.

function kr_ambil(): array { return $_SESSION["keranjang"] ?? []; }
function kr_jumlah(): int { return array_sum(kr_ambil()); }

function kr_tambah(int $pid, int $jml): string
{
    $p = produk_id($pid);
    if (!$p) return "Produk tidak ditemukan.";
    if ($p["stok"] < 1) return "Maaf, stok produk ini habis.";
    $kr = kr_ambil();
    $baru = ($kr[$pid] ?? 0) + max(1, $jml);
    $kr[$pid] = min($baru, (int)$p["stok"]);
    $_SESSION["keranjang"] = $kr;
    return $baru > $p["stok"] ? "Jumlah disesuaikan dengan stok tersedia ({$p["stok"]})." : "";
}
function kr_set(int $pid, int $jml): void
{
    $kr = kr_ambil();
    if ($jml <= 0) unset($kr[$pid]);
    elseif (isset($kr[$pid])) $kr[$pid] = $jml;
    $_SESSION["keranjang"] = $kr;
}
function kr_kosongkan(): void { unset($_SESSION["keranjang"], $_SESSION["kupon"]); }

// Item keranjang lengkap; jumlah dipangkas sesuai stok, produk nonaktif dibuang
function kr_item(): array
{
    $kr = kr_ambil();
    if (!$kr) return [];
    $in = implode(",", array_fill(0, count($kr), "?"));
    $rows = semua("SELECT id, nama, slug, harga, stok, gambar FROM produk WHERE aktif = 1 AND id IN ($in)", array_keys($kr));
    $out = [];
    foreach ($rows as $r) {
        $j = min((int)$kr[$r["id"]], (int)$r["stok"]);
        if ($j < 1) continue;
        $r["jumlah"] = $j;
        $r["subtotal"] = $j * (int)$r["harga"];
        $out[] = $r;
    }
    // sinkronkan sesi
    $_SESSION["keranjang"] = array_column($out, "jumlah", "id");
    return $out;
}

// ---- kupon ----
// Mengembalikan [kupon|null, galat|null]
function kupon_cek(string $kode, int $subtotal): array
{
    $kode = strtoupper(trim($kode));
    if ($kode === "") return [null, null];
    $k = ambil("SELECT * FROM kupon WHERE kode = ?", [$kode]);
    if (!$k || !$k["aktif"]) return [null, "Kode kupon tidak ditemukan."];
    if ($k["berlaku_sampai"] && $k["berlaku_sampai"] < date("Y-m-d")) return [null, "Kupon sudah kedaluwarsa."];
    if ($k["kuota"] !== null && $k["terpakai"] >= $k["kuota"]) return [null, "Kuota kupon sudah habis."];
    if ($subtotal < $k["min_belanja"]) return [null, "Minimal belanja untuk kupon ini " . rupiah($k["min_belanja"]) . "."];
    return [$k, null];
}
function kupon_diskon(?array $k, int $subtotal): int
{
    if (!$k) return 0;
    $d = $k["jenis"] === "persen" ? intdiv($subtotal * (int)$k["nilai"], 100) : (int)$k["nilai"];
    if ($k["jenis"] === "persen" && $k["maks_diskon"] !== null) $d = min($d, (int)$k["maks_diskon"]);
    return max(0, min($d, $subtotal));
}
function ongkir_hitung(string $kurir, int $subtotal): int
{
    if (!isset(KURIR[$kurir])) return 0;
    if ($kurir === "reguler" && $subtotal >= GRATIS_ONGKIR_MIN) return 0;
    return KURIR[$kurir]["tarif"];
}
function total_hitung(array $item, string $kurir, ?array $kupon): array
{
    $sub = array_sum(array_column($item, "subtotal"));
    $dis = kupon_diskon($kupon, $sub);
    $ong = $item ? ongkir_hitung($kurir, $sub) : 0;
    return ["subtotal" => $sub, "diskon" => $dis, "ongkir" => $ong, "total" => $sub - $dis + $ong];
}
