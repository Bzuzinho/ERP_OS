<?php

namespace App\Http\Requests\Inventory;

use App\Models\ResourceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ApproveResourceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('resources.approve');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.quantity_approved' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        /** @var ResourceRequest|null $resourceRequest */
        $resourceRequest = $this->route('resourceRequest');

        if (! $resourceRequest) {
            return;
        }

        if ((int) $resourceRequest->organization_id !== (int) $this->user()->organization_id) {
            throw ValidationException::withMessages(['organization' => 'Requisicao fora da sua organizacao.']);
        }

        if ($resourceRequest->status !== 'requested') {
            throw ValidationException::withMessages(['status' => 'Apenas requisicoes em estado requested podem ser aprovadas.']);
        }
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Deve fornecer quantidades aprovadas.',
        ];
    }
}
