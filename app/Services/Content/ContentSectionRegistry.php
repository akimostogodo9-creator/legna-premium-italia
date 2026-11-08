<?php

namespace App\Services\Content;

use InvalidArgumentException;

/**
 * Catalogue des sections de contenu modifiables (R-CONTENU-01, étape 4 §5.1).
 *
 * Types de champs : text (une ligne), textarea (plusieurs lignes), richtext (éditeur),
 * image (fichier), items (liste de blocs — utilisé par « Perché sceglierci »).
 *
 * Les sections propres à l'entreprise (histoire, zones, frais, délais…) démarrent VIDES :
 * rien n'est inventé (R-LIV-03, CDC §16).
 */
class ContentSectionRegistry
{
    /** @return array<string, array<string, array{label: string, fields: array<string,string>, required: string[], defaults: array}>> */
    public function all(): array
    {
        return [
            'home' => [
                'hero' => [
                    'label' => 'Sezione principale (Hero)',
                    'fields' => [
                        'title' => 'text', 'subtitle' => 'textarea',
                        'primary_label' => 'text', 'secondary_label' => 'text',
                        'image' => 'image', 'image_alt' => 'text',
                    ],
                    'required' => ['title', 'primary_label'],
                    'defaults' => [
                        'title' => 'Legna da ardere di qualità per il tuo comfort quotidiano',
                        'subtitle' => 'Scopri la nostra selezione di legna da ardere e trova la soluzione ideale per riscaldare la tua casa.',
                        'primary_label' => 'Scopri i prodotti',
                        'secondary_label' => 'Contattaci',
                        'image' => null,
                        'image_alt' => 'Legna da ardere',
                    ],
                ],
                'presentazione' => [
                    'label' => 'Presentazione',
                    'fields' => ['title' => 'text', 'text' => 'richtext', 'image' => 'image', 'image_alt' => 'text'],
                    'required' => ['title'],
                    'defaults' => [
                        'title' => 'La nostra passione per la legna da ardere',
                        'text' => '<p>Scopri la nostra selezione di legna da ardere e contattaci per ricevere informazioni sui prodotti e sulle modalità di consegna.</p>',
                        'image' => null,
                        'image_alt' => null,
                    ],
                ],
                'prodotti' => [
                    'label' => 'Prodotti in evidenza',
                    'fields' => ['title' => 'text', 'subtitle' => 'text', 'button_label' => 'text'],
                    'required' => ['title'],
                    'defaults' => [
                        'title' => 'Scopri i nostri prodotti',
                        'subtitle' => 'Una selezione di legna da ardere pensata per le tue esigenze.',
                        'button_label' => 'Vedi tutti i prodotti',
                    ],
                ],
                'categorie' => [
                    'label' => 'Categorie',
                    'fields' => ['title' => 'text', 'subtitle' => 'text'],
                    'required' => ['title'],
                    'defaults' => ['title' => 'Le nostre categorie', 'subtitle' => null],
                ],
                'perche_sceglierci' => [
                    'label' => 'Perché sceglierci',
                    'fields' => ['title' => 'text', 'items' => 'items'],
                    'required' => ['title'],
                    'defaults' => [
                        'title' => 'Perché sceglierci',
                        // R-CONTENU-02 : max 4 blocs, chacun désactivable (seuls les avantages réels)
                        'items' => [
                            ['icon' => 'quality', 'title' => 'Qualità', 'text' => 'Una selezione di prodotti pensata per offrirti qualità.', 'visible' => true],
                            ['icon' => 'trust', 'title' => 'Affidabilità', 'text' => 'Informazioni chiare e un servizio attento alle esigenze dei clienti.', 'visible' => true],
                            ['icon' => 'delivery', 'title' => 'Consegna', 'text' => 'Scopri le nostre modalità di consegna.', 'visible' => true],
                            ['icon' => 'support', 'title' => 'Assistenza', 'text' => 'Contattaci per ricevere maggiori informazioni sui nostri prodotti.', 'visible' => true],
                        ],
                    ],
                ],
                'consegna' => [
                    'label' => 'Consegna (riepilogo)',
                    'fields' => ['title' => 'text', 'button_label' => 'text'],
                    'required' => ['title'],
                    // Le texte du résumé vient du paramètre « delivery_summary » (R-LIV-04)
                    'defaults' => ['title' => 'Consegna della legna da ardere', 'button_label' => 'Scopri le modalità di consegna'],
                ],
                'faq' => [
                    'label' => 'Domande frequenti',
                    'fields' => ['title' => 'text', 'button_label' => 'text'],
                    'required' => ['title'],
                    'defaults' => ['title' => 'Domande frequenti', 'button_label' => 'Tutte le domande'],
                ],
                'cta_finale' => [
                    'label' => 'Invito finale',
                    'fields' => ['title' => 'text', 'text' => 'textarea'],
                    'required' => ['title'],
                    'defaults' => [
                        'title' => 'Hai domande?',
                        'text' => 'Contattaci per ricevere maggiori informazioni sui nostri prodotti.',
                    ],
                ],
            ],

            'prodotti' => [
                'intro' => [
                    'label' => 'Introduzione del catalogo',
                    'fields' => ['title' => 'text', 'text' => 'textarea', 'image' => 'image'],
                    'required' => ['title'],
                    'defaults' => ['title' => 'Tutta la nostra legna da ardere', 'text' => null, 'image' => null],
                ],
            ],

            'chi_siamo' => [
                'intro' => [
                    'label' => 'Introduzione',
                    'fields' => ['title' => 'text', 'text' => 'richtext', 'image' => 'image'],
                    'required' => ['title'],
                    'defaults' => ['title' => 'Chi siamo', 'text' => null, 'image' => null],
                ],
                'storia' => $this->textBlock('La nostra storia'),
                'missione' => $this->textBlock('La nostra missione'),
                'valori' => $this->textBlock('I nostri valori'),
                'impegno' => $this->textBlock('Il nostro impegno verso i clienti'),
            ],

            'consegna' => [
                'intro' => [
                    'label' => 'Introduzione',
                    'fields' => ['title' => 'text', 'text' => 'richtext', 'image' => 'image'],
                    'required' => ['title'],
                    'defaults' => ['title' => 'Consegna della legna da ardere', 'text' => null, 'image' => null],
                ],
                'zone' => $this->textBlock('Zone di consegna'),
                'modalita' => $this->textBlock('Modalità di consegna'),
                'costi' => $this->textBlock('Costi di consegna'),
                'tempi' => $this->textBlock('Tempi di consegna'),
                'quantita_minime' => $this->textBlock('Quantità minime'),
                'ricezione' => $this->textBlock('Condizioni di ricezione'),
            ],

            'contatti' => [
                'intro' => [
                    'label' => 'Introduzione',
                    'fields' => ['title' => 'text', 'text' => 'textarea'],
                    'required' => ['title'],
                    'defaults' => [
                        'title' => 'Contatti',
                        'text' => 'Scrivici per ricevere informazioni sui nostri prodotti e sulle modalità di consegna.',
                    ],
                ],
            ],
        ];
    }

    /** Définition d'une section précise. */
    public function get(string $page, string $key): array
    {
        $definition = $this->all()[$page][$key] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException("Sezione sconosciuta: {$page}.{$key}");
        }

        return $definition;
    }

    /** Liste des pages qui ont des sections. */
    public function pages(): array
    {
        return array_keys($this->all());
    }

    /** Bloc « titre + texte riche » dont le texte démarre vide (contenu propre à l'entreprise). */
    private function textBlock(string $title): array
    {
        return [
            'label' => $title,
            'fields' => ['title' => 'text', 'body' => 'richtext', 'image' => 'image'],
            'required' => ['title'],
            'defaults' => ['title' => $title, 'body' => null, 'image' => null],
        ];
    }
}
