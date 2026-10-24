
<?php
/**
 * Konfigurasi Database SIMDOSEN
 * Kompatibel dengan PHP 7.2+
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', 'sql101.infinityfree.com');
    define('DB_NAME', 'if0_43196240_simdosen');
    define('DB_USER', 'if0_43196240');
    define('DB_PASS', 'umbBVlpns4Y');
}

if (!function_exists('getDBConnection')) {
    function getDBConnection()
    {
        static $koneksi = null;

        if ($koneksi instanceof PDO) {
            return $koneksi;
        }

        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname=' . DB_NAME
             . ';charset=utf8mb4';

        $opsi = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );

        $koneksi = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            $opsi
        );

        return $koneksi;
    }
}

$pdo = getDBConnection();
