<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestDropboxConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropbox:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Dropbox connection with read/write operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Dropbox Connection...');
        $this->newLine();

        try {
            // Check if Dropbox is enabled
            if (!env('DROPBOX_ENABLED', false)) {
                $this->error('❌ Dropbox is not enabled. Set DROPBOX_ENABLED=true in .env');
                return 1;
            }

            if (empty(env('DROPBOX_ACCESS_TOKEN'))) {
                $this->error('❌ Dropbox access token is not set. Set DROPBOX_ACCESS_TOKEN in .env');
                return 1;
            }

            // Test 1: List directories (Read permission)
            $this->info('Test 1/5: Checking read permission...');
            $directories = Storage::disk('dropbox')->directories('/');
            $this->line('  ✓ Read permission OK');
            $this->line('  Found ' . count($directories) . ' directories');
            $this->newLine();

            // Test 2: Create test directory
            $this->info('Test 2/5: Creating test directory...');
            $testDir = '/backups';
            if (!Storage::disk('dropbox')->exists($testDir)) {
                Storage::disk('dropbox')->makeDirectory($testDir);
                $this->line('  ✓ Directory created: ' . $testDir);
            } else {
                $this->line('  ✓ Directory already exists: ' . $testDir);
            }
            $this->newLine();

            // Test 3: Write test file
            $this->info('Test 3/5: Writing test file...');
            $testFile = '/backups/test_connection_' . now()->format('YmdHis') . '.txt';
            $testContent = 'Dropbox connection test - ' . now()->toDateTimeString() . PHP_EOL;
            $testContent .= 'Server: ' . gethostname() . PHP_EOL;
            $testContent .= 'PHP Version: ' . PHP_VERSION . PHP_EOL;
            
            Storage::disk('dropbox')->put($testFile, $testContent);
            $this->line('  ✓ File written: ' . $testFile);
            $this->line('  Size: ' . strlen($testContent) . ' bytes');
            $this->newLine();

            // Test 4: Read test file
            $this->info('Test 4/5: Reading test file...');
            $readContent = Storage::disk('dropbox')->get($testFile);
            
            if ($readContent === $testContent) {
                $this->line('  ✓ File read successfully');
                $this->line('  Content matches!');
            } else {
                $this->warn('  ⚠ Content mismatch detected');
                $this->line('  Written length: ' . strlen($testContent) . ' bytes');
                $this->line('  Read length: ' . strlen($readContent) . ' bytes');
                $this->line('  Written: ' . bin2hex(substr($testContent, 0, 50)));
                $this->line('  Read: ' . bin2hex(substr($readContent, 0, 50)));
                
                // Try trimming whitespace
                if (trim($readContent) === trim($testContent)) {
                    $this->line('  ✓ Content matches after trimming whitespace');
                } else {
                    throw new \Exception('File content mismatch - Read/Write test failed');
                }
            }
            $this->newLine();

            // Test 5: Delete test file (cleanup)
            $this->info('Test 5/5: Deleting test file (cleanup)...');
            Storage::disk('dropbox')->delete($testFile);
            $this->line('  ✓ File deleted successfully');
            $this->newLine();

            // Summary
            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info('✅ All tests passed successfully!');
            $this->info('═══════════════════════════════════════');
            $this->newLine();
            $this->line('Dropbox connection is working correctly.');
            $this->line('You can now use Dropbox for database backups.');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('═══════════════════════════════════════');
            $this->error('❌ Test Failed!');
            $this->error('═══════════════════════════════════════');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            if ($this->option('verbose')) {
                $this->line('Stack trace:');
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }
}
