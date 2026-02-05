<?php

namespace App\Http\Controllers;

use App\Models\StockOpname; // Import StockOpname model
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'notes' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.product_batch_id' => 'required|exists:product_batches,id',
            'details.*.system_stock' => 'required|integer|min:0',
            'details.*.physical_stock' => 'required|integer|min:0',
        ]);

        $stockOpname = \App\Models\StockOpname::create([
            'user_id' => auth()->id(),
            'opname_date' => now(),
            'notes' => $validatedData['notes'],
            'is_migrated' => false,
        ]);

        foreach ($validatedData['details'] as $detail) {
            $stockOpname->details()->create($detail);
        }

        return response()->json(['message' => 'Stock opname created successfully', 'stock_opname_id' => $stockOpname->id], 201);
    }

    public function apply(StockOpname $stockOpname)
    {
        foreach ($stockOpname->details as $detail) {
            $detail->save();
        }

        $stockOpname->is_migrated = true; // Use the existing 'is_migrated' column
        $stockOpname->save();

        return response()->json(['message' => 'Stock opname applied successfully'], 200);
    }
}
