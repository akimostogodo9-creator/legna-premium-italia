<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Stats\StatsRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

// Contrat étape 6 (08-09 §2) : POST /statistiche/clic → 204 / 422 / 429 — sprint 6
class StatsController extends Controller
{
    public function click(Request $request, StatsRecorder $stats): Response
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'type' => ['required', Rule::in(['order', 'whatsapp'])],
        ]);

        if (! $this->isBot($request)) {
            $stats->increment(Product::findOrFail($data['product_id']), $data['type']);
        }

        return response()->noContent();
    }

    /** R-STAT-04 : les robots connus ne sont pas comptés. */
    private function isBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless/i', (string) $request->userAgent());
    }
}
