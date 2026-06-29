<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ship extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeForOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function captainAssignments(): HasMany
    {
        return $this->hasMany(ShipCaptainAssignment::class);
    }

    public function activeCaptainAssignment(): HasOne
    {
        return $this->hasOne(ShipCaptainAssignment::class)->where('is_active', true)->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FishDeliveryInvoice::class);
    }

    public function monthlyClosings(): HasMany
    {
        return $this->hasMany(MonthlyClosing::class);
    }
}
