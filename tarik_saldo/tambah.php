<?php
require "../auth.php";
require_permission("tarik_saldo");

$uid   = (int) $_SESSION["id_user"];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $id_user = is_admin() ? (int) ($_POST["id_user"] ?? 0) : $uid;   // non-admin selalu atas nama sendiri
    $nominal = round((float) ($_POST["nominal"] ?? 0), 2);

    $user = mysqli_fetch_assoc(db_select($koneksi, "SELECT saldo FROM users WHERE id_user = ?", "i", [$id_user]));

    if (!$user) {
        $error = "Nasabah tidak ditemukan.";
    } elseif ($nominal <= 0) {
        $error = "Nominal harus lebih dari 0.";
    } elseif ($nominal > (float) $user["saldo"]) {
        $error = "Nominal melebihi saldo (" . rupiah($user["saldo"]) . ").";
    } else {
        db_exec($koneksi, "INSERT INTO tarik_saldo (id_user, nominal, status) VALUES (?, ?, 'pending')",
            "is", [$id_user, dec($nominal)]);
        flash("success", "Pengajuan penarikan terkirim dan menunggu persetujuan admin.");
        header("Location: index.php");
        exit();
    }
}

$users = is_admin() ? mysqli_query($koneksi, "SELECT id_user, nama FROM users ORDER BY nama") : null;
$saldo = is_admin() ? null : (mysqli_fetch_assoc(db_select($koneksi, "SELECT saldo FROM users WHERE id_user = ?", "i", [$uid]))["saldo"] ?? 0);

layout_start("Ajukan Penarikan", "tarik_saldo");
?>
        <div class="form-card">
            <h2>Ajukan Penarikan Saldo</h2>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($saldo !== null): ?>
                <p class="hint">Saldo Anda saat ini: <strong><?= rupiah($saldo) ?></strong></p>
            <?php endif; ?>

            <form method="POST" action="tambah.php">
                <?= csrf_field() ?>
                <?php if (is_admin()): ?>
                <div class="form-group">
                    <label>Nasabah</label>
                    <select name="id_user" class="input-control" required>
                        <option value="">-- Pilih Nasabah --</option>
                        <?php while ($u = mysqli_fetch_assoc($users)): ?>
                            <option value="<?= (int) $u["id_user"] ?>"><?= e($u["nama"]) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Nominal Penarikan (Rp)</label>
                    <input type="number" step="1" min="1" name="nominal" class="input-control" placeholder="Contoh: 50000" required>
                </div>

                <button type="submit" class="btn-submit">Ajukan Penarikan</button>
            </form>

            <a class="btn-kembali" href="index.php">&larr; Kembali ke daftar penarikan</a>
        </div>
<?php layout_end(); ?>
