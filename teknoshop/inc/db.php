<?php
// Koneksi database (PDO + MySQL).
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $ex) {
            http_response_code(500);
            exit("Koneksi database gagal. Pastikan MySQL aktif, database toko_online sudah diimpor (database.sql), "
                . "dan pengaturan di inc/config.php benar.");
        }
    }
    return $pdo;
}
