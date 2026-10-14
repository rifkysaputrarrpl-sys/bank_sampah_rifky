<?php
require "../auth.php";
require_admin();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $jenis = trim($_POST["jenis_sampah"] ?? "");
    $harga = (float) ($_POST["harga_per_kg"] ?? 0);
    $stok  = (float) ($_POST["stok_kg"] ?? 0);

    if ($jenis === "" || $harga < 0 || $stok < 0) {
        $error = "Isi jenis sampah, dan pastikan harga serta stok tidak negatif.";
    } else {
        db_exec($koneksi, "INSERT INTO sampah (Jenis_sampah, Harga_per_kg, stok_kg) VALUES (?, ?, ?)",
            "sss", [$jenis, dec($harga), dec($stok)]);
        flash("success", "Jenis sampah berhasil ditambahkan.");
        header("Location: index.php");
        exit();
    }
}

layout_start("Tambah Sampah", "sampah");
?>
        <div class="form-card">
            <h2>Tambah Data Sampah</h2>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <form method="POST" action="tambah.php">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Jenis Sampah</label>
                    <input type="text" name="jenis_sampah" class="input-control" placeholder="Contoh: Plastik Pet, Kardus, Botol Kaca" required>
                </div>
                <div class="form-group">
                    <label>Harga per Kg (Rp)</label>
                    <input type="number" step="0.01" min="0" name="harga_per_kg" class="input-control" placeholder="Contoh: 3000" required>
                </div>
                <div class="form-group">
                    <label>Stok Awal (Kg)</label>
                    <input type="number" step="0.01" min="0" name="stok_kg" class="input-control" placeholder="Stok Awal" value="0" required>
                </div>
                <button type="submit" class="btn-submit">Simpan Sampah</button>
            </form>

            <a class="btn-kembali" href="index.php">&larr; Kembali ke daftar sampah</a>
        </div>
<?php layout_end(); ?>
