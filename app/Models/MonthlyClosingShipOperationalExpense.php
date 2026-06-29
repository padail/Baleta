<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyClosingShipOperationalExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_closing_ship_item_id',
        'description',
        'amount',
    ];

    public function shipItem(): BelongsTo
    {
        return $this->belongsTo(MonthlyClosingShipItem::class, 'monthly_closing_ship_item_id');
    }
}
