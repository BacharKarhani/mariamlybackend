<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust if you use policies
    }

    public function rules(): array
    {
        return [
            'image'      => ['required','image','mimes:jpg,jpeg,png,webp','max:1024'], // 1MB max
            'sort_order' => ['nullable','integer'],
            'is_active'  => ['nullable','boolean'],
        ];
    }
}
