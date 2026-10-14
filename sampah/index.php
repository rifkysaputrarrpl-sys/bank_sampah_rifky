<?php
require "../auth.php";
require_admin();

$data = mysqli_query($koneksi, "SELECT * FROM sampah ORDER BY Id_sampah DESC");
layout_start("Data Sampah", "sampah");
?>
        <div class="header-action">
            <h2>Kategori & Stok Sampah</h2>
            <a class="btn-tambah" href="tambah.php">➕ Tambah Sampah</a>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr><th>ID</th><th>Jenis Sampah</th><th>Harga / Kg</th><th>Stok (Kg)</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td>#<?= e($row["Id_sampah"]) ?></td>
                        <td><strong><?= e($row["Jenis_sampah"]) ?></strong></td>
                        <td style="color: #22d3ee; font-weight: 700;"><?= rupiah($row["Harga_per_kg"]) ?></td>
                        <td><?= number_format($row["stok_kg"], 2) ?> Kg</td>
                        <td>
                            <a class="btn-hapus" href="hapus.php?id=<?= (int) $row["Id_sampah"] ?>" onclick="return confirm('Hapus sampah ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
<?php layout_end(); ?>
