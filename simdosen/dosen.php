
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

wajibLogin();

$kataKunci = trim($_GET['cari'] ?? '');
$daftarDosen = [];
$error = '';

try {
    if ($kataKunci !== '') {
        $sql = "SELECT id, nomor_registrasi, nama, nip, nuptk,
                       fakultas, program_studi, jabatan_fungsional,
                       status_aktivitas
                FROM dosen
                WHERE nama LIKE :nama
                   OR nip LIKE :nip
                   OR nuptk LIKE :nuptk
                   OR nomor_registrasi LIKE :nomor
                   OR fakultas LIKE :fakultas
                   OR program_studi LIKE :prodi
                ORDER BY nama ASC";

        $stmt = $pdo->prepare($sql);
        $pencarian = '%' . $kataKunci . '%';

        $stmt->execute([
            'nama' => $pencarian,
            'nip' => $pencarian,
            'nuptk' => $pencarian,
            'nomor' => $pencarian,
            'fakultas' => $pencarian,
            'prodi' => $pencarian
        ]);
    } else {
        $sql = "SELECT id, nomor_registrasi, nama, nip, nuptk,
                       fakultas, program_studi, jabatan_fungsional,
                       status_aktivitas
                FROM dosen
                ORDER BY nama ASC";

        $stmt = $pdo->query($sql);
    }

    $daftarDosen = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Kesalahan halaman dosen: ' . $e->getMessage());
    $error = 'Data dosen belum dapat ditampilkan. Periksa struktur tabel database.';
}

function hDosen($nilai): string
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$namaPengguna = $_SESSION['nama'] ?? 'Pengguna';
$rolePengguna = $_SESSION['role'] ?? '';

