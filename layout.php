<?php
// layout.php — kerangka halaman (sidebar menu mengikuti role)

function layout_start(string $title, string $active = ""): void
{
    $menu = [
        "dashboard"   => ["📊 Dashboard", "index.php"],
        "sampah"      => ["🗑️ Sampah", "sampah/index.php"],
        "transaksi"   => ["💸 Transaksi", "transaksi/index.php"],
        "top_up"      => ["💳 Top Up", "top_up/index.php"],
        "tarik_saldo" => ["🏧 Tarik Saldo", "tarik_saldo/index.php"],
    ];
    $nama  = $_SESSION["nama"] ?? "";
    $role  = $_SESSION["role"] ?? "user";
    $flash = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - Bank Sampah Rifky</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/style.css">
</head>
<body>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="sidebar">
        <div>
            <div class="brand"><span>♻️</span> Bank Sampah Rifky</div>
            <div class="menu-list">
                <?php foreach ($menu as $key => [$label, $url]): ?>
                    <?php if (can($key)): ?>
                        <a href="<?= BASE_URL . $url ?>" class="menu-item<?= $key === $active ? " active" : "" ?>"><?= $label ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="<?= BASE_URL ?>logout.php" class="menu-item logout">🚪 Logout</a>
    </div>

    <div class="main-wrapper">
        <div class="top-user-bar">
            <div class="user-badge">
                <div class="user-avatar"><?= e(strtoupper(substr($nama, 0, 1))) ?></div>
                <span><?= e($nama) ?> (<strong><?= e(ucfirst($role)) ?></strong>)</span>
            </div>
        </div>
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash[0]) ?>"><?= e($flash[1]) ?></div>
        <?php endif; ?>
<?php
}

function layout_end(): void
{
    echo "    </div>\n</body>\n</html>";
}
