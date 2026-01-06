<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\YearEndClosingService;
use Illuminate\Support\Facades\Log;

class ExecuteYearEndClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'year:close 
                            {year : The year to close (e.g., 2024)} 
                            {--user-id=1 : User ID performing the closing}
                            {--no-backup : Skip database backup (NOT RECOMMENDED)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute year-end closing process to archive old data and create opening balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = (int) $this->argument('year');
        $userId = (int) $this->option('user-id');
        $noBackup = $this->option('no-backup');
        
        $this->info("===========================================");
        $this->info("   YEAR-END CLOSING PROCESS");
        $this->info("===========================================");
        $this->newLine();
        
        $this->info("Closing Year: {$year}");
        $this->info("User ID: {$userId}");
        $this->newLine();
        
        // Warning
        if ($noBackup) {
            $this->error("⚠️  WARNING: Backup is DISABLED. This is NOT recommended!");
            $this->newLine();
        }
        
        // Confirmation
        if (!$this->confirm("Are you sure you want to close year {$year}? This will DELETE all transaction data!")) {
            $this->error("Operation cancelled.");
            return 1;
        }
        
        $this->newLine();
        $this->info("Starting year-end closing process...");
        $this->newLine();
        
        try {
            $service = new YearEndClosingService();
            
            // Step 1: Validation
            $this->info("[1/4] Validating...");
            $service->validateClosing($year);
            $this->line("✓ Validation passed");
            $this->newLine();
            
            // Step 2: Backup (if not skipped)
            if (!$noBackup) {
                $this->info("[2/4] Creating database backup...");
                $this->line("This may take a few minutes...");
                $backupFile = $service->createDatabaseBackup();
                $this->line("✓ Backup created: {$backupFile}");
            } else {
                $this->warn("[2/4] Skipping backup (--no-backup flag set)");
            }
            $this->newLine();
            
            // Step 3: Create opening balance
            $this->info("[3/4] Creating opening balance...");
            $bar = $this->output->createProgressBar(3);
            $bar->start();
            
            $closing = $service->executeClosing($year, $userId);
            
            $bar->advance(3);
            $bar->finish();
            $this->newLine();
            $this->line("✓ Opening balance created");
            $this->newLine();
            
            // Step 4: Summary
            $this->info("[4/4] Process completed successfully!");
            $this->newLine();
            
            $this->info("===========================================");
            $this->info("   SUMMARY");
            $this->info("===========================================");
            $this->table(
                ['Item', 'Count'],
                [
                    ['Transactions Deleted', number_format($closing->total_transactions_deleted)],
                    ['Purchases Deleted', number_format($closing->total_purchases_deleted)],
                    ['Journal Entries Deleted', number_format($closing->total_journal_entries_deleted)],
                    ['Expenses Deleted', number_format($closing->total_expenses_deleted)],
                    ['Payments Deleted', number_format($closing->total_payments_deleted)],
                    ['Stock Movements Deleted', number_format($closing->total_stock_movements_deleted)],
                    ['Opening Purchase', $closing->openingPurchase->invoice_number ?? 'N/A'],
                    ['Backup File', $closing->backup_file_path ?? 'N/A'],
                ]
            );
            $this->newLine();
            
            $this->info("Year-end closing for {$year} completed successfully! ✓");
            $this->newLine();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("✗ Year-end closing failed!");
            $this->error("Error: {$e->getMessage()}");
            $this->newLine();
            
            Log::error("Year-end closing command failed: {$e->getMessage()}", [
                'year' => $year,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}
