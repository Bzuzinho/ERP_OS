<?php

namespace App\Http\Requests\Inventory;

use App\Models\ResourceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReturnResourceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('resources.return');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.quantity_returned' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Deve fornecer quantidades para devolucao.',
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

        if (! in_array($resourceRequest->status, ['delivered', 'partially_returned'], true)) {
            throw ValidationException::withMessages(['status' => 'Apenas requisicoes entregues podem ser devolvidas.']);
        }
    }
}
