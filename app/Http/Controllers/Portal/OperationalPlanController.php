<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\OperationalPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationalPlanController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['plan_type', 'start_date', 'end_date']);

        $plans = OperationalPlan::query()
            ->whereIn('visibility', ['public', 'portal'])
            ->whereIn('status', ['approved', 'scheduled', 'in_progress', 'completed'])
            ->when($filters['plan_type'] ?? null, fn ($query, $value) => $query->where('plan_type', $value))
            ->when($filters['start_date'] ?? null, fn ($query, $value) => $query->whereDate('start_date', '>=', $value))
            ->when($filters['end_date'] ?? null, fn ($query, $value) => $query->whereDate('end_date', '<=', $value))
            ->with(['relatedSpace:id,name,location_text'])
            ->orderBy('start_date')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Portal/OperationalPlans/Index', [
            'plans' => $plans,
            'filters' => $filters,
            'types' => OperationalPlan::TYPES,
        ]);
    }

    public function show(OperationalPlan $operationalPlan): Response
    {
        if (! in_array($operationalPlan->visibility, ['public', 'portal'], true)
            || ! in_array($operationalPlan->status, ['approved', 'scheduled', 'in_progress', 'completed'], true)) {
            abort(404);
        }

        $operationalPlan->load([
            'relatedSpace:id,name,location_text',
            'documents:id,title,description,visibility,status,related_type,related_id',
        ]);

        $publicDocuments = $operationalPlan->documents->filter(fn ($document) => in_array($document->visibility, ['public', 'portal'], true))->values();

        return Inertia::render('Portal/OperationalPlans/Show', [
            'plan' => [
                'id' => $operationalPlan->id,
                'title' => $operationalPlan->title,
                'description' => $operationalPlan->description,
                'plan_type' => $operationalPlan->plan_type,
                'status' => $operationalPlan->status,
                'start_date' => $operationalPlan->start_date,
                'end_date' => $operationalPlan->end_date,
                'related_space' => $operationalPlan->relatedSpace,
                'documents' => $publicDocuments,
            ],
        ]);
    }
}
