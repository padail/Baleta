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
        'closing_period_number',
        'period_label',
        'month',
        'year',
        'period_started_at',
        'period_ended_at',
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
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
            'period_started_at' => 'date',
            'period_ended_at' => 'date',
            'captain_percentage' => 'decimal:2',
        ];
    }

    public function scopeForOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }


    public function getDisplayPeriodAttribute(): string
    {
        return $this->period_label ?: 'Tutup Bulan '.($this->closing_period_number ?: '-');
    }

    public function getDisplayDateRangeAttribute(): string
    {
        if ($this->period_started_at && $this->period_ended_at) {
            return $this->period_started_at->format('d/m/Y').' sampai '.$this->period_ended_at->format('d/m/Y');
        }

        return $this->closed_at?->format('d/m/Y') ?: $this->created_at?->format('d/m/Y') ?: '-';
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
