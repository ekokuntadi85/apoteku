<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "deleting" event.
     *
     * @param  \App\Models\Product  $product
     * @return bool
     */
    public function deleting(Product $product)
    {
        Log::info('ProductObserver: Deleting event triggered for product ID: ' . $product->id);

        // Load the counts of related models
        $product->loadCount(['productBatches', 'transactionDetails']);

        Log::info('ProductObserver: Product ID ' . $product->id . ' - productBatches_count: ' . $product->product_batches_count . ', transactionDetails_count: ' . $product->transaction_details_count);

        // Check if any related records exist
        if ($product->product_batches_count > 0 || $product->transaction_details_count > 0) {
            Log::info('ProductObserver: Preventing deletion for product ID: ' . $product->id . ' due to existing relations.');
            // Flash a session message to be displayed in the UI
            Session::flash('error', 'Produk tidak dapat dihapus karena memiliki riwayat transaksi.');

            // Returning false from the "deleting" event will cancel the delete operation
            return false;
        }

        Log::info('ProductObserver: Allowing deletion for product ID: ' . $product->id . '.');
        return true;
    }
}
