<?php
require "auth.php";
require_login();

$uid   = (int) $_SESSION["id_user"];
$stats = []; // [judul, nilai, satuan, warna]

if (is_admin()) {
    $totalUser   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users"))["total"];
    $rSampah     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total, SUM(stok_kg) AS total_stok FROM sampah"));
    $totalTrx    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi"))["total"];
    $pendingTarik = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tarik_saldo WHERE status='pending'"))["total"];
    $pendingTopup = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM top_up WHERE status='pending'"))["total"];

    $stats = [
        ["Total Pengguna",    number_format($totalUser), "", ""],
        ["Kategori Sampah",   number_format($rSampah["total"]), "", ""],
        ["Total Stok Sampah", number_format($rSampah["total_stok"] ?? 0, 2), "Kg", ""],
        ["Total Transaksi",   number_format($totalTrx), "", ""],
        ["Penarikan Pending", number_format($pendingTarik), "", "#fbbf24"],
        ["Top Up Pending",    number_format($pendingTopup), "", "#fbbf24"],
    ];
} else {
    $saldo   = mysqli_fetch_assoc(db_select($koneksi, "SELECT saldo FROM users WHERE id_user = ?", "i", [$uid]))["saldo"] ?? 0;
    $myTrx   = mysqli_fetch_assoc(db_select($koneksi, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = ?", "i", [$uid]))["total"];
    $myTopup = mysqli_fetch_assoc(db_select($koneksi, "SELECT COUNT(*) AS total FROM top_up WHERE id_user = ? AND status = 'pending'", "i", [$uid]))["total"];

    $stats = [
        ["Saldo Saya",           rupiah($saldo), "", ""],
        ["Transaksi Saya",       number_format($myTrx), "", ""],
        ["Top Up Menunggu ACC",  number_format($myTopup), "", "#fbbf24"],
    ];
}

layout_start("Dashboard", "dashboard");
?>
        <div class="welcome-card">
            <h1>Selamat Datang, <?= e($_SESSION["nama"]) ?>! 👋</h1>
            <p>Kelola data pengelolaan sampah, transaksi nasabah, dan pencatatan saldo dalam satu sistem terpadu.</p>
        </div>

        <div class="quick-actions">
            <?php if (can("setor")): ?>
                <a href="transaksi/tambah.php?tipe=setor" class="action-btn">➕ Setor Sampah</a>
            <?php endif; ?>
            <?php if (can("beli")): ?>
                <a href="transaksi/tambah.php?tipe=tarik" class="action-btn">🛒 Beli Sampah</a>
            <?php endif; ?>
            <?php if (!is_admin() && can("top_up")): ?>
                <a href="top_up/tambah.php" class="action-btn">💳 Top Up Saldo</a>
            <?php endif; ?>
            <?php if (can("tarik_saldo")): ?>
                <a href="tarik_saldo/tambah.php" class="action-btn">💰 Ajukan Penarikan</a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
                <a href="sampah/tambah.php" class="action-btn">📦 Tambah Jenis Sampah</a>
            <?php endif; ?>
        </div>

        <div class="stats-grid">
            <?php foreach ($stats as [$judul, $nilai, $satuan, $warna]): ?>
                <div class="stat-card">
                    <div class="title"><?= e($judul) ?></div>
                    <div class="value"<?= $warna ? ' style="color: ' . $warna . ';"' : "" ?>>
                        <?= e($nilai) ?><?= $satuan ? " <span>" . e($satuan) . "</span>" : "" ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
<?php layout_end(); ?>
