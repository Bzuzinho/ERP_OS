<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Task;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('complete', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx',
        ]);

        $file = $request->file('file');

        // Store file
        $path = $file->store(
            "tasks/{$task->id}",
            'public'
        );

        // Create attachment record
        Attachment::create([
            'organization_id' => $task->organization_id,
            'uploaded_by' => $request->user()->id,
            'attachable_type' => Task::class,
            'attachable_id' => $task->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'visibility' => 'internal',
        ]);

        return back()->with('success', 'Arquivo anexado com sucesso.');
    }

    public function download(Request $request, Task $task, Attachment $attachment): mixed
    {
        $this->authorize('view', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        // Verify attachment belongs to this task
        if ($attachment->attachable_id !== $task->id || $attachment->attachable_type !== Task::class) {
            abort(404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
