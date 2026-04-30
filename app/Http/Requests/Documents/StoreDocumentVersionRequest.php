<?php

namespace App\Http\Requests\Documents;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof Document && $this->user()->can('update', $document);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
