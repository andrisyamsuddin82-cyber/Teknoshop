<?php
// Sesi, login, peran pengguna.

function mulai_sesi(): void
{
    if (session_status() !== PHP_SESSION_NONE) return;
    session_name("teknoshop");
    session_set_cookie_params([
        "path" => base_url() === "" ? "/" : base_url(),
        "httponly" => true,
        "samesite" => "Lax",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    ]);
    session_start();
}

function pengguna(): ?array
{
    if (array_key_exists("_pengguna", $GLOBALS)) return $GLOBALS["_pengguna"];
    $id = $_SESSION["uid"] ?? null;
    $u = $id ? ambil("SELECT id, nama, email, telepon, alamat, peran FROM pengguna WHERE id = ?", [$id]) : null;
    if ($id && !$u) unset($_SESSION["uid"]);
    return $GLOBALS["_pengguna"] = $u;
}
function sudah_login(): bool { return pengguna() !== null; }
function adalah_admin(): bool { return (pengguna()["peran"] ?? "") === "admin"; }

function wajib_login(): void
{
    if (!sudah_login()) {
        set_flash("Silakan masuk terlebih dahulu.", "warn");
        redirect("login.php?kembali=" . rawurlencode(path_sekarang() . (!empty($_SERVER["QUERY_STRING"]) && ($_SERVER["REQUEST_METHOD"] ?? "") === "GET" ? "?" . $_SERVER["QUERY_STRING"] : "")));
    }
}
function wajib_admin(): void
{
    if (!sudah_login()) redirect("login.php?kembali=" . rawurlencode(path_sekarang()));
    if (!adalah_admin()) {
        http_response_code(403);
        exit("Akses ditolak. Halaman ini khusus administrator.");
    }
}

// Pembatasan percobaan masuk: 5 kali gagal -> tunggu 60 detik
function login_terkunci(): int
{
    $l = $_SESSION["login_gagal"] ?? ["n" => 0, "t" => 0];
    if ($l["n"] >= 5 && time() - $l["t"] < 60) return 60 - (time() - $l["t"]);
    return 0;
}
function login_coba(string $email, string $sandi): bool
{
    if (login_terkunci() > 0) return false;
    $u = ambil("SELECT id, password FROM pengguna WHERE email = ?", [strtolower($email)]);
    if ($u && password_verify($sandi, $u["password"])) {
        session_regenerate_id(true);
        $_SESSION["uid"] = (int)$u["id"];
        unset($GLOBALS["_pengguna"]);
        unset($_SESSION["login_gagal"]);
        return true;
    }
    $l = $_SESSION["login_gagal"] ?? ["n" => 0, "t" => 0];
    if ($l["n"] >= 5) $l["n"] = 0;
    $_SESSION["login_gagal"] = ["n" => $l["n"] + 1, "t" => time()];
    return false;
}
function logout_sesi(): void
{
    // Kosongkan data dan ganti ID sesi sehingga ID lama tidak berlaku lagi
    $_SESSION = [];
    unset($GLOBALS["_pengguna"]);
    session_regenerate_id(true);
}
