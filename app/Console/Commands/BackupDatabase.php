<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat backup database dan menyimpannya secara lokal di storage/app/db-backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $databaseConfig = config('database.connections.mysql');

        $host = $databaseConfig['host'];
        $database = $databaseConfig['database'];
        $username = $databaseConfig['username'];
        $password = $databaseConfig['password'];

        $backupDir = storage_path('app/db-backups');
        $backupFileName = 'backup-' . $database . '-' . now()->format('YmdHis') . '.sql';
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $backupFileName;

        // Ensure the backups directory exists and is writable
        if (!file_exists($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                $this->error('Gagal membuat direktori backup: ' . $backupDir);
                $this->error('Pastikan direktori storage/app memiliki izin tulis.');
                return;
            }
        }

        $command = sprintf(
            'mariadb-dump -h%s -u%s -p%s --skip-ssl %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        $process = Process::run($command);

        if ($process->successful()) {
            $this->info('Backup database berhasil dibuat: ' . $backupFileName);
        } else {
            $this->error('Backup database gagal.');
        }
        // Debugging: Always log output
        $this->info('Mariadump Standard Output: ' . $process->output());
        $this->error('Mariadump Error Output: ' . $process->errorOutput());
    }
}
