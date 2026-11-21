<?php
// Pembuatan dan perubahan status pesanan.

function riwayat_tambah(int $oid, string $status, string $catatan): void
{
    q("INSERT INTO riwayat_status (pesanan_id, status, catatan, dibuat) VALUES (?,?,?,?)",
        [$oid, $status, $catatan, date("Y-m-d H:i:s")]);
}

// Membuat pesanan dari keranjang. Mengembalikan [kode|null, galat|null]
function pesanan_buat(array $u, array $in): array
{
    $item = kr_item();
    if (!$item) return [null, "Keranjang masih kosong."];
    $kurir = $in["kurir"];
    $metode = $in["metode"];
    if (!isset(KURIR[$kurir]) || !isset(METODE_BAYAR[$metode])) return [null, "Pilihan kurir atau pembayaran tidak valid."];
    $sub = array_sum(array_column($item, "subtotal"));
    [$kupon, $gal] = kupon_cek($in["kupon"] ?? "", $sub);
    if ($gal) return [null, $gal];
    $t = total_hitung($item, $kurir, $kupon);
    $sekarang = date("Y-m-d H:i:s");
    $status = $metode === "cod" ? "diproses" : "menunggu_pembayaran";

    $pdo = db();
    $pdo->beginTransaction();
    try {
        foreach ($item as $it) {
            // Pengurangan stok atomik: gagal bila stok sudah tidak cukup
            $st = q("UPDATE produk SET stok = stok - ?, terjual = terjual + ? WHERE id = ? AND aktif = 1 AND stok >= ?",
                [$it["jumlah"], $it["jumlah"], $it["id"], $it["jumlah"]]);
            if ($st->rowCount() !== 1) {
                $pdo->rollBack();
                return [null, "Stok \"{$it["nama"]}\" tidak mencukupi. Periksa kembali keranjang Anda."];
            }
        }
        if ($kupon) {
            $st = q("UPDATE kupon SET terpakai = terpakai + 1 WHERE id = ? AND aktif = 1 AND (kuota IS NULL OR terpakai < kuota)", [$kupon["id"]]);
            if ($st->rowCount() !== 1) {
                $pdo->rollBack();
                return [null, "Kuota kupon sudah habis."];
            }
        }
        $kode = null;
        for ($i = 0; $i < 5 && !$kode; $i++) {
            $coba = "INV-" . date("Ymd") . "-" . str_pad((string)random_int(1, 99999), 5, "0", STR_PAD_LEFT);
            if (!nilai("SELECT 1 FROM pesanan WHERE kode = ?", [$coba])) $kode = $coba;
        }
        if (!$kode) throw new RuntimeException("kode");
        q("INSERT INTO pesanan (kode, pengguna_id, status, nama_penerima, telepon, alamat, kurir, ongkir, metode_bayar,
                subtotal, diskon, total, kupon_kode, catatan, resi, dibuat, diperbarui)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$kode, $u["id"], $status, $in["nama"], $in["telepon"], $in["alamat"], $kurir, $t["ongkir"], $metode,
             $t["subtotal"], $t["diskon"], $t["total"], $kupon["kode"] ?? null, $in["catatan"] ?: null, null, $sekarang, $sekarang]);
        $oid = (int)$pdo->lastInsertId();
        foreach ($item as $it) {
            q("INSERT INTO pesanan_item (pesanan_id, produk_id, nama, harga, jumlah) VALUES (?,?,?,?,?)",
                [$oid, $it["id"], $it["nama"], $it["harga"], $it["jumlah"]]);
        }
        riwayat_tambah($oid, $status, $metode === "cod" ? "Pesanan dibuat (bayar di tempat)" : "Pesanan dibuat");
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return [null, "Pesanan gagal dibuat. Silakan coba lagi."];
    }
    kr_kosongkan();
    return [$kode, null];
}

function pesanan_kode(string $kode, ?int $uid = null): ?array
{
    $sql = "SELECT o.*, g.nama AS pelanggan_nama, g.email AS pelanggan_email FROM pesanan o JOIN pengguna g ON g.id = o.pengguna_id WHERE o.kode = ?";
    $par = [$kode];
    if ($uid !== null) { $sql .= " AND o.pengguna_id = ?"; $par[] = $uid; }
    return ambil($sql, $par);
}
function pesanan_item(int $oid): array
{
    return semua("SELECT i.*, p.slug, p.gambar FROM pesanan_item i JOIN produk p ON p.id = i.produk_id WHERE i.pesanan_id = ? ORDER BY i.id", [$oid]);
}
function pesanan_riwayat(int $oid): array
{
    return semua("SELECT * FROM riwayat_status WHERE pesanan_id = ? ORDER BY id", [$oid]);
}

// Ubah status dengan validasi transisi. Pembatalan mengembalikan stok dan kuota kupon.
// $peran: "admin" atau "pelanggan". Mengembalikan galat atau null bila berhasil.
function pesanan_ubah_status(array $o, string $baru, string $peran, string $catatan = "", ?string $resi = null): ?string
{
    $lama = $o["status"];
    if ($peran === "admin") {
        if (!in_array($baru, TRANSISI_ADMIN[$lama] ?? [], true)) return "Perubahan status tidak diizinkan.";
    } else {
        $boleh = [
            "menunggu_pembayaran" => ["menunggu_verifikasi", "dibatalkan"],
            "dikirim" => ["selesai"],
        ];
        if (!in_array($baru, $boleh[$lama] ?? [], true)) return "Perubahan status tidak diizinkan.";
    }
    if ($baru === "dikirim" && ($resi === null || $resi === "")) return "Nomor resi wajib diisi saat mengirim pesanan.";

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Kunci logika: hanya berhasil bila status masih sama seperti saat dibaca
        $st = q("UPDATE pesanan SET status = ?, diperbarui = ?" . ($baru === "dikirim" ? ", resi = ?" : "") . " WHERE id = ? AND status = ?",
            array_merge([$baru, date("Y-m-d H:i:s")], $baru === "dikirim" ? [$resi] : [], [$o["id"], $lama]));
        if ($st->rowCount() !== 1) {
            $pdo->rollBack();
            return "Status pesanan sudah berubah. Muat ulang halaman.";
        }
        if ($baru === "dibatalkan") {
            foreach (pesanan_item((int)$o["id"]) as $it) {
                q("UPDATE produk SET stok = stok + ?, terjual = CASE WHEN terjual >= ? THEN terjual - ? ELSE 0 END WHERE id = ?",
                    [$it["jumlah"], $it["jumlah"], $it["jumlah"], $it["produk_id"]]);
            }
            if ($o["kupon_kode"]) q("UPDATE kupon SET terpakai = CASE WHEN terpakai > 0 THEN terpakai - 1 ELSE 0 END WHERE kode = ?", [$o["kupon_kode"]]);
        }
        $teks = $catatan !== "" ? $catatan : [
            "menunggu_verifikasi" => "Pelanggan mengonfirmasi pembayaran",
            "menunggu_pembayaran" => "Pembayaran belum diterima, menunggu pembayaran ulang",
            "diproses" => "Pembayaran terverifikasi, pesanan diproses",
            "dikirim" => "Pesanan dikirim (resi $resi)",
            "selesai" => "Pesanan diterima pelanggan",
            "dibatalkan" => $peran === "admin" ? "Pesanan dibatalkan oleh admin" : "Pesanan dibatalkan oleh pelanggan",
        ][$baru];
        riwayat_tambah((int)$o["id"], $baru, $teks);
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return "Gagal mengubah status. Coba lagi.";
    }
    return null;
}
