<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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
    protected $description = 'Membuat backup database, menyimpannya secara lokal, dan mengunggahnya ke Dropbox jika diaktifkan.';

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
                return 1; // Return non-zero on failure
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
            $this->info('Backup database lokal berhasil dibuat: ' . $backupFileName);

            // Upload to Dropbox if enabled
            if (config('filesystems.disks.dropbox.authorization_token') && env('DROPBOX_ENABLED', false)) {
                $this->info('Mengunggah backup ke Dropbox...');
                
                $remotePath = 'backups/' . $backupFileName;
                $fileContent = File::get($backupPath);

                $isUploaded = Storage::disk('dropbox')->put($remotePath, $fileContent);

                if ($isUploaded) {
                    $this->info('Upload ke Dropbox berhasil.');
                    // Optionally, delete the local backup file after successful upload
                    // File::delete($backupPath);
                    // $this->info('Backup lokal telah dihapus.');
                } else {
                    $this->error('Upload ke Dropbox gagal.');
                    return 1; // Return non-zero on failure
                }
            } else {
                $this->info('Upload ke Dropbox dilewati (tidak diaktifkan atau token tidak dikonfigurasi).');
            }

        } else {
            $this->error('Backup database gagal.');
            $this->error('Mariadump Error Output: ' . $process->errorOutput());
            return 1; // Return non-zero on failure
        }
        
        return 0; // Return zero on success
    }
}
