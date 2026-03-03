<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', [Category::class, $this->route('flatshare')]) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->input('icon'))) {
            $this->merge([
                'icon' => Category::DEFAULT_ICON,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(
                    fn ($query) => $query->where('flatshare_id', $this->route('flatshare')?->id)
                ),
            ],
            'icon' => ['required', Rule::in(array_keys(Category::iconOptions()))],
        ];
    }
}
