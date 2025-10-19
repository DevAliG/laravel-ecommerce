<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
/**
 * @property string $name
 * @property float $price
 * @property string $short_description
 * @property int $qty
 * @property string $sku
 * @property string $description
 * @property array $colors
 */


class ProductStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required' , 'image' , 'max:2048'],
            'images.*' => ['required' , 'image' , 'max:2048'],
            'name' => ['required' , 'string' , 'max:255'],
            'price' => ['required' , 'numeric'],
            'colors' => ['nullable'],
            'short_description' => ['required' , 'string' , 'max:255'],
            'qty' => ['required' , 'numeric'],
            'sku' => ['required' , 'string' , 'max:255'],
            'description' => ['required' , 'string' ],
        ];
    }
}
