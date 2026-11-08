<?php

namespace Tests\Unit;

use App\Services\Ordering\TemplateRenderer;
use PHPUnit\Framework\TestCase;

// R-ORDINA-07 — sprint 6
class TemplateRendererTest extends TestCase
{
    public function test_remplace_seulement_les_variables_autorisees(): void
    {
        $renderer = new TemplateRenderer;

        $result = $renderer->render('Prodotto: {nome_prodotto} {secret} {prezzo}', [
            'nome_prodotto' => 'Legna di faggio',
            'prezzo' => '€ 129,00',
            'secret' => 'NON DEVE APPARIRE',
        ]);

        $this->assertSame('Prodotto: Legna di faggio {secret} € 129,00', $result);
    }

    public function test_detecte_les_variables_inconnues(): void
    {
        $renderer = new TemplateRenderer;

        $this->assertSame([], $renderer->unknownVariables('Ciao {nome_prodotto} {url_prodotto}'));
        $this->assertSame(['nome', 'prezo'], $renderer->unknownVariables('{nome} {prezo} {nome}'));
    }
}
