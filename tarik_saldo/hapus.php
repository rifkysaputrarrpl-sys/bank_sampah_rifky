<?php
require "../auth.php";
require_admin();

$id = (int) ($_GET["id"] ?? 0);
if ($id > 0) {
    db_exec($koneksi, "DELETE FROM tarik_saldo WHERE id_tarik = ?", "i", [$id]);
    flash("success", "Data penarikan dihapus.");
}

header("Location: index.php");
exit();
