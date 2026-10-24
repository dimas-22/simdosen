<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Koneksi database tidak tersedia.');
}

function e(string $nilai): string
{
    return htmlspecialchars($nilai, ENT_QUOTES, 'UTF-8');
}

$tahun = date('Y');
$namaPengguna = (string) ($_SESSION['nama'] ?? 'Pengguna');

try {
    // Mengambil jumlah seluruh dosen
    $totalDosen = (int) $pdo->query(
        "SELECT COUNT(*) FROM dosen"
    )->fetchColumn();

    // Statistik status aktivitas dosen
    $stmt = $pdo->query(
        "SELECT status_aktivitas, COUNT(*) AS jumlah
         FROM dosen
         GROUP BY status_aktivitas"
    );
    $statAktivitas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Statistik status sertifikasi dosen
    $stmt = $pdo->query(
        "SELECT status_serdos, COUNT(*) AS jumlah
         FROM dosen
         GROUP BY status_serdos"
    );
    $statSerdos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Mengambil data dosen untuk laporan
    $stmt = $pdo->query(
        "SELECT
            id,
            nomor_registrasi,
            nuptk,
            nama,
            nip,
            jabatan_fungsional,
            kepangkatan,
            pendidikan_terakhir,
            rumpun_ilmu,
            status_serdos,
            status_pegawai,
            ikatan_kerja,
            status_aktivitas,
            penempatan,
            fakultas,
            program_studi
         FROM dosen
         ORDER BY nama ASC"
    );

    $daftarDosen = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    error_log('Laporan dosen: ' . $ex->getMessage());
    http_response_code(500);
    exit('Laporan gagal dimuat. Periksa struktur tabel dosen dan log hosting.');
}

