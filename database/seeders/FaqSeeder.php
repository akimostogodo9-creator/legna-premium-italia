<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * Questions du CDC §18.
 * Seule « Come posso ordinare? » a une réponse (elle décrit le fonctionnement du site, pas une donnée commerciale).
 * Les autres sont INACTIVES avec « [DA COMPLETARE] » : l'admin les complète puis les active.
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->exists()) {
            return; // ne jamais écraser les FAQ déjà saisies
        }

        $orderAnswer = '<p>Scegli il prodotto che ti interessa e premi il pulsante <strong>«Ordina»</strong>: '
            .'si aprirà la tua app di posta con un messaggio già compilato. Completa quantità e indirizzo di consegna, '
            .'poi premi <strong>«Invia»</strong>. Ti risponderemo per confermare disponibilità, prezzo e consegna.</p>';

        $faqs = [
            ['Ordini', 'Come posso ordinare?', $orderAnswer, true, true],
            ['Prodotti', 'Quali tipi di legna vendete?', null, true, false],
            ['Contatti', 'Come posso contattarvi?', null, false, false],
            ['Consegna', 'Dove effettuate le consegne?', null, true, false],
            ['Consegna', 'Quanto costa la consegna?', null, false, false],
            ['Prodotti', 'Come posso conoscere la disponibilità?', null, false, false],
            ['Prezzi', 'Come posso ricevere informazioni sui prezzi?', null, true, false],
        ];

        foreach ($faqs as $order => [$topic, $question, $answer, $onHome, $active]) {
            Faq::create([
                'topic' => $topic,
                'question' => $question,
                'answer' => $answer ?? '<p>[DA COMPLETARE]</p>',
                'show_on_home' => $onHome,
                'is_active' => $active,
                'sort_order' => $order,
            ]);
        }
    }
}
