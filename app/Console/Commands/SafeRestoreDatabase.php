<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SafeRestoreDatabase extends Command
{
    protected $signature = 'db:safe-restore {file : SQL backup file path}';
    protected $description = 'Safely restore database and run pending migrations';

    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("🔄 Starting safe database restore...");
        $this->newLine();

        // 1. Confirm action
        if (!$this->confirm('This will DROP all tables and restore from backup. Continue?', false)) {
            $this->warn('Restore cancelled.');
            return 0;
        }

        try {
            // 2. Restore SQL
            $this->info("Step 1: Restoring SQL backup...");
            $this->restoreSQL($file);
            $this->info("✅ SQL restored successfully");
            $this->newLine();

            // 3. Run migrations (to add new columns)
            $this->info("Step 2: Running pending migrations...");
            Artisan::call('migrate', ['--force' => true]);
            $this->line(Artisan::output());
            $this->info("✅ Migrations completed");
            $this->newLine();

            // 4. Verify critical tables
            $this->info("Step 3: Verifying schema...");
            $this->verifySchema();
            $this->newLine();

            $this->info("✅ Database restored successfully!");
            $this->info("You can now use the application with all new features.");

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to restore database: {$e->getMessage()}");
            return 1;
        }
    }

    private function restoreSQL(string $file)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Drop all tables first
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Restore from SQL file
        if (str_ends_with($file, '.gz')) {
            // Gunzip first
            $sqlFile = str_replace('.gz', '', $file);
            exec("gunzip -c {$file} > {$sqlFile}");
            $file = $sqlFile;
        }

        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($file)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("MySQL restore failed with code {$returnCode}");
        }
    }

    private function verifySchema()
    {
        // Verify stock_opnames has new columns
        $columns = DB::select("SHOW COLUMNS FROM stock_opnames");
        $columnNames = array_column($columns, 'Field');

        $requiredColumns = ['status', 'journal_entry_id'];
        $missing = array_diff($requiredColumns, $columnNames);

        if (count($missing) > 0) {
            $this->warn("⚠️  Missing columns in stock_opnames: " . implode(', ', $missing));
            $this->warn("Migration might have failed. Please run manually:");
            $this->line("php artisan migrate");
        } else {
            $this->info("✅ stock_opnames table has all required columns");
        }

        // Verify journal_entries exists
        $tables = DB::select("SHOW TABLES LIKE 'journal_entries'");
        if (count($tables) > 0) {
            $this->info("✅ journal_entries table exists");
        } else {
            $this->warn("⚠️  journal_entries table not found");
        }
    }
}
