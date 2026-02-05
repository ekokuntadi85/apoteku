<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets (Harta) - 100-199
            ['code' => '101', 'name' => 'Kas', 'type' => 'asset'],
            ['code' => '102', 'name' => 'Bank', 'type' => 'asset'],
            ['code' => '103', 'name' => 'Piutang Usaha', 'type' => 'asset'],
            ['code' => '104', 'name' => 'Persediaan Obat', 'type' => 'asset'], // Stock Inventory
            ['code' => '105', 'name' => 'Perlengkapan', 'type' => 'asset'],
            
            // Liabilities (Kewajiban) - 200-299
            ['code' => '201', 'name' => 'Hutang Usaha', 'type' => 'liability'], // Accounts Payable
            ['code' => '202', 'name' => 'Hutang Gaji', 'type' => 'liability'],
            
            // Equity (Modal) - 300-399
            ['code' => '301', 'name' => 'Modal Awal', 'type' => 'equity'],
            ['code' => '302', 'name' => 'Laba Ditahan', 'type' => 'equity'],
            
            // Revenue (Pendapatan) - 400-499
            ['code' => '401', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue'],
            ['code' => '402', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue'],
            
            // Expenses (Beban) - 500-599
            ['code' => '501', 'name' => 'Beban Pokok Penjualan (HPP)', 'type' => 'expense'], // COGS
            ['code' => '502', 'name' => 'Beban Gaji', 'type' => 'expense'],
            ['code' => '503', 'name' => 'Beban Listrik & Air', 'type' => 'expense'],
            ['code' => '504', 'name' => 'Beban Sewa', 'type' => 'expense'],
            ['code' => '505', 'name' => 'Beban Perlengkapan', 'type' => 'expense'],
            ['code' => '506', 'name' => 'Beban Penyusutan', 'type' => 'expense'],
            ['code' => '507', 'name' => 'Beban Lain-lain', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
