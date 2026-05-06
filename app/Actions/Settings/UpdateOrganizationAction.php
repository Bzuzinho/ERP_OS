<?php

namespace App\Actions\Settings;

use App\Models\Organization;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UpdateOrganizationAction
{
    public function execute(Organization $organization, array $data): Organization
    {
        $organization->update([
            'name'            => $data['name'],
            'code'            => $data['code'] ?? $organization->code,
            'nif'             => $data['nif'] ?? null,
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'phone_secondary' => $data['phone_secondary'] ?? null,
            'fax'             => $data['fax'] ?? null,
            'website'         => $data['website'] ?? null,
            'address'         => $data['address'] ?? null,
            'postal_code'     => $data['postal_code'] ?? null,
            'city'            => $data['city'] ?? null,
            'county'          => $data['county'] ?? null,
            'district'        => $data['district'] ?? null,
            'country'         => $data['country'] ?? 'Portugal',
            'president_name'  => $data['president_name'] ?? null,
            'iban'            => $data['iban'] ?? null,
            'facebook_url'    => $data['facebook_url'] ?? null,
            'instagram_url'   => $data['instagram_url'] ?? null,
            'description'     => $data['description'] ?? null,
        ]);

        return $organization->fresh();
    }

    public function updateLogo(Organization $organization, UploadedFile $file): Organization
    {
        if ($organization->logo_path) {
            Storage::disk('public')->delete($organization->logo_path);
        }

        $path = $file->store('organizations/logos', 'public');

        $organization->update(['logo_path' => $path]);

        return $organization->fresh();
    }
}
