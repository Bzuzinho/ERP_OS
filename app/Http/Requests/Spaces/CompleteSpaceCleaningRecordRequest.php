<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceCleaningRecord;
use Illuminate\Foundation\Http\FormRequest;

class CompleteSpaceCleaningRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('spaceCleaning'));
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
        ];
    }
}
