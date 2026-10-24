<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$namaPengguna = htmlspecialchars(
    (string) ($_SESSION['nama'] ?? 'Pengguna'),
    ENT_QUOTES,
    'UTF-8'
);

$baseUrl = 'https://simdosen.gt.tc/simdosen';
$endpoint = $baseUrl . '/api/dosen.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dokumentasi API - SIMDOSEN</title>
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

        header {
            background: #123c75;
            color: white;
            padding: 22px 5%;
        }

        header h1 {
            margin: 0 0 8px;
            font-size: 25px;
        }

        header p {
            margin: 0;
            color: #dbeafe;
        }

        nav {
            background: white;
            padding: 14px 5%;
            border-bottom: 1px solid #e2e8f0;
        }

        nav a {
            color: #1d4ed8;
            text-decoration: none;
            margin-right: 18px;
            font-weight: bold;
        }

        main {
            max-width: 1000px;
            margin: 28px auto;
            padding: 0 18px 35px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px #0f172a0a;
            border: 1px solid #e2e8f0;
        }

        h2 {
            color: #123c75;
            margin-top: 0;
        }

        h3 {
            margin-top: 22px;
            color: #334155;
        }

        .endpoint {
            padding: 15px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .method {
            display: inline-block;
            background: #15803d;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            padding: 5px 10px;
            margin-right: 8px;
        }

        pre {
            background: #0f172a;
            color: #e2e8f0;
            padding: 18px;
            border-radius: 8px;
            overflow-x: auto;
            line-height: 1.6;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        code {
            font-family: Consolas, monospace;
        }

        .note {
            border-left: 4px solid #2563eb;
            background: #eff6ff;
            padding: 14px;
            line-height: 1.7;
        }

        .warning {
            border-left: 4px solid #d97706;
            background: #fffbeb;
            padding: 14px;
            line-height: 1.7;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
        }

        .back {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 7px;
            color: white;
            background: #1d4ed8;
            text-decoration: none;
        }

        footer {
            text-align: center;
            padding: 22px;
            color: #64748b;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .card {
                padding: 17px;
            }

            header h1 {
                font-size: 21px;
            }

            th, td {
                padding: 8px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>SIMDOSEN — Dokumentasi Web Service API</h1>
    <p>Sistem Informasi Manajemen Dosen</p>
</header>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="dosen.php">Data Dosen</a>
    <a href="api_tokens.php">Token API</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <div class="card">
        <h2>Selamat datang, <?= $namaPengguna ?></h2>
        <p>
            Halaman ini menjelaskan penggunaan Web Service API
            SIMDOSEN untuk mengambil data dosen dalam format JSON.
        </p>
        <div class="note">
            API ini menggunakan autentikasi Bearer Token.
            Hanya token terdaftar yang belum kedaluwarsa dan dimiliki
            pengguna aktif yang dapat digunakan.
        </div>
    </div>

    <div class="card">
        <h2>1. Endpoint API</h2>

        <div class="endpoint">
            <span class="method">GET</span>
            <code><?= htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8') ?></code>
        </div>

        <p>
            Endpoint digunakan untuk mengambil daftar data dosen
            dari database SIMDOSEN.
        </p>

        <table>
            <tr>
                <th>Properti</th>
                <th>Keterangan</th>
            </tr>
            <tr>
                <td>Metode HTTP</td>
                <td>GET</td>
            </tr>
            <tr>
                <td>Format respons</td>
                <td>JSON</td>
            </tr>
            <tr>
                <td>Autentikasi</td>
                <td>Bearer Token</td>
            </tr>
            <tr>
                <td>Data</td>
                <td>Daftar dosen sesuai kolom yang tersedia pada endpoint</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>2. Autentikasi</h2>

        <p>
            Buat token melalui menu Manajemen Token API.
            Simpan token asli saat ditampilkan karena database hanya
            menyimpan nilai hash token.
        </p>

        <h3>Header permintaan</h3>
        <pre><code>Authorization: Bearer TOKEN_ANDA
Accept: application/json</code></pre>

        <div class="warning">
            Jangan membagikan token kepada orang lain, menuliskannya
            di laporan, atau memasukkannya ke URL. Jika token bocor,
            nonaktifkan token tersebut dan buat token baru.
        </div>
    </div>

    <div class="card">
        <h2>3. Contoh Permintaan</h2>

        <h3>Contoh menggunakan cURL</h3>
        <pre><code>curl -X GET \
  "<?= htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8') ?>" \
  -H "Authorization: Bearer TOKEN_ANDA" \
  -H "Accept: application/json"</code></pre>

        <p>
            Ganti <code>TOKEN_ANDA</code> dengan token asli yang
            masih berlaku. Jangan menggunakan contoh token palsu
            sebagai kredensial.
        </p>
    </div>

    <div class="card">
        <h2>4. Contoh Respons Berhasil</h2>

        <p>
            Jika token valid dan permintaan berhasil diproses,
            API mengembalikan HTTP 200 dengan struktur berikut.
        </p>

        <pre><code>{
  "status": true,
  "pesan": "Data dosen berhasil diambil.",
  "jumlah": 2,
  "data": [
    {
      "id": 1,
      "nama": "Contoh Nama Dosen"
    }
  ]
}</code></pre>

        <p>
            Contoh di atas hanya ilustrasi struktur JSON.
            Respons sebenarnya berisi seluruh kolom dosen yang
            dipilih pada file <code>api/dosen.php</code>.
        </p>
    </div>

    <div class="card">
        <h2>5. Kode Status HTTP</h2>

        <table>
            <tr>
                <th>Kode</th>
                <th>Makna</th>
            </tr>
            <tr>
                <td>200</td>
                <td>Permintaan berhasil dan data dosen dikembalikan.</td>
            </tr>
            <tr>
                <td>401</td>
                <td>Token tidak ditemukan, tidak valid, atau kedaluwarsa.</td>
            </tr>
            <tr>
                <td>405</td>
                <td>Metode selain GET tidak diizinkan.</td>
            </tr>
            <tr>
                <td>500</td>
                <td>Terjadi kesalahan pada server saat memproses permintaan.</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>6. Alur Kerja API</h2>
        <ol>
            <li>Aplikasi klien mengirim permintaan GET.</li>
            <li>Klien menyertakan Bearer Token pada header Authorization.</li>
            <li>Server menghitung hash token dan memeriksa database.</li>
            <li>Server memeriksa masa berlaku token dan status pengguna.</li>
            <li>Jika valid, server mengambil data dari tabel dosen.</li>
            <li>Server mengembalikan respons JSON kepada klien.</li>
        </ol>
    </div>

    <a href="dashboard.php" class="back">Kembali ke Dashboard</a>
</main>

<footer>
    SIMDOSEN &copy; <?= date('Y') ?> — Dokumentasi Web Service API
</footer>

</body>
</html>
