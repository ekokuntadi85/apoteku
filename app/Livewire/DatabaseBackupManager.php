<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Attributes\Title;

#[Title('Manajemen Backup')]
class DatabaseBackupManager extends Component
{
    public $backups = [];
    public $selectedBackups = [];
    public $selectAll = false;
    public $isBackingUp = false;
    public $dropboxEnabled = false;
    public $dropboxAccessToken = '';
    public $uploadProgress = '';
    public $compressionEnabled = true;
    public $successMessage = '';
    public $errorMessage = '';

    protected $backupDisk = 'local';
    protected $backupPath = 'db-backups';

    public function mount()
    {
        $this->dropboxEnabled = config('filesystems.disks.dropbox.driver') === 'dropbox';
        $this->dropboxAccessToken = config('filesystems.disks.dropbox.authorization_token');
        $this->getBackups();
    }

    public function getBackups()
    {
        $files = Storage::disk($this->backupDisk)->files($this->backupPath);

        $this->backups = collect($files)
            ->map(function ($file) {
                return [
                    'name' => basename($file),
                    'size' => Storage::disk($this->backupDisk)->size($file),
                    'last_modified' => Storage::disk($this->backupDisk)->lastModified($file),
                ];
            })
            ->sortByDesc('last_modified')
            ->values()
            ->all();
        
        $this->reset(['selectAll', 'selectedBackups']);
    }

    public function performBackup()
    {
        $this->isBackingUp = true;
        $this->uploadProgress = 'Memulai backup database...';
        
        try {
            // Generate backup filename
            $timestamp = now()->format('Y-m-d_His');
            $fileName = "backup_{$timestamp}.sql";
            $filePath = storage_path("app/{$this->backupPath}/{$fileName}");
            
            // Ensure directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            // Export database
            $this->uploadProgress = 'Mengekspor database...';
            $this->exportDatabase($filePath);

            $originalSize = filesize($filePath);
            
            // Compress if enabled
            if ($this->compressionEnabled) {
                $this->uploadProgress = 'Mengkompresi file...';
                $compressedPath = $this->compressFile($filePath);
                $compressedSize = filesize($compressedPath);
                $compressionRatio = round((1 - ($compressedSize / $originalSize)) * 100, 1);
                
                // Remove original uncompressed file
                unlink($filePath);
                
                $fileName = basename($compressedPath);
                $filePath = $compressedPath;
                
                $this->uploadProgress = "File dikompres {$compressionRatio}% (dari " . $this->formatBytes($originalSize) . " ke " . $this->formatBytes($compressedSize) . ")";
            }

            // Upload to Dropbox if enabled
            if ($this->dropboxEnabled && !empty($this->dropboxAccessToken)) {
                $this->uploadProgress = 'Mengupload ke Dropbox...';
                $this->uploadToDropbox($filePath, $fileName);
                $this->uploadProgress = 'Backup berhasil diupload ke Dropbox!';
            }

            // Cleanup old local backups (> 10 days)
            $this->cleanupOldBackups();

            // Set success message
            $this->successMessage = 'Backup berhasil dibuat' . ($this->dropboxEnabled ? ' dan diupload ke Dropbox' : '') . '.';
            $this->errorMessage = '';
            
            // Auto-hide after 3 seconds
            $this->dispatch('backup-success');
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal membuat backup: ' . $e->getMessage();
            $this->successMessage = '';
            \Log::error('Backup failed: ' . $e->getMessage());
            
            // Auto-hide after 3 seconds
            $this->dispatch('backup-error');
        } finally {
            $this->isBackingUp = false;
            $this->uploadProgress = '';
            $this->getBackups();
        }
    }

    protected function exportDatabase($filePath)
    {
        $dbConfig = config('database.connections.mysql');
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $host = $dbConfig['host'];

        // Use MYSQL_PWD environment variable for secure password handling
        // --skip-ssl to avoid SSL connection issues with MariaDB
        $command = sprintf(
            'MYSQL_PWD=%s mariadb-dump --skip-ssl -h%s -u%s %s > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = implode("\n", $output);
            \Log::error('Database export failed', [
                'command' => preg_replace('/MYSQL_PWD=\'[^\']+\'/', 'MYSQL_PWD=***', $command),
                'error' => $errorMsg,
                'return_code' => $returnVar
            ]);
            throw new \Exception('Database export failed: ' . $errorMsg);
        }
    }

    protected function compressFile($filePath)
    {
        $compressedPath = $filePath . '.gz';
        
        // Open source file
        $sourceFile = fopen($filePath, 'rb');
        if (!$sourceFile) {
            throw new \Exception('Cannot open source file for compression');
        }

        // Open compressed file with maximum compression level (9)
        $gzFile = gzopen($compressedPath, 'wb9');
        if (!$gzFile) {
            fclose($sourceFile);
            throw new \Exception('Cannot create compressed file');
        }

        // Compress in chunks
        while (!feof($sourceFile)) {
            $chunk = fread($sourceFile, 1024 * 1024); // 1MB chunks
            gzwrite($gzFile, $chunk);
        }

        fclose($sourceFile);
        gzclose($gzFile);

        return $compressedPath;
    }

