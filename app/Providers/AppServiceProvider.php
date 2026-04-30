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
use App\Models\MeetingMinute;
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
use App\Policies\MeetingMinutePolicy;
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

        Vite::prefetch(concurrency: 3);
    }
}
