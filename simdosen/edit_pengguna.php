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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: pengguna.php?pesan=id_tidak_valid');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, nama, username, role, aktif
     FROM pengguna WHERE id = :id LIMIT 1'
);
$stmt->execute(['id' => $id]);
$pengguna = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pengguna) {
    header('Location: pengguna.php?pesan=pengguna_tidak_ditemukan');
    exit;
}

$error = '';
$nama = (string) $pengguna['nama'];
$username = (string) $pengguna['username'];
$role = (string) $pengguna['role'];
$aktif = (int) $pengguna['aktif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajibCSRF('csrf_pengguna');

    $nama = trim((string) ($_POST['nama'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $role = (string) ($_POST['role'] ?? '');
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    $password = (string) ($_POST['password'] ?? '');
    $konfirmasi = (string) ($_POST['konfirmasi_password'] ?? '');

    $roleValid = ['administrator', 'operator', 'pimpinan'];

    if ($nama === '' || mb_strlen($nama) > 100) {
        $error = 'Nama wajib diisi dan maksimal 100 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
        $error = 'Username harus 3–50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif (!in_array($role, $roleValid, true)) {
        $error = 'Role yang dipilih tidak valid.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($password !== '' && $password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $cek = $pdo->prepare(
            'SELECT id FROM pengguna
             WHERE username = :username AND id <> :id LIMIT 1'
        );
        $cek->execute([
            'username' => $username,
            'id' => $id
        ]);

        if ($cek->fetch()) {
            $error = 'Username sudah digunakan oleh pengguna lain.';
        }
    }

    // Mencegah administrator menonaktifkan akun sendiri.
    if (
        $error === '' &&
        (int) ($_SESSION['user_id'] ?? 0) === $id &&
        $aktif !== 1
    ) {
        $error = 'Anda tidak dapat menonaktifkan akun sendiri.';
    }

    // Mencegah administrator menghapus akses administrator
    // terakhir yang masih aktif.
    if (
        $error === '' &&
        $pengguna['role'] === 'administrator' &&
        (int) $pengguna['aktif'] === 1 &&
        ($role !== 'administrator' || $aktif !== 1)
    ) {
        $cekAdmin = $pdo->prepare(
            "SELECT COUNT(*) FROM pengguna
             WHERE role = 'administrator'
             AND aktif = 1 AND id <> :id"
        );
        $cekAdmin->execute(['id' => $id]);

        if ((int) $cekAdmin->fetchColumn() === 0) {
            $error = 'Tidak dapat mengubah role atau menonaktifkan administrator aktif terakhir.';
        }
    }

    if ($error === '') {
        try {
            if ($password !== '') {
                $sql = 'UPDATE pengguna
                        SET nama = :nama,
                            username = :username,
                            role = :role,
                            aktif = :aktif,
                            password_hash = :password_hash
                        WHERE id = :id';

                $params = [
                    'nama' => $nama,
                    'username' => $username,
                    'role' => $role,
                    'aktif' => $aktif,
                    'password_hash' => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    ),
                    'id' => $id
                ];
            } else {
                $sql = 'UPDATE pengguna
                        SET nama = :nama,
                            username = :username,
                            role = :role,
                            aktif = :aktif
                        WHERE id = :id';

                $params = [
                    'nama' => $nama,
                    'username' => $username,
                    'role' => $role,
                    'aktif' => $aktif,
                    'id' => $id
                ];
            }

            $update = $pdo->prepare($sql);
            $update->execute($params);

            try {
                catatAudit(
                    $pdo,
                    'Mengubah pengguna',
                    'ID pengguna: ' . $id
                );
            } catch (Throwable $e) {
                error_log('Audit pengguna gagal: ' . $e->getMessage());
            }

            header('Location: pengguna.php?pesan=edit_berhasil');
            exit;
        } catch (PDOException $e) {
            error_log('Edit pengguna gagal: ' . $e->getMessage());
            $error = 'Data pengguna gagal diperbarui. Silakan periksa kembali.';
        }
    }
}

function eEditPengguna(mixed $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Pengguna | SIMDOSEN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 25px 15px;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 680px;
            margin: 25px auto;
            padding: 28px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 5px 22px rgba(15, 23, 42, .08);
        }
        h2 { margin-top: 0; color: #1d4ed8; }
        .info { color: #64748b; font-size: 14px; line-height: 1.6; }
        label {
            display: block;
            margin: 17px 0 7px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 15px;
        }
        .checkbox {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 18px 0;
        }
        .checkbox input { width: auto; }
        .checkbox label { margin: 0; }
        .error {
            padding: 12px;
            border-radius: 7px;
            background: #fee2e2;
            color: #991b1b;
            margin: 15px 0;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }
        .btn {
            display: inline-block;
            padding: 11px 17px;
            border: 0;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .primary { background: #2563eb; color: white; }
        .secondary { background: #e2e8f0; color: #1e293b; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Pengguna</h2>
    <p class="info">
        Perbarui informasi akun pengguna SIMDOSEN.
        Kosongkan password jika tidak ingin menggantinya.
    </p>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= eEditPengguna($error) ?>
        </div>
    <?php endif; ?>

    <form method="post"
          action="edit_pengguna.php?id=<?= (int) $id ?>">
        <?= inputCSRF('csrf_pengguna') ?>

        <label for="nama">Nama lengkap</label>
        <input id="nama" name="nama" type="text"
               maxlength="100" required
               value="<?= eEditPengguna($nama) ?>">

        <label for="username">Username</label>
        <input id="username" name="username" type="text"
               minlength="3" maxlength="50" required
               value="<?= eEditPengguna($username) ?>">

        <label for="role">Role pengguna</label>
        <select id="role" name="role" required>
            <option value="administrator"
                <?= $role === 'administrator' ? 'selected' : '' ?>>
                Administrator
            </option>
            <option value="operator"
                <?= $role === 'operator' ? 'selected' : '' ?>>
                Operator
            </option>
            <option value="pimpinan"
                <?= $role === 'pimpinan' ? 'selected' : '' ?>>
                Pimpinan
            </option>
        </select>

        <div class="checkbox">
            <input id="aktif" name="aktif" type="checkbox"
                   value="1" <?= $aktif === 1 ? 'checked' : '' ?>>
            <label for="aktif">Akun aktif</label>
        </div>

        <label for="password">Password baru (opsional)</label>
        <input id="password" name="password" type="password"
               minlength="8" autocomplete="new-password">

        <label for="konfirmasi_password">Konfirmasi password baru</label>
        <input id="konfirmasi_password"
               name="konfirmasi_password" type="password"
               minlength="8" autocomplete="new-password">

        <div class="actions">
            <button type="submit" class="btn primary">
                Simpan Perubahan
            </button>
            <a href="pengguna.php" class="btn secondary">
                Kembali
            </a>
        </div>
    </form>
</div>
</body>
</html>
