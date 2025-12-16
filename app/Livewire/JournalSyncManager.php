<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Title;

#[Title('Sinkronisasi Jurnal')]
class JournalSyncManager extends Component
{
    public $stats = [];
    public $syncLog = '';
    public $isSyncing = false;
    public $syncProgress = 0;
    
    public function mount()
    {
        $this->refreshStats();
    }
    
    public function refreshStats()
    {
        $this->stats = [
            'transactions' => Transaction::count(),
            'purchases' => Purchase::count(),
            'expenses' => Expense::count(),
            'journal_entries' => JournalEntry::count(),
            'sales_journals' => JournalEntry::where('reference_number', 'like', 'INV-%')->count(),
            'cogs_journals' => JournalEntry::where('reference_number', 'like', 'COGS-%')->count(),
            'purchase_journals' => JournalEntry::where('reference_number', 'like', 'PUR-%')->count(),
            'expense_journals' => JournalEntry::where('reference_number', 'like', 'EXP-%')->count(),
        ];
        
        // Calculate expected journals
        $this->stats['expected_min'] = $this->stats['transactions'] + $this->stats['purchases'] + $this->stats['expenses'];
        $this->stats['coverage_percent'] = $this->stats['expected_min'] > 0 
            ? round(($this->stats['journal_entries'] / ($this->stats['expected_min'] * 2)) * 100, 1)
            : 100;
    }
    
    public function syncAll()
    {
        $this->isSyncing = true;
        $this->syncLog = "🔄 Memulai sinkronisasi penuh...\n\n";
        $this->syncProgress = 0;
        
        // Check if dataset is too large for web sync
        $totalData = $this->stats['transactions'] + $this->stats['purchases'] + $this->stats['expenses'];
        
        if ($totalData > 5000) {
            $this->syncLog .= "⚠️ PERINGATAN: Dataset terlalu besar ({$totalData} records)\n";
            $this->syncLog .= "   Sync via web mungkin timeout.\n\n";
            $this->syncLog .= "📌 REKOMENDASI: Jalankan via terminal:\n";
            $this->syncLog .= "   php artisan finance:sync-historical-journals\n\n";
            $this->syncLog .= "Tetap melanjutkan sync via web...\n\n";
        }
        
        try {
            // Increase time limit for large datasets
            set_time_limit(600); // 10 minutes
            
            // Run sync command
            Artisan::call('finance:sync-historical-journals');
            $output = Artisan::output();
            
            $this->syncLog .= $output;
            $this->syncLog .= "\n✅ Sinkronisasi selesai!\n";
            $this->syncProgress = 100;
            
            // Refresh stats
            $this->refreshStats();
            
            session()->flash('message', 'Sinkronisasi jurnal berhasil!');
        } catch (\Exception $e) {
            $this->syncLog .= "\n❌ Error: " . $e->getMessage() . "\n";
            $this->syncLog .= "\n📌 Jika timeout, jalankan via terminal:\n";
            $this->syncLog .= "   php artisan finance:sync-historical-journals\n";
            
            session()->flash('error', 'Sinkronisasi gagal. Coba via terminal untuk dataset besar.');
        } finally {
            $this->isSyncing = false;
        }
    }
    
    public function fixMissingCOGS()
    {
        $this->isSyncing = true;
        $this->syncLog = "🔧 Memperbaiki COGS yang hilang...\n\n";
        
        try {
            Artisan::call('finance:fix-missing-cogs');
            $output = Artisan::output();
            
            $this->syncLog .= $output;
            $this->syncLog .= "\n✅ Perbaikan COGS selesai!\n";
            
            $this->refreshStats();
            
            session()->flash('message', 'Perbaikan COGS berhasil!');
        } catch (\Exception $e) {
            $this->syncLog .= "\n❌ Error: " . $e->getMessage() . "\n";
            session()->flash('error', 'Perbaikan COGS gagal: ' . $e->getMessage());
        } finally {
            $this->isSyncing = false;
        }
    }
    
    public function clearAllJournals()
    {
        try {
            JournalEntry::truncate();
            \DB::table('journal_details')->truncate();
            
            $this->syncLog = "🗑️ Semua journal entries telah dihapus.\n";
            $this->syncLog .= "Silakan jalankan 'Sync Semua' untuk membuat ulang.\n";
            
            $this->refreshStats();
            
            session()->flash('message', 'Semua journal entries telah dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus journals: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.journal-sync-manager');
    }
}
