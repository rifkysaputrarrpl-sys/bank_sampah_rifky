<?php
require "../auth.php";
require_permission("transaksi");

$uid   = (int) $_SESSION["id_user"];
$admin = is_admin();

$sql = "SELECT t.*, u.nama, s.Jenis_sampah
        FROM transaksi t
        JOIN users u ON t.id_user = u.id_user
        JOIN sampah s ON t.id_sampah = s.Id_sampah";
$types  = "";
$params = [];
if (!$admin) {               // selain admin hanya melihat transaksi miliknya sendiri
    $sql   .= " WHERE t.id_user = ?";
    $types  = "i";
    $params = [$uid];
}
$sql .= " ORDER BY t.id_transaksi DESC";
$data = db_select($koneksi, $sql, $types, $params);

layout_start("Data Transaksi", "transaksi");
?>
        <div class="header-action">
            <h2><?= $admin ? "Data Transaksi" : "Riwayat Transaksi Saya" ?></h2>
            <?php if (can("setor") || can("beli")): ?>
                <a class="btn-tambah" href="tambah.php">➕ <?= $admin ? "Tambah Transaksi" : "Setor / Beli Sampah" ?></a>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($admin): ?><th>Nasabah</th><?php endif; ?>
                        <th>Jenis Sampah</th><th>Tipe</th><th>Berat</th><th>Total</th><th>Tanggal</th>
                        <?php if ($admin): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $ada = false; while ($row = mysqli_fetch_assoc($data)): $ada = true; ?>
                    <tr>
                        <td>#<?= e($row["id_transaksi"]) ?></td>
                        <?php if ($admin): ?><td><strong><?= e($row["nama"]) ?></strong></td><?php endif; ?>
                        <td><?= e($row["Jenis_sampah"]) ?></td>
                        <td>
                            <span class="badge-tipe tipe-<?= e($row["tipe"]) ?>">
                                <?= $row["tipe"] === "setor" ? "Setor" : "Beli" ?>
                            </span>
                        </td>
                        <td><?= e($row["berat_kg"]) ?> Kg</td>
                        <td style="color: #22d3ee; font-weight: 700;"><?= rupiah($row["total_rp"]) ?></td>
                        <td><?= e($row["tanggal"]) ?></td>
                        <?php if ($admin): ?>
                        <td>
                            <a class="btn-hapus" href="hapus.php?id=<?= (int) $row["id_transaksi"] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$ada): ?>
                        <tr><td colspan="8" class="empty-row">Belum ada transaksi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php layout_end(); ?>
