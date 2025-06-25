<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:1',
            'price_per_day' => 'required|numeric|min:0',
            'available_from' => 'nullable|date|after_or_equal:today',
            'available_to' => 'nullable|date|after:available_from',
            'is_available' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A terrain title is required.',
            'location.required' => 'A terrain location is required.',
            'area_size.required' => 'The area size is required.',
            'area_size.min' => 'The area size must be at least 1.',
            'price_per_day.required' => 'The price per day is required.',
            'price_per_day.min' => 'The price per day must be at least 0.',
            'available_to.after' => 'The available to date must be after the available from date.',
            'main_image.image' => 'The main image must be an image file.',
            'main_image.max' => 'The main image may not be greater than 2MB.',
        ];
    }
}
