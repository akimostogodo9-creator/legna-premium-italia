<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Stats\StatsRecorder;
use App\Support\BotDetector;
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

        // Sprint 10 : détection des robots commune (BotDetector)
        if (! BotDetector::isBot($request->userAgent())) {
            $stats->increment(Product::findOrFail($data['product_id']), $data['type']);
        }

        return response()->noContent();
    }
}
