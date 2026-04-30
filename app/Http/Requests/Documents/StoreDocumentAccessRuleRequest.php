<?php

namespace App\Http\Requests\Documents;

use App\Models\DocumentAccessRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentAccessRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DocumentAccessRule::class);
    }

    public function rules(): array
    {
        return [
            'document_id' => ['required', 'exists:documents,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'role_name' => ['nullable', 'string', 'max:255'],
            'permission' => ['required', Rule::in(['view', 'download', 'manage'])],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('user_id') && ! $this->filled('contact_id') && ! $this->filled('role_name')) {
                $validator->errors()->add('user_id', 'Defina user_id, contact_id ou role_name.');
            }
        });
    }
}
