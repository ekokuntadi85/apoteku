<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\DatabaseBackupManager;
use Illuminate\Support\Facades\Storage;

class AutoBackupDatabase extends Command
{
    protected $signature = 'backup:auto';
    protected $description = 'Automatically backup database with compression and Dropbox upload';

    public function handle()
    {
        $this->info('🔄 Starting automatic database backup...');
        $this->newLine();

        try {
            $backupPath = 'db-backups';
            $timestamp = now()->format('Y-m-d_His');
            $fileName = "backup_{$timestamp}.sql";
            $filePath = storage_path("app/{$backupPath}/{$fileName}");
            
            // Ensure directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            // 1. Export database
            $this->info('📤 Exporting database...');
            $this->exportDatabase($filePath);
            $originalSize = filesize($filePath);
            $this->line("  ✓ Database exported: " . $this->formatBytes($originalSize));

            // 2. Compress
            $this->info('🗜️  Compressing file...');
            $compressedPath = $this->compressFile($filePath);
            $compressedSize = filesize($compressedPath);
            $compressionRatio = round((1 - ($compressedSize / $originalSize)) * 100, 1);
            unlink($filePath); // Remove uncompressed
            
            $fileName = basename($compressedPath);
            $this->line("  ✓ Compressed {$compressionRatio}%: " . $this->formatBytes($compressedSize));

            // 3. Upload to Dropbox if enabled
            if (env('DROPBOX_ENABLED', false) && !empty(env('DROPBOX_ACCESS_TOKEN'))) {
                $this->info('☁️  Uploading to Dropbox...');
                $fileContent = file_get_contents($compressedPath);
                $dropboxPath = '/backups/' . $fileName;
                Storage::disk('dropbox')->put($dropboxPath, $fileContent);
                $this->line("  ✓ Uploaded to Dropbox: {$dropboxPath}");
            }

            // 4. Cleanup old backups (> 10 days)
            $this->info('🧹 Cleaning up old backups...');
            $deletedCount = $this->cleanupOldBackups($backupPath);
            if ($deletedCount > 0) {
                $this->line("  ✓ Deleted {$deletedCount} old backup(s)");
            } else {
                $this->line("  ✓ No old backups to delete");
            }

            $this->newLine();
            $this->info('✅ Backup completed successfully!');
            $this->line("   File: {$fileName}");
            $this->line("   Size: " . $this->formatBytes($compressedSize));
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            \Log::error('Auto-backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function exportDatabase($filePath)
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', 'db');

        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --skip-ssl -h%s -u%s %s > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Database export failed: ' . implode("\n", $output));
        }
    }

    protected function compressFile($filePath)
    {
        $gzFile = $filePath . '.gz';
        
        $fp = gzopen($gzFile, 'wb9');
        if (!$fp) {
            throw new \Exception('Cannot create compressed file');
        }

        $sourceFile = fopen($filePath, 'rb');
        while (!feof($sourceFile)) {
            gzwrite($fp, fread($sourceFile, 1024 * 1024));
        }

        fclose($sourceFile);
        gzclose($fp);

        return $gzFile;
    }

    protected function cleanupOldBackups($backupPath)
    {
        $files = Storage::disk('local')->files($backupPath);
        $deletedCount = 0;
        $cutoffDate = now()->subDays(10);

        foreach ($files as $file) {
            $lastModified = Storage::disk('local')->lastModified($file);
            
            if ($lastModified < $cutoffDate->timestamp) {
                Storage::disk('local')->delete($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
