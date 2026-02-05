<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;

use Livewire\Attributes\Title;

#[Title('Tambah Produk')]
class ProductCreate extends Component
{
    public $name;
    public $sku;
    public $category_id;
    public $productUnits = [];

    public function mount()
    {
        $this->productUnits[] = [
            'name' => '',
            'is_base_unit' => true,
            'conversion_factor' => 1,
            'selling_price' => 0,
            'purchase_price' => 0,
        ];
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'productUnits.*.name' => 'required|string|max:255|exists:units,name',
            'productUnits.*.conversion_factor' => 'required|numeric|min:0.01',
            'productUnits.*.selling_price' => 'required|numeric|min:0',
            'productUnits.*.purchase_price' => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama produk wajib diisi.',
        'sku.required' => 'SKU produk wajib diisi.',
        'sku.unique' => 'SKU produk sudah ada.',
        'category_id.required' => 'Kategori wajib dipilih.',
        'category_id.exists' => 'Kategori tidak valid.',
        'productUnits.*.name.required' => 'Nama satuan wajib diisi.',
        'productUnits.*.conversion_factor.required' => 'Faktor konversi wajib diisi.',
        'productUnits.*.conversion_factor.numeric' => 'Faktor konversi harus berupa angka.',
        'productUnits.*.conversion_factor.min' => 'Faktor konversi minimal 0.01.',
        'productUnits.*.selling_price.required' => 'Harga jual satuan wajib diisi.',
        'productUnits.*.selling_price.numeric' => 'Harga jual satuan harus berupa angka.',
        'productUnits.*.selling_price.min' => 'Harga jual satuan tidak boleh negatif.',
        'productUnits.*.purchase_price.required' => 'Harga beli satuan wajib diisi.',
        'productUnits.*.purchase_price.numeric' => 'Harga beli satuan harus berupa angka.',
        'productUnits.*.purchase_price.min' => 'Harga beli satuan tidak boleh negatif.',
    ];

    public function addUnit()
    {
        $baseUnitPurchasePrice = 0;
        foreach ($this->productUnits as $unit) {
            if ($unit['is_base_unit']) {
                $baseUnitPurchasePrice = $unit['purchase_price'];
                break;
            }
        }

        $this->productUnits[] = [
            'name' => '',
            'is_base_unit' => false,
            'conversion_factor' => 1,
            'selling_price' => 0,
            'purchase_price' => $baseUnitPurchasePrice, // Default to base unit price
        ];
    }

    public function updatedProductUnits($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'conversion_factor' || ($field === 'purchase_price' && $this->productUnits[$index]['is_base_unit'])) {
            $this->recalculatePurchasePrices();
        }
    }

    private function recalculatePurchasePrices()
    {
        $baseUnitPurchasePrice = 0;
        foreach ($this->productUnits as $unit) {
            if ($unit['is_base_unit']) {
                $baseUnitPurchasePrice = $unit['purchase_price'];
                break;
            }
        }

        foreach ($this->productUnits as $key => $unit) {
            if (!$unit['is_base_unit']) {
                // Only recalculate if the user hasn't manually overridden it
                // This is a simple check, more robust logic might be needed for complex scenarios
                if ($unit['purchase_price'] == round($baseUnitPurchasePrice * $unit['conversion_factor'], 2) || $unit['purchase_price'] == 0) {
                    $this->productUnits[$key]['purchase_price'] = round($baseUnitPurchasePrice * $unit['conversion_factor'], 2);
                }
            }
        }
    }

    public function removeUnit($index)
    {
        unset($this->productUnits[$index]);
        $this->productUnits = array_values($this->productUnits);
    }

    public function save()
    {
        $this->validate();

        $product = Product::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
        ]);

        foreach ($this->productUnits as $unitData) {
            $product->productUnits()->create($unitData);
        }

        session()->flash('message', 'Produk berhasil ditambahkan.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        $categories = Category::all();
        $units = \App\Models\Unit::all();
        return view('livewire.product-create', compact('categories', 'units'));
    }
}