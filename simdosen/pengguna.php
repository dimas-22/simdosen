
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/rbac.php';

wajibLogin();
wajibRole(['administrator']);

function hPengguna($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$kataKunci = trim((string)($_GET['cari'] ?? ''));
$daftarPengguna = [];
$error = '';

try {
    if ($kataKunci !== '') {
        $stmt = $pdo->prepare(
            "SELECT id, nama, username, role, aktif, created_at
             FROM pengguna
             WHERE nama LIKE :nama OR username LIKE :username
             ORDER BY id DESC"
        );

        $pencarian = '%' . $kataKunci . '%';

        $stmt->execute([
            'nama' => $pencarian,
            'username' => $pencarian
        ]);
    } else {
        $stmt = $pdo->query(
            "SELECT id, nama, username, role, aktif, created_at
             FROM pengguna
             ORDER BY id DESC"
        );
    }

    $daftarPengguna = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Kesalahan pengguna.php: ' . $e->getMessage());
    $error = 'Data pengguna tidak dapat ditampilkan. Periksa struktur database.';
}

$namaLogin = $_SESSION['nama'] ?? 'Administrator';
$pesan = (string)($_GET['pesan'] ?? '');

$pesanTampil = [
    'tambah_berhasil' => 'Pengguna berhasil ditambahkan.',
    'edit_berhasil' => 'Data pengguna berhasil diperbarui.',
    'status_berhasil' => 'Status pengguna berhasil diperbarui.',
    'gagal' => 'Operasi tidak berhasil dilakukan.'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Pengguna - SIMDOSEN</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .navbar {
            background: #123c69;
            padding: 16px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .brand {
            color: white;
            text-decoration: none;
            font-size: 22px;
            font-weight: bold;
        }

        .brand small {
            display: block;
            margin-top: 5px;
            font-size: 11px;
            font-weight: normal;
            color: #dbeafe;
        }

        .nav-menu {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 9px 11px;
            border-radius: 6px;
            font-size: 14px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: #2563eb;
        }

        .container {
            width: 94%;
            max-width: 1250px;
            margin: 30px auto;
            min-height: 65vh;
        }

        h1 { color: #123c69; margin-bottom: 8px; }

        .muted {
            color: #64748b;
            line-height: 1.6;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0f172a0b;
            margin-top: 22px;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form {
            display: flex;
            gap: 8px;
            flex: 1;
            flex-wrap: wrap;
        }

        input[type="text"] {
            flex: 1;
            min-width: 190px;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .primary { background: #2563eb; color: white; }
        .secondary { background: #e2e8f0; color: #334155; }

        .table-wrap {
            overflow-x: auto;
            margin-top: 18px;
        }

        table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }

        th {
            background: #eff6ff;
            color: #123c69;
            white-space: nowrap;
        }

        .role {
            display: inline-block;
            padding: 5px 9px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 20px;
            font-size: 12px;
        }

        .aktif {
            color: #166534;
            background: #dcfce7;
        }

        .nonaktif {
            color: #991b1b;
            background: #fee2e2;
        }

        .notice {
            padding: 13px 15px;
            margin-top: 18px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #64748b;
            background: white;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 650px) {
            .navbar { align-items: flex-start; flex-direction: column; }
            .container { width: 96%; margin-top: 20px; }
            .card { padding: 14px; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a class="brand" href="dashboard.php">
        SIMDOSEN
        <small>Sistem Informasi Manajemen Dosen</small>
    </a>

    <nav class="nav-menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="dosen.php">Data Dosen</a>
        <a href="laporan.php">Laporan</a>
        <a href="pengguna.php" class="active">Pengguna</a>
        <a href="audit_log.php">Audit Log</a>
        <a href="logout.php">Keluar</a>
    </nav>
</header>

<main class="container">
    <h1>Manajemen Pengguna</h1>
    <p class="muted">
        Selamat datang, <?= hPengguna($namaLogin) ?>.
        Kelola akun dan peran pengguna aplikasi SIMDOSEN di halaman ini.
    </p>

    <?php if (isset($pesanTampil[$pesan])): ?>
        <div class="notice <?= $pesan !== 'gagal' ? 'success' : '' ?>">
            <?= hPengguna($pesanTampil[$pesan]) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="notice"><?= hPengguna($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <div class="toolbar">
            <form method="get" action="pengguna.php" class="search-form">
                <input
                    type="text"
                    name="cari"
                    placeholder="Cari nama atau username..."
                    value="<?= hPengguna($kataKunci) ?>"
                >

                <button type="submit" class="btn primary">Cari</button>
                <a href="pengguna.php" class="btn secondary">Reset</a>
            </form>

            <a href="tambah_pengguna.php" class="btn primary">
                + Tambah Pengguna
            </a>
        </div>

        <p class="muted">
            Total pengguna: <strong><?= count($daftarPengguna) ?></strong>
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($daftarPengguna): ?>
                    <?php foreach ($daftarPengguna as $i => $pengguna): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= hPengguna($pengguna['nama']) ?></td>
                            <td><?= hPengguna($pengguna['username']) ?></td>
                            <td>
                                <span class="role">
                                    <?= hPengguna($pengguna['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int)$pengguna['aktif'] === 1): ?>
                                    <span class="role aktif">Aktif</span>
                                <?php else: ?>
                                    <span class="role nonaktif">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= hPengguna($pengguna['created_at']) ?></td>
                            <td>
                                <a
                                    href="edit_pengguna.php?id=<?= (int)$pengguna['id'] ?>"
                                    class="btn primary"
                                >
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">
                            Belum ada pengguna atau data tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> SIMDOSEN |
    Universitas Muhammadiyah Bengkulu
</footer>

</body>
</html>
