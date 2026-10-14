<?php
require "../auth.php";
require_permission("top_up");

$uid   = (int) $_SESSION["id_user"];
$admin = is_admin();

$sql = "SELECT tu.*, u.nama
        FROM top_up tu
        JOIN users u ON tu.id_user = u.id_user";
$types  = "";
$params = [];
if (!$admin) {               // selain admin hanya melihat top up miliknya sendiri
    $sql   .= " WHERE tu.id_user = ?";
    $types  = "i";
    $params = [$uid];
}
$sql .= " ORDER BY tu.id_topup DESC";
$data = db_select($koneksi, $sql, $types, $params);

layout_start("Top Up Saldo", "top_up");
?>
        <div class="header-action">
            <h2><?= $admin ? "Persetujuan Top Up" : "Top Up Saldo Saya" ?></h2>
            <?php if (!$admin): ?>
                <a class="btn-tambah" href="tambah.php">➕ Ajukan Top Up</a>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($admin): ?><th>Nasabah</th><?php endif; ?>
                        <th>Nominal</th><th>Tanggal</th><th>Status</th>
                        <?php if ($admin): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $ada = false; while ($row = mysqli_fetch_assoc($data)): $ada = true; ?>
                    <tr>
                        <td>#<?= e($row["id_topup"]) ?></td>
                        <?php if ($admin): ?><td><strong><?= e($row["nama"]) ?></strong></td><?php endif; ?>
                        <td style="color: #22d3ee; font-weight: 700;"><?= rupiah($row["nominal"]) ?></td>
                        <td><?= e($row["tanggal"]) ?></td>
                        <td>
                            <span class="badge-status status-<?= e($row["status"]) ?>"><?= e(ucfirst($row["status"])) ?></span>
                        </td>
                        <?php if ($admin): ?>
                        <td>
                            <div class="action-btns">
                                <?php if ($row["status"] === "pending"): ?>
                                    <form class="inline-form" method="POST" action="proses.php" onsubmit="return confirm('Setujui top up ini? Saldo nasabah akan bertambah.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $row["id_topup"] ?>">
                                        <input type="hidden" name="aksi" value="setuju">
                                        <button type="submit" class="btn-setuju">Setujui</button>
                                    </form>
                                    <form class="inline-form" method="POST" action="proses.php" onsubmit="return confirm('Tolak top up ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $row["id_topup"] ?>">
                                        <input type="hidden" name="aksi" value="tolak">
                                        <button type="submit" class="btn-tolak">Tolak</button>
                                    </form>
                                <?php endif; ?>
                                <form class="inline-form" method="POST" action="proses.php" onsubmit="return confirm('Hapus data ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $row["id_topup"] ?>">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <button type="submit" class="btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$ada): ?>
                        <tr><td colspan="6" class="empty-row">Belum ada pengajuan top up.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php layout_end(); ?>
