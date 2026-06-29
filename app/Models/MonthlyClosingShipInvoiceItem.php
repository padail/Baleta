<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyClosingShipInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_closing_ship_item_id',
        'invoice_id',
        'invoice_number',
        'invoice_date',
        'total_boxes',
        'total_income',
        'total_expense',
        'net_income',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
        ];
    }

    public function shipItem(): BelongsTo
    {
        return $this->belongsTo(MonthlyClosingShipItem::class, 'monthly_closing_ship_item_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FishDeliveryInvoice::class);
    }
}
