<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishDeliveryInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'buyer_id',
        'fish_type',
        'box_count',
        'price_per_box',
        'subtotal',
        'notes',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FishDeliveryInvoice::class, 'invoice_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }
}
