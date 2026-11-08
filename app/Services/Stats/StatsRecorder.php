<?php

namespace App\Services\Stats;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * R-STAT-03 : compteurs agrégés par jour, AUCUNE donnée personnelle.
 * Un clic ≠ une commande : l'admin le voit comme « richieste avviate » (sprint 10).
 * Les vues (R-STAT-01/02) sont ajoutées au sprint 10.
 */
class StatsRecorder
{
    private const COLUMNS = ['order' => 'order_clicks', 'whatsapp' => 'whatsapp_clicks', 'view' => 'views'];

    public function increment(Product $product, string $type): void
    {
        $column = self::COLUMNS[$type] ?? null;

        if ($column === null) {
            return;
        }

        $now = now();

        // Une seule requête, sans risque de doublon même avec des clics simultanés.
        // $column vient de la liste fixe ci-dessus : jamais d'une donnée saisie.
        DB::table('product_daily_stats')->upsert(
            [[
                'product_id' => $product->id,
                'date' => $now->toDateString(),
                $column => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['product_id', 'date'],
            [$column => DB::raw("{$column} + 1"), 'updated_at' => $now],
        );
    }
}
