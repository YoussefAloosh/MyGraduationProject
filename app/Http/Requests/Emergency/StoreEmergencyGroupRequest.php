<?php

namespace App\Http\Requests\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'center_lat' => 'required|numeric|between:-90,90',
            'center_lng' => 'required|numeric|between:-180,180',
            'radius_km'  => 'nullable|numeric|min:1|max:50',
        ];
    }
}