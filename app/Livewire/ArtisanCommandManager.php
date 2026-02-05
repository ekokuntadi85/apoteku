<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Title;

#[Title('Artisan Command Manager')]
class ArtisanCommandManager extends Component
{
    public $commandOutput = '';
    public $isRunning = false;
    public $lastCommand = '';
    public $commandHistory = [];

    // Common artisan commands grouped by category
    public $commands = [
        'Cache Management' => [
            ['name' => 'Clear Config Cache', 'command' => 'config:clear', 'icon' => '🔄', 'description' => 'Clear configuration cache'],
            ['name' => 'Cache Config', 'command' => 'config:cache', 'icon' => '💾', 'description' => 'Cache configuration files'],
            ['name' => 'Clear Route Cache', 'command' => 'route:clear', 'icon' => '🔄', 'description' => 'Clear route cache'],
            ['name' => 'Cache Routes', 'command' => 'route:cache', 'icon' => '💾', 'description' => 'Cache application routes'],
            ['name' => 'Clear View Cache', 'command' => 'view:clear', 'icon' => '🔄', 'description' => 'Clear compiled views'],
            ['name' => 'Cache Views', 'command' => 'view:cache', 'icon' => '💾', 'description' => 'Compile all views'],
            ['name' => 'Clear All Cache', 'command' => 'cache:clear', 'icon' => '🧹', 'description' => 'Clear application cache'],
            ['name' => 'Clear Compiled', 'command' => 'clear-compiled', 'icon' => '🧹', 'description' => 'Remove compiled class file'],
        ],
        'Optimization' => [
            ['name' => 'Optimize Application', 'command' => 'optimize', 'icon' => '⚡', 'description' => 'Cache config, routes, and views'],
            ['name' => 'Clear Optimizations', 'command' => 'optimize:clear', 'icon' => '🔄', 'description' => 'Clear all cached optimizations'],
        ],
        'Database' => [
            ['name' => 'Run Migrations', 'command' => 'migrate', 'icon' => '🗄️', 'description' => 'Run database migrations'],
            ['name' => 'Rollback Migration', 'command' => 'migrate:rollback', 'icon' => '↩️', 'description' => 'Rollback last migration', 'dangerous' => true],
            ['name' => 'Refresh Database', 'command' => 'migrate:refresh', 'icon' => '🔄', 'description' => 'Reset and re-run migrations', 'dangerous' => true],
            ['name' => 'Seed Database', 'command' => 'db:seed', 'icon' => '🌱', 'description' => 'Seed the database'],
        ],
        'Queue & Jobs' => [
            ['name' => 'Restart Queue', 'command' => 'queue:restart', 'icon' => '🔄', 'description' => 'Restart queue worker daemons'],
            ['name' => 'Clear Failed Jobs', 'command' => 'queue:flush', 'icon' => '🧹', 'description' => 'Delete all failed queue jobs'],
        ],
        'Storage' => [
            ['name' => 'Create Storage Link', 'command' => 'storage:link', 'icon' => '🔗', 'description' => 'Create symbolic link from public/storage to storage/app/public'],
        ],
        'Maintenance' => [
            ['name' => 'Down (Maintenance)', 'command' => 'down', 'icon' => '🔒', 'description' => 'Put application in maintenance mode', 'dangerous' => true],
            ['name' => 'Up (Live)', 'command' => 'up', 'icon' => '✅', 'description' => 'Bring application out of maintenance mode'],
        ],
        'Business Analytics' => [
            ['name' => 'Analyze Purchase Cycle', 'command' => 'app:analyze-purchase-cycle', 'icon' => '📊', 'description' => 'Analyze sales and inventory to recommend sustainable purchasing cycle and budget'],
            ['name' => 'Generate Purchase Recommendation', 'command' => 'app:generate-purchase-recommendation', 'icon' => '🎯', 'description' => 'Generate prioritized purchase recommendation based on sales velocity and stock'],
            ['name' => 'Top Selling Products Report', 'command' => 'app:report-top-selling-products', 'icon' => '🏆', 'description' => 'Analyze top 100 best-selling products and identify low stock items'],
        ],
        'Purchase Management' => [
            ['name' => 'Update Unpaid Purchases', 'command' => 'purchases:update-unpaid --force', 'icon' => '💰', 'description' => 'Update all unpaid purchases to paid status', 'dangerous' => true],
        ],
        'Database Backup' => [
            ['name' => 'Backup Database', 'command' => 'db:backup', 'icon' => '💾', 'description' => 'Create database backup and save to storage/app/db-backups'],
        ],
    ];

    public function mount()
    {
        // Load command history from session
        $this->commandHistory = session()->get('artisan_command_history', []);
    }

    public function runCommand($command, $commandName)
    {
        $this->isRunning = true;
        $this->lastCommand = $commandName;
        $this->commandOutput = '';

        try {
            // Capture the output
            Artisan::call($command);
            $output = Artisan::output();

            $this->commandOutput = $output ?: "✅ Command executed successfully with no output.";
            
            // Add to history
            $this->addToHistory($commandName, $command, 'success');

        } catch (\Exception $e) {
            $this->commandOutput = "❌ Error: " . $e->getMessage();
            $this->addToHistory($commandName, $command, 'error');
        }

        $this->isRunning = false;
    }

    private function addToHistory($name, $command, $status)
    {
        $historyItem = [
            'name' => $name,
            'command' => $command,
            'status' => $status,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        // Add to beginning of array
        array_unshift($this->commandHistory, $historyItem);

        // Keep only last 10 commands
        $this->commandHistory = array_slice($this->commandHistory, 0, 10);

        // Save to session
        session()->put('artisan_command_history', $this->commandHistory);
    }

    public function clearOutput()
    {
        $this->commandOutput = '';
        $this->lastCommand = '';
    }

    public function clearHistory()
    {
        $this->commandHistory = [];
        session()->forget('artisan_command_history');
    }

    public function render()
    {
        return view('livewire.artisan-command-manager');
    }
}
