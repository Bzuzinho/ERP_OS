<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentAccessRule;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Event;
use App\Models\InventoryBreakage;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryRestockRequest;
use App\Models\MeetingMinute;
use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceMaintenanceRecord;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use App\Policies\AttachmentPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ContactPolicy;
use App\Policies\DocumentAccessRulePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\DocumentTypePolicy;
use App\Policies\DocumentVersionPolicy;
use App\Policies\EventPolicy;
use App\Policies\InventoryBreakagePolicy;
use App\Policies\InventoryCategoryPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\InventoryLoanPolicy;
use App\Policies\InventoryLocationPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\InventoryRestockRequestPolicy;
use App\Policies\MeetingMinutePolicy;
use App\Policies\SpaceCleaningRecordPolicy;
use App\Policies\SpaceMaintenanceRecordPolicy;
use App\Policies\SpacePolicy;
use App\Policies\SpaceReservationPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(DocumentType::class, DocumentTypePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(DocumentVersion::class, DocumentVersionPolicy::class);
        Gate::policy(DocumentAccessRule::class, DocumentAccessRulePolicy::class);
        Gate::policy(MeetingMinute::class, MeetingMinutePolicy::class);
        Gate::policy(Space::class, SpacePolicy::class);
        Gate::policy(SpaceReservation::class, SpaceReservationPolicy::class);
        Gate::policy(SpaceMaintenanceRecord::class, SpaceMaintenanceRecordPolicy::class);
        Gate::policy(SpaceCleaningRecord::class, SpaceCleaningRecordPolicy::class);
        Gate::policy(InventoryCategory::class, InventoryCategoryPolicy::class);
        Gate::policy(InventoryLocation::class, InventoryLocationPolicy::class);
        Gate::policy(InventoryItem::class, InventoryItemPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(InventoryLoan::class, InventoryLoanPolicy::class);
        Gate::policy(InventoryRestockRequest::class, InventoryRestockRequestPolicy::class);
        Gate::policy(InventoryBreakage::class, InventoryBreakagePolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
