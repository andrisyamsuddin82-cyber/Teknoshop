<?php
// Fungsi umum: escape, URL, format, flash, CSRF, upload.

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
}

// ---- URL ----
function base_url(): string
{
    static $b = null;
    if ($b !== null) return $b;
    if (BASE_URL_MANUAL !== null) return $b = rtrim(BASE_URL_MANUAL, "/");
    $doc = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"] ?? "") ?: "");
    $root = str_replace("\\", "/", realpath(ROOT_PATH) ?: ROOT_PATH);
    if ($doc !== "" && stripos($root, $doc) === 0) {
        return $b = rtrim(substr($root, strlen($doc)), "/");
    }
    return $b = "";
}
function url(string $path = ""): string
{
    return base_url() . "/" . ltrim($path, "/");
}
function aset(string $path): string
{
    $f = ROOT_PATH . "/" . ltrim($path, "/");
    $v = is_file($f) ? filemtime($f) : 1;
    return url($path) . "?v=" . $v;
}
function gambar_url(?string $g): string
{
    if (!$g) return url("assets/img/placeholder.svg");
    // Foto produk dari URL HTTPS eksternal (foto katalog asli).
    if (preg_match('~^https://[a-zA-Z0-9.-]+(?::[0-9]+)?/[^\s]*$~', $g)) return $g;
    // Foto yang diunggah admin tetap menggunakan path lokal.
    if (is_file(ROOT_PATH . "/" . $g)) return url($g);
    return url("assets/img/placeholder.svg");
}
// Jika foto dari server luar gagal dimuat (hotlink diblokir / URL mati),
// tampilkan ilustrasi lokal produk agar area gambar tidak kosong.
function gambar_fallback_attr(?string $slug): string
{
    $nama = slugify((string)$slug);
    $file = "assets/img/produk/" . $nama . ".svg";
    if (!is_file(ROOT_PATH . "/" . $file)) $file = "assets/img/placeholder.svg";
    return 'onerror="this.onerror=null;this.src=\'' . e(url($file)) . '\';"';
}

function path_sekarang(): string
{
    $p = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ?: "/";
    $b = base_url();
    if ($b !== "" && strpos($p, $b) === 0) $p = substr($p, strlen($b));
    return "/" . ltrim($p, "/");
}
function redirect(string $path): void
{
    // $path selalu relatif terhadap akar situs, mis. "katalog.php?q=a"
    header("Location: " . url($path));
    exit;
}
// Alamat tujuan kembali: hanya path internal
function aman_kembali(?string $t, string $cadangan = "index.php"): string
{
    if (!$t || $t[0] !== "/" || strpos($t, "//") === 0 || strpos($t, "\\") !== false || preg_match('/[\r\n]/', $t)) {
        return $cadangan;
    }
    $b = base_url();
    if ($b !== "" && strpos($t, $b . "/") === 0) $t = substr($t, strlen($b) + 1);
    return ltrim($t, "/");
}

// ---- format ----
function rupiah($n): string
{
    return "Rp" . number_format((int)$n, 0, ",", ".");
}
function tgl($s, bool $jam = false): string
{
    if (!$s) return "-";
    $t = strtotime($s);
    $bulan = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    $o = date("j", $t) . " " . $bulan[(int)date("n", $t)] . " " . date("Y", $t);
    return $jam ? $o . ", " . date("H:i", $t) : $o;
}
function slugify(string $t): string
{
    $t = strtolower(trim($t));
    $t = preg_replace('/[^a-z0-9]+/', "-", $t);
    return trim($t, "-") ?: "produk";
}
function potong(string $t, int $n): string
{
    return mb_strlen($t) > $n ? rtrim(mb_substr($t, 0, $n - 1)) . "…" : $t;
}
function status_info(string $s): array
{
    return STATUS_PESANAN[$s] ?? ["label" => $s, "kelas" => "info"];
}

// ---- input ----
function post_teks(string $k, int $maks = 5000): string
{
    $v = $_POST[$k] ?? "";
    return is_string($v) ? mb_substr(trim($v), 0, $maks) : "";
}
function get_teks(string $k): string
{
    $v = $_GET[$k] ?? "";
    return is_string($v) ? trim($v) : "";
}
function get_int(string $k, int $def = 0): int
{
    $v = $_GET[$k] ?? null;
    return is_string($v) && ctype_digit($v) ? (int)$v : $def;
}
function post_int(string $k, int $def = 0): int
{
    $v = $_POST[$k] ?? null;
    return is_string($v) && preg_match('/^-?\d+$/', $v) ? (int)$v : $def;
}
function wajib_post(): void
{
    if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
        http_response_code(405);
        exit("Metode tidak diizinkan.");
    }
    cek_csrf();
}

// ---- flash ----
function set_flash(string $pesan, string $jenis = "ok"): void
{
    $_SESSION["flash"][] = [$jenis, $pesan];
}
function ambil_flash(): array
{
    $f = $_SESSION["flash"] ?? [];
    unset($_SESSION["flash"]);
    return $f;
}

// ---- CSRF ----
function token_csrf(): string
{
    if (empty($_SESSION["csrf"])) $_SESSION["csrf"] = bin2hex(random_bytes(32));
    return $_SESSION["csrf"];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(token_csrf()) . '">';
}
function cek_csrf(): void
{
    $t = $_SESSION["csrf"] ?? "";
    $k = $_POST["csrf"] ?? "";
    // Token sesi wajib ada: dua string kosong dianggap sama oleh hash_equals
    if ($t === "" || !is_string($k) || !hash_equals($t, $k)) {
        http_response_code(403);
        exit("Permintaan tidak valid. Muat ulang halaman lalu coba lagi.");
    }
}

// ---- upload gambar produk ----
// Mengembalikan [path|null, galat|null]
function simpan_gambar(array $f): array
{
    if (($f["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f["error"] !== UPLOAD_ERR_OK) return [null, "Unggah gambar gagal (kode " . (int)$f["error"] . ")."];
    if ($f["size"] > MAKS_UPLOAD_BYTE) return [null, "Ukuran gambar maksimal 2 MB."];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f["tmp_name"]);
    $ext = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"][$mime] ?? null;
    if (!$ext || @getimagesize($f["tmp_name"]) === false) return [null, "Berkas harus berupa gambar JPG, PNG, atau WebP."];
    $dir = ROOT_PATH . "/uploads/produk";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $nama = bin2hex(random_bytes(10)) . "." . $ext;
    if (!move_uploaded_file($f["tmp_name"], "$dir/$nama")) return [null, "Gambar tidak dapat disimpan."];
    return ["uploads/produk/$nama", null];
}
function hapus_gambar(?string $g): void
{
    if ($g && strpos($g, "uploads/produk/") === 0 && is_file(ROOT_PATH . "/" . $g)) @unlink(ROOT_PATH . "/" . $g);
}
