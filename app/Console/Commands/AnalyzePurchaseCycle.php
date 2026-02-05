<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyzePurchaseCycle extends Command
{
    protected $signature = 'app:analyze-purchase-cycle
                            {--balance=3000000 : Your current cash balance for purchasing.}
                            {--sales-period=30 : The historical period in days to analyze.}';

    protected $description = 'Analyzes sales and inventory to recommend a sustainable purchasing cycle and budget.';

    public function handle()
    {
        $salesPeriod = (int) $this->option('sales-period');
        $this->info("--- Analisa Perencanaan Pembelian (Periode Analisa: {$salesPeriod} Hari) ---");
        $this->line('');

        // --- Step 1: Calculate Total Inventory Value ---
        $this->comment("Langkah 1: Menghitung Total Nilai Inventaris (Modal Stok Saat Ini)");
        $totalInventoryValue = DB::table('product_batches')
            ->where('stock', '>', 0)
            ->sum(DB::raw('stock * purchase_price'));

        $this->line("Rumus: <fg=yellow>Total Nilai Inventaris = SUM(Stok Batch * Harga Beli Batch)</>");
        $this->line("Hasil: <fg=green>Rp " . number_format($totalInventoryValue, 2) . "</>");
        $this->line('');

        // --- Step 2: Calculate Average Daily COGS ---
        $this->comment("Langkah 2: Menghitung Rata-rata Modal Harian yang Terjual (Average Daily COGS)");
        $totalCogs = DB::table('transactions as t')
            ->join('transaction_details as td', 't.id', '=', 'td.transaction_id')
            ->join('transaction_detail_batches as tdb', 'td.id', '=', 'tdb.transaction_detail_id')
            ->join('product_batches as pb', 'tdb.product_batch_id', '=', 'pb.id')
            ->where('t.created_at', '>=', Carbon::now()->subDays($salesPeriod))
            ->sum(DB::raw('tdb.quantity * pb.purchase_price'));

        $avgDailyCogs = $totalCogs / $salesPeriod;

        $this->line("Rumus: <fg=yellow>Total COGS = SUM(Kuantitas Terjual * Harga Beli Batch)</>");
        $this->line("Perhitungan: Total COGS selama {$salesPeriod} hari adalah <fg=yellow>Rp " . number_format($totalCogs, 2) . "</>");
        $this->line("Rumus: <fg=yellow>Rata-rata COGS Harian = Total COGS / {$salesPeriod} Hari</>");
        $this->line("Hasil: <fg=green>Rp " . number_format($avgDailyCogs, 2) . "</>");
        $this->line('');

        if ($avgDailyCogs <= 0) {
            $this->error('Analisa tidak dapat dilanjutkan karena tidak ada data penjualan (COGS) pada periode yang ditentukan.');
            return;
        }

        // --- Step 3: Calculate Days of Inventory Remaining ---
        $this->comment("Langkah 3: Menghitung 'Daya Tahan' Stok");
        $daysOfInventory = $totalInventoryValue / $avgDailyCogs;
        $this->line("Rumus: <fg=yellow>Daya Tahan Stok = Total Nilai Inventaris / Rata-rata COGS Harian</>");
        $this->line("Perhitungan: <fg=yellow>Rp " . number_format($totalInventoryValue, 2) . " / Rp " . number_format($avgDailyCogs, 2) . "</>");
        $this->line("Hasil: <fg=green>" . number_format($daysOfInventory, 1) . " Hari</>");
        $this->line('');

        // --- Step 4: Calculate Recommended Purchase Frequency ---
        $this->comment("Langkah 4: Menentukan Rekomendasi Frekuensi Pembelian");
        $purchaseFrequency = floor($daysOfInventory / 2);
        $this->line("Rumus: <fg=yellow>Frekuensi Pembelian = Daya Tahan Stok / 2</>");
        $this->line("Perhitungan: <fg=yellow>" . number_format($daysOfInventory, 1) . " Hari / 2</>");
        $this->line("Hasil: <fg=green>" . $purchaseFrequency . " Hari Sekali</> (dibulatkan ke bawah)");
        $this->line('');

        // --- Step 5: Calculate Recommended Purchase Amount ---
        $this->comment("Langkah 5: Menentukan Rekomendasi Alokasi Budget per Pembelian");
        $purchaseAmount = $avgDailyCogs * $purchaseFrequency;
        $this->line("Rumus: <fg=yellow>Alokasi Budget = Rata-rata COGS Harian * Frekuensi Pembelian</>");
        $this->line("Perhitungan: <fg=yellow>Rp " . number_format($avgDailyCogs, 2) . " * " . $purchaseFrequency . " Hari</>");
        $this->line("Hasil: <fg=green>Rp " . number_format($purchaseAmount, 2) . "</>");
        $this->line('');

        // --- Final Summary ---
        $this->info('--- Rekomendasi Strategis ---');
        $headers = ['Metrik', 'Hasil Rekomendasi'];
        $rows = [
            ['Siklus Pembelian Optimal', "Setiap {$purchaseFrequency} hari sekali"],
            ['Alokasi Budget per Pembelian', "Rp " . number_format($purchaseAmount, 2)]
        ];
        $this->table($headers, $rows);
        $this->line("Catatan: Saldo awal Anda (<fg=yellow>Rp ".number_format((float)$this->option('balance'), 2).")</>) berfungsi sebagai dana penyangga untuk kelancaran siklus ini.");
    }
}