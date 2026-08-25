<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Warehouse::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($this->route('warehouse')),
            ],
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'manager_id' => 'nullable|exists:users,id',
            'is_main' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام انبار الزامی است.',
            'name.max' => 'نام انبار نباید بیش از 255 کاراکتر باشد.',
            'code.unique' => 'کد انبار تکراری است.',
            'branch_id.exists' => 'شعبه مورد نظر وجود ندارد.',
            'manager_id.exists' => 'کاربر مورد نظر وجود ندارد.',
        ];
    }
}
