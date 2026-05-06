<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\UpdateOrganizationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateOrganizationLogoRequest;
use App\Http\Requests\Settings\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    private function resolveOrganization(Request $request): Organization
    {
        return Organization::findOrFail($request->user()->organization_id);
    }

    public function edit(Request $request): Response
    {
        $this->authorize('settings.update');

        $organization = $this->resolveOrganization($request);

        return Inertia::render('Admin/Settings/Organization/Edit', [
            'organization' => $organization->only([
                'id', 'name', 'code', 'nif', 'email', 'phone', 'phone_secondary',
                'fax', 'website', 'address', 'postal_code', 'city', 'county',
                'district', 'country', 'president_name', 'iban', 'facebook_url',
                'instagram_url', 'description', 'logo_path',
            ]),
            'logoUrl' => $organization->logo_path
                ? Storage::disk('public')->url($organization->logo_path)
                : null,
        ]);
    }

    public function update(UpdateOrganizationRequest $request, UpdateOrganizationAction $action): RedirectResponse
    {
        $organization = $this->resolveOrganization($request);
        $action->execute($organization, $request->validated());

        return back()->with('success', 'Dados da organização atualizados com sucesso.');
    }

    public function updateLogo(UpdateOrganizationLogoRequest $request, UpdateOrganizationAction $action): RedirectResponse
    {
        $organization = $this->resolveOrganization($request);
        $action->updateLogo($organization, $request->file('logo'));

        return back()->with('success', 'Logótipo atualizado com sucesso.');
    }
}
