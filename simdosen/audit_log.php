<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/rbac.php';

wajibLogin();
wajibRole(['administrator']);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Koneksi database tidak tersedia.');
}

function e($nilai): string
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Pengaturan pencarian dan pagination
$cari = trim((string)($_GET['cari'] ?? ''));
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$batas = 15;
$offset = ($halaman - 1) * $batas;

$pesanError = '';
$totalData = 0;
$totalHalaman = 1;
$logs = [];

try {
    // Periksa kolom tabel audit_log agar query sesuai database
    $kolom = $pdo->query('SHOW COLUMNS FROM audit_log')
                 ->fetchAll(PDO::FETCH_COLUMN);

    $kolomAktivitas = in_array('aktivitas', $kolom, true)
        ? 'aktivitas'
        : null;

    $kolomDetail = in_array('detail', $kolom, true)
        ? 'detail'
        : (in_array('keterangan', $kolom, true) ? 'keterangan' : null);

    $kolomTanggal = in_array('created_at', $kolom, true)
        ? 'created_at'
        : (in_array('waktu', $kolom, true) ? 'waktu' : null);

    $kolomPengguna = in_array('pengguna_id', $kolom, true)
        ? 'pengguna_id'
        : (in_array('user_id', $kolom, true) ? 'user_id' : null);

    if (!$kolomAktivitas || !$kolomTanggal) {
        throw new RuntimeException(
            'Kolom aktivitas atau tanggal tidak ditemukan pada tabel audit_log.'
        );
    }

    $kondisi = [];
    $parameter = [];

    if ($cari !== '') {
        $bagianCari = ["a.`$kolomAktivitas` LIKE :cari"];
        $parameter[':cari'] = '%' . $cari . '%';

        if ($kolomDetail !== null) {
            $bagianCari[] = "a.`$kolomDetail` LIKE :cari";
        }

        $kondisi[] = '(' . implode(' OR ', $bagianCari) . ')';
    }

    $where = $kondisi
        ? ' WHERE ' . implode(' AND ', $kondisi)
        : '';

    $stmtTotal = $pdo->prepare(
        "SELECT COUNT(*) FROM audit_log a$where"
    );
    $stmtTotal->execute($parameter);
    $totalData = (int)$stmtTotal->fetchColumn();

    $totalHalaman = max(1, (int)ceil($totalData / $batas));

    if ($halaman > $totalHalaman) {
        $halaman = $totalHalaman;
        $offset = ($halaman - 1) * $batas;
    }

    $kolomSelect = [
        'a.id AS id',
        "a.`$kolomAktivitas` AS aktivitas"
    ];

    $kolomSelect[] = $kolomDetail !== null
        ? "a.`$kolomDetail` AS detail"
        : "'' AS detail";

    $kolomSelect[] = "a.`$kolomTanggal` AS tanggal";

    $join = '';
    $namaPengguna = "'' AS nama_pengguna";

    if ($kolomPengguna !== null) {
        $join = " LEFT JOIN pengguna p ON p.id = a.`$kolomPengguna`";
        $namaPengguna = "COALESCE(p.nama, p.username, '') AS nama_pengguna";
    }

    $sql = "SELECT " . implode(', ', $kolomSelect) . ",
                   $namaPengguna
            FROM audit_log a
            $join
            $where
            ORDER BY a.id DESC
            LIMIT $batas OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parameter);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log('Audit log error: ' . $e->getMessage());
    $pesanError = 'Data audit belum dapat ditampilkan. Periksa struktur tabel audit_log di database.';
}

$awal = $totalData > 0 ? $offset + 1 : 0;
$akhir = min($offset + $batas, $totalData);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Log - SIMDOSEN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        .navbar {
            background: #123b72;
            color: white;
            padding: 17px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .navbar strong { font-size: 20px; }
        .navbar a { color: white; text-decoration: none; }
        .container {
            max-width: 1200px;
            margin: 28px auto;
            padding: 0 18px;
        }
        .heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 7px; font-size: 26px; }
        .muted { color: #64748b; font-size: 14px; }
        .card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .06);
            margin-bottom: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-number { font-size: 27px; font-weight: bold; margin-top: 8px; }
        .toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        input {
            flex: 1;
            min-width: 200px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            border: 0;
            border-radius: 7px;
            padding: 11px 15px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-secondary { background: #475569; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            vertical-align: top;
        }
        th { background: #eff6ff; color: #1e3a8a; white-space: nowrap; }
        .badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 5px 8px;
            border-radius: 20px;
            font-size: 12px;
        }
        .alert {
            padding: 13px;
            border-radius: 7px;
            background: #fff7ed;
            color: #9a3412;
            margin-bottom: 15px;
        }
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }
        .footer { text-align: center; color: #64748b; font-size: 12px; padding: 20px; }
        @media (max-width: 600px) {
            .card { padding: 15px; }
            h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
<header class="navbar">
    <strong>SIMDOSEN</strong>
    <a href="dashboard.php">← Kembali ke Dashboard</a>
</header>

<main class="container">
    <div class="heading">
        <div>
            <h1>Audit Log</h1>
            <div class="muted">Riwayat aktivitas pengguna dalam aplikasi SIMDOSEN.</div>
        </div>
        <a class="btn btn-secondary" href="audit_log.php">Muat Ulang</a>
    </div>

    <?php if ($pesanError !== ''): ?>
        <div class="alert"><?= e($pesanError) ?></div>
    <?php else: ?>
        <div class="stats">
            <section class="card">
                <div class="muted">Total catatan aktivitas</div>
                <div class="stat-number"><?= number_format($totalData) ?></div>
            </section>
            <section class="card">
                <div class="muted">Halaman</div>
                <div class="stat-number"><?= $halaman ?> / <?= $totalHalaman ?></div>
            </section>
        </div>

        <section class="card">
            <form class="toolbar" method="get" action="audit_log.php">
                <input
                    type="search"
                    name="cari"
                    value="<?= e($cari) ?>"
                    placeholder="Cari aktivitas atau detail..."
                >
                <button class="btn" type="submit">Cari</button>
                <a class="btn btn-secondary" href="audit_log.php">Reset</a>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Pengguna</th>
                            <th>Aktivitas</th>
                            <th>Detail</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$logs): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:28px">
                                Belum ada catatan aktivitas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td><?= $offset + $i + 1 ?></td>
                                <td><?= e($log['nama_pengguna'] ?: 'Sistem') ?></td>
                                <td><span class="badge"><?= e($log['aktivitas']) ?></span></td>
                                <td><?= e($log['detail'] ?: '-') ?></td>
                                <td><?= e($log['tanggal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="muted">
                    Menampilkan <?= $awal ?>–<?= $akhir ?> dari <?= $totalData ?> catatan
                </div>
                <div>
                    <?php if ($halaman > 1): ?>
                        <a class="btn btn-secondary"
                           href="?halaman=<?= $halaman - 1 ?>&cari=<?= urlencode($cari) ?>">
                            Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php if ($halaman < $totalHalaman): ?>
                        <a class="btn"
                           href="?halaman=<?= $halaman + 1 ?>&cari=<?= urlencode($cari) ?>">
                            Berikutnya
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="footer">
    SIMDOSEN &copy; <?= date('Y') ?> — Sistem Informasi Manajemen Dosen
</footer>
</body>
</html>