$bolehKelola = in_array(
    $rolePengguna,
    ['administrator', 'operator'],
    true
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dosen - SIMDOSEN</title>

    <style>
        * {
            box-sizing: border-box;
        }

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
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .brand {
            color: white;
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
        }

        .brand small {
            display: block;
            font-size: 11px;
            font-weight: normal;
            margin-top: 5px;
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

        .user-label {
            color: white;
            font-size: 12px;
        }

        .container {
            width: 94%;
            max-width: 1400px;
            margin: 30px auto;
            min-height: 65vh;
        }

        .page-heading {
            margin-bottom: 22px;
        }

        .page-heading h1 {
            margin: 0 0 8px;
            color: #123c69;
            font-size: 28px;
        }

        .page-heading p {
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0f172a0b;
            margin-bottom: 22px;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            flex: 1;
        }

        input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            border: none;
            padding: 9px 12px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            font-weight: bold;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1150px;
        }

        th, td {
            padding: 13px 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 13px;
            vertical-align: middle;
        }

        th {
            background: #eff6ff;
            color: #123c69;
            white-space: nowrap;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            background: #dcfce7;
            color: #166534;
        }

        .badge-cuti {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        .notice {
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .notice-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .notice-success {
            background: #dcfce7;
            color: #166534;
        }

        .empty {
            padding: 25px;
            text-align: center;
            color: #64748b;
        }

        .aksi {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #64748b;
            background: white;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
        }

        @media (max-width: 650px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .container {
                width: 96%;
                margin-top: 20px;
            }

            .card {
                padding: 14px;
            }
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
        <a href="dosen.php" class="active">Data Dosen</a>
        <a href="laporan.php">Laporan</a>

        <?php if ($rolePengguna === 'administrator'): ?>
            <a href="pengguna.php">Pengguna</a>
            <a href="audit_log.php">Audit Log</a>
        <?php endif; ?>

        <span class="user-label">
            <?= hDosen($namaPengguna) ?>
            (<?= hDosen($rolePengguna) ?>)
        </span>

        <a href="logout.php">Keluar</a>
    </nav>
</header>

<main class="container">

    <section class="page-heading">
        <h1>Data Dosen</h1>
        <p>
            Informasi dosen Universitas Muhammadiyah Bengkulu.
            Gunakan kolom pencarian untuk menemukan data.
        </p>
    </section>

    <?php
    $pesan = $_GET['pesan'] ?? '';
    $pesanTampil = [
        'tambah_berhasil' => 'Data dosen berhasil ditambahkan.',
        'edit_berhasil' => 'Data dosen berhasil diperbarui.',
        'hapus_berhasil' => 'Data dosen berhasil dihapus.',
        'id_tidak_valid' => 'ID dosen tidak valid.',
        'data_tidak_ditemukan' => 'Data dosen tidak ditemukan.'
    ];
    ?>

    <?php if (isset($pesanTampil[$pesan])): ?>
        <div class="notice <?= in_array($pesan, [
            'tambah_berhasil', 'edit_berhasil', 'hapus_berhasil'
        ], true) ? 'notice-success' : 'notice-error' ?>">
            <?= hDosen($pesanTampil[$pesan]) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="notice notice-error">
            <?= hDosen($error) ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <div class="toolbar">

            <form method="GET" action="dosen.php" class="search-form">
                <input
                    type="text"
                    name="cari"
                    value="<?= hDosen($kataKunci) ?>"
                    placeholder="Cari nama, NIP, NUPTK, fakultas, atau prodi..."
                >

                <button type="submit" class="btn btn-primary">
                    Cari
                </button>

                <a href="dosen.php" class="btn btn-secondary">
                    Reset
                </a>
            </form>

            <?php if ($bolehKelola): ?>
                <a href="tambah_dosen.php" class="btn btn-primary">
                    + Tambah Dosen
                </a>
            <?php endif; ?>

        </div>

        <p>
            Total hasil:
            <strong><?= count($daftarDosen) ?></strong> data
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Registrasi</th>
                        <th>Nama Dosen</th>
                        <th>NIP</th>
                        <th>NUPTK</th>
                        <th>Fakultas</th>
                        <th>Program Studi</th>
                        <th>Jabatan Fungsional</th>
                        <th>Status Aktivitas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($daftarDosen) > 0): ?>

                    <?php foreach ($daftarDosen as $i => $dosen): ?>
                        <?php
                        $status = $dosen['status_aktivitas'] ?? '';
                        $kelasStatus = '';

                        if ($status === 'Cuti') {
                            $kelasStatus = 'badge-cuti';
                        } elseif ($status === 'Nonaktif') {
                            $kelasStatus = 'badge-nonaktif';
                        }
                        ?>

                        <tr>
                            <td><?= $i + 1 ?></td>

                            <td>
                                <?= hDosen($dosen['nomor_registrasi'] ?? '') ?>
                            </td>

                            <td>
                                <?= hDosen($dosen['nama'] ?? '') ?>
                            </td>

                            <td>
                                <?= hDosen($dosen['nip'] ?? '') ?>
                            </td>

                            <td>
                                <?= hDosen($dosen['nuptk'] ?? '') ?>
                            </td>

                            <td>
                                <?= hDosen($dosen['fakultas'] ?? '') ?>
                            </td>

                            <td>
                                <?= hDosen($dosen['program_studi'] ?? '') ?>
                            </td>

                            <td>
                                <?= hDosen($dosen['jabatan_fungsional'] ?? '') ?>
                            </td>

                            <td>
                                <span class="badge <?= hDosen($kelasStatus) ?>">
                                    <?= hDosen(
                                        $status !== '' ? $status : 'Belum diisi'
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($bolehKelola): ?>
                                    <div class="aksi">
                                        <a
                                            href="edit_dosen.php?id=<?= (int)$dosen['id'] ?>"
                                            class="btn btn-primary"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="hapus_dosen.php?id=<?= (int)$dosen['id'] ?>"
                                            class="btn btn-danger"
                                        >
                                            Hapus
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="10" class="empty">
                            Belum ada data dosen atau data tidak ditemukan.
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
