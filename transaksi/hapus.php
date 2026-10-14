<?php
require "../auth.php";
require_admin();

$id = (int) ($_GET["id"] ?? 0);
if ($id > 0) {
    db_exec($koneksi, "DELETE FROM transaksi WHERE id_transaksi = ?", "i", [$id]);
    flash("success", "Data transaksi dihapus.");
}

header("Location: index.php");
exit();
