<?php
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

try {
    logAction($_SESSION['user_id'], 'database_backup');

    // Get database config
    $database = new Database();
    $dbConfig = $database->getConfig();

    $backupFile = sys_get_temp_dir() . '/tracetrack_backup_' . date('Y-m-d_H-i-s') . '.sql';

    $command = sprintf(
        'mysqldump --user=%s --password=%s --host=%s %s > %s',
        escapeshellarg($dbConfig['user']),
        escapeshellarg($dbConfig['password']),
        escapeshellarg($dbConfig['host']),
        escapeshellarg($dbConfig['db_name']),
        escapeshellarg($backupFile)
    );

    $output = null;
    $resultCode = 0;
    exec($command, $output, $resultCode);

    if ($resultCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
        header('Content-Length: ' . filesize($backupFile));
        readfile($backupFile);
        unlink($backupFile);
        exit;
    } else {
        die("Backup failed. Check database credentials in config/database.php");
    }
} catch (Throwable $e) {
    die("Backup error: " . $e->getMessage());
}
