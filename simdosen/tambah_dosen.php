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

wajibLogin();
wajibRole(['administrator', 'operator']);

$error = '';
$berhasil = false;

$data = [
    'nomor_registrasi' => '',
    'nuptk' => '',
    'nama' => '',
    'nip' => '',
    'jabatan_fungsional' => '',
    'kepangkatan' => '',
    'pendidikan_terakhir' => '',
    'rumpun_ilmu' => '',
    'status_serdos' => 'Belum',
    'status_pegawai' => '',
    'ikatan_kerja' => '',
    'status_aktivitas' => 'Aktif',
    'penempatan' => '',
    'fakultas' => '',
    'program_studi' => ''
];

function hTambahDosen($nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajibCSRF('csrf_dosen');

    foreach ($data as $kolom => $nilaiAwal) {
        $data[$kolom] = trim((string) ($_POST[$kolom] ?? ''));
    }

    if ($data['nama'] === '') {
        $error = 'Nama dosen wajib diisi.';
    } elseif (
        !in_array(
            $data['status_serdos'],
            ['Sudah', 'Belum', 'Tidak Berlaku'],
            true
        )
    ) {
        $error = 'Status sertifikasi dosen tidak valid.';
    } elseif (
        !in_array(
            $data['status_pegawai'],
            ['PNS', 'Tetap', 'Kontrak', 'Honorer', 'Lainnya'],
            true
        )
    ) {
        $error = 'Status pegawai tidak valid.';
    } elseif (
        !in_array(
            $data['ikatan_kerja'],
            ['Dosen Tetap', 'Dosen Tidak Tetap', 'Praktisi', 'Lainnya'],
            true
        )
    ) {
        $error = 'Ikatan kerja tidak valid.';
    } elseif (
        !in_array(
            $data['status_aktivitas'],
            ['Aktif', 'Cuti', 'Nonaktif'],
            true
        )
    ) {
        $error = 'Status aktivitas tidak valid.';
    } else {
        try {
            $sql = "INSERT INTO dosen (
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
                    ) VALUES (
                        :nomor_registrasi,
                        :nuptk,
                        :nama,
                        :nip,
                        :jabatan_fungsional,
                        :kepangkatan,
                        :pendidikan_terakhir,
                        :rumpun_ilmu,
                        :status_serdos,
                        :status_pegawai,
                        :ikatan_kerja,
                        :status_aktivitas,
                        :penempatan,
                        :fakultas,
                        :program_studi
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'nomor_registrasi' => $data['nomor_registrasi'],
                'nuptk' => $data['nuptk'],
                'nama' => $data['nama'],
                'nip' => $data['nip'],
                'jabatan_fungsional' => $data['jabatan_fungsional'],
                'kepangkatan' => $data['kepangkatan'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir'],
                'rumpun_ilmu' => $data['rumpun_ilmu'],
                'status_serdos' => $data['status_serdos'],
                'status_pegawai' => $data['status_pegawai'],
                'ikatan_kerja' => $data['ikatan_kerja'],
                'status_aktivitas' => $data['status_aktivitas'],
                'penempatan' => $data['penempatan'],
                'fakultas' => $data['fakultas'],
                'program_studi' => $data['program_studi']
            ]);

            $idBaru = (int) $pdo->lastInsertId();

            // Catat aktivitas jika modul audit tersedia.
            $fileAudit = __DIR__ . '/includes/audit.php';

            if (is_file($fileAudit)) {
                require_once $fileAudit;

                if (function_exists('catatAudit')) {
                    try {
                        catatAudit(
                            $pdo,
                            'Menambahkan data dosen',
                            'ID dosen: ' . $idBaru . '; Nama: ' . $data['nama']
                        );
                    } catch (Throwable $e) {
                        error_log('Audit tambah dosen: ' . $e->getMessage());
                    }
                }
            }

            header('Location: dosen.php?pesan=tambah_berhasil');
            exit;
        } catch (PDOException $e) {
            error_log('Simpan dosen gagal: ' . $e->getMessage());
            $error = 'Data dosen gagal disimpan. Periksa database dan coba kembali.';
        }
    }
}

