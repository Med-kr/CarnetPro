<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $flatshare = $this->route('flatshare');
        $user = $this->user();

        if (! $user || ! $flatshare) {
            return false;
        }

        return $flatshare->memberships()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    protected function prepareForValidation(): void
    {
        $customMethod = trim((string) $this->input('custom_method'));

        if ($this->input('method_option') === \App\Models\Payment::METHOD_CUSTOM && $customMethod !== '') {
            $this->merge([
                'method' => $customMethod,
            ]);
        } elseif ($this->filled('method_option')) {
            $this->merge([
                'method' => (string) $this->input('method_option'),
            ]);
        }
    }

    public function rules(): array
    {
        $flatshare = $this->route('flatshare');

        return [
            'from_user_id' => [
                'required',
                Rule::exists('memberships', 'user_id')->where(fn ($query) => $query->where('flatshare_id', $flatshare?->id)->whereNull('left_at')),
            ],
            'to_user_id' => [
                'required',
                'different:from_user_id',
                Rule::exists('memberships', 'user_id')->where(fn ($query) => $query->where('flatshare_id', $flatshare?->id)->whereNull('left_at')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method_option' => ['required', Rule::in(array_keys(\App\Models\Payment::methodOptions()))],
            'method' => ['required', 'string', 'max:100'],
            'custom_method' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(\App\Models\Payment::statusOptions()))],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $customMethod = trim((string) $this->input('custom_method'));

            if ($this->input('method_option') === \App\Models\Payment::METHOD_CUSTOM && $customMethod === '') {
                $validator->errors()->add('custom_method', 'Custom payment method is required.');
            }
        });
    }
}
