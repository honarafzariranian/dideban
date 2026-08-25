<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOrganization;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_percent',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:3',
        'tax_percent' => 'decimal:2',
        'total' => 'decimal:3',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Helper Methods
    public function calculateTotal(): void
    {
        $this->total = $this->quantity * $this->unit_price;
    }

    public function getTaxAmountAttribute(): float
    {
        return $this->total * ($this->tax_percent / 100);
    }

    public function getGrandTotalAttribute(): float
    {
        return $this->total + $this->tax_amount;
    }
}
