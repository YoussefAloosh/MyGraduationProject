<?php

namespace App\Http\Requests\Reaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreReactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'             => 'required|in:like,dislike',
            'reactionable_id'  => 'required|integer',
            'reactionable_type'=> 'required|string|in:article,comment',
        ];
    }
}