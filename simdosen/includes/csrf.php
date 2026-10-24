
<?php
/**
 * Perlindungan CSRF untuk SIMDOSEN
 * Kompatibel dengan PHP 7.2+
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Membuat token CSRF untuk formulir.
 */
if (!function_exists('buatTokenCSRF')) {
    function buatTokenCSRF($nama = 'csrf_token')
    {
        if (
            !isset($_SESSION[$nama]) ||
            !is_string($_SESSION[$nama]) ||
            strlen($_SESSION[$nama]) !== 64
        ) {
            $_SESSION[$nama] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$nama];
    }
}

/**
 * Menghasilkan input hidden untuk formulir.
 */
if (!function_exists('inputCSRF')) {
    function inputCSRF($nama = 'csrf_token')
    {
        $token = buatTokenCSRF($nama);

        return '<input type="hidden" name="' .
            htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') .
            '" value="' .
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8') .
            '">';
    }
}

/**
 * Memeriksa token CSRF yang dikirim melalui POST.
 */
if (!function_exists('verifikasiCSRF')) {
    function verifikasiCSRF($nama = 'csrf_token')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        if (
            !isset($_SESSION[$nama]) ||
            !isset($_POST[$nama]) ||
            !is_string($_SESSION[$nama]) ||
            !is_string($_POST[$nama])
        ) {
            return false;
        }

        return hash_equals(
            $_SESSION[$nama],
            $_POST[$nama]
        );
    }
}

/**
 * Menghentikan permintaan jika token tidak valid.
 */
if (!function_exists('wajibCSRF')) {
    function wajibCSRF($nama = 'csrf_token')
    {
        if (!verifikasiCSRF($nama)) {
            http_response_code(403);

            exit(
                'Permintaan ditolak karena token keamanan tidak valid. ' .
                'Silakan muat ulang halaman dan coba kembali.'
            );
        }
    }
}
