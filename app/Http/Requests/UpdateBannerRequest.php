<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'image'      => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'sort_order' => ['nullable','integer','min:0'],
            'is_active'  => ['nullable','boolean'],
        ];
    }
}
