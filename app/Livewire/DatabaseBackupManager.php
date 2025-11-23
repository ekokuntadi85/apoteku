<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Attributes\Title;

#[Title('Manajemen Backup')]
class DatabaseBackupManager extends Component
{
    public $backups = [];
    public $selectedBackups = [];
    public $selectAll = false;
    public $isBackingUp = false;

    protected $backupDisk = 'local';
    protected $backupPath = 'db-backups'; // Corrected path

    public function mount()
    {
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
        try {
            Artisan::call('db:backup');
            session()->flash('message', 'Backup berhasil dibuat.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat backup: ' . $e->getMessage());
        } finally {
            $this->isBackingUp = false;
            $this->getBackups();
        }
    }

    public function downloadBackup($fileName)
    {
        $filePath = $this->backupPath . '/' . $fileName;

        if (!Storage::disk($this->backupDisk)->exists($filePath)) {
            session()->flash('error', 'File backup tidak ditemukan.');
            return;
        }

        return Storage::disk($this->backupDisk)->download($filePath);
    }

    public function deleteBackup($fileName)
    {
        $filePath = $this->backupPath . '/' . $fileName;

        if (Storage::disk($this->backupDisk)->exists($filePath)) {
            Storage::disk($this->backupDisk)->delete($filePath);
            session()->flash('message', 'Backup berhasil dihapus.');
        } else {
            session()->flash('error', 'File backup tidak ditemukan.');
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
            session()->flash('message', "{$deletedCount} backup berhasil dihapus.");
        } else {
            session()->flash('error', 'Tidak ada backup yang dihapus. File mungkin tidak ditemukan.');
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

    public function render()
    {
        return view('livewire.database-backup-manager');
    }
}
