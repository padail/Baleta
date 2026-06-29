<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyClosingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_closing_id',
        'invoice_id',
        'ship_id',
        'captain_id',
        'ship_name',
        'captain_name',
        'invoice_date',
        'total_boxes',
        'total_income',
        'total_expense',
        'net_income',
        'operational_expense',
        'distributable_income',
        'captain_percentage',
        'captain_share',
        'owner_share',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'captain_percentage' => 'decimal:2',
        ];
    }

    public function monthlyClosing(): BelongsTo
    {
        return $this->belongsTo(MonthlyClosing::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FishDeliveryInvoice::class, 'invoice_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Captain::class);
    }
}
