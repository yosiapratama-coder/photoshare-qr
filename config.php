<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'photoshare_qr';
const DB_USER = 'root';
const DB_PASS = '';
// Untuk localhost gunakan URL ini. Saat hosting, ganti ke URL publik HTTPS, tanpa slash akhir.
const BASE_URL = 'http://localhost/photoshare_qr';

const MAX_FILE_BYTES = 12 * 1024 * 1024; // 12 MB per foto
const MAX_FILES_PER_REQUEST = 20;

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}
function base_url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
