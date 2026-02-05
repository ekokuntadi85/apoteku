<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

class SlowProductImportManager extends Component
{
    use WithFileUploads;

    public $file;

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls',
    ];

    protected $messages = [
        'file.required' => 'File wajib diunggah.',
        'file.mimes' => 'File harus berformat .xlsx atau .xls.',
    ];

    public function import()
    {
        $this->validate();

        try {
            // Dapatkan path sementara dari file yang diunggah
            $filePath = $this->file->getRealPath();

            // Mengimpor data menggunakan antrian
            Excel::import(new ProductsImport, $filePath);

            session()->flash('message', 'Impor telah dijadwalkan! Proses akan berjalan di latar belakang.');

            // Reset input file setelah berhasil
            $this->reset('file');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat menjadwalkan impor: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.slow-product-import-manager');
    }
}
