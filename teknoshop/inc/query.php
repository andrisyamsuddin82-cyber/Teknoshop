<?php
// Pembantu query: semua memakai prepared statement.
function q(string $sql, array $p = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute(array_values($p));
    return $st;
}
function ambil(string $sql, array $p = []): ?array
{
    $r = q($sql, $p)->fetch();
    return $r === false ? null : $r;
}
function semua(string $sql, array $p = []): array
{
    return q($sql, $p)->fetchAll();
}
function nilai(string $sql, array $p = [])
{
    $r = q($sql, $p)->fetchColumn();
    return $r === false ? null : $r;
}
