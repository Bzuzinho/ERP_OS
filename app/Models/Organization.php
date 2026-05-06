<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'code',
    'slug',
    'nif',
    'email',
    'phone',
    'phone_secondary',
    'fax',
    'website',
    'address',
    'postal_code',
    'city',
    'county',
    'district',
    'country',
    'president_name',
    'iban',
    'facebook_url',
    'instagram_url',
    'description',
    'logo_path',
    'is_active',
])]
class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(ServiceArea::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function documentTypes(): HasMany
    {
        return $this->hasMany(DocumentType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function meetingMinutes(): HasMany
    {
        return $this->hasMany(MeetingMinute::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}