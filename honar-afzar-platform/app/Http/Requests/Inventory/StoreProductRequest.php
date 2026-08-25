<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\InventoryProduct::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_fa' => 'nullable|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventory_products', 'code')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($this->route('product')),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventory_products', 'sku')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($this->route('product')),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventory_products', 'barcode')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($this->route('product')),
            ],
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string|max:1000',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0|gte:min_stock',
            'reorder_point' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'has_serial_number' => 'boolean',
            'has_batch' => 'boolean',
            'has_expiry' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام محصول الزامی است.',
            'name.max' => 'نام محصول نباید بیش از 255 کاراکتر باشد.',
            'code.unique' => 'کد محصول تکراری است.',
            'sku.unique' => 'کد SKU تکراری است.',
            'barcode.unique' => 'بارکد تکراری است.',
            'max_stock.gte' => 'حداکثر موجودی باید بیشتر از حداقل موجودی باشد.',
            'min_stock.min' => 'حداقل موجودی نمیتواند منفی باشد.',
            'cost_price.min' => 'قیمت تمام شده نمیتواند منفی باشد.',
            'selling_price.min' => 'قیمت فروش نمیتواند منفی باشد.',
        ];
    }
}
