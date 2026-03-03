<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', [Expense::class, $this->route('flatshare')]) ?? false;
    }

    public function rules(): array
    {
        $flatshare = $this->route('flatshare');

        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_at' => ['required', 'date'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('flatshare_id', $flatshare?->id)),
            ],
            'payer_id' => [
                'required',
                Rule::exists('memberships', 'user_id')->where(
                    fn ($query) => $query->where('flatshare_id', $flatshare?->id)->whereNull('left_at')
                ),
            ],
        ];
    }
}
