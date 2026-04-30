<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateInventoryItemAction
{
    public function __construct(
        private readonly RegisterInventoryMovementAction $registerMovementAction,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $creator, array $data): InventoryItem
    {
        return DB::transaction(function () use ($creator, $data) {
            $organizationId = $creator->organization_id;
            $baseSlug = Str::slug($data['slug'] ?? $data['name']);
            $slug = $this->makeUniqueSlug($organizationId, $baseSlug);
            $initialStock = (float) ($data['current_stock'] ?? 0);

            $item = InventoryItem::create([
                ...$data,
                'organization_id' => $organizationId,
                'slug' => $slug,
                'current_stock' => 0,
            ]);

            if ($initialStock > 0) {
                $this->registerMovementAction->execute($item, $creator, [
                    'movement_type' => 'entry',
                    'quantity' => $initialStock,
                    'unit_cost' => $item->unit_cost,
                    'notes' => 'Stock inicial do item.',
                ]);
            }

            $this->activityLogger->log(
                subject: $item,
                action: 'inventory.item.created',
                user: $creator,
                organization: $item->organization,
                newValues: $item->only(['name', 'slug', 'item_type', 'status', 'current_stock', 'minimum_stock']),
                description: 'Item de inventario criado.',
            );

            return $item;
        });
    }

    private function makeUniqueSlug(?int $organizationId, string $baseSlug): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'item';
        $counter = 1;

        while (InventoryItem::query()->where('organization_id', $organizationId)->where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug.'-'.$counter;
        }

        return $slug;
    }
}
