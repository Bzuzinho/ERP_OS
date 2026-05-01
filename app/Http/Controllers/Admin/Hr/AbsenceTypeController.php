<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreAbsenceTypeRequest;
use App\Http\Requests\Hr\UpdateAbsenceTypeRequest;
use App\Models\AbsenceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AbsenceTypeController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', AbsenceType::class);

        $types = AbsenceType::where('organization_id', auth()->user()->organization_id)
            ->when(request('status'), fn($q) => $q->where('is_active', request('status') === 'active'))
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Admin/AbsenceTypes/Index', [
            'types' => $types,
            'filters' => request()->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AbsenceTypes/Create');
    }

    public function store(StoreAbsenceTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = auth()->user()->organization_id;
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        AbsenceType::create($data);

        return redirect()->route('admin.hr.absence-types.index')
            ->with('success', 'Tipo de ausência criado com sucesso!');
    }

    public function edit(AbsenceType $absenceType): Response
    {
        return Inertia::render('Admin/AbsenceTypes/Edit', [
            'type' => $absenceType,
        ]);
    }

    public function update(UpdateAbsenceTypeRequest $request, AbsenceType $absenceType): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        $absenceType->update($data);

        return redirect()->route('admin.hr.absence-types.index')
            ->with('success', 'Tipo de ausência atualizado com sucesso!');
    }

    public function destroy(AbsenceType $absenceType): RedirectResponse
    {
        $absenceType->delete();

        return redirect()->route('admin.hr.absence-types.index')
            ->with('success', 'Tipo de ausência eliminado com sucesso!');
    }
}
