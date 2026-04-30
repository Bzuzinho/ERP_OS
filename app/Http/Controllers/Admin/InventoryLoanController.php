<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\CreateInventoryLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryLoanRequest;
use App\Models\Contact;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryLoanController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryLoan::class);

        $status = $request->string('status')->toString();
        $itemId = $request->string('item_id')->toString();
        $borrower = $request->string('borrower')->toString();
        $overdue = $request->boolean('overdue');

        $loans = InventoryLoan::query()
            ->with(['item:id,name,sku', 'borrowerUser:id,name', 'borrowerContact:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($itemId, fn ($query) => $query->where('inventory_item_id', $itemId))
            ->when($borrower, fn ($query) => $query
                ->whereHas('borrowerUser', fn ($builder) => $builder->where('name', 'like', "%{$borrower}%"))
                ->orWhereHas('borrowerContact', fn ($builder) => $builder->where('name', 'like', "%{$borrower}%")))
            ->when($overdue, fn ($query) => $query->where(function ($builder) {
                $builder->where('status', 'overdue')
                    ->orWhere(function ($activeBuilder) {
                        $activeBuilder->where('status', 'active')
                            ->whereNotNull('expected_return_at')
                            ->where('expected_return_at', '<', now());
                    });
            }))
            ->latest('loaned_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/InventoryLoans/Index', [
            'loans' => $loans,
            'items' => InventoryItem::query()->select('id', 'name', 'sku')->orderBy('name')->get(),
            'statuses' => InventoryLoan::STATUSES,
            'filters' => compact('status', 'itemId', 'borrower', 'overdue'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InventoryLoan::class);

        return Inertia::render('Admin/InventoryLoans/Create', [
            'items' => InventoryItem::query()->where('is_loanable', true)->select('id', 'name', 'sku', 'current_stock')->orderBy('name')->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'contacts' => Contact::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreInventoryLoanRequest $request, CreateInventoryLoanAction $action): RedirectResponse
    {
        try {
            $loan = $action->execute($request->user(), $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['loan' => $exception->getMessage()]);
        }

        return to_route('admin.inventory-loans.show', $loan)->with('success', 'Emprestimo criado com sucesso.');
    }

    public function show(InventoryLoan $inventoryLoan): Response
    {
        $this->authorize('view', $inventoryLoan);

        $inventoryLoan->load([
            'item:id,name,sku,unit',
            'borrowerUser:id,name',
            'borrowerContact:id,name',
            'loanedBy:id,name',
            'returnedTo:id,name',
            'relatedTicket:id,reference,title',
            'relatedTask:id,title',
            'relatedEvent:id,title',
            'relatedSpaceReservation:id,purpose,status',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/InventoryLoans/Show', [
            'loan' => $inventoryLoan,
            'canReturn' => request()->user()->can('return', $inventoryLoan),
        ]);
    }
}
