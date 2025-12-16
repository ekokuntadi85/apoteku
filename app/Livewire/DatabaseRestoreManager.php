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
                    // AUTOMATIC: Smart Migration Detection
                    $this->restoreLog .= "Mendeteksi migration yang sudah ada...\n";
                    
                    $migrationPath = database_path('migrations');
                    $migrationFiles = glob($migrationPath . '/*.php');
                    
                    $markedCount = 0;
                    foreach ($migrationFiles as $file) {
                        $migrationName = basename($file, '.php');
                        $tableName = $this->getTableNameFromMigration($migrationName);
                        
                        $shouldMark = false;

                        if ($tableName && \Schema::hasTable($tableName)) {
                            // Case 1: Create Table Migration
                            if (str_contains($migrationName, '_create_')) {
                                $shouldMark = true;
                            }
                            // Case 2: Add Column Migration - Check if columns exist by inspecting file
                            elseif (str_contains($migrationName, '_add_') || str_contains($migrationName, '_to_')) {
                                $columns = $this->getColumnsFromMigrationFile($file);
                                if (!empty($columns)) {
                                    // Robustness Check: If ANY of the columns defined in the file already exist in the table,
                                    // we assume this migration (or at least this part of it) has already run.
                                    // We cannot safely run it again without crashing on "Duplicate column".
                                    $anyColumnExists = false;
                                    foreach ($columns as $column) {
                                        if (\Schema::hasColumn($tableName, $column)) {
                                            $anyColumnExists = true;
                                            break;
                                        }
                                    }
                                    
                                    if ($anyColumnExists) {
                                        $shouldMark = true;
                                    }
                                } else {
                                    // Fallback: If no columns detected (e.g. complex migration), check if we can infer from filename
                                    $columnName = $this->getColumnNameFromMigration($migrationName);
                                    if ($columnName && \Schema::hasColumn($tableName, $columnName)) {
                                        $shouldMark = true;
                                    }
                                }
                            }
                        }

                        if ($shouldMark) {
                            // Check if migration record exists
                            $exists = \DB::table('migrations')
                                ->where('migration', $migrationName)
                                ->exists();
                            
                            if (!$exists) {
                                \DB::table('migrations')->insert([
                                    'migration' => $migrationName,
                                    'batch' => 1
                                ]);
                                $this->restoreLog .= "  ✓ {$migrationName} (detected)\n";
                                $markedCount++;
                            } else {
                                $this->restoreLog .= "  • {$migrationName} (already recorded)\n";
                            }
                        } else {
                            // Debug logging for failed detection
                            $reason = "Unknown";
                            if (!$tableName) $reason = "TableName not found";
                            elseif (!\Schema::hasTable($tableName)) $reason = "Table {$tableName} missing";
                            elseif (isset($columns) && empty($columns)) $reason = "No columns found in file";
                            elseif (isset($allColumnsExist) && !$allColumnsExist) $reason = "Column check failed";
                            
                            $this->restoreLog .= "  - {$migrationName} skipped ({$reason})\n";
                            if (isset($columns) && !empty($columns)) {
                                $this->restoreLog .= "    Detected columns: " . implode(', ', $columns) . "\n";
                                $this->restoreLog .= "    Target table: {$tableName}\n";
                            }
                        }
                    }
                    
                    if ($markedCount > 0) {
                        $this->restoreLog .= "\n{$markedCount} migration ditandai sebagai sudah dijalankan.\n";
                    } else {
                        $this->restoreLog .= "Semua migration yang relevan sudah tercatat.\n";
                    }
                    
                    // Now run remaining migrations (new ones)
                    $this->restoreLog .= "\nMenjalankan migration baru...\n";
                    try {
                        \Artisan::call('migrate', ['--force' => true]);
                        $migrationOutput = \Artisan::output();
                        
                        // Only show output if there were actual migrations
                        if (trim($migrationOutput) && !str_contains($migrationOutput, 'Nothing to migrate')) {
                            $this->restoreLog .= $migrationOutput;
                        } else {
                            $this->restoreLog .= "✓ Tidak ada migration baru yang perlu dijalankan.\n";
                        }
                    } catch (\Exception $e) {
                         // Catch here to show partial output
                         $this->restoreLog .= "\nERROR executing migrate: " . $e->getMessage() . "\n";
                         throw $e;
                    }
                    
                    $this->restoreLog .= "\n✅ MIGRATION SELESAI!\n";

                    // Validate Finance Module Data
                    $this->validateFinanceModule();
                    
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
        // Pattern: create_TABLENAME_table or create_TABLENAME_tables
        if (preg_match('/create_(.+)_tables?$/', $migration, $matches)) {
            return $matches[1];
        }
        
        // Pattern: add_..._to_TABLENAME_and_... (Complex limit)
        if (preg_match('/to_(.+?)_and_/', $migration, $matches)) {
             return $matches[1];
        }

        // Pattern: add_..._to_TABLENAME_table (optional _table suffix)
        // We use non-greedy matching for the table name to handle the optional suffix correctly if present
        if (preg_match('/to_(.+?)(?:_table)?$/', $migration, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getColumnNameFromMigration($migration)
    {
        // Pattern: add_COLUMN_to_TABLENAME_table
        if (preg_match('/add_(.+?)_to_/', $migration, $matches)) {
            // If there are multiple columns (e.g. col1_and_col2), just taking the first part usually works for a "hasColumn" check 
            // if we assume checking one is enough evidence.
            $parts = explode('_and_', $matches[1]);
            return $parts[0];
        }
        return null;
    }

    private function getColumnsFromMigrationFile($filePath)
    {
        $content = file_get_contents($filePath);
        $columns = [];

        // Match $table->method('column_name')
        // We capture the method name (Group 1) and the column name (Group 2)
        if (preg_match_all('/\$table->([a-zA-Z]+)\([\'"](.+?)[\'"]/', $content, $matches, PREG_SET_ORDER)) {
            $ignoredMethods = [
                'index', 'unique', 'foreign', 'primary', 'spatialIndex',
                'dropColumn', 'dropSoftDeletes', 'dropMorphs', 'dropRememberToken',
                'renameColumn', 'after', 'comment', 'change'
            ];

            foreach ($matches as $match) {
                $method = $match[1];
                $column = $match[2];

                if (!in_array($method, $ignoredMethods)) {
                    $columns[] = $column;
                }
            }
        }

        return $columns;
    }

    private function validateFinanceModule()
    {
        $this->restoreLog .= "\n=== VALIDASI MODUL KEUANGAN ===\n";
        
        try {
            // Check Accounts
            if (\Schema::hasTable('accounts')) {
                $count = \DB::table('accounts')->count();
                if ($count === 0) {
                    $this->restoreLog .= "⚠️ Tabel Accounts kosong. Menjalankan Seeder...\n";
                    $beeder = new \Database\Seeders\AccountSeeder();
                    $beeder->run();
                    $this->restoreLog .= "✅ Default Chart of Accounts berhasil dibuat.\n";
                } else {
                    $this->restoreLog .= "✅ Tabel Accounts valid ({$count} akun ditemukan).\n";
                }
            } else {
                $this->restoreLog .= "❌ Tabel Accounts TIDAK DITEMUKAN! Migration mungkin gagal.\n";
            }

            // Check Expense Categories
            if (\Schema::hasTable('expense_categories')) {
                 $this->restoreLog .= "✅ Tabel Expense Categories valid.\n";
            }
            
            // Check Journal Entries
            if (\Schema::hasTable('journal_entries')) {
                 $entryCount = \DB::table('journal_entries')->count();
                 $transactionCount = \DB::table('transactions')->where('type', 'POS')->count();
                 $purchaseCount = \DB::table('purchases')->count();
                 $expenseCount = \Schema::hasTable('expenses') ? \DB::table('expenses')->count() : 0;
                 
                 $this->restoreLog .= "✅ Tabel Journal Entries valid ({$entryCount} entri).\n";
                 $this->restoreLog .= "   Data: {$transactionCount} transaksi, {$purchaseCount} pembelian, {$expenseCount} pengeluaran.\n";
                 
                 // Smart Check: If we have data but NO or FEW Journals
                 $totalDataCount = $transactionCount + $purchaseCount + $expenseCount;
                 
                 if ($totalDataCount > 0 && $entryCount < ($totalDataCount * 0.5)) {
                     $this->restoreLog .= "⚠️ Terdeteksi data tanpa jurnal lengkap (Backup lama)...\n";
                     $this->restoreLog .= "   📌 PENTING: Silakan buka menu 'Sinkronisasi Jurnal' untuk sync data.\n";
                     $this->restoreLog .= "   Menu: Keuangan > Sinkronisasi Jurnal\n";
                     $this->restoreLog .= "   Atau jalankan manual: php artisan finance:sync-historical-journals\n\n";
                     
                     // AUTO-SYNC DISABLED untuk mempercepat restore
                     // User bisa sync manual via halaman Journal Sync Manager
                     /*
                     try {
                         \Artisan::call('finance:sync-historical-journals');
                         $output = \Artisan::output();
                         $this->restoreLog .= $output;
                         $this->restoreLog .= "✅ Sinkronisasi Selesai!\n";
                         $newEntryCount = \DB::table('journal_entries')->count();
                         $this->restoreLog .= "   Journal entries sekarang: {$newEntryCount}\n";
                     } catch (\Exception $syncError) {
                         $this->restoreLog .= "❌ Error saat sync: " . $syncError->getMessage() . "\n";
                         $this->restoreLog .= "   Silakan jalankan manual: php artisan finance:sync-historical-journals\n";
                     }
                     */
                 } else {
                     $this->restoreLog .= "✅ Journal coverage baik ({$entryCount} dari ~" . ($totalDataCount * 2) . " expected).\n";
                 }
            }

        } catch (\Exception $e) {
            $this->restoreLog .= "❌ Error saat validasi keuangan: " . $e->getMessage() . "\n";
        }
    }

    public function render()
    {
        return view('livewire.database-restore-manager');
    }
}
