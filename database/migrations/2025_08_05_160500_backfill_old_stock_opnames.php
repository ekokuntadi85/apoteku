<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the first admin user to assign to the migrated opnames
        $adminUser = User::first();
        if (!$adminUser) {
            // Or handle the case where no user exists
            return;
        }

        $oldMovements = DB::table('stock_movements')->where('type', 'OP')->orderBy('created_at')->get();

        if ($oldMovements->isEmpty()) {
            return;
        }

        // Group movements that happened very close to each other
        $groupedMovements = $oldMovements->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->round('5 seconds')->toDateTimeString();
        });

        foreach ($groupedMovements as $timestamp => $movements) {
            DB::transaction(function () use ($timestamp, $movements, $adminUser) {
                $firstMovement = $movements->first();

                $opname = StockOpname::create([
                    'opname_date' => Carbon::parse($timestamp),
                    'notes' => $firstMovement->remarks ?? 'Opname Migrasi',
                    'user_id' => $adminUser->id,
                    'is_migrated' => true,
                ]);

                foreach ($movements as $movement) {
                    // For migrated data, we don't know the exact system/physical stock,
                    // so we store the difference and work from there.
                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'product_batch_id' => $movement->product_batch_id,
                        'system_stock' => 0, // Placeholder
                        'physical_stock' => $movement->quantity, // Store difference as physical
                        'difference' => $movement->quantity,
                    ]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is one-way. We don't want to accidentally delete the new structure.
        // If needed, a manual cleanup would be safer.
    }
};