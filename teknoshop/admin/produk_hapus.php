<?php
require __DIR__ . "/../inc/bootstrap.php";
wajib_admin();
wajib_post();
$p = ambil("SELECT * FROM produk WHERE id = ?", [post_int("id")]);
if (!$p) { set_flash("Produk tidak ditemukan.", "bad"); redirect("admin/produk.php"); }
if ((int)nilai("SELECT COUNT(*) FROM pesanan_item WHERE produk_id = ?", [$p["id"]]) > 0) {
    // Sudah pernah dipesan: jangan hapus agar riwayat pesanan utuh
    q("UPDATE produk SET aktif = 0 WHERE id = ?", [$p["id"]]);
    set_flash("Produk sudah pernah dipesan, jadi dinonaktifkan (bukan dihapus) agar riwayat pesanan tetap utuh.", "warn");
} else {
    q("DELETE FROM produk WHERE id = ?", [$p["id"]]);
    hapus_gambar($p["gambar"]);
    set_flash("Produk dihapus.");
}
redirect("admin/produk.php");
