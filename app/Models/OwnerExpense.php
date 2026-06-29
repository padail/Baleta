<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerExpense extends Model
{
    use HasFactory;

    public const TYPE_OPERATIONAL = 'operational';
    public const TYPE_NON_OPERATIONAL = 'non_operational';

    public const STATUS_POSTED = 'posted';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'owner_id',
        'monthly_closing_id',
        'ship_id',
        'expense_date',
        'expense_type',
        'description',
        'amount',
        'status',
        'created_by',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
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

    public function scopeForPeriod(Builder $query, int $month, int $year): Builder
    {
        return $query->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
    }

    public function scopeNonOperational(Builder $query): Builder
    {
        return $query->where('expense_type', self::TYPE_NON_OPERATIONAL);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function monthlyClosing(): BelongsTo
    {
        return $this->belongsTo(MonthlyClosing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return match ($this->expense_type) {
            self::TYPE_OPERATIONAL => 'Operasional Lama',
            self::TYPE_NON_OPERATIONAL => 'Non-Operasional Owner',
            default => $this->expense_type,
        };
    }
}
