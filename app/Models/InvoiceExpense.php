<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceExpense extends Model
{
    use HasFactory;

    public const TYPE_SHIPPING = 'shipping';
    public const TYPE_UNLOADING = 'unloading';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'invoice_id',
        'expense_type',
        'description',
        'quantity',
        'unit_price',
        'amount',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FishDeliveryInvoice::class, 'invoice_id');
    }
}
