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

$error = '';
$pesan = '';
$tokenBaru = $_SESSION['token_baru'] ?? null;
unset($_SESSION['token_baru']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajibCSRF('csrf_api_token');

    $aksi = (string) ($_POST['aksi'] ?? '');

    if ($aksi === 'buat') {
        $hari = filter_var(
            $_POST['masa_berlaku'] ?? 30,
            FILTER_VALIDATE_INT
        );

        if ($hari === false || $hari < 1 || $hari > 365) {
            $error = 'Masa berlaku token harus antara 1 sampai 365 hari.';
        } else {
            try {
                $tokenAsli = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $tokenAsli);

                $kedaluwarsa = new DateTimeImmutable('now');
                $kedaluwarsa = $kedaluwarsa
                    ->modify('+' . $hari . ' days')
                    ->format('Y-m-d H:i:s');

                $stmt = $pdo->prepare(
                    'INSERT INTO api_tokens
                        (pengguna_id, token_hash, expires_at)
                     VALUES
                        (:pengguna_id, :token_hash, :expires_at)'
                );

                $stmt->execute([
                    'pengguna_id' => (int) $_SESSION['user_id'],
                    'token_hash' => $tokenHash,
                    'expires_at' => $kedaluwarsa
                ]);

                try {
                    catatAudit(
                        $pdo,
                        'Membuat token API',
                        'ID token: ' . $pdo->lastInsertId()
                    );
                } catch (Throwable $e) {
                    error_log('Audit token API gagal: ' . $e->getMessage());
                }

                $_SESSION['token_baru'] = $tokenAsli;

                header('Location: api_tokens.php?status=berhasil');
                exit;
            } catch (Throwable $e) {
                error_log('Pembuatan token API gagal: ' . $e->getMessage());
                $error = 'Token gagal dibuat. Periksa kembali konfigurasi database.';
            }
        }
    } elseif ($aksi === 'nonaktifkan') {
        $idToken = filter_var(
            $_POST['id_token'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$idToken || $idToken < 1) {
            $error = 'ID token tidak valid.';
        } else {
            try {
                // Tabel tidak memiliki kolom "aktif".
                // Token dinonaktifkan dengan mengakhiri masa berlakunya.
                $stmt = $pdo->prepare(
                    'UPDATE api_tokens
                     SET expires_at = NOW()
                     WHERE id = :id AND expires_at > NOW()'
                );
                $stmt->execute(['id' => $idToken]);

                if ($stmt->rowCount() > 0) {
                    try {
                        catatAudit(
                            $pdo,
                            'Menonaktifkan token API',
                            'ID token: ' . $idToken
                        );
                    } catch (Throwable $e) {
                        error_log('Audit token API gagal: ' . $e->getMessage());
                    }
                }

                header('Location: api_tokens.php?status=dinonaktifkan');
                exit;
            } catch (Throwable $e) {
                error_log('Penonaktifan token gagal: ' . $e->getMessage());
                $error = 'Token gagal dinonaktifkan.';
            }
        }
    } else {
        $error = 'Aksi tidak dikenali.';
    }
}

if (($_GET['status'] ?? '') === 'berhasil') {
    $pesan = 'Token berhasil dibuat. Salin dan simpan token asli sekarang.';
} elseif (($_GET['status'] ?? '') === 'dinonaktifkan') {
    $pesan = 'Permintaan penonaktifan token telah diproses.';
}

try {
    $stmt = $pdo->query(
        'SELECT t.id, t.pengguna_id, t.expires_at, t.created_at,
                p.nama AS nama_pembuat
         FROM api_tokens t
         LEFT JOIN pengguna p ON p.id = t.pengguna_id
         ORDER BY t.id DESC'
    );
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Daftar token API gagal: ' . $e->getMessage());
    $tokens = [];
    $error = 'Daftar token tidak dapat dimuat. Periksa Error Logs.';
}

