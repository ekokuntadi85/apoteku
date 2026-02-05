<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Illuminate\Validation\Rule;

use Livewire\Attributes\Title;

#[Title('Edit Produk')]
class ProductEdit extends Component
{
    public $productId;
    public $name;
    public $sku;
    public $category_id;
    public $productUnits = [];

    public function mount(Product $product)
    {
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->category_id = $product->category_id;
        $this->productUnits = $product->productUnits->toArray();

        if (empty($this->productUnits)) {
            // Initialize base unit if none exists
            $this->productUnits[] = [
                'name' => '',
                'is_base_unit' => true,
                'conversion_factor' => 1,
                'selling_price' => 0,
                'member_price' => null,
                'purchase_price' => 0,
            ];
        }

        // Load last purchase price for the base unit
        $lastBatch = \App\Models\ProductBatch::where('product_id', $this->productId)
                                            ->latest('created_at')
                                            ->first();

        if ($lastBatch) {
            // Find the base unit in the productUnits array and update its purchase_price
            foreach ($this->productUnits as $key => $unit) {
                if ($unit['is_base_unit']) {
                    $this->productUnits[$key]['purchase_price'] = $lastBatch->purchase_price;
                    break;
                }
            }
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
            'category_id' => 'required|exists:categories,id',
            'productUnits.*.name' => 'required|string|max:255',
            'productUnits.*.conversion_factor' => 'required|numeric|min:0.01',
            'productUnits.*.selling_price' => 'required|numeric|min:0',
            'productUnits.*.member_price' => 'nullable|numeric|min:0',
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
            'member_price' => null,
            'purchase_price' => $baseUnitPurchasePrice, // Default to base unit price
        ];
    }

    public function removeUnit($index)
    {
        // If the unit has an ID, it means it exists in the database, so we need to delete it
        if (isset($this->productUnits[$index]['id'])) {
            \App\Models\ProductUnit::destroy($this->productUnits[$index]['id']);
        }
        unset($this->productUnits[$index]);
        $this->productUnits = array_values($this->productUnits);
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

    public function save()
    {
        $this->validate();

        $product = Product::find($this->productId);
        $product->update([
            'name' => $this->name,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
        ]);

        // Sync product units
        $existingUnitIds = collect($this->productUnits)->pluck('id')->filter()->toArray();
        $product->productUnits()->whereNotIn('id', $existingUnitIds)->delete();

        foreach ($this->productUnits as $unitData) {
            if (isset($unitData['id'])) {
                // Update existing unit
                $product->productUnits()->find($unitData['id'])->update($unitData);
            } else {
                // Create new unit
                $product->productUnits()->create($unitData);
            }
        }

        session()->flash('message', 'Produk berhasil diperbarui.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        $categories = Category::all();
        $units = \App\Models\Unit::all();
        return view('livewire.product-edit', compact('categories', 'units'));
    }
}
