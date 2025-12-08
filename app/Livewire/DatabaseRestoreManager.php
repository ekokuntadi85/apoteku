<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

#[Title('Restore Database')]
class DatabaseRestoreManager extends Component
{
    use WithFileUploads;

    public $sqlFile;
    public $isRestoring = false;
    public $restoreLog = '';

    public function restoreDatabase()
    {
        $this->validate([
                        'sqlFile' => 'required|file|mimes:sql,txt,bin', // 'bin' for application/octet-stream
        ]);

        $this->isRestoring = true;
        $this->restoreLog = "Memulai proses restore...\n";

        try {
            // Simpan file yang di-upload ke direktori sementara yang aman
            $tempPath = $this->sqlFile->store('temp_restores');
            $absoluteTempPath = Storage::path($tempPath);
            $this->restoreLog .= "File backup disimpan sementara di: {$absoluteTempPath}\n";

            // Ambil konfigurasi database
            $dbConfig = config('database.connections.mysql');
            $host = $dbConfig['host'];
            $database = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];

            $this->restoreLog .= "Menjalankan perintah mysql untuk restore...\n";

            // Bangun dan jalankan perintah mysql
            $command = sprintf(
                'mariadb -h%s -u%s -p%s --skip-ssl %s < %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($absoluteTempPath)
            );

            $process = Process::run($command);

            if ($process->successful()) {
                $this->restoreLog .= "\nPROSES RESTORE BERHASIL!\n";
                
                // IMPORTANT: Smart migration - mark existing migrations as ran, then run new ones
                $this->restoreLog .= "\n=== MENJALANKAN MIGRATION ===\n";
                $this->restoreLog .= "Memeriksa dan mengupdate struktur database...\n";
                
                try {
                    // Get list of migrations that should exist based on backup
                    $existingMigrations = [
                        '2025_08_11_120000_create_transaction_detail_batches_table',
                        '2025_08_17_195259_create_kartu_monitoring_suhus_table',
                        '2025_11_14_111630_create_settings_table',
                    ];
                    
                    // Mark existing migrations as ran if tables exist
                    foreach ($existingMigrations as $migration) {
                        $tableName = $this->getTableNameFromMigration($migration);
                        if ($tableName && \Schema::hasTable($tableName)) {
                            // Check if migration record exists
                            $exists = \DB::table('migrations')
                                ->where('migration', $migration)
                                ->exists();
                            
                            if (!$exists) {
                                \DB::table('migrations')->insert([
                                    'migration' => $migration,
                                    'batch' => 1
                                ]);
                                $this->restoreLog .= "✓ Marked '{$migration}' as ran (table exists)\n";
                            }
                        }
                    }
                    
                    // Now run remaining migrations (new ones)
                    $this->restoreLog .= "\nMenjalankan migration baru...\n";
                    \Artisan::call('migrate', ['--force' => true]);
                    $migrationOutput = \Artisan::output();
                    
                    // Only show output if there were actual migrations
                    if (trim($migrationOutput) && !str_contains($migrationOutput, 'Nothing to migrate')) {
                        $this->restoreLog .= $migrationOutput;
                    } else {
                        $this->restoreLog .= "✓ Tidak ada migration baru yang perlu dijalankan.\n";
                    }
                    
                    $this->restoreLog .= "\n✅ MIGRATION SELESAI!\n";
                    
                    session()->flash('message', 'Database berhasil di-restore dan schema telah diupdate.');
                } catch (\Exception $e) {
                    $this->restoreLog .= "\nWARNING: Migration gagal - " . $e->getMessage() . "\n";
                    $this->restoreLog .= "Database sudah di-restore, tapi ada masalah dengan migration.\n";
                    session()->flash('message', 'Database berhasil di-restore. Cek log untuk detail migration.');
                }
            } else {
                $this->restoreLog .= "\nPROSES RESTORE GAGAL!\n";
                $this->restoreLog .= "Error Output: " . $process->errorOutput();
                session()->flash('error', 'Gagal me-restore database. Lihat log untuk detail.');
            }

            // Hapus file sementara
            Storage::delete($tempPath);
            $this->restoreLog .= "\nFile backup sementara telah dihapus.";

        } catch (\Exception $e) {
            $this->restoreLog .= "\nTerjadi exception: " . $e->getMessage();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        } finally {
            $this->isRestoring = false;
            // Reset file input
            $this->reset('sqlFile');
        }
    }
    
    /**
     * Extract table name from migration filename
     * e.g., "2025_08_11_120000_create_transaction_detail_batches_table" -> "transaction_detail_batches"
     */
    private function getTableNameFromMigration($migration)
    {
        // Pattern: create_TABLENAME_table or add_COLUMN_to_TABLENAME_table
        if (preg_match('/create_(.+)_table$/', $migration, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/to_(.+)_table$/', $migration, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    public function render()
    {
        return view('livewire.database-restore-manager');
    }
}
