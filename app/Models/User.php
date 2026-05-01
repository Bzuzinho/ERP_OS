<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'organization_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function closedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'closed_by');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function completedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'completed_by');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function uploadedDocumentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'uploaded_by');
    }

    public function createdDocumentAccessRules(): HasMany
    {
        return $this->hasMany(DocumentAccessRule::class, 'created_by');
    }

    public function createdMeetingMinutes(): HasMany
    {
        return $this->hasMany(MeetingMinute::class, 'created_by');
    }

    public function approvedMeetingMinutes(): HasMany
    {
        return $this->hasMany(MeetingMinute::class, 'approved_by');
    }

    public function requestedSpaceReservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class, 'requested_by_user_id');
    }

    public function approvedSpaceReservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class, 'approved_by');
    }

    public function rejectedSpaceReservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class, 'rejected_by');
    }

    public function cancelledSpaceReservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class, 'cancelled_by');
    }

    public function decidedSpaceReservationApprovals(): HasMany
    {
        return $this->hasMany(SpaceReservationApproval::class, 'decided_by');
    }

    public function assignedSpaceMaintenanceRecords(): HasMany
    {
        return $this->hasMany(SpaceMaintenanceRecord::class, 'assigned_to');
    }

    public function completedSpaceMaintenanceRecords(): HasMany
    {
        return $this->hasMany(SpaceMaintenanceRecord::class, 'completed_by');
    }

    public function assignedSpaceCleaningRecords(): HasMany
    {
        return $this->hasMany(SpaceCleaningRecord::class, 'assigned_to');
    }

    public function completedSpaceCleaningRecords(): HasMany
    {
        return $this->hasMany(SpaceCleaningRecord::class, 'completed_by');
    }

    public function inventoryLocationResponsibilities(): HasMany
    {
        return $this->hasMany(InventoryLocation::class, 'responsible_user_id');
    }

    public function requestedInventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'requested_by');
    }

    public function handledInventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'handled_by');
    }

    public function loanedInventoryLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'loaned_by');
    }

    public function receivedInventoryReturns(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'returned_to');
    }

    public function requestedInventoryRestocks(): HasMany
    {
        return $this->hasMany(InventoryRestockRequest::class, 'requested_by');
    }

    public function approvedInventoryRestocks(): HasMany
    {
        return $this->hasMany(InventoryRestockRequest::class, 'approved_by');
    }

    public function reportedInventoryBreakages(): HasMany
    {
        return $this->hasMany(InventoryBreakage::class, 'reported_by');
    }

    public function resolvedInventoryBreakages(): HasMany
    {
        return $this->hasMany(InventoryBreakage::class, 'resolved_by');
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
