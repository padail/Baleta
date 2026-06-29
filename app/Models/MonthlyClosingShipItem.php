<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyClosingShipItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_closing_id',
        'owner_id',
        'ship_id',
        'captain_id',
        'ship_name',
        'captain_name',
        'total_invoices',
        'total_boxes',
        'total_income',
        'total_invoice_expense',
        'total_daily_net_income',
        'total_ship_operational_expense',
        'net_after_ship_operational',
        'captain_percentage',
        'captain_share',
        'owner_share',
    ];

    protected function casts(): array
    {
        return [
            'captain_percentage' => 'decimal:2',
        ];
    }

    public function scopeForOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    public function monthlyClosing(): BelongsTo
    {
        return $this->belongsTo(MonthlyClosing::class);
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Captain::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(MonthlyClosingShipInvoiceItem::class);
    }

    public function operationalExpenses(): HasMany
    {
        return $this->hasMany(MonthlyClosingShipOperationalExpense::class);
    }
}
