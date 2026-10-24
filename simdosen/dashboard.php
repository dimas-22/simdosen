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

wajibLogin();

$namaPengguna = $_SESSION['nama'] ?? 'Pengguna';
$rolePengguna = $_SESSION['role'] ?? 'operator';

$totalDosen = 0;
$dosenAktif = 0;
$dosenCuti = 0;
$dosenNonaktif = 0;
$errorData = '';

try {
    $totalDosen = (int) $pdo
        ->query("SELECT COUNT(*) FROM dosen")
        ->fetchColumn();

    $dosenAktif = (int) $pdo
        ->query("SELECT COUNT(*) FROM dosen WHERE status_aktivitas = 'Aktif'")
        ->fetchColumn();

    $dosenCuti = (int) $pdo
        ->query("SELECT COUNT(*) FROM dosen WHERE status_aktivitas = 'Cuti'")
        ->fetchColumn();

    $dosenNonaktif = (int) $pdo
        ->query("SELECT COUNT(*) FROM dosen WHERE status_aktivitas = 'Nonaktif'")
        ->fetchColumn();
} catch (PDOException $e) {
    error_log('Dashboard SIMDOSEN: ' . $e->getMessage());
    $errorData = 'Data ringkasan belum dapat ditampilkan.';
}

function hDashboard($nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIMDOSEN</title>

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
            padding: 17px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .brand {
            color: white;
            text-decoration: none;
            font-size: 23px;
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
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-menu a {
            text-decoration: none;
            color: white;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: #2563eb;
        }

        .user-label {
            font-size: 13px;
            padding: 9px 12px;
            background: #ffffff20;
            border-radius: 6px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            min-height: 62vh;
        }

        .welcome {
            background: linear-gradient(120deg, #123c69, #2563eb);
            color: white;
            border-radius: 14px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .welcome h1 {
            margin: 0 0 12px;
            font-size: 28px;
        }

        .welcome p {
            margin: 0;
            line-height: 1.7;
            color: #e0ecff;
        }

        .section-title {
            margin: 28px 0 16px;
            color: #123c69;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 3px 12px #0f172a08;
        }

        .stat-card p {
            margin: 0 0 14px;
            color: #64748b;
            font-size: 14px;
        }

        .stat-card h2 {
            margin: 0;
            font-size: 30px;
            color: #123c69;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .menu-card {
            display: block;
            padding: 23px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            color: #1e293b;
            transition: transform .2s, box-shadow .2s;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px #0f172a12;
        }

        .menu-card h3 {
            color: #123c69;
            margin: 0 0 10px;
        }

        .menu-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .notice {
            background: #fef3c7;
            color: #92400e;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .footer {
            background: white;
            text-align: center;
            color: #64748b;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 550px) {
            .stats,
            .menu-grid {
                grid-template-columns: 1fr;
            }

            .welcome h1 {
                font-size: 23px;
            }

            .container {
                width: 94%;
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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="dosen.php">Data Dosen</a>
        <a href="laporan.php">Laporan</a>

        <?php if ($rolePengguna === 'administrator'): ?>
            <a href="pengguna.php">Pengguna</a>
            <a href="audit_log.php">Audit Log</a>
        <?php endif; ?>

        <span class="user-label">
            <?= hDashboard($namaPengguna) ?>
            (<?= hDashboard($rolePengguna) ?>)
        </span>

        <a href="logout.php">Keluar</a>
    </nav>
</header>

<main class="container">

    <section class="welcome">
        <h1>Selamat Datang, <?= hDashboard($namaPengguna) ?>!</h1>
        <p>
            Anda berhasil masuk ke Sistem Informasi Manajemen Dosen
            Universitas Muhammadiyah Bengkulu.
            Kelola dan pantau informasi dosen melalui dashboard ini.
        </p>
    </section>

    <?php if ($errorData !== ''): ?>
        <div class="notice">
            <?= hDashboard($errorData) ?>
            Periksa struktur tabel dosen jika masalah berlanjut.
        </div>
    <?php endif; ?>

    <h2 class="section-title">Ringkasan Data Dosen</h2>

    <section class="stats">
        <div class="stat-card">
            <p>Total Dosen</p>
            <h2><?= $totalDosen ?></h2>
        </div>

        <div class="stat-card">
            <p>Dosen Aktif</p>
            <h2><?= $dosenAktif ?></h2>
        </div>

        <div class="stat-card">
            <p>Dosen Cuti</p>
            <h2><?= $dosenCuti ?></h2>
        </div>

        <div class="stat-card">
            <p>Dosen Nonaktif</p>
            <h2><?= $dosenNonaktif ?></h2>
        </div>
    </section>

    <h2 class="section-title">Menu Aplikasi</h2>

    <section class="menu-grid">
        <a class="menu-card" href="dosen.php">
            <h3>Data Dosen</h3>
            <p>Melihat, mencari, menambah, dan mengelola data dosen.</p>
        </a>

        <a class="menu-card" href="laporan.php">
            <h3>Laporan Dosen</h3>
            <p>Melihat ringkasan dan menyiapkan laporan data dosen.</p>
        </a>

        <?php if ($rolePengguna === 'administrator'): ?>
            <a class="menu-card" href="pengguna.php">
                <h3>Manajemen Pengguna</h3>
                <p>Mengelola akun pengguna dan peran akses aplikasi.</p>
            </a>

            <a class="menu-card" href="audit_log.php">
                <h3>Audit Log</h3>
                <p>Melihat catatan aktivitas pengguna dalam aplikasi.</p>
            </a>
        <?php endif; ?>
    </section>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> SIMDOSEN |
    Universitas Muhammadiyah Bengkulu
</footer>

</body>
</html>
