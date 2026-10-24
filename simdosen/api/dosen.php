
<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');

    echo json_encode([
        'status' => false,
        'pesan' => 'Metode yang diizinkan hanya GET.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';

function kirimJSON(int $kode, array $data): void
{
    http_response_code($kode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// Token dikirim melalui header Authorization:
// Authorization: Bearer TOKEN_ANDA
$headerAuth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if ($headerAuth === '' && function_exists('getallheaders')) {
    $headers = getallheaders();

    foreach ($headers as $nama => $nilai) {
        if (strtolower($nama) === 'authorization') {
            $headerAuth = $nilai;
            break;
        }
    }
}

if (!preg_match('/^Bearer\s+([a-fA-F0-9]{64})$/i', trim($headerAuth), $cocok)) {
    kirimJSON(401, [
        'status' => false,
        'pesan' => 'Token API tidak ditemukan atau formatnya tidak valid.'
    ]);
}

$tokenAsli = $cocok[1];
$tokenHash = hash('sha256', $tokenAsli);

try {
    // Pastikan token terdaftar, belum kedaluwarsa,
    // dan pemilik token masih merupakan pengguna aktif.
    $stmt = $pdo->prepare(
        "SELECT t.id, t.pengguna_id
         FROM api_tokens t
         INNER JOIN pengguna p ON p.id = t.pengguna_id
         WHERE t.token_hash = :token_hash
           AND t.expires_at > NOW()
           AND p.aktif = 1
         LIMIT 1"
    );

    $stmt->execute(['token_hash' => $tokenHash]);
    $token = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$token) {
        kirimJSON(401, [
            'status' => false,
            'pesan' => 'Token tidak valid, kedaluwarsa, atau akun pemilik tidak aktif.'
        ]);
    }

    // Ambil data sesuai struktur tabel dosen yang diberikan.
    $stmtDosen = $pdo->query(
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
        program_studi,
        created_at,
        updated_at
     FROM dosen
     ORDER BY nama ASC"
);

    $dataDosen = $stmtDosen->fetchAll(PDO::FETCH_ASSOC);

    kirimJSON(200, [
        'status' => true,
        'pesan' => 'Data dosen berhasil diambil.',
        'jumlah' => count($dataDosen),
        'data' => $dataDosen
    ]);

} catch (Throwable $e) {
    error_log('API dosen gagal: ' . $e->getMessage());

    kirimJSON(500, [
        'status' => false,
        'pesan' => 'Terjadi kesalahan pada server API.'
    ]);
}
