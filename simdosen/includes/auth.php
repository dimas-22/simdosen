
<?php
/**
 * Autentikasi SIMDOSEN
 * Mengelola session dan pemeriksaan login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memastikan pengguna sudah login.
 */
if (!function_exists('wajibLogin')) {
    function wajibLogin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}

/**
 * Mengambil data pengguna yang sedang login.
 */
if (!function_exists('penggunaLogin')) {
    function penggunaLogin()
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return array(
            'id'       => $_SESSION['user_id'],
            'nama'     => isset($_SESSION['nama'])
                ? $_SESSION['nama'] : '',
            'username' => isset($_SESSION['username'])
                ? $_SESSION['username'] : '',
            'role'     => isset($_SESSION['role'])
                ? $_SESSION['role'] : 'operator'
        );
    }
}

/**
 * Memastikan pengguna sudah login dan mengambil data akunnya.
 */
if (!function_exists('cekLogin')) {
    function cekLogin()
    {
        wajibLogin();
        return penggunaLogin();
    }
}

/**
 * Mengakhiri session pengguna.
 */
if (!function_exists('keluarAplikasi')) {
    function keluarAplikasi()
    {
        $_SESSION = array();

        if (ini_get('session.use_cookies')) {
            $parameter = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parameter['path'],
                $parameter['domain'],
                $parameter['secure'],
                $parameter['httponly']
            );
        }

        session_destroy();
    }
}
