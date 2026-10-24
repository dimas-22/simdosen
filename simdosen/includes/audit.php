
<?php
/**
 * Audit Log SIMDOSEN
 * Mencatat aktivitas pengguna ke tabel audit_log.
 */

if (!function_exists('catatAudit')) {
    function catatAudit(
        PDO $pdo,
        string $aktivitas,
        ?string $detail = null
    ): bool {
        try {
            $penggunaId = null;

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_SESSION['user_id'])) {
                $penggunaId = (int) $_SESSION['user_id'];
            }

            $ipAddress = isset($_SERVER['REMOTE_ADDR'])
                ? substr($_SERVER['REMOTE_ADDR'], 0, 45)
                : null;

            $sql = "INSERT INTO audit_log
                    (pengguna_id, aktivitas, detail, ip_address)
                    VALUES
                    (:pengguna_id, :aktivitas, :detail, :ip_address)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute(array(
                ':pengguna_id' => $penggunaId,
                ':aktivitas' => substr($aktivitas, 0, 100),
                ':detail' => $detail,
                ':ip_address' => $ipAddress
            ));

            return true;

        } catch (Throwable $e) {
            error_log(
                'Audit log SIMDOSEN gagal: ' . $e->getMessage()
            );

            return false;
        }
    }
}
