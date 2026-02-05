<?php

namespace App\Observers;

use App\Models\StockOpname;

class StockOpnameObserver
{
    /**
     * Handle the StockOpname "deleting" event.
     *
     * This method is triggered before a StockOpname record is deleted.
     * We loop through its details and delete them one by one.
     * This ensures that the StockOpnameDetailObserver is fired for each detail,
     * correctly reverting stock adjustments and logging the movement.
     *
     * @param  \App\Models\StockOpname  $stockOpname
     * @return void
     */
    public function deleting(StockOpname $stockOpname)
    {
        foreach ($stockOpname->details as $detail) {
            $detail->delete();
        }
    }
}
