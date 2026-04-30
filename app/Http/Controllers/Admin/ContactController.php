<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contacts\StoreContactRequest;
use App\Http\Requests\Contacts\UpdateContactRequest;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Contact::class);

        $search = $request->string('search')->toString();

        $contacts = Contact::query()
            ->with(['organization:id,name', 'user:id,name'])
            ->when($search, fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Contacts/Index', [
            'filters' => [
                'search' => $search,
            ],
            'contacts' => $contacts,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Contact::class);

        return Inertia::render('Admin/Contacts/Create', [
            'contactTypes' => Contact::TYPES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $addresses = $validated['addresses'] ?? [];
        unset($validated['addresses']);

        $contact = Contact::create([
            ...$validated,
            'organization_id' => $validated['organization_id'] ?? $request->user()->organization_id,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($addresses as $address) {
            $contact->addresses()->create($address);
        }

        return to_route('admin.contacts.show', $contact)->with('success', 'Contacto criado com sucesso.');
    }

    public function show(Contact $contact): Response
    {
        $this->authorize('view', $contact);

        $contact->load(['organization:id,name', 'user:id,name', 'addresses', 'tickets:id,reference,title,status,priority,contact_id,updated_at']);

        return Inertia::render('Admin/Contacts/Show', [
            'contact' => $contact,
        ]);
    }

    public function edit(Contact $contact): Response
    {
        $this->authorize('update', $contact);

        $contact->load('addresses');

        return Inertia::render('Admin/Contacts/Edit', [
            'contact' => $contact,
            'contactTypes' => Contact::TYPES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validated();
        $addresses = $validated['addresses'] ?? [];
        unset($validated['addresses']);

        $contact->update($validated);

        $keptAddressIds = [];

        foreach ($addresses as $addressData) {
            $addressId = $addressData['id'] ?? null;

            if ($addressId) {
                $address = $contact->addresses()->whereKey($addressId)->first();

                if ($address) {
                    $address->update($addressData);
                    $keptAddressIds[] = $address->id;
                }

                continue;
            }

            $newAddress = $contact->addresses()->create($addressData);
            $keptAddressIds[] = $newAddress->id;
        }

        $contact->addresses()->when($keptAddressIds !== [], fn ($query) => $query->whereNotIn('id', $keptAddressIds))->delete();

        return to_route('admin.contacts.show', $contact)->with('success', 'Contacto atualizado com sucesso.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return to_route('admin.contacts.index')->with('success', 'Contacto eliminado com sucesso.');
    }
}
