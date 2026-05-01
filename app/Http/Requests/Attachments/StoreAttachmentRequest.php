<?php

namespace App\Http\Requests\Attachments;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && $this->user()->can('create', [\App\Models\Attachment::class, $ticket]);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp,txt',
            ],
            'visibility' => ['nullable', Rule::in(['internal', 'public'])],
        ];
    }
}
