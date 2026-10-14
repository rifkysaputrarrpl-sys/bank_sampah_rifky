<?php
require "../auth.php";
require_permission("top_up");

// Admin tidak mengajukan top up; admin hanya menyetujui
if (is_admin()) {
    header("Location: index.php");
    exit();
}

const MAX_TOPUP = 100000000;   // batas per pengajuan (Rp), ubah sesuai kebutuhan

$uid   = (int) $_SESSION["id_user"];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $nominal = round((float) ($_POST["nominal"] ?? 0), 2);

    if ($nominal <= 0 || $nominal > MAX_TOPUP) {
        $error = "Nominal harus lebih dari 0 dan maksimal " . rupiah(MAX_TOPUP) . ".";
    } else {
        db_exec($koneksi, "INSERT INTO top_up (id_user, nominal, status) VALUES (?, ?, 'pending')",
            "is", [$uid, dec($nominal)]);
        flash("success", "Pengajuan top up terkirim. Saldo bertambah setelah disetujui admin.");
        header("Location: index.php");
        exit();
    }
}

$saldo = mysqli_fetch_assoc(db_select($koneksi, "SELECT saldo FROM users WHERE id_user = ?", "i", [$uid]))["saldo"] ?? 0;

layout_start("Ajukan Top Up", "top_up");
?>
        <div class="form-card">
            <h2>Ajukan Top Up Saldo</h2>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <p class="hint">
                Saldo Anda saat ini: <strong><?= rupiah($saldo) ?></strong><br>
                Setelah diajukan, top up berstatus <strong>Pending</strong> dan saldo baru bertambah setelah admin menyetujui.
            </p>

            <form method="POST" action="tambah.php">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Nominal Top Up (Rp)</label>
                    <input type="number" step="1" min="1" max="<?= MAX_TOPUP ?>" name="nominal" class="input-control" placeholder="Contoh: 50000" required autofocus>
                </div>
                <button type="submit" class="btn-submit">Ajukan Top Up</button>
            </form>

            <a class="btn-kembali" href="index.php">&larr; Kembali ke daftar top up</a>
        </div>
<?php layout_end(); ?>
