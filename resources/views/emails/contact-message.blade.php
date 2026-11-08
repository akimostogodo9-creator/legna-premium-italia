{{-- F-CONT-05 (sprint 9) : toutes les valeurs passent par {{ }} — échappées, aucun HTML du visiteur n'est interprété --}}
<!DOCTYPE html>
<html lang="it">
<body style="margin:0;padding:24px;background:#F5EFE5;font-family:Georgia,serif;color:#3B2418;">
    <table role="presentation" width="100%" style="max-width:600px;margin:0 auto;background:#FFFCF7;border-radius:12px;padding:28px;">
        <tr><td>
            <h1 style="margin:0 0 16px;font-family:Arial,sans-serif;font-size:20px;">Nuova richiesta di informazioni</h1>
            <p style="margin:0 0 4px;"><strong>Nome:</strong> {{ $msg->name }}</p>
            <p style="margin:0 0 4px;"><strong>Email:</strong> {{ $msg->email }}</p>
            @if ($msg->phone)
                <p style="margin:0 0 4px;"><strong>Telefono:</strong> {{ $msg->phone }}</p>
            @endif
            <p style="margin:0 0 16px;"><strong>Argomento:</strong> {{ $msg->subject->label() }}</p>
            <div style="white-space:pre-line;padding:16px;background:#F5EFE5;border-radius:8px;">{{ $msg->message }}</div>
            <p style="margin:24px 0 0;font-family:Arial,sans-serif;font-size:14px;">
                Rispondi direttamente a questa email per scrivere a {{ $msg->name }}.<br>
                <a href="{{ $adminUrl }}" style="color:#75462B;">Apri il messaggio nell'area amministrazione</a>
            </p>
        </td></tr>
    </table>
</body>
</html>
