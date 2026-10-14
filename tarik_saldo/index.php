<?php
require "../auth.php";
require_permission("tarik_saldo");

$uid   = (int) $_SESSION["id_user"];
$admin = is_admin();

$sql = "SELECT ts.*, u.nama
        FROM tarik_saldo ts
        JOIN users u ON ts.id_user = u.id_user";
$types  = "";
$params = [];
if (!$admin) {
    $sql   .= " WHERE ts.id_user = ?";
    $types  = "i";
    $params = [$uid];
}
$sql .= " ORDER BY ts.id_tarik DESC";
$data = db_select($koneksi, $sql, $types, $params);

layout_start("Tarik Saldo", "tarik_saldo");
?>
        <div class="header-action">
            <h2><?= $admin ? "Penarikan Saldo" : "Penarikan Saldo Saya" ?></h2>
            <a class="btn-tambah" href="tambah.php">➕ Ajukan Penarikan</a>
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
                        <td>#<?= e($row["id_tarik"]) ?></td>
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
                                    <form class="inline-form" method="POST" action="proses.php" onsubmit="return confirm('Setujui penarikan ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $row["id_tarik"] ?>">
                                        <input type="hidden" name="aksi" value="setuju">
                                        <button type="submit" class="btn-setuju">Setujui</button>
                                    </form>
                                    <form class="inline-form" method="POST" action="proses.php" onsubmit="return confirm('Tolak penarikan ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $row["id_tarik"] ?>">
                                        <input type="hidden" name="aksi" value="tolak">
                                        <button type="submit" class="btn-tolak">Tolak</button>
                                    </form>
                                <?php endif; ?>
                                <a class="btn-hapus" href="hapus.php?id=<?= (int) $row["id_tarik"] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$ada): ?>
                        <tr><td colspan="6" class="empty-row">Belum ada pengajuan penarikan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php layout_end(); ?>
