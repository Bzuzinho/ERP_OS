<?php

namespace App\Services\Reports;

use App\Models\Document;
use App\Models\User;
use App\Services\Dashboard\DocumentKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DocumentReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly DocumentKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = Document::query()
            ->where('documents.organization_id', $user->organization_id)
            ->with(['type:id,name', 'uploader:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('documents.status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->whereHas('type', fn ($typeQuery) => $typeQuery->where('name', $value)))
            ->when($normalized['search'], fn ($q, $value) => $q->where('documents.title', 'like', "%{$value}%"))
            ->latest('documents.created_at');

        $this->filters->applyDateRange($query, $normalized, 'documents.created_at');

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = Document::query()
            ->where('documents.organization_id', $user->organization_id)
            ->with(['type:id,name', 'uploader:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('documents.status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->whereHas('type', fn ($typeQuery) => $typeQuery->where('name', $value)))
            ->when($normalized['search'], fn ($q, $value) => $q->where('documents.title', 'like', "%{$value}%"))
            ->latest('documents.created_at');

        $this->filters->applyDateRange($query, $normalized, 'documents.created_at');

        return $query->limit(5000)->get()->map(fn (Document $document) => [
            'titulo' => $document->title,
            'tipo' => $document->type?->name,
            'visibilidade' => $document->visibility,
            'estado' => $document->status,
            'versao' => $document->current_version,
            'carregado_por' => $document->uploader?->name,
            'criado_em' => optional($document->created_at)->toDateTimeString(),
            'entidade_relacionada' => $document->related_type ? class_basename($document->related_type).'#'.$document->related_id : null,
        ]);
    }
}
