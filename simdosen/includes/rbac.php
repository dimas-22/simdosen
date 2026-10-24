
<?php
/**
 * RBAC - Role Based Access Control
 * SIMDOSEN Universitas Muhammadiyah Bengkulu
 */

require_once __DIR__ . '/auth.php';

/**
 * Daftar role yang tersedia.
 */
if (!function_exists('daftarRole')) {
    function daftarRole()
    {
        return array(
            'administrator',
            'operator',
            'pimpinan'
        );
    }
}

/**
 * Memeriksa apakah pengguna memiliki salah satu role.
 */
if (!function_exists('punyaRole')) {
    function punyaRole($roles)
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        }

        if (!isset($_SESSION['role'])) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = array($roles);
        }

        return in_array(
            $_SESSION['role'],
            $roles,
            true
        );
    }
}

/**
 * Memastikan pengguna memiliki role yang diizinkan.
 */
if (!function_exists('wajibRole')) {
    function wajibRole($roles)
    {
        wajibLogin();

        if (!punyaRole($roles)) {
            http_response_code(403);

            echo '<!DOCTYPE html>';
            echo '<html lang="id"><head>';
            echo '<meta charset="UTF-8">';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
            echo '<title>Akses Ditolak - SIMDOSEN</title>';
            echo '<style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f1f5f9;
                    text-align: center;
                    padding: 70px 20px;
                }
                .kotak {
                    background: white;
                    max-width: 500px;
                    margin: auto;
                    padding: 35px;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px #0001;
                }
                h1 { color: #dc2626; }
                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 11px 20px;
                    background: #164b91;
                    color: white;
                    text-decoration: none;
                    border-radius: 7px;
                }
            </style></head><body>';

            echo '<div class="kotak">';
            echo '<h1>Akses Ditolak</h1>';
            echo '<p>Anda tidak memiliki izin untuk membuka halaman ini.</p>';
            echo '<a href="dashboard.php">Kembali ke Dashboard</a>';
            echo '</div></body></html>';

            exit;
        }
    }
}

/**
 * Memeriksa izin berdasarkan nama hak akses.
 */
if (!function_exists('bolehAkses')) {
    function bolehAkses($fitur)
    {
        $aturan = array(
            'dashboard' => array(
                'administrator',
                'operator',
                'pimpinan'
            ),

            'lihat_dosen' => array(
                'administrator',
                'operator',
                'pimpinan'
            ),

            'tambah_dosen' => array(
                'administrator',
                'operator'
            ),

            'edit_dosen' => array(
                'administrator',
                'operator'
            ),

            'hapus_dosen' => array(
                'administrator',
                'operator'
            ),

            'laporan' => array(
                'administrator',
                'operator',
                'pimpinan'
            ),

            'kelola_pengguna' => array(
                'administrator'
            ),

            'audit_log' => array(
                'administrator'
            ),

            'kelola_api' => array(
                'administrator'
            )
        );

        if (!isset($aturan[$fitur])) {
            return false;
        }

        return punyaRole($aturan[$fitur]);
    }
}
