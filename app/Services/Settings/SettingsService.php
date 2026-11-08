<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

// Lecture/écriture des paramètres [param] de l'étape 2, avec cache (étape 4 §6.1)
class SettingsService
{
    /** Valeur d'un paramètre, convertie selon son type. */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $value = $this->cast($all[$key]['value'], $all[$key]['type']);

        return $value ?? $default;
    }

    /** Vrai si le paramètre existe ET n'est pas vide (ex. e-mail de commande — R-ORDINA-05). */
    public function filled(string $key): bool
    {
        $value = $this->get($key);

        return ! ($value === null || $value === '' || $value === []);
    }

    /** Tous les paramètres d'un groupe : ['clé' => valeur]. */
    public function group(string $group): array
    {
        return collect($this->all())
            ->filter(fn (array $setting) => $setting['group'] === $group)
            ->map(fn (array $setting) => $this->cast($setting['value'], $setting['type']))
            ->all();
    }

    /**
     * Modifie un paramètre EXISTANT (créé par le seeder).
     * Refuse une clé inconnue : évite les fautes de frappe silencieuses.
     */
    public function set(string $key, mixed $value): void
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            throw new InvalidArgumentException("Parametro sconosciuto: {$key}");
        }

        $setting->update(['value' => $this->serialize($value, $setting->type)]);
        $this->flush();
    }

    /** Modifie plusieurs paramètres d'un coup (écran Impostazioni). */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function flush(): void
    {
        Cache::forget(config('legna.settings_cache_key'));
    }

    /** @return array<string, array{value: ?string, type: string, group: string}> */
    private function all(): array
    {
        return Cache::rememberForever(config('legna.settings_cache_key'), function () {
            return Setting::query()
                ->get(['key', 'value', 'type', 'group'])
                ->mapWithKeys(fn (Setting $s) => [$s->key => [
                    'value' => $s->value,
                    'type' => $s->type,
                    'group' => $s->group,
                ]])
                ->all();
        });
    }

    private function cast(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'bool' => $value === '1',
            'json' => json_decode($value, true),
            default => $value, // string, text, email, phone
        };
    }

    private function serialize(mixed $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'int' => (string) (int) $value,
            default => trim((string) $value),
        };
    }
}
