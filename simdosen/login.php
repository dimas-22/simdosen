<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasiCSRF('csrf_login');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $sql = "SELECT id, nama, username, password_hash, role, aktif
                    FROM pengguna
                    WHERE username = :username
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username]);
            $pengguna = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $pengguna &&
                password_verify($password, $pengguna['password_hash']) &&
                (int) $pengguna['aktif'] === 1
            ) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $pengguna['id'];
                $_SESSION['nama'] = $pengguna['nama'];
                $_SESSION['username'] = $pengguna['username'];
                $_SESSION['role'] = $pengguna['role'];

                header('Location: dashboard.php');
                exit;
            }

            $error = 'Username atau password salah, atau akun tidak aktif.';
        } catch (PDOException $e) {
            error_log('Kesalahan login SIMDOSEN: ' . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba kembali.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMDOSEN</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #123c69, #2563eb);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 15px 45px #00000030;
        }

        .logo {
            width: 65px;
            height: 65px;
            border-radius: 14px;
            background: #dbeafe;
            color: #123c69;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            font-weight: bold;
            margin: 0 auto 18px;
        }

        h1 {
            text-align: center;
            color: #123c69;
            margin: 0 0 8px;
            font-size: 25px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        label {
            display: block;
            margin: 16px 0 8px;
            color: #334155;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
        }

        .copyright {
            text-align: center;
            color: #64748b;
            font-size: 12px;
            margin-top: 25px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">SD</div>

    <h1>SIMDOSEN</h1>

    <div class="subtitle">
        Sistem Informasi Manajemen Dosen<br>
        Universitas Muhammadiyah Bengkulu
    </div>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <?= inputCSRF('csrf_login') ?>

        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Masukkan username"
            autocomplete="username"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password"
            autocomplete="current-password"
            required
        >

        <button type="submit">Masuk ke Aplikasi</button>
    </form>

    <a href="index.php" class="back-link">
        &larr; Kembali ke halaman utama
    </a>

    <div class="copyright">
        &copy; <?= date('Y') ?> SIMDOSEN<br>
        Universitas Muhammadiyah Bengkulu
    </div>
</div>

</body>
</html>
