<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// R-STAT : compteurs agrégés par jour, aucune donnée personnelle
class ProductDailyStat extends Model
{
    protected $fillable = ['product_id', 'date', 'views', 'order_clicks', 'whatsapp_clicks'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'order_clicks' => 'integer',
            'whatsapp_clicks' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
