<?php

// Textes de l'interface d'administration (tout en italien — R-ADM-04)
return [
    'app_name' => 'Legna Premium Italia',
    'area' => 'Area amministrazione',

    'nav' => [
        'group' => 'Gestione',
        'dashboard' => 'Pannello',
        'products' => 'Prodotti',
        'categories' => 'Categorie',
        'content' => 'Contenuti',
        'faq' => 'FAQ',
        'legal' => 'Pagine legali',
        'settings' => 'Impostazioni',
        'messages' => 'Messaggi',
        'users' => 'Utenti',
        'profile' => 'Profilo',
        'view_site' => 'Vedi il sito',
        'logout' => 'Esci',
    ],

    'actions' => [
        'save' => 'Salva',
        'cancel' => 'Annulla',
        'create' => 'Crea',
        'edit' => 'Modifica',
        'delete' => 'Elimina',
        'confirm' => 'Conferma',
        'back' => 'Indietro',
        'activate' => 'Attiva',
        'deactivate' => 'Disattiva',
    ],

    'messages' => [
        'saved' => 'Salvato con successo.',
        'deleted' => 'Eliminato con successo.',
        'confirm_delete' => 'Sei sicuro di voler eliminare questo elemento? L\'operazione non può essere annullata.',
        'unsaved_changes' => 'Ci sono modifiche non salvate. Vuoi davvero lasciare la pagina?',
        'coming_soon' => 'Questa sezione sarà disponibile a breve.',
    ],

    'auth' => [
        'login_title' => 'Accedi all\'area amministrazione',
        'login_description' => 'Inserisci email e password per accedere',
        'email' => 'Email',
        'email_placeholder' => 'email@esempio.it',
        'password' => 'Password',
        'remember' => 'Ricordami',
        'login' => 'Accedi',
        'forgot' => 'Password dimenticata?',
        'inactive' => 'Questo account è disattivato. Contatta un altro amministratore.',
        'show_password' => 'Mostra password',
        'hide_password' => 'Nascondi password',

        // Sprint 2 (T9) : pages « mot de passe oublié », « réinitialisation », « confirmation »
        'forgot_title' => 'Password dimenticata',
        'forgot_description' => 'Inserisci la tua email: ti invieremo un link per scegliere una nuova password',
        'send_link' => 'Invia il link',
        'back_to_login' => 'Torna all\'accesso',
        'reset_title' => 'Scegli una nuova password',
        'reset_description' => 'Almeno 12 caratteri, con lettere e numeri',
        'new_password' => 'Nuova password',
        'confirm_password' => 'Conferma password',
        'reset_button' => 'Salva la password',
        'confirm_title' => 'Conferma la password',
        'confirm_description' => 'Area protetta: conferma la tua password per continuare',
        'confirm_button' => 'Conferma',
    ],

    'users' => [
        'title' => 'Utenti amministratori',
        'new' => 'Nuovo amministratore',
        'name' => 'Nome',
        'email' => 'Email',
        'status' => 'Stato',
        'active' => 'Attivo',
        'inactive' => 'Disattivato',
        'last_login' => 'Ultimo accesso',
        'never' => 'Mai',
        'you' => '(tu)',
        'created' => 'Amministratore creato. Gli abbiamo inviato un\'email per scegliere la password.',
        'cannot_change_self' => 'Non puoi disattivare o eliminare il tuo account.',
        'cannot_remove_last' => 'Deve rimanere almeno un amministratore attivo.',
    ],

    'profile' => [
        'title' => 'Il mio profilo',
        'current_password' => 'Password attuale',
        'new_password' => 'Nuova password',
        'confirm_password' => 'Conferma password',
        'password_section' => 'Cambia password',
    ],
];
