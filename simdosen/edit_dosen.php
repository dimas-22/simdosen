
<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/rbac.php';
require_once __DIR__ . '/includes/csrf.php';

wajibLogin();
wajibRole(['administrator', 'operator']);

function hEdit($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: dosen.php?pesan=id_tidak_valid');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM dosen WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosen) {
    header('Location: dosen.php?pesan=data_tidak_ditemukan');
    exit;
}

$kolom = [
    'nomor_registrasi' => 'Nomor Registrasi',
    'nuptk' => 'NUPTK',
    'nama' => 'Nama Dosen',
    'nip' => 'NIP',
    'jabatan_fungsional' => 'Jabatan Fungsional',
    'kepangkatan' => 'Kepangkatan',
    'pendidikan_terakhir' => 'Pendidikan Terakhir',
    'rumpun_ilmu' => 'Rumpun Ilmu',
    'penempatan' => 'Penempatan',
    'fakultas' => 'Fakultas',
    'program_studi' => 'Program Studi'
];

$enum = [
    'status_serdos' => [
        'Sudah', 'Belum', 'Tidak Berlaku'
    ],
    'status_pegawai' => [
        'PNS', 'Tetap', 'Kontrak', 'Honorer', 'Lainnya'
    ],
    'ikatan_kerja' => [
        'Dosen Tetap', 'Dosen Tidak Tetap',
        'Praktisi', 'Lainnya'
    ],
    'status_aktivitas' => [
        'Aktif', 'Cuti', 'Nonaktif'
    ]
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        wajibCSRF('csrf_dosen');

        $data = [];

        foreach ($kolom as $field => $label) {
            $data[$field] = trim((string)($_POST[$field] ?? ''));
        }

        foreach ($enum as $field => $options) {
            $value = (string)($_POST[$field] ?? '');

            if (!in_array($value, $options, true)) {
                throw new RuntimeException(
                    'Pilihan ' . $field . ' tidak valid.'
                );
            }

            $data[$field] = $value;
        }

        if ($data['nama'] === '') {
            throw new RuntimeException('Nama dosen wajib diisi.');
        }

        $sql = "UPDATE dosen SET
            nomor_registrasi = :nomor_registrasi,
            nuptk = :nuptk,
            nama = :nama,
            nip = :nip,
            jabatan_fungsional = :jabatan_fungsional,
            kepangkatan = :kepangkatan,
            pendidikan_terakhir = :pendidikan_terakhir,
            rumpun_ilmu = :rumpun_ilmu,
            status_serdos = :status_serdos,
            status_pegawai = :status_pegawai,
            ikatan_kerja = :ikatan_kerja,
            status_aktivitas = :status_aktivitas,
            penempatan = :penempatan,
            fakultas = :fakultas,
            program_studi = :program_studi,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $data['id'] = $id;
        $stmt->execute($data);

        // Catat aktivitas jika modul audit tersedia.
        if (is_file(__DIR__ . '/includes/audit.php')) {
            require_once __DIR__ . '/includes/audit.php';

            if (function_exists('catatAudit')) {
                catatAudit(
                    $pdo,
                    'Mengubah data dosen',
                    'ID dosen: ' . $id
                );
            }
        }

        header('Location: dosen.php?pesan=edit_berhasil');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

function nilaiEdit(array $dosen, string $field): string
{
    return hEdit($_POST[$field] ?? $dosen[$field] ?? '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Dosen - SIMDOSEN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        .container {
            max-width: 1050px;
            margin: 30px auto;
            padding: 20px;
        }
        .header {
            background: #123b70;
            color: white;
            padding: 24px;
            border-radius: 12px 12px 0 0;
        }
        .content {
            background: white;
            padding: 25px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 5px 20px #0000000d;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
            background: white;
        }
        .field { margin-bottom: 4px; }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 11px 17px;
            border: 0;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .save { background: #1769aa; color: white; }
        .cancel { background: #e2e8f0; color: #1e293b; }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 18px;
        }
        @media (max-width: 650px) {
            .container { margin: 10px auto; padding: 10px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Edit Data Dosen</h2>
        <p>Perbarui informasi dosen SIMDOSEN.</p>
    </div>

    <div class="content">
        <?php if ($error !== ''): ?>
            <div class="error">
                <?= hEdit($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= inputCSRF('csrf_dosen') ?>

            <div class="grid">
                <?php foreach ($kolom as $field => $label): ?>
                    <div class="field">
                        <label for="<?= hEdit($field) ?>">
                            <?= hEdit($label) ?>
                            <?php if ($field === 'nama'): ?>*<?php endif; ?>
                        </label>
                        <input
                            type="text"
                            id="<?= hEdit($field) ?>"
                            name="<?= hEdit($field) ?>"
                            value="<?= nilaiEdit($dosen, $field) ?>"
                            <?= $field === 'nama' ? 'required' : '' ?>
                        >
                    </div>
                <?php endforeach; ?>

                <?php foreach ($enum as $field => $options): ?>
                    <div class="field">
                        <label for="<?= hEdit($field) ?>">
                            <?= hEdit(ucwords(str_replace('_', ' ', $field))) ?>
                        </label>
                        <select
                            id="<?= hEdit($field) ?>"
                            name="<?= hEdit($field) ?>"
                            required
                        >
                            <option value="">-- Pilih --</option>
                            <?php foreach ($options as $option): ?>
                                <option
                                    value="<?= hEdit($option) ?>"
                                    <?= nilaiEdit($dosen, $field) === hEdit($option)
                                        ? 'selected' : '' ?>
                                >
                                    <?= hEdit($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="actions">
                <button type="submit" class="btn save">
                    Simpan Perubahan
                </button>
                <a href="dosen.php" class="btn cancel">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
