<?php
// Pengaturan utama Teknoshop. Sesuaikan bagian database dengan XAMPP Anda.

const DB_HOST = "localhost";
const DB_NAME = "toko_online";
const DB_USER = "root";
const DB_PASS = "";

const NAMA_TOKO = "Teknoshop";
const TAGLINE = "Gadget dan perlengkapan belajar untuk mahasiswa";
const ZONA_WAKTU = "Asia/Makassar";
const PER_HALAMAN = 12;
const MAKS_UPLOAD_BYTE = 2097152;      // 2 MB
const GRATIS_ONGKIR_MIN = 500000;      // kurir reguler gratis jika subtotal >= nilai ini
const BASE_URL_MANUAL = null;          // isi mis. "/teknoshop" jika alamat otomatis salah
const MODE_DEMO = true;                // true: tampilkan akun contoh di halaman login

const KURIR = [
    "hemat"   => ["nama" => "Hemat",   "estimasi" => "4-6 hari",  "tarif" => 9000],
    "reguler" => ["nama" => "Reguler", "estimasi" => "2-3 hari",  "tarif" => 15000],
    "ekspres" => ["nama" => "Ekspres", "estimasi" => "1 hari",    "tarif" => 30000],
];

// Nomor di bawah ini fiktif, hanya untuk contoh tampilan.
const METODE_BAYAR = [
    "transfer" => ["nama" => "Transfer Bank", "info" => "Bank Contoh 1234567890 a.n. Teknoshop Indonesia"],
    "ewallet"  => ["nama" => "E-Wallet",      "info" => "Dompet Digital 0812-0000-0000 a.n. Teknoshop Indonesia"],
    "cod"      => ["nama" => "Bayar di Tempat (COD)", "info" => "Bayar tunai kepada kurir saat pesanan tiba"],
];

const STATUS_PESANAN = [
    "menunggu_pembayaran" => ["label" => "Menunggu Pembayaran", "kelas" => "warn"],
    "menunggu_verifikasi" => ["label" => "Menunggu Verifikasi", "kelas" => "info"],
    "diproses"            => ["label" => "Diproses",            "kelas" => "info"],
    "dikirim"             => ["label" => "Dikirim",             "kelas" => "violet"],
    "selesai"             => ["label" => "Selesai",             "kelas" => "ok"],
    "dibatalkan"          => ["label" => "Dibatalkan",          "kelas" => "bad"],
];

// Perpindahan status yang boleh dilakukan admin
const TRANSISI_ADMIN = [
    "menunggu_pembayaran" => ["diproses", "dibatalkan"],
    "menunggu_verifikasi" => ["diproses", "menunggu_pembayaran", "dibatalkan"],
    "diproses"            => ["dikirim", "dibatalkan"],
    "dikirim"             => ["selesai"],
];
