<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Seeder;

/**
 * Paramètres de l'étape 4 §6.1.
 * Coordonnées, e-mails et WhatsApp démarrent VIDES : les boutons correspondants sont masqués
 * tant que l'admin ne les a pas saisis (R-ORDINA-05, R-WA-04).
 * Un paramètre déjà modifié par l'admin n'est JAMAIS écrasé (firstOrCreate).
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as [$group, $key, $type, $value]) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'type' => $type,
                    'value' => match (true) {
                        $value === null => null,
                        $type === 'json' => json_encode($value, JSON_UNESCAPED_UNICODE),
                        $type === 'bool' => $value ? '1' : '0',
                        default => (string) $value,
                    },
                ],
            );
        }

        app(SettingsService::class)->flush();
    }

    /** @return array<int, array{0: string, 1: string, 2: string, 3: mixed}> [groupe, clé, type, valeur] */
    private function definitions(): array
    {
        $orderBody = <<<'TXT'
Buongiorno,

Vorrei ordinare il seguente prodotto:

Prodotto: {nome_prodotto}
Quantità desiderata:
Indirizzo di consegna:
CAP:
Città:
Telefono:

Vi prego di contattarmi per confermare la disponibilità, il prezzo e le modalità di consegna.

Cordiali saluti.
TXT;

        $availabilityBody = <<<'TXT'
Buongiorno,

Vorrei sapere quando sarà di nuovo disponibile il seguente prodotto:

Prodotto: {nome_prodotto}
Quantità desiderata:
CAP:
Città:
Telefono:

Grazie, resto in attesa di una vostra risposta.

Cordiali saluti.
TXT;

        $generalBody = <<<'TXT'
Buongiorno,

Vorrei ricevere maggiori informazioni sulla vostra legna da ardere.

Nome:
Telefono:
Città:

Cordiali saluti.
TXT;

        return [
            // Entreprise (R-LEG-03) — à remplir par le vendeur, jamais inventé
            ['azienda', 'company_name', 'string', null],
            ['azienda', 'company_address', 'text', null],
            ['azienda', 'company_vat', 'string', null],
            ['azienda', 'company_tax_code', 'string', null],
            ['azienda', 'company_rea', 'string', null],
            ['azienda', 'company_phone', 'phone', null],
            ['azienda', 'company_hours', 'text', null],
            ['azienda', 'delivery_area', 'text', null],

            // Contacts (R-ORDINA-01, R-CONT-03, R-WA)
            ['contatti', 'order_email', 'email', null],
            ['contatti', 'contact_email', 'email', null],
            ['contatti', 'whatsapp_number', 'phone', null],
            ['contatti', 'whatsapp_message', 'text', 'Buongiorno, vorrei ricevere maggiori informazioni sulla vostra legna da ardere.'],
            ['contatti', 'whatsapp_product_message', 'text', 'Buongiorno, vorrei ricevere informazioni su: {nome_prodotto}'],

            // Modèles « Ordina » (R-ORDINA-07/09, F-ORD-07)
            ['ordina', 'order_subject_template', 'string', 'Richiesta di ordine - {nome_prodotto}'],
            ['ordina', 'order_body_template', 'text', $orderBody],
            ['ordina', 'availability_subject_template', 'string', 'Richiesta di disponibilità - {nome_prodotto}'],
            ['ordina', 'availability_body_template', 'text', $availabilityBody],
            ['ordina', 'general_subject_template', 'string', 'Richiesta di informazioni - Legna da ardere'],
            ['ordina', 'general_body_template', 'text', $generalBody],

            // Catalogue (R-PRIX-04, R-ORD-02, R-LIV-04)
            ['catalogo', 'show_indicative_price_notice', 'bool', true],
            ['catalogo', 'products_per_page', 'int', 12],
            ['catalogo', 'featured_count', 'int', 4],
            ['catalogo', 'delivery_summary', 'text', null],

            // Formulaire de contact (R-CONT-05/06)
            ['modulo', 'contact_form_enabled', 'bool', true],
            ['modulo', 'contact_rate_limit_per_hour', 'int', 5],
            ['modulo', 'contact_retention_months', 'int', 24],

            // Images (R-IMG-02) et statistiques (R-STAT-02)
            ['immagini', 'image_max_mb', 'int', 8],
            ['statistiche', 'view_dedup_minutes', 'int', 30],

            // Identité visuelle (F-ACONT-05)
            ['identita', 'logo_light', 'string', null],
            ['identita', 'logo_dark', 'string', null],
            ['identita', 'favicon', 'string', null],
            ['identita', 'default_share_image', 'string', null],
            // Sprint 3 : texte de présentation du footer (texte générique fourni par le dossier, modifiable)
            ['identita', 'footer_description', 'text', 'Scopri la nostra selezione di legna da ardere e contattaci per ricevere informazioni sui prodotti e sulle modalità di consegna.'],
        ];
    }
}
