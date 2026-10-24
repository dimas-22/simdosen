
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$judulHalaman = $judulHalaman ?? 'SIMDOSEN';
$halamanAktif = $halamanAktif ?? '';
$namaPengguna = $_SESSION['nama'] ?? 'Pengguna';
$rolePengguna = $_SESSION['role'] ?? '';

function h($nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($judulHalaman) ?> - SIMDOSEN</title>

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
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
        }

        .brand span {
            display: block;
            font-size: 11px;
            font-weight: normal;
            margin-top: 4px;
            color: #dbeafe;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: #2563eb;
        }

        .user-info {
            background: #ffffff1a;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 13px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 28px auto;
            min-height: 65vh;
        }

        .page-heading {
            margin-bottom: 22px;
        }

        .page-heading h1 {
            margin: 0 0 8px;
            color: #123c69;
            font-size: 27px;
        }

        .page-heading p {
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #0f172a0d;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: white;
            color: #64748b;
            font-size: 13px;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 700px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .nav-menu {
                width: 100%;
            }

            .nav-menu a {
                padding: 8px;
            }

            .container {
                width: 94%;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a class="brand" href="dashboard.php">
        SIMDOSEN
        <span>Sistem Informasi Manajemen Dosen</span>
    </a>

    <nav class="nav-menu">
        <a href="dashboard.php"
           class="<?= $halamanAktif === 'dashboard' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="dosen.php"
           class="<?= $halamanAktif === 'dosen' ? 'active' : '' ?>">
            Data Dosen
        </a>

        <a href="laporan.php"
           class="<?= $halamanAktif === 'laporan' ? 'active' : '' ?>">
            Laporan
        </a>

        <?php if ($rolePengguna === 'administrator'): ?>
            <a href="pengguna.php"
               class="<?= $halamanAktif === 'pengguna' ? 'active' : '' ?>">
                Pengguna
            </a>

            <a href="audit_log.php"
               class="<?= $halamanAktif === 'audit' ? 'active' : '' ?>">
                Audit Log
            </a>
        <?php endif; ?>

        <span class="user-info">
            <?= h($namaPengguna) ?>
            (<?= h($rolePengguna) ?>)
        </span>

        <a href="logout.php">Keluar</a>
    </nav>
</header>

<main class="container">
