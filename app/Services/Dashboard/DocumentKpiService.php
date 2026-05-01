<?php

namespace App\Services\Dashboard;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\MeetingMinute;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class DocumentKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $base = Document::query()->where('documents.organization_id', $user->organization_id);
        $this->filters->applyDateRange($base, $normalized);

        $base
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->whereHas('type', fn ($typeQuery) => $typeQuery->where('name', $value)))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"));

        $documentIds = (clone $base)->pluck('id');

        return [
            'active_documents' => (clone $base)->where('is_active', true)->count(),
            'by_type' => (clone $base)
                ->leftJoin('document_types', 'document_types.id', '=', 'documents.document_type_id')
                ->selectRaw('COALESCE(document_types.name, \'Sem tipo\') as label, COUNT(*) as total')
                ->groupBy('document_types.name')
                ->pluck('total', 'label')
                ->toArray(),
            'restricted' => (clone $base)->where('visibility', 'restricted')->count(),
            'public_or_portal' => (clone $base)->whereIn('visibility', ['public', 'portal'])->count(),
            'versions_in_period' => DocumentVersion::query()->whereIn('document_id', $documentIds)->count(),
            'meeting_minutes_draft' => MeetingMinute::query()->where('organization_id', $user->organization_id)->where('status', 'draft')->count(),
            'meeting_minutes_approved' => MeetingMinute::query()->where('organization_id', $user->organization_id)->where('status', 'approved')->count(),
        ];
    }
}
