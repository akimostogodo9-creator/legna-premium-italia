<?php

namespace App\Services\Admin;

use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductDailyStat;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * F-DASH-01→03 (sprint 10) : chiffres du tableau de bord.
 * R-STAT-05 : aucune notion de commande ni de chiffre d'affaires.
 */
class DashboardService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        return [
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'hidden' => Product::where('is_active', false)->count(),
            ],
            'unread' => ContactMessage::unread()->count(),
            'period7' => $this->totals(7),
            'period30' => $this->totals(30),
            'topProducts' => $this->topProducts(30, 5),
            'daily' => $this->dailyViews(30),
            'latestMessages' => ContactMessage::latest()->latest('id')->limit(5)->get()->map(fn (ContactMessage $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'subject' => $m->subject->label(),
                'status' => $m->status->value,
                'received' => $m->created_at->format('d/m H:i'),
            ]),
        ];
    }

    /** @return array{views: int, order_clicks: int, whatsapp_clicks: int} */
    private function totals(int $days): array
    {
        $row = ProductDailyStat::query()
            ->where('date', '>=', now()->subDays($days - 1)->toDateString())
            ->selectRaw('COALESCE(SUM(views),0) AS views, COALESCE(SUM(order_clicks),0) AS order_clicks, COALESCE(SUM(whatsapp_clicks),0) AS whatsapp_clicks')
            ->toBase()
            ->first();

        return [
            'views' => (int) $row->views,
            'order_clicks' => (int) $row->order_clicks,
            'whatsapp_clicks' => (int) $row->whatsapp_clicks,
        ];
    }

    /** @return list<array{id: int, name: string, views: int, requests: int}> */
    private function topProducts(int $days, int $limit): array
    {
        return ProductDailyStat::query()
            ->join('products', 'products.id', '=', 'product_daily_stats.product_id')
            ->where('product_daily_stats.date', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('products.id', 'products.name')
            ->orderByDesc(DB::raw('SUM(product_daily_stats.views)'))
            ->limit($limit)
            ->toBase()
            ->get([
                'products.id',
                'products.name',
                DB::raw('SUM(product_daily_stats.views) AS views'),
                DB::raw('SUM(product_daily_stats.order_clicks + product_daily_stats.whatsapp_clicks) AS requests'),
            ])
            ->map(fn (object $row) => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'views' => (int) $row->views,
                'requests' => (int) $row->requests,
            ])
            ->values()
            ->all();
    }

    /**
     * Vues par jour, jours sans visite inclus (valeur 0).
     *
     * @return list<array{date: string, views: int}>
     */
    private function dailyViews(int $days): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $values = ProductDailyStat::query()
            ->where('date', '>=', $from->toDateString())
            ->groupBy('date')
            ->selectRaw('date, SUM(views) AS views')
            ->toBase()
            ->pluck('views', 'date')
            ->mapWithKeys(fn ($v, $date) => [substr((string) $date, 0, 10) => (int) $v]);

        return collect(CarbonPeriod::create($from, now()->startOfDay()))
            ->map(fn (CarbonInterface $day) => [
                'date' => $day->format('d/m'),
                'views' => $values[$day->toDateString()] ?? 0,
            ])
            ->values()
            ->all();
    }
}
