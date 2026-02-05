<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductImportManager extends Component
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
            Excel::import(new ProductsImport, $this->file);
            session()->flash('message', 'Import produk berhasil!');
            $this->reset('file'); // Clear the file input after successful import
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = 'Import gagal. Beberapa baris memiliki masalah:';
            foreach ($failures as $failure) {
                $errorMessage .= "\n- Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            session()->flash('error', $errorMessage);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.product-import-manager');
    }
}