function eApiToken(mixed $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen API Token | SIMDOSEN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 16px;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, sans-serif;
        }
        .container { max-width: 1100px; margin: auto; }
        .panel {
            background: #fff;
            padding: 24px;
            margin-bottom: 22px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(15,23,42,.06);
        }
        h1 { color: #1d4ed8; margin-top: 0; }
        .muted { color: #64748b; line-height: 1.6; }
        label { display: block; margin: 15px 0 8px; font-weight: bold; }
        input, select {
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
        }
        button, .btn {
            display: inline-block;
            padding: 10px 14px;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .primary { background: #2563eb; color: white; }
        .danger { background: #dc2626; color: white; }
        .secondary { background: #e2e8f0; color: #1e293b; }
        .success-box, .error-box {
            padding: 13px;
            border-radius: 7px;
            margin: 14px 0;
        }
        .success-box { background: #dcfce7; color: #166534; }
        .error-box { background: #fee2e2; color: #991b1b; }
        .token {
            display: block;
            padding: 12px;
            margin: 10px 0;
            background: #fff;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            overflow-wrap: anywhere;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        th { background: #eff6ff; }
        .aktif { color: #15803d; font-weight: bold; }
        .nonaktif { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">

    <div class="panel">
        <h1>Manajemen API Token</h1>
        <p class="muted">
            Kelola token untuk mengakses Web Service SIMDOSEN.
            Halaman ini hanya dapat digunakan administrator.
        </p>
        <a class="btn secondary" href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <div class="panel">
        <h2>Buat Token Baru</h2>

        <?php if ($pesan !== ''): ?>
            <div class="success-box"><?= eApiToken($pesan) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="error-box"><?= eApiToken($error) ?></div>
        <?php endif; ?>

        <?php if (is_string($tokenBaru) && $tokenBaru !== ''): ?>
            <div class="success-box">
                <strong>Token asli — simpan sekarang:</strong>
                <code class="token" id="tokenBaru"><?= eApiToken($tokenBaru) ?></code>
                <button type="button" class="primary" onclick="salinToken()">
                    Salin Token
                </button>
                <p>Token asli tidak akan ditampilkan kembali setelah halaman dimuat ulang.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="api_tokens.php">
            <?= inputCSRF('csrf_api_token') ?>

            <input type="hidden" name="aksi" value="buat">

            <label for="masa_berlaku">Masa berlaku token</label>
            <select id="masa_berlaku" name="masa_berlaku">
                <option value="7">7 hari</option>
                <option value="30" selected>30 hari</option>
                <option value="90">90 hari</option>
                <option value="180">180 hari</option>
                <option value="365">365 hari</option>
            </select>

            <p>
                <button type="submit" class="primary">Buat Token</button>
            </p>
        </form>
    </div>

    <div class="panel">
        <h2>Daftar Token API</h2>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Pembuat</th>
                    <th>Dibuat</th>
                    <th>Kedaluwarsa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$tokens): ?>
                    <tr><td colspan="6">Belum ada token API.</td></tr>
                <?php else: ?>
                    <?php foreach ($tokens as $token): ?>
                        <?php $masihAktif = strtotime((string) $token['expires_at']) > time(); ?>
                        <tr>
                            <td><?= (int) $token['id'] ?></td>
                            <td><?= eApiToken($token['nama_pembuat'] ?? ('Pengguna #' . $token['pengguna_id'])) ?></td>
                            <td><?= eApiToken($token['created_at'] ?? '-') ?></td>
                            <td><?= eApiToken($token['expires_at']) ?></td>
                            <td>
                                <?php if ($masihAktif): ?>
                                    <span class="aktif">Aktif</span>
                                <?php else: ?>
                                    <span class="nonaktif">Kedaluwarsa / Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($masihAktif): ?>
                                    <form method="post" action="api_tokens.php"
                                          onsubmit="return confirm('Nonaktifkan token ini?')">
                                        <?= inputCSRF('csrf_api_token') ?>
                                        <input type="hidden" name="aksi" value="nonaktifkan">
                                        <input type="hidden" name="id_token"
                                               value="<?= (int) $token['id'] ?>">
                                        <button type="submit" class="danger">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function salinToken() {
    const elemen = document.getElementById('tokenBaru');
    if (!elemen) return;

    const teks = elemen.textContent.trim();

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(teks)
            .then(() => alert('Token berhasil disalin.'))
            .catch(() => alert('Silakan salin token secara manual.'));
    } else {
        alert('Silakan salin token secara manual.');
    }
}
</script>
</body>
</html>
