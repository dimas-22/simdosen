<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/rbac.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/audit.php';

wajibLogin();
wajibRole(['administrator']);

function hTambahPengguna($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$errors = [];
$nama = '';
$username = '';
$role = 'operator';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajibCSRF('csrf_pengguna');

    $nama = trim((string)($_POST['nama'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $konfirmasi = (string)($_POST['konfirmasi_password'] ?? '');
    $role = (string)($_POST['role'] ?? 'operator');

    $roleValid = ['administrator', 'operator', 'pimpinan'];

    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if ($username === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
        $errors[] = 'Username wajib 3–50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!in_array($role, $roleValid, true)) {
        $errors[] = 'Role yang dipilih tidak valid.';
    }

    if (!$errors) {
        try {
            $cek = $pdo->prepare(
                'SELECT id FROM pengguna WHERE username = :username LIMIT 1'
            );
            $cek->execute(['username' => $username]);

            if ($cek->fetch()) {
                $errors[] = 'Username sudah digunakan. Silakan pilih username lain.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO pengguna (nama, username, password_hash, role, aktif)
                     VALUES (:nama, :username, :password_hash, :role, 1)'
                );

                $stmt->execute([
                    'nama' => $nama,
                    'username' => $username,
                    'password_hash' => $hash,
                    'role' => $role
                ]);

                $idBaru = (int)$pdo->lastInsertId();

                try {
                    catatAudit(
                        $pdo,
                        'Menambahkan pengguna',
                        'ID pengguna: ' . $idBaru . ', username: ' . $username
                    );
                } catch (Throwable $e) {
                    error_log('Audit tambah pengguna: ' . $e->getMessage());
                }

                header('Location: pengguna.php?pesan=tambah_berhasil');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Tambah pengguna gagal: ' . $e->getMessage());
            $errors[] = 'Pengguna gagal disimpan. Periksa struktur tabel database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Pengguna - SIMDOSEN</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #123c69;
            padding: 18px 5%;
            color: white;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        main {
            max-width: 750px;
            width: 94%;
            margin: 30px auto;
        }

        h1 { color: #123c69; }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0f172a0b;
        }

        .field { margin-bottom: 18px; }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }

        .hint {
            color: #64748b;
            font-size: 12px;
            margin-top: 6px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .btn {
            display: inline-block;
            padding: 11px 16px;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        .primary { background: #2563eb; color: white; }
        .secondary { background: #e2e8f0; color: #334155; }

        .errors {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .errors li { margin: 5px 0; }
    </style>
</head>
<body>

<header>
    <a href="pengguna.php">SIMDOSEN — Kembali ke Manajemen Pengguna</a>
</header>

<main>
    <h1>Tambah Pengguna</h1>
    <p>Buat akun baru untuk administrator, operator, atau pimpinan.</p>

    <section class="card">

        <?php if ($errors): ?>
            <div class="errors">
                <strong>Periksa kembali data berikut:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= hTambahPengguna($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="tambah_pengguna.php">
            <?= inputCSRF('csrf_pengguna') ?>

            <div class="field">
                <label for="nama">Nama lengkap</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    maxlength="100"
                    value="<?= hTambahPengguna($nama) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_.-]+"
                    value="<?= hTambahPengguna($username) ?>"
                    autocomplete="username"
                    required
                >
                <div class="hint">
                    Gunakan minimal 3 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                <div class="hint">Minimal 8 karakter.</div>
            </div>

            <div class="field">
                <label for="konfirmasi_password">Konfirmasi password</label>
                <input
                    type="password"
                    id="konfirmasi_password"
                    name="konfirmasi_password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="field">
                <label for="role">Role pengguna</label>
                <select id="role" name="role" required>
                    <option value="operator" <?= $role === 'operator' ? 'selected' : '' ?>>
                        Operator
                    </option>
                    <option value="pimpinan" <?= $role === 'pimpinan' ? 'selected' : '' ?>>
                        Pimpinan
                    </option>
                    <option value="administrator" <?= $role === 'administrator' ? 'selected' : '' ?>>
                        Administrator
                    </option>
                </select>
                <div class="hint">
                    Berikan role administrator hanya kepada pengguna yang memang berwenang.
                </div>
            </div>

            <div class="buttons">
                <button type="submit" class="btn primary">Simpan Pengguna</button>
                <a href="pengguna.php" class="btn secondary">Batal</a>
            </div>
        </form>
    </section>
</main>

</body>
</html>
