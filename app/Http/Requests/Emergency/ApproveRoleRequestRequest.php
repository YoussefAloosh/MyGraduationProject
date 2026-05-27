<?php

namespace App\Http\Requests\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRoleRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}