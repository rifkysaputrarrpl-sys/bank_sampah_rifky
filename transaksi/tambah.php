<?php
require "../auth.php";
require_login();

// Tipe yang boleh dipakai sesuai role.
// Di database: 'setor' = setor sampah, 'tarik' = beli sampah (stok keluar, saldo berkurang)
$allowed = [];
if (can("setor")) { $allowed["setor"] = "Setor Sampah (saldo bertambah)"; }
if (can("beli"))  { $allowed["tarik"] = "Beli Sampah (saldo berkurang)"; }
if (!$allowed) { forbidden(); }

$uid   = (int) $_SESSION["id_user"];
$error = "";

$tipeDipilih = $_GET["tipe"] ?? array_key_first($allowed);
if (!isset($allowed[$tipeDipilih])) { $tipeDipilih = array_key_first($allowed); }
$sel = ["id_user" => 0, "id_sampah" => 0, "berat_kg" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $tipe      = $_POST["tipe"] ?? "";
    $id_sampah = (int) ($_POST["id_sampah"] ?? 0);
    $berat     = round((float) ($_POST["berat_kg"] ?? 0), 2);
    $id_user   = is_admin() ? (int) ($_POST["id_user"] ?? 0) : $uid;   // non-admin selalu atas nama sendiri

    $tipeDipilih = isset($allowed[$tipe]) ? $tipe : $tipeDipilih;
    $sel = ["id_user" => $id_user, "id_sampah" => $id_sampah, "berat_kg" => $_POST["berat_kg"] ?? ""];

    if (!isset($allowed[$tipe])) {
        $error = "Tipe transaksi ini tidak diizinkan untuk role Anda.";
    } elseif ($id_user <= 0 || $id_sampah <= 0 || $berat <= 0) {
        $error = "Lengkapi semua data dan pastikan berat lebih dari 0.";
    } else {
        try {
            mysqli_begin_transaction($koneksi);

            $sampah = mysqli_fetch_assoc(db_select($koneksi,
                "SELECT Harga_per_kg, stok_kg FROM sampah WHERE Id_sampah = ? FOR UPDATE", "i", [$id_sampah]));
            $user = mysqli_fetch_assoc(db_select($koneksi,
                "SELECT saldo FROM users WHERE id_user = ? FOR UPDATE", "i", [$id_user]));

            if (!$sampah || !$user) {
                throw new RuntimeException("Data nasabah atau jenis sampah tidak ditemukan.");
            }

            $total = round($berat * (float) $sampah["Harga_per_kg"], 2);

            if ($tipe === "setor") {
                db_exec($koneksi, "UPDATE sampah SET stok_kg = stok_kg + ? WHERE Id_sampah = ?", "si", [dec($berat), $id_sampah]);
                db_exec($koneksi, "UPDATE users SET saldo = saldo + ? WHERE id_user = ?", "si", [dec($total), $id_user]);
            } else {
                if ((float) $sampah["stok_kg"] < $berat) {
                    throw new RuntimeException("Stok tidak cukup. Stok tersedia: " . number_format($sampah["stok_kg"], 2) . " Kg.");
                }
                if ((float) $user["saldo"] < $total) {
                    throw new RuntimeException("Saldo tidak cukup. Saldo: " . rupiah($user["saldo"]) . ", dibutuhkan: " . rupiah($total) . ". Silakan top up terlebih dahulu.");
                }
                db_exec($koneksi, "UPDATE sampah SET stok_kg = stok_kg - ? WHERE Id_sampah = ?", "si", [dec($berat), $id_sampah]);
                db_exec($koneksi, "UPDATE users SET saldo = saldo - ? WHERE id_user = ?", "si", [dec($total), $id_user]);
            }

            db_exec($koneksi,
                "INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, total_rp) VALUES (?, ?, ?, ?, ?)",
                "iisss", [$id_user, $id_sampah, $tipe, dec($berat), dec($total)]);

            mysqli_commit($koneksi);
            flash("success", "Transaksi berhasil disimpan (" . rupiah($total) . ").");
            header("Location: index.php");
            exit();
        } catch (RuntimeException $ex) {
            mysqli_rollback($koneksi);
            $error = $ex->getMessage();
        } catch (Throwable $ex) {
            mysqli_rollback($koneksi);
            $error = "Terjadi kesalahan pada database. Transaksi dibatalkan.";
        }
    }
}

$users   = is_admin() ? mysqli_query($koneksi, "SELECT id_user, nama, role FROM users ORDER BY nama") : null;
$sampahs = mysqli_query($koneksi, "SELECT Id_sampah, Jenis_sampah, Harga_per_kg, stok_kg FROM sampah ORDER BY Jenis_sampah");
$saldoSaya = is_admin() ? null : (mysqli_fetch_assoc(db_select($koneksi, "SELECT saldo FROM users WHERE id_user = ?", "i", [$uid]))["saldo"] ?? 0);

layout_start("Setor / Beli Sampah", "transaksi");
?>
        <div class="form-card">
            <h2>Setor / Beli Sampah</h2>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($saldoSaya !== null): ?>
                <p class="hint">Saldo Anda saat ini: <strong><?= rupiah($saldoSaya) ?></strong></p>
            <?php endif; ?>

            <form method="POST" action="tambah.php">
                <?= csrf_field() ?>
                <?php if (is_admin()): ?>
                <div class="form-group">
                    <label>Nasabah</label>
                    <select name="id_user" class="input-control" required>
                        <option value="">-- Pilih Nasabah --</option>
                        <?php while ($u = mysqli_fetch_assoc($users)): ?>
                            <option value="<?= (int) $u["id_user"] ?>" <?= $sel["id_user"] == $u["id_user"] ? "selected" : "" ?>>
                                <?= e($u["nama"]) ?> (<?= e($u["role"]) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Tipe Transaksi</label>
                    <select name="tipe" class="input-control" required>
                        <?php foreach ($allowed as $val => $label): ?>
                            <option value="<?= e($val) ?>" <?= $val === $tipeDipilih ? "selected" : "" ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Sampah</label>
                    <select name="id_sampah" class="input-control" required>
                        <option value="">-- Pilih Sampah --</option>
                        <?php while ($s = mysqli_fetch_assoc($sampahs)): ?>
                            <option value="<?= (int) $s["Id_sampah"] ?>" <?= $sel["id_sampah"] == $s["Id_sampah"] ? "selected" : "" ?>>
                                <?= e($s["Jenis_sampah"]) ?> — <?= rupiah($s["Harga_per_kg"]) ?>/Kg (stok <?= number_format($s["stok_kg"], 2) ?> Kg)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Berat (Kg)</label>
                    <input type="number" step="0.01" min="0.01" name="berat_kg" class="input-control" placeholder="Contoh: 2.5" value="<?= e($sel["berat_kg"]) ?>" required>
                </div>

                <button type="submit" class="btn-submit">Simpan Transaksi</button>
            </form>

            <a class="btn-kembali" href="index.php">&larr; Kembali ke daftar transaksi</a>
        </div>
<?php layout_end(); ?>