$namaPengguna = $_SESSION['nama'] ?? 'Pengguna';
$rolePengguna = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dosen - SIMDOSEN</title>

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
            color: white;
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
            font-size: 11px;
            font-weight: normal;
            margin-top: 5px;
            color: #dbeafe;
        }

        .nav-menu {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
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
            width: 92%;
            max-width: 1100px;
            margin: 30px auto;
            min-height: 65vh;
        }

        .heading {
            margin-bottom: 22px;
        }

        .heading h1 {
            color: #123c69;
            margin: 0 0 8px;
        }

        .heading p {
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0f172a0b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #334155;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: white;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: 2px solid #bfdbfe;
            border-color: #2563eb;
        }

        .required {
            color: #dc2626;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 7px;
            padding: 12px 17px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
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

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .section-title {
            grid-column: 1 / -1;
            color: #123c69;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin: 8px 0 0;
            font-size: 17px;
        }

        .footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            text-align: center;
            padding: 20px;
            font-size: 13px;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                grid-column: auto;
            }

            .container {
                width: 95%;
            }

            .card {
                padding: 17px;
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
        <span><?= hTambahDosen($namaPengguna) ?> (<?= hTambahDosen($rolePengguna) ?>)</span>
        <a href="logout.php">Keluar</a>
    </nav>
</header>

<main class="container">
    <section class="heading">
        <h1>Tambah Data Dosen</h1>
        <p>Lengkapi informasi dosen, lalu tekan tombol Simpan Data.</p>
    </section>

    <?php if ($error !== ''): ?>
        <div class="error"><?= hTambahDosen($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <form method="POST" action="tambah_dosen.php">
            <?= inputCSRF('csrf_dosen') ?>

            <div class="form-grid">
                <h2 class="section-title">Identitas Dosen</h2>

                <div class="form-group">
                    <label for="nama">Nama Dosen <span class="required">*</span></label>
                    <input id="nama" name="nama" type="text"
                           value="<?= hTambahDosen($data['nama']) ?>"
                           maxlength="255" required>
                </div>

                <div class="form-group">
                    <label for="nomor_registrasi">Nomor Registrasi</label>
                    <input id="nomor_registrasi" name="nomor_registrasi"
                           type="text" value="<?= hTambahDosen($data['nomor_registrasi']) ?>">
                </div>

                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input id="nip" name="nip" type="text"
                           value="<?= hTambahDosen($data['nip']) ?>">
                </div>

                <div class="form-group">
                    <label for="nuptk">NUPTK</label>
                    <input id="nuptk" name="nuptk" type="text"
                           value="<?= hTambahDosen($data['nuptk']) ?>">
                </div>

                <h2 class="section-title">Informasi Akademik dan Kepegawaian</h2>

                <div class="form-group">
                    <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
                    <input id="pendidikan_terakhir" name="pendidikan_terakhir"
                           type="text" placeholder="Contoh: S2"
                           value="<?= hTambahDosen($data['pendidikan_terakhir']) ?>">
                </div>

                <div class="form-group">
                    <label for="rumpun_ilmu">Rumpun Ilmu</label>
                    <input id="rumpun_ilmu" name="rumpun_ilmu" type="text"
                           value="<?= hTambahDosen($data['rumpun_ilmu']) ?>">
                </div>

                <div class="form-group">
                    <label for="jabatan_fungsional">Jabatan Fungsional</label>
                    <input id="jabatan_fungsional" name="jabatan_fungsional"
                           type="text" value="<?= hTambahDosen($data['jabatan_fungsional']) ?>">
                </div>

                <div class="form-group">
                    <label for="kepangkatan">Kepangkatan</label>
                    <input id="kepangkatan" name="kepangkatan" type="text"
                           value="<?= hTambahDosen($data['kepangkatan']) ?>">
                </div>

                <div class="form-group">
                    <label for="status_serdos">Status Sertifikasi Dosen</label>
                    <select id="status_serdos" name="status_serdos" required>
                        <?php foreach (['Sudah', 'Belum', 'Tidak Berlaku'] as $opsi): ?>
                            <option value="<?= hTambahDosen($opsi) ?>"
                                <?= $data['status_serdos'] === $opsi ? 'selected' : '' ?>>
                                <?= hTambahDosen($opsi) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status_pegawai">Status Pegawai <span class="required">*</span></label>
                    <select id="status_pegawai" name="status_pegawai" required>
                        <option value="">-- Pilih Status Pegawai --</option>
                        <?php foreach (['PNS', 'Tetap', 'Kontrak', 'Honorer', 'Lainnya'] as $opsi): ?>
                            <option value="<?= hTambahDosen($opsi) ?>"
                                <?= $data['status_pegawai'] === $opsi ? 'selected' : '' ?>>
                                <?= hTambahDosen($opsi) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ikatan_kerja">Ikatan Kerja <span class="required">*</span></label>
                    <select id="ikatan_kerja" name="ikatan_kerja" required>
                        <option value="">-- Pilih Ikatan Kerja --</option>
                        <?php foreach (['Dosen Tetap', 'Dosen Tidak Tetap', 'Praktisi', 'Lainnya'] as $opsi): ?>
                            <option value="<?= hTambahDosen($opsi) ?>"
                                <?= $data['ikatan_kerja'] === $opsi ? 'selected' : '' ?>>
                                <?= hTambahDosen($opsi) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status_aktivitas">Status Aktivitas <span class="required">*</span></label>
                    <select id="status_aktivitas" name="status_aktivitas" required>
                        <?php foreach (['Aktif', 'Cuti', 'Nonaktif'] as $opsi): ?>
                            <option value="<?= hTambahDosen($opsi) ?>"
                                <?= $data['status_aktivitas'] === $opsi ? 'selected' : '' ?>>
                                <?= hTambahDosen($opsi) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h2 class="section-title">Penempatan Dosen</h2>

                <div class="form-group">
                    <label for="penempatan">Penempatan</label>
                    <input id="penempatan" name="penempatan" type="text"
                           value="<?= hTambahDosen($data['penempatan']) ?>">
                </div>

                <div class="form-group">
                    <label for="fakultas">Fakultas</label>
                    <input id="fakultas" name="fakultas" type="text"
                           value="<?= hTambahDosen($data['fakultas']) ?>">
                </div>

                <div class="form-group">
                    <label for="program_studi">Program Studi</label>
                    <input id="program_studi" name="program_studi" type="text"
                           value="<?= hTambahDosen($data['program_studi']) ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Simpan Data
                </button>

                <a href="dosen.php" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </section>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> SIMDOSEN |
    Universitas Muhammadiyah Bengkulu
</footer>

</body>
</html>
