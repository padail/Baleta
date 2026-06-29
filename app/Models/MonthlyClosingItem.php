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
        'invoice_date',
        'total_boxes',
        'total_income',
        'total_expense',
        'net_income',
    ];

    protected function casts(): array
    {
        return ['invoice_date' => 'date'];
    }

    public function monthlyClosing(): BelongsTo
    {
        return $this->belongsTo(MonthlyClosing::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FishDeliveryInvoice::class, 'invoice_id');
    }
}
