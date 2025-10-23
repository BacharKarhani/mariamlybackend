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
            'image'      => ['required','file','mimes:jpg,jpeg,png,webp,mp4','max:10240'], // 10MB max for videos
            'sort_order' => ['nullable','integer'],
            'is_active'  => ['nullable','boolean'],
        ];
    }
}
