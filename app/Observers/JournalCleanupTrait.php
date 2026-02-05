<?php

namespace App\Observers;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\Log;

trait JournalCleanupTrait
{
    /**
     * Delete all journal entries with reference matching the pattern
     * 
     * @param string $referencePrefix The prefix of the reference number (e.g., 'INV-', 'PUR-')
     * @param string|int $identifier The identifier (e.g., invoice_number, id)
     * @return int Number of deleted entries
     */
    protected function deleteRelatedJournals(string $referencePrefix, $identifier): int
    {
        $pattern = $referencePrefix . $identifier;
        
        // Find all journal entries that start with this pattern
        $entries = JournalEntry::where('reference_number', 'LIKE', $pattern . '%')->get();
        
        if ($entries->isEmpty()) {
            Log::info("No journal entries found for pattern: {$pattern}");
            return 0;
        }
        
        $count = 0;
        foreach ($entries as $entry) {
            Log::info("Deleting journal entry: {$entry->reference_number}");
            $entry->delete();
            $count++;
        }
        
        Log::info("Deleted {$count} journal entries for pattern: {$pattern}");
        return $count;
    }
    
    /**
     * Delete a specific journal entry by exact reference number
     * 
     * @param string $referenceNumber The exact reference number
     * @return bool
     */
    protected function deleteJournalByReference(string $referenceNumber): bool
    {
        $entry = JournalEntry::where('reference_number', $referenceNumber)->first();
        
        if ($entry) {
            Log::info("Deleting journal entry: {$referenceNumber}");
            $entry->delete();
            return true;
        }
        
        return false;
    }
}
