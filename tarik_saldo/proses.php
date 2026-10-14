<?php
require "../auth.php";
require_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $id   = (int) ($_POST["id"] ?? 0);
    $aksi = $_POST["aksi"] ?? "";

    try {
        mysqli_begin_transaction($koneksi);

        $tarik = mysqli_fetch_assoc(db_select($koneksi,
            "SELECT * FROM tarik_saldo WHERE id_tarik = ? FOR UPDATE", "i", [$id]));

        if (!$tarik || $tarik["status"] !== "pending") {
            flash("error", "Data tidak ditemukan atau sudah diproses.");
        } elseif ($aksi === "setuju") {
            $user = mysqli_fetch_assoc(db_select($koneksi,
                "SELECT saldo FROM users WHERE id_user = ? FOR UPDATE", "i", [(int) $tarik["id_user"]]));

            if (!$user || (float) $user["saldo"] < (float) $tarik["nominal"]) {
                flash("error", "Saldo nasabah tidak cukup untuk penarikan ini. Penarikan tidak diproses.");
            } else {
                db_exec($koneksi, "UPDATE users SET saldo = saldo - ? WHERE id_user = ?",
                    "si", [dec($tarik["nominal"]), (int) $tarik["id_user"]]);
                db_exec($koneksi, "UPDATE tarik_saldo SET status = 'disetujui' WHERE id_tarik = ?", "i", [$id]);
                flash("success", "Penarikan disetujui.");
            }
        } elseif ($aksi === "tolak") {
            db_exec($koneksi, "UPDATE tarik_saldo SET status = 'ditolak' WHERE id_tarik = ?", "i", [$id]);
            flash("success", "Penarikan ditolak.");
        }

        mysqli_commit($koneksi);
    } catch (Throwable $ex) {
        mysqli_rollback($koneksi);
        flash("error", "Terjadi kesalahan pada database. Tidak ada perubahan yang disimpan.");
    }
}

header("Location: index.php");
exit();