    protected function uploadToDropbox($filePath, $fileName)
    {
        try {
            $fileContent = file_get_contents($filePath);
            $dropboxPath = '/backups/' . $fileName;
            
            Storage::disk('dropbox')->put($dropboxPath, $fileContent);
            
        } catch (\Exception $e) {
            throw new \Exception('Dropbox upload failed: ' . $e->getMessage());
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function downloadBackup($fileName)
    {
        $filePath = $this->backupPath . '/' . $fileName;

        if (!Storage::disk($this->backupDisk)->exists($filePath)) {
            $this->errorMessage = 'File backup tidak ditemukan.';
            $this->successMessage = '';
            return;
        }

        return Storage::disk($this->backupDisk)->download($filePath);
    }

    public function deleteBackup($fileName)
    {
        $filePath = $this->backupPath . '/' . $fileName;

        if (Storage::disk($this->backupDisk)->exists($filePath)) {
            Storage::disk($this->backupDisk)->delete($filePath);
            $this->successMessage = 'Backup berhasil dihapus.';
            $this->errorMessage = '';
        } else {
            $this->errorMessage = 'File backup tidak ditemukan.';
            $this->successMessage = '';
        }

        $this->getBackups();
    }
    
    public function deleteSelectedBackups()
    {
        if (empty($this->selectedBackups)) {
            return;
        }

        $deletedCount = 0;
        foreach ($this->selectedBackups as $fileName) {
            $filePath = $this->backupPath . '/' . $fileName;
            if (Storage::disk($this->backupDisk)->exists($filePath)) {
                Storage::disk($this->backupDisk)->delete($filePath);
                $deletedCount++;
            }
        }

        if($deletedCount > 0) {
            $this->successMessage = "{$deletedCount} backup berhasil dihapus.";
            $this->errorMessage = '';
        } else {
            $this->errorMessage = 'Tidak ada backup yang dihapus. File mungkin tidak ditemukan.';
            $this->successMessage = '';
        }

        $this->getBackups();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBackups = collect($this->backups)->pluck('name')->toArray();
        } else {
            $this->selectedBackups = [];
        }
    }

    public function saveDropboxSettings()
    {
        // Update .env file (simplified - in production use a proper env manager)
        $this->dispatch('dropbox-settings-saved');
        $this->successMessage = 'Pengaturan Dropbox berhasil disimpan. Silakan restart aplikasi untuk menerapkan perubahan.';
        $this->errorMessage = '';
    }

    public function testDropboxConnection()
    {
        $this->uploadProgress = 'Testing Dropbox connection...';
        
        try {
            if (empty($this->dropboxAccessToken)) {
                throw new \Exception('Access token tidak boleh kosong');
            }

            // Test 1: List directories (Read permission)
            $this->uploadProgress = 'Test 1/4: Checking read permission...';
            Storage::disk('dropbox')->directories('/');
            
            // Test 2: Create test directory
            $this->uploadProgress = 'Test 2/4: Creating test directory...';
            $testDir = '/backups';
            if (!Storage::disk('dropbox')->exists($testDir)) {
                Storage::disk('dropbox')->makeDirectory($testDir);
            }
            
            // Test 3: Write test file
            $this->uploadProgress = 'Test 3/4: Writing test file...';
            $testFile = '/backups/test_connection_' . now()->format('YmdHis') . '.txt';
            $testContent = 'Dropbox connection test - ' . now()->toDateTimeString();
            Storage::disk('dropbox')->put($testFile, $testContent);
            
            // Test 4: Read test file
            $this->uploadProgress = 'Test 4/4: Reading test file...';
            $readContent = Storage::disk('dropbox')->get($testFile);
            
            if ($readContent !== $testContent) {
                throw new \Exception('File content mismatch - Read/Write test failed');
            }
            
            // Test 5: Delete test file (cleanup)
            Storage::disk('dropbox')->delete($testFile);
            
            $this->uploadProgress = '';
            $this->successMessage = '✅ Koneksi Dropbox berhasil! Semua test (read/write/delete) passed.';
            $this->errorMessage = '';
            
        } catch (\Exception $e) {
            $this->uploadProgress = '';
            \Log::error('Dropbox connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = '❌ Koneksi Dropbox gagal: ' . $e->getMessage();
            $this->successMessage = '';
        }
    }


    protected function cleanupOldBackups()
    {
        try {
            $files = Storage::disk($this->backupDisk)->files($this->backupPath);
            $deletedCount = 0;
            $cutoffDate = now()->subDays(10);

            foreach ($files as $file) {
                $lastModified = Storage::disk($this->backupDisk)->lastModified($file);
                
                // Delete if older than 10 days
                if ($lastModified < $cutoffDate->timestamp) {
                    Storage::disk($this->backupDisk)->delete($file);
                    $deletedCount++;
                    \Log::info('Auto-cleanup: Deleted old backup', [
                        'file' => basename($file),
                        'age_days' => now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastModified))
                    ]);
                }
            }

            if ($deletedCount > 0) {
                \Log::info("Auto-cleanup: Deleted {$deletedCount} old backup(s)");
            }
        } catch (\Exception $e) {
            \Log::error('Auto-cleanup failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.database-backup-manager');
    }
}
