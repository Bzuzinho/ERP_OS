<?php

namespace App\Http\Requests\Spaces;

use App\Models\Space;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('space'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Space::STATUSES)],
        ];
    }
}
