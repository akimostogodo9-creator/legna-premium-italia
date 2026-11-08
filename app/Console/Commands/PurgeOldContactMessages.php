<?php

namespace App\Console\Commands;

use App\Services\Contact\ContactService;
use Illuminate\Console\Command;

// R-CONT-06 / F-SYS-01 (sprint 9) : minimisation des données (RGPD)
class PurgeOldContactMessages extends Command
{
    protected $signature = 'legna:purge-contact-messages';

    protected $description = 'Elimina i messaggi di contatto più vecchi della durata di conservazione';

    public function handle(ContactService $contact): int
    {
        $deleted = $contact->purgeOld();
        $this->info("Messaggi eliminati: {$deleted}");

        return self::SUCCESS;
    }
}
