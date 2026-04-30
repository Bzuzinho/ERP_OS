<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Documents\ApproveMeetingMinuteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeetingMinutes\ApproveMeetingMinuteRequest;
use App\Models\MeetingMinute;
use Illuminate\Http\RedirectResponse;

class MeetingMinuteApprovalController extends Controller
{
    public function __invoke(ApproveMeetingMinuteRequest $request, MeetingMinute $meetingMinute, ApproveMeetingMinuteAction $approveMeetingMinuteAction): RedirectResponse
    {
        $approveMeetingMinuteAction->execute($meetingMinute, $request->user());

        return to_route('admin.meeting-minutes.show', $meetingMinute)->with('success', 'Ata aprovada com sucesso.');
    }
}
