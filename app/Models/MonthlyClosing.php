<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyClosing extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'owner_id',
        'closing_number',
        'month',
        'year',
        'total_ships',
        'total_invoices',
        'total_boxes',
        'total_income',
        'total_expense',
        'net_income',
        'daily_net_income',
        'operational_expense_total',
        'distributable_income',
        'captain_percentage',
        'captain_share',
        'owner_share',
        'non_operational_expense_total',
        'owner_final_income',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'captain_percentage' => 'decimal:2',
        ];
    }

    public function scopeForOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi lama. Disimpan agar aplikasi yang sudah pernah migrasi tidak rusak.
     * Alur baru memakai shipItems().
     */
    public function items(): HasMany
    {
        return $this->hasMany(MonthlyClosingItem::class);
    }

    public function shipItems(): HasMany
    {
        return $this->hasMany(MonthlyClosingShipItem::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(OwnerExpense::class);
    }

    public function nonOperationalExpenses(): HasMany
    {
        return $this->hasMany(OwnerExpense::class)
            ->where('expense_type', OwnerExpense::TYPE_NON_OPERATIONAL);
    }
}
