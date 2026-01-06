<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\YearEndClosingService;
use App\Models\YearEndClosing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;

#[Title('Tutup Tahun - Muazara')]
class YearEndClosingManager extends Component
{
    public $closingYear;
    public $confirmationText = '';
    public $showConfirmModal = false;
    public $processing = false;
    public $errorMessage = '';
    public $successMessage = '';
    
    // Results after closing
    public $lastClosing = null;

    public function mount()
    {
        // Default to last year
        $this->closingYear = now()->year - 1;
    }

    public function showConfirmation()
    {
        $this->validate([
            'closingYear' => 'required|integer|min:2000|max:' . (now()->year - 1),
        ], [
            'closingYear.required' => 'Tahun harus dipilih',
            'closingYear.max' => 'Tidak dapat menutup tahun berjalan atau masa depan',
        ]);
        
        // Clear previous messages
        $this->errorMessage = '';
        $this->successMessage = '';
        
        // Check if already closed successfully
        $existing = YearEndClosing::where('closing_year', $this->closingYear)->first();
        if ($existing) {
            // If completed, don't allow retry
            if ($existing->status === 'completed') {
                $closedDate = $existing->closed_at ? $existing->closed_at->format('d/m/Y H:i') : 'N/A';
                $this->errorMessage = "Tahun {$this->closingYear} sudah pernah ditutup pada {$closedDate}";
                return;
            }
            
            // If processing or failed, delete the old record and allow retry
            if ($existing->status === 'processing' || $existing->status === 'failed') {
                $existing->delete();
                Log::info("Deleting {$existing->status} year-end closing record for retry", [
                    'year' => $this->closingYear,
                    'old_status' => $existing->status
                ]);
            }
        }
        
        $this->showConfirmModal = true;
    }

    public function closeModal()
    {
        $this->showConfirmModal = false;
        $this->confirmationText = '';
    }

    public function executeClosing()
    {
        // Validate confirmation text
        $expectedText = "TUTUP TAHUN {$this->closingYear}";
        if (trim(strtoupper($this->confirmationText)) !== $expectedText) {
            $this->errorMessage = "Teks konfirmasi tidak sesuai. Ketik: {$expectedText}";
            return;
        }
        
        $this->processing = true;
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->closeModal();
        
        try {
            $service = new YearEndClosingService();
            
            Log::info("User {$this->getUserName()} is executing year-end closing for year {$this->closingYear}");
            
            $closing = $service->executeClosing($this->closingYear, Auth::id());
            
            $this->lastClosing = $closing;
            $this->successMessage = "✅ Tutup tahun {$this->closingYear} berhasil dilakukan!";
            
            // Dispatch browser event for notification
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Tutup tahun {$this->closingYear} berhasil! Data sudah direset dan saldo awal sudah dibuat."
            ]);
            
        } catch (\Exception $e) {
            Log::error("Year-end closing failed: {$e->getMessage()}");
            $this->errorMessage = "❌ Gagal melakukan tutup tahun: {$e->getMessage()}";
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Tutup tahun gagal: {$e->getMessage()}"
            ]);
        } finally {
            $this->processing = false;
            $this->confirmationText = '';
        }
    }

    public function getYearOptionsProperty()
    {
        $currentYear = now()->year;
        $years = [];
        
        // Show last 5 years
        for ($i = 1; $i <= 5; $i++) {
            $year = $currentYear - $i;
            $years[$year] = $year;
        }
        
        return $years;
    }

    public function getClosingHistoryProperty()
    {
        return YearEndClosing::with(['user', 'openingPurchase'])
            ->orderBy('closing_year', 'desc')
            ->get();
    }

    private function getUserName()
    {
        return Auth::user()->name ?? 'Unknown';
    }

    public function render()
    {
        return view('livewire.year-end-closing-manager');
    }
}
