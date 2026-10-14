<?php
require "../auth.php";
require_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $id   = (int) ($_POST["id"] ?? 0);
    $aksi = $_POST["aksi"] ?? "";

    try {
        mysqli_begin_transaction($koneksi);

        $topup = mysqli_fetch_assoc(db_select($koneksi,
            "SELECT * FROM top_up WHERE id_topup = ? FOR UPDATE", "i", [$id]));

        if (!$topup) {
            flash("error", "Data top up tidak ditemukan.");
        } elseif ($aksi === "hapus") {
            db_exec($koneksi, "DELETE FROM top_up WHERE id_topup = ?", "i", [$id]);
            flash("success", "Data top up dihapus.");
        } elseif ($topup["status"] !== "pending") {
            flash("error", "Top up ini sudah diproses sebelumnya.");
        } elseif ($aksi === "setuju") {
            db_exec($koneksi, "UPDATE users SET saldo = saldo + ? WHERE id_user = ?",
                "si", [dec($topup["nominal"]), (int) $topup["id_user"]]);
            db_exec($koneksi, "UPDATE top_up SET status = 'disetujui' WHERE id_topup = ?", "i", [$id]);
            flash("success", "Top up disetujui, saldo nasabah bertambah " . rupiah($topup["nominal"]) . ".");
        } elseif ($aksi === "tolak") {
            db_exec($koneksi, "UPDATE top_up SET status = 'ditolak' WHERE id_topup = ?", "i", [$id]);
            flash("success", "Top up ditolak.");
        }

        mysqli_commit($koneksi);
    } catch (Throwable $ex) {
        mysqli_rollback($koneksi);
        flash("error", "Terjadi kesalahan pada database. Tidak ada perubahan yang disimpan.");
    }
}

header("Location: index.php");
exit();
