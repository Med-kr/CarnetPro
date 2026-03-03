<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('category')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $category = $this->route('category');

        if ($category && blank($this->input('name'))) {
            $this->merge([
                'name' => $category->name,
            ]);
        }

        if (blank($this->input('icon'))) {
            $this->merge([
                'icon' => $category?->icon ?: \App\Models\Category::DEFAULT_ICON,
            ]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('flatshare_id', $category?->flatshare_id))
                    ->ignore($category?->id),
            ],
            'icon' => ['required', Rule::in(array_keys(\App\Models\Category::iconOptions()))],
        ];
    }
}
