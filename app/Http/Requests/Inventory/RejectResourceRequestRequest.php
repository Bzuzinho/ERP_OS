<?php

namespace App\Http\Requests\Inventory;

use App\Models\ResourceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RejectResourceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('resources.approve');
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Motivo da rejeicao e obrigatorio.',
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
            throw ValidationException::withMessages(['status' => 'Apenas requisicoes em estado requested podem ser rejeitadas.']);
        }
    }
}