function jumlahStatus(array $data, string $status): int
{
    return (int) ($data[$status] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Data Dosen | SIMDOSEN</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: #1e293b;
    background: #f1f5f9;
    line-height: 1.6;
}

header {
    padding: 22px 5%;
    color: white;
    background: linear-gradient(110deg, #123c75, #2168c7);
}

header h1 {
    margin: 0;
    font-size: 26px;
}

header p {
    margin: 4px 0 0;
    color: #dbeafe;
}

nav {
    padding: 14px 5%;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

nav a {
    display: inline-block;
    margin-right: 18px;
    color: #1d4ed8;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
}

nav a:hover {
    text-decoration: underline;
}

main {
    max-width: 1250px;
    margin: 28px auto;
    padding: 0 18px 35px;
}

.page-heading {
    margin-bottom: 22px;
}

.page-heading h2 {
    margin: 0;
    color: #123c75;
}

.page-heading p {
    margin: 5px 0;
    color: #64748b;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    padding: 20px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 12px #0f172a08;
}

.stat-card span {
    display: block;
    color: #64748b;
    font-size: 13px;
}

.stat-card strong {
    display: block;
    margin-top: 5px;
    color: #1d4ed8;
    font-size: 30px;
}

.paper {
    padding: 28px;
    margin-bottom: 22px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 12px #0f172a08;
}

.paper h3 {
    margin-top: 0;
    color: #123c75;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    white-space: nowrap;
}

th, td {
    padding: 11px 12px;
    border: 1px solid #e2e8f0;
    text-align: left;
}

th {
    color: white;
    background: #174a8b;
}

tbody tr:nth-child(even) {
    background: #f8fafc;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: bold;
}

.badge-aktif {
    background: #dcfce7;
    color: #166534;
}

.badge-nonaktif {
    background: #fee2e2;
    color: #991b1b;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 20px 0;
}

.button {
    display: inline-block;
    padding: 11px 16px;
    border: 0;
    border-radius: 8px;
    background: #1d4ed8;
    color: white;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
}

.button-secondary {
    background: #475569;
}

.button:hover {
    opacity: .9;
}

.empty {
    padding: 22px;
    color: #64748b;
    text-align: center;
}

footer {
    padding: 20px;
    color: #64748b;
    text-align: center;
    font-size: 12px;
}

@media (max-width: 800px) {
    .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .paper {
        padding: 18px;
    }
}

@media (max-width: 450px) {
    .stats {
        grid-template-columns: 1fr 1fr;
        gap: 9px;
    }

    .stat-card {
        padding: 13px;
    }

    .stat-card strong {
        font-size: 24px;
    }

    nav a {
        margin-bottom: 8px;
    }
}

@media print {
    body {
        background: white;
        color: black;
        font-size: 10pt;
    }

    header, nav, .actions, footer {
        display: none !important;
    }

    main {
        max-width: none;
        margin: 0;
        padding: 0;
    }

    .paper, .stat-card {
        box-shadow: none;
        border: 1px solid #ccc;
        break-inside: avoid;
    }

    .stats {
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .stat-card {
        padding: 10px;
    }

    .stat-card strong {
        font-size: 18px;
    }

    .table-wrap {
        overflow: visible;
    }

    table {
        font-size: 8pt;
        white-space: normal;
    }

    th, td {
        padding: 6px;
    }

    th {
        color: black;
        background: #e5e7eb;
    }

    @page {
        size: landscape;
        margin: 12mm;
    }
}
</style>
</head>

<body>

<header>
    <h1>SIMDOSEN</h1>
    <p>Sistem Informasi Manajemen Dosen</p>
</header>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="dosen.php">Data Dosen</a>
    <a href="pengguna.php">Manajemen Pengguna</a>
    <a href="audit_log.php">Audit Log</a>
    <a href="dokumentasi_api.php">Dokumentasi API</a>
</nav>

<main>

    <div class="page-heading">
        <h2>Laporan Data Dosen</h2>
        <p>
            Laporan berdasarkan data yang tersimpan di database SIMDOSEN.
        </p>
        <p>
            Dicetak oleh: <strong><?= e($namaPengguna) ?></strong>
            | Tanggal: <?= e(date('d-m-Y')) ?>
        </p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <span>Total Dosen</span>
            <strong><?= $totalDosen ?></strong>
        </div>

        <div class="stat-card">
            <span>Dosen Aktif</span>
            <strong><?= jumlahStatus($statAktivitas, 'Aktif') ?></strong>
        </div>

        <div class="stat-card">
            <span>Dosen Cuti</span>
            <strong><?= jumlahStatus($statAktivitas, 'Cuti') ?></strong>
        </div>

        <div class="stat-card">
            <span>Sudah Sertifikasi</span>
            <strong><?= jumlahStatus($statSerdos, 'Sudah') ?></strong>
        </div>
    </div>

    <section class="paper">
        <h3>Rekapitulasi Status Dosen</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Status Aktivitas</td>
                        <td>Aktif</td>
                        <td><?= jumlahStatus($statAktivitas, 'Aktif') ?></td>
                    </tr>
                    <tr>
                        <td>Status Aktivitas</td>
                        <td>Cuti</td>
                        <td><?= jumlahStatus($statAktivitas, 'Cuti') ?></td>
                    </tr>
                    <tr>
                        <td>Status Aktivitas</td>
                        <td>Nonaktif</td>
                        <td><?= jumlahStatus($statAktivitas, 'Nonaktif') ?></td>
                    </tr>
                    <tr>
                        <td>Sertifikasi Dosen</td>
                        <td>Sudah</td>
                        <td><?= jumlahStatus($statSerdos, 'Sudah') ?></td>
                    </tr>
                    <tr>
                        <td>Sertifikasi Dosen</td>
                        <td>Belum</td>
                        <td><?= jumlahStatus($statSerdos, 'Belum') ?></td>
                    </tr>
                    <tr>
                        <td>Sertifikasi Dosen</td>
                        <td>Tidak Berlaku</td>
                        <td><?= jumlahStatus($statSerdos, 'Tidak Berlaku') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="paper">
        <h3>Daftar Data Dosen</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Registrasi</th>
                        <th>NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>NIP</th>
                        <th>Jabatan Fungsional</th>
                        <th>Kepangkatan</th>
                        <th>Pendidikan</th>
                        <th>Status Serdos</th>
                        <th>Status Pegawai</th>
                        <th>Status Aktivitas</th>
                        <th>Fakultas</th>
                        <th>Program Studi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($daftarDosen) > 0): ?>
                    <?php foreach ($daftarDosen as $index => $dosen): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e((string) ($dosen['nomor_registrasi'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['nuptk'] ?? '-')) ?></td>
                            <td><strong><?= e((string) ($dosen['nama'] ?? '-')) ?></strong></td>
                            <td><?= e((string) ($dosen['nip'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['jabatan_fungsional'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['kepangkatan'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['pendidikan_terakhir'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['status_serdos'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['status_pegawai'] ?? '-')) ?></td>
                            <td>
                                <?php
                                $status = (string) ($dosen['status_aktivitas'] ?? '-');
                                $kelas = $status === 'Aktif'
                                    ? 'badge badge-aktif'
                                    : ($status === 'Nonaktif' ? 'badge badge-nonaktif' : 'badge');
                                ?>
                                <span class="<?= e($kelas) ?>"><?= e($status) ?></span>
                            </td>
                            <td><?= e((string) ($dosen['fakultas'] ?? '-')) ?></td>
                            <td><?= e((string) ($dosen['program_studi'] ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13" class="empty">
                            Belum ada data dosen yang tersimpan.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p style="margin-top:15px;color:#64748b;font-size:12px">
            Jumlah data dalam laporan: <?= count($daftarDosen) ?> dosen.
        </p>
    </section>

    <div class="actions">
        <button class="button" type="button" onclick="window.print()">
            Cetak / Simpan PDF
        </button>
        <a class="button button-secondary" href="dosen.php">
            Kembali ke Data Dosen
        </a>
        <a class="button button-secondary" href="dashboard.php">
            Dashboard
        </a>
    </div>

</main>

<footer>
    SIMDOSEN &copy; <?= e($tahun) ?> — Universitas Muhammadiyah Bengkulu
</footer>

</body>
</html>
