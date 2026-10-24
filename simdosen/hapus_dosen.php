
<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/rbac.php';
require_once __DIR__ . '/includes/csrf.php';

wajibLogin();
wajibRole(['administrator', 'operator']);

function hHapusDosen($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: dosen.php?pesan=id_tidak_valid');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, nama, nomor_registrasi, nip
     FROM dosen WHERE id = :id LIMIT 1'
);
$stmt->execute(['id' => $id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosen) {
    header('Location: dosen.php?pesan=data_tidak_ditemukan');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        wajibCSRF('csrf_hapus_dosen');

        // Pastikan ID yang dikirim tetap sama dengan data yang dikonfirmasi.
        $idPost = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$idPost || $idPost !== $id) {
            throw new RuntimeException('ID dosen tidak valid.');
        }

        $stmt = $pdo->prepare('DELETE FROM dosen WHERE id = :id');
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() < 1) {
            throw new RuntimeException('Data dosen tidak berhasil dihapus.');
        }

        if (is_file(__DIR__ . '/includes/audit.php')) {
            require_once __DIR__ . '/includes/audit.php';

            if (function_exists('catatAudit')) {
                catatAudit(
                    $pdo,
                    'Menghapus data dosen',
                    'ID: ' . $id . '; Nama: ' . $dosen['nama']
                );
            }
        }

        header('Location: dosen.php?pesan=hapus_berhasil');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hapus Data Dosen - SIMDOSEN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        .card {
            max-width: 560px;
            margin: 50px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px #00000010;
        }
        h2 { color: #b91c1c; margin-top: 0; }
        .warning {
            padding: 15px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            line-height: 1.7;
        }
        .error {
            padding: 12px;
            margin-bottom: 15px;
            color: #991b1b;
            background: #fee2e2;
            border-radius: 7px;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 22px;
        }
        button, .btn {
            padding: 11px 16px;
            border: 0;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .delete { color: white; background: #dc2626; }
        .cancel { color: #1e293b; background: #e2e8f0; }
    </style>
</head>
<body>
<div class="card">
    <h2>Konfirmasi Hapus Dosen</h2>

    <?php if ($error !== ''): ?>
        <div class="error"><?= hHapusDosen($error) ?></div>
    <?php endif; ?>

    <div class="warning">
        Apakah Anda yakin ingin menghapus data dosen berikut?
        <br><br>
        <strong>Nama:</strong>
        <?= hHapusDosen($dosen['nama']) ?><br>

        <strong>Nomor Registrasi:</strong>
        <?= hHapusDosen($dosen['nomor_registrasi']) ?><br>

        <strong>NIP:</strong>
        <?= hHapusDosen($dosen['nip']) ?><br><br>

        Tindakan ini akan menghapus data secara permanen.
    </div>

    <form method="post">
        <?= inputCSRF('csrf_hapus_dosen') ?>
        <input type="hidden" name="id" value="<?= (int)$id ?>">

        <div class="actions">
            <button type="submit" class="delete">
                Ya, Hapus Data
            </button>

            <a href="dosen.php" class="btn cancel">
                Batal
            </a>
        </div>
    </form>
</div>
</body>
</html>
