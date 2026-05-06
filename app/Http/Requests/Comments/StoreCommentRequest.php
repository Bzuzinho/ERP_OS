<?php

namespace App\Http\Requests\Comments;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && $this->user()->can('create', [\App\Models\Comment::class, $ticket]);
    }

    public function rules(): array
    {
        $visibilityOptions = $this->routeIs('portal.*') ? ['public'] : ['internal', 'public'];

        return [
            'body' => ['required', 'string'],
            'visibility' => ['nullable', Rule::in($visibilityOptions)],
        ];
    }
}
