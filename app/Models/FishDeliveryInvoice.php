<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishDeliveryInvoice extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'owner_id',
        'ship_id',
        'captain_id',
        'invoice_number',
        'invoice_date',
        'carrier_boat_name',
        'total_boxes',
        'shipping_cost',
        'unloading_cost_per_box',
        'total_unloading_cost',
        'additional_expense',
        'total_expense',
        'total_income',
        'net_income',
        'status',
        'notes',
        'client_uuid',
        'sync_status',
        'created_by',
        'posted_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'posted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function scopeForOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Captain::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FishDeliveryInvoiceItem::class, 'invoice_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(InvoiceExpense::class, 'invoice_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_POSTED => 'Sudah Diposting',
            self::STATUS_CLOSED => 'Sudah Ditutup',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
