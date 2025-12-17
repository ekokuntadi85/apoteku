<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListDropboxFiles extends Command
{
    protected $signature = 'dropbox:list {path=/}';
    protected $description = 'List files and directories in Dropbox';

    public function handle()
    {
        try {
            $path = $this->argument('path');
            
            $this->info("📁 Listing Dropbox contents: {$path}");
            $this->newLine();

            // List directories
            $directories = Storage::disk('dropbox')->directories($path);
            if (count($directories) > 0) {
                $this->line('Directories:');
                foreach ($directories as $dir) {
                    $this->line('  📁 ' . $dir);
                }
                $this->newLine();
            }

            // List files
            $files = Storage::disk('dropbox')->files($path);
            if (count($files) > 0) {
                $this->line('Files:');
                foreach ($files as $file) {
                    $size = Storage::disk('dropbox')->size($file);
                    $this->line('  📄 ' . $file . ' (' . $this->formatBytes($size) . ')');
                }
            } else {
                $this->line('No files found.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
