<?php

namespace App\Services\Stats;

use App\Models\Product;
use App\Services\Settings\SettingsService;
use App\Support\BotDetector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * R-STAT-03 : compteurs agrégés par jour, AUCUNE donnée personnelle.
 * Un clic ≠ une commande : l'admin le voit comme « richieste avviate » (sprint 10).
 * Les vues (R-STAT-01/02) sont comptées depuis le sprint 10 (recordView).
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

    /**
     * R-STAT-01/02 (sprint 10) : +1 vue par produit, au maximum une fois par visiteur et par N minutes.
     * L'empreinte (IP + navigateur + produit + jour, hachée avec un sel secret) reste
     * QUELQUES MINUTES en cache puis disparaît : rien n'est conservé en base, l'IP est irrécupérable.
     */
    public function recordView(Product $product, ?string $ip, ?string $userAgent): bool
    {
        if (BotDetector::isBot($userAgent)) {
            return false;
        }

        $salt = config('legna.stats_hash_salt') ?: config('app.key');
        $fingerprint = hash_hmac('sha256', implode('|', [$ip, $userAgent, $product->id, now()->toDateString()]), (string) $salt);
        $minutes = max(1, (int) app(SettingsService::class)->get('view_dedup_minutes', 30));

        // Cache::add n'écrit QUE si la clé n'existe pas encore : vrai = nouvelle visite
        if (! Cache::add('view:'.$fingerprint, 1, now()->addMinutes($minutes))) {
            return false;
        }

        $this->increment($product, 'view');

        return true;
    }
}
