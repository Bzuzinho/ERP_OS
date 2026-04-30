<?php

namespace App\Http\Requests\Documents;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Document::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['nullable', 'exists:document_types,id'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'max:20480'],
            'visibility' => ['required', Rule::in(Document::VISIBILITIES)],
            'status' => ['nullable', Rule::in(Document::STATUSES)],
            'related_type' => ['nullable', 'string', 'max:255'],
            'related_id' => ['nullable', 'integer'],
        ];
    }
}
