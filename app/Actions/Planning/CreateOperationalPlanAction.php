<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOperationalPlanAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $creator, array $data): OperationalPlan
    {
        return DB::transaction(function () use ($creator, $data) {
            $slug = $this->resolveSlug($creator->organization_id, $data['slug'] ?? null, $data['title']);
            $status = $data['status'] ?? 'draft';

            if (! in_array($status, ['draft', 'pending_approval'], true)) {
                $status = 'draft';
            }

            $plan = OperationalPlan::create([
                ...$data,
                'organization_id' => $creator->organization_id,
                'slug' => $slug,
                'status' => $status,
                'created_by' => $creator->id,
                'progress_percent' => 0,
            ]);

            foreach (($data['participants'] ?? []) as $participant) {
                $plan->participants()->create($participant);
            }

            foreach (($data['resources'] ?? []) as $resource) {
                $plan->resources()->create($resource);
            }

            $this->activityLogger->log(
                subject: $plan,
                action: 'planning.operational_plan.created',
                user: $creator,
                organization: $plan->organization,
                newValues: $plan->only(['title', 'slug', 'plan_type', 'status', 'visibility', 'start_date', 'end_date']),
                description: 'Plano operacional criado.',
            );

            return $plan;
        });
    }

    private function resolveSlug(?int $organizationId, ?string $providedSlug, string $title): string
    {
        $base = Str::slug($providedSlug ?: $title);
        $candidate = $base ?: Str::random(8);
        $counter = 1;

        while (OperationalPlan::query()
            ->where('organization_id', $organizationId)
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
