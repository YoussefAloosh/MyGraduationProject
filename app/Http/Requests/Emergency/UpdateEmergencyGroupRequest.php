<?php

namespace App\Http\Requests\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmergencyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'sometimes|required|string|max:255',
            'center_lat' => 'sometimes|required|numeric|between:-90,90',
            'center_lng' => 'sometimes|required|numeric|between:-180,180',
            'radius_km'  => 'sometimes|nullable|numeric|min:1|max:50',
            'is_active'  => 'sometimes|boolean',
        ];
    }
}