<?php
session_start();
require "koneksi.php";

$error = "";

if (isset($_SESSION["id_user"])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST["username"]);
    $password = $_POST["password"];

    $query  = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    $user   = mysqli_fetch_assoc($query);

    if ($user) {
        // Cek apakah password cocok menggunakan hash atau plain-text bawaan DB
        $is_valid = password_verify($password, $user["password"]) || ($password === $user["password"]);

        if ($is_valid) {
            $_SESSION["id_user"] = $user["id_user"];
            $_SESSION["nama"]    = $user["nama"];
            $_SESSION["role"]    = $user["role"] ?? 'nasabah';
            header("Location: index.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bank Sampah Rifky</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background-color: #0a0915;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .bg-glow-1 {
            position: absolute; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.35) 0%, transparent 70%);
            top: -150px; left: -150px; filter: blur(90px); border-radius: 50%; pointer-events: none;
        }
        .bg-glow-2 {
            position: absolute; width: 550px; height: 550px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.25) 0%, transparent 70%);
            bottom: -180px; right: -150px; filter: blur(100px); border-radius: 50%; pointer-events: none;
        }
        .login-card {
            width: 100%; max-width: 420px; padding: 40px;
            background: rgba(18, 16, 38, 0.7);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(139, 92, 246, 0.25);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 30px rgba(139, 92, 246, 0.15);
            z-index: 10; margin: 20px;
        }
        .brand-logo { text-align: center; margin-bottom: 28px; }
        .brand-logo h1 { font-size: 26px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .brand-logo h1 span {
            background: linear-gradient(135deg, #a855f7, #06b6d4);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .brand-logo p { font-size: 13px; color: #94a3b8; margin-top: 4px; }
        .alert-error {
            background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171; padding: 12px 16px; border-radius: 12px; font-size: 13px;
            margin-bottom: 20px; text-align: center;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 8px; }
        .input-control {
            width: 100%; background: rgba(10, 9, 21, 0.7);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 12px; padding: 12px 16px; color: #ffffff;
            font-family: inherit; font-size: 14px; outline: none; transition: all 0.25s ease;
        }
        .input-control:focus {
            border-color: #a855f7; box-shadow: 0 0 15px rgba(168, 85, 247, 0.3); background: rgba(10, 9, 21, 0.9);
        }
        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border: none; border-radius: 12px; color: #ffffff;
            font-family: inherit; font-size: 15px; font-weight: 700; cursor: pointer;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4); transition: all 0.25s ease; margin-top: 10px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 25px rgba(139, 92, 246, 0.6); filter: brightness(1.1); }
    </style>
</head>
<body>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="login-card">
        <div class="brand-logo">
            <h1>♻️ Bank Sampah <span>Rifky</span></h1>
            <p>Silakan masuk ke akun Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="input-control" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="input-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-submit">Masuk Sistem</button>
        </form>
    </div>
</body>
</html>