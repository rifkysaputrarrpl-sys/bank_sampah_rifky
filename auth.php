<?php
// auth.php — session, hak akses per role, dan helper bersama.
// Cukup `require "auth.php";` (halaman di root) atau `require "../auth.php";` (halaman di subfolder).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/koneksi.php";

// URL dasar aplikasi (mis. /bank_sampah/) supaya link & redirect aman dari subfolder
$docRoot = rtrim(str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"])), "/");
$appDir  = rtrim(str_replace("\\", "/", realpath(__DIR__)), "/");
define("BASE_URL", substr($appDir, strlen($docRoot)) . "/");

/*
 * HAK AKSES PER ROLE — ubah di sini kalau aturan berubah.
 *  dashboard   : halaman dashboard
 *  sampah      : kelola jenis & stok sampah
 *  transaksi   : lihat riwayat transaksi (admin = semua, lainnya = milik sendiri)
 *  setor       : boleh setor sampah
 *  beli        : boleh beli sampah (saldo berkurang, stok berkurang)
 *  top_up      : ajukan top up saldo (admin = menyetujui)
 *  tarik_saldo : ajukan penarikan saldo (admin = menyetujui)
 */
const PERMISSIONS = [
    "admin"    => ["dashboard", "sampah", "transaksi", "setor", "beli", "top_up", "tarik_saldo"],
    "nasabah"  => ["dashboard", "transaksi", "setor", "beli", "top_up", "tarik_saldo"],
    "siswa"    => ["dashboard", "transaksi", "setor", "beli", "top_up"],
    "guru"     => ["dashboard", "transaksi", "setor", "beli", "top_up"],
    "pengepul" => ["dashboard", "transaksi", "beli", "top_up"],
];

function current_role(): string { return $_SESSION["role"] ?? ""; }
function is_admin(): bool { return current_role() === "admin"; }
function can(string $permission): bool {
    return in_array($permission, PERMISSIONS[current_role()] ?? [], true);
}

function require_login(): void {
    if (!isset($_SESSION["id_user"])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}
function require_permission(string $permission): void {
    require_login();
    if (!can($permission)) { forbidden(); }
}
function require_admin(): void {
    require_login();
    if (!is_admin()) { forbidden(); }
}
function forbidden(): void {
    http_response_code(403);
    layout_start("Akses Ditolak");
    echo '<div class="form-card"><h2>Akses Ditolak</h2>'
       . '<p class="hint">Role Anda tidak memiliki izin untuk membuka halaman ini.</p>'
       . '<a class="btn-kembali" href="' . BASE_URL . 'index.php">&larr; Kembali ke dashboard</a></div>';
    layout_end();
    exit();
}

// ---------- Helper umum ----------
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); }
function rupiah($n): string { return "Rp " . number_format((float) $n, 0, ",", "."); }
function flash(string $type, string $msg): void { $_SESSION["flash"] = [$type, $msg]; }

// Prepared statement singkat
function db_exec(mysqli $db, string $sql, string $types = "", array $params = []): mysqli_stmt {
    $stmt = mysqli_prepare($db, $sql);
    if ($types !== "") { mysqli_stmt_bind_param($stmt, $types, ...$params); }
    mysqli_stmt_execute($stmt);
    return $stmt;
}
function db_select(mysqli $db, string $sql, string $types = "", array $params = []) {
    return mysqli_stmt_get_result(db_exec($db, $sql, $types, $params));
}
function dec($n): string { return number_format((float) $n, 2, ".", ""); }

// CSRF untuk semua form POST
function csrf_token(): string {
    if (empty($_SESSION["csrf"])) { $_SESSION["csrf"] = bin2hex(random_bytes(32)); }
    return $_SESSION["csrf"];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_verify(): void {
    if (!hash_equals($_SESSION["csrf"] ?? "", $_POST["csrf"] ?? "")) {
        http_response_code(419);
        die("Sesi form tidak valid. Silakan kembali, muat ulang halaman, lalu coba lagi.");
    }
}

require_once __DIR__ . "/layout.php";
