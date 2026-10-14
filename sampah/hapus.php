<?php
require "../auth.php";
require_admin();

$id = (int) ($_GET["id"] ?? 0);
if ($id > 0) {
    db_exec($koneksi, "DELETE FROM sampah WHERE Id_sampah = ?", "i", [$id]);
    flash("success", "Jenis sampah dihapus.");
}

header("Location: index.php");
exit();
