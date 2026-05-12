<?php

namespace App\Http\Requests\Inventory;

use App\Models\Event;
use App\Models\OperationalPlan;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreResourceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('resources.create');
    }

    public function rules(): array
    {
        $orgId = (int) $this->user()->organization_id;

        $requestableTypeMap = [
            'space_reservation' => SpaceReservation::class,
            'event' => Event::class,
            'task' => Task::class,
            'ticket' => Ticket::class,
            'operational_plan' => OperationalPlan::class,
        ];

        return [
            'organization_id' => ['required', 'integer', Rule::in([$orgId])],
            'title' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'needed_from' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'needed_until' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'requestable_type' => ['nullable', 'string', Rule::in(array_keys($requestableTypeMap))],
            'requestable_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => [
                'required',
                'integer',
                Rule::exists('inventory_items', 'id')->where(fn ($query) => $query
                    ->where('organization_id', $orgId)
                    ->where('is_active', true)),
            ],
            'items.*.quantity_requested' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Deve fornecer pelo menos um item.',
            'items.min' => 'Deve fornecer pelo menos um item.',
            'items.*.quantity_requested.min' => 'Quantidade tem de ser maior que zero.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('requestable_type');
            $id = $this->input('requestable_id');

            if (empty($type) || empty($id)) {
                return;
            }

            $map = [
                'space_reservation' => SpaceReservation::class,
                'event' => Event::class,
                'task' => Task::class,
                'ticket' => Ticket::class,
                'operational_plan' => OperationalPlan::class,
            ];

            if (! isset($map[$type])) {
                return;
            }

            $model = $map[$type]::query()->find($id);
            if (! $model || (int) $model->organization_id !== (int) $this->user()->organization_id) {
                $validator->errors()->add('requestable_id', 'Entidade relacionada fora da sua organizacao.');
            }
        });
    }
}
