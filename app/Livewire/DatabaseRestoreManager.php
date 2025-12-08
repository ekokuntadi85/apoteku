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
                
                // IMPORTANT: Run migrations to update schema
                $this->restoreLog .= "\n=== MENJALANKAN MIGRATION ===\n";
                $this->restoreLog .= "Mengupdate struktur database...\n";
                
                try {
                    \Artisan::call('migrate', ['--force' => true]);
                    $migrationOutput = \Artisan::output();
                    $this->restoreLog .= $migrationOutput;
                    $this->restoreLog .= "\nMIGRATION SELESAI!\n";
                    
                    session()->flash('message', 'Database berhasil di-restore dan schema telah diupdate.');
                } catch (\Exception $e) {
                    $this->restoreLog .= "\nWARNING: Migration gagal - " . $e->getMessage() . "\n";
                    $this->restoreLog .= "Anda mungkin perlu menjalankan 'php artisan migrate' secara manual.\n";
                    session()->flash('message', 'Database berhasil di-restore, tapi migration gagal. Jalankan migration manual.');
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

    public function render()
    {
        return view('livewire.database-restore-manager');
    }
}
