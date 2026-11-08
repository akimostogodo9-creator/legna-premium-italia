<?php

namespace Tests\Unit;

use App\Services\Content\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

// R-CONTENU-04 / étape 7 (XSS) — sprint 4
class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizer;
    }

    public function test_les_balises_autorisees_sont_gardees(): void
    {
        $html = '<h2>Titolo</h2><p><strong>Faggio</strong> <em>stagionato</em></p><ul><li>uno</li></ul>';

        $this->assertSame($html, $this->sanitizer->clean($html));
    }

    public function test_les_scripts_et_attributs_dangereux_sont_supprimes(): void
    {
        $clean = $this->sanitizer->clean('<p onclick="alert(1)">Ciao</p><script>alert(1)</script><img src=x onerror=alert(1)>');

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('Ciao', $clean);
    }

    public function test_les_liens_javascript_sont_neutralises(): void
    {
        $clean = $this->sanitizer->clean('<p><a href="javascript:alert(1)">x</a> <a href="https://example.test">ok</a></p>');

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('href="https://example.test"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_un_contenu_vide_devient_null(): void
    {
        $this->assertNull($this->sanitizer->clean('<p></p>'));
        $this->assertNull($this->sanitizer->clean('<p>&nbsp;</p>'));
        $this->assertNull($this->sanitizer->clean(null));
    }
}
