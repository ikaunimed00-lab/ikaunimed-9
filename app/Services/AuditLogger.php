<?php

namespace App\Services;

use App\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    public const MAX_STRING_LENGTH = 1000;

    /**
     * Never stored, at any depth of the changes payload.
     *
     * @var list<string>
     */
    public const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
        'two_factor_secret',
        'two_factor_recovery_codes',
        '_token',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $context = [];

    /**
     * @param  array<string, mixed>  $changes
     */
    public function record(string $action, ?Model $subject = null, array $changes = [], ?int $userId = null): ?AuditLog
    {
        $payload = $this->sanitize([...$changes, ...$this->context]);

        try {
            return AuditLog::query()->create([
                'user_id' => $userId ?? Auth::id(),
                'aksi' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'perubahan' => $payload === [] ? null : $payload,
                'ip' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Old/new values of the given attributes that changed in the last save.
     *
     * @param  list<string>|null  $only
     * @return array{old: array<string, mixed>, new: array<string, mixed>}|array{}
     */
    public function changesOf(Model $model, ?array $only = null): array
    {
        $changed = collect($model->getChanges())
            ->except(['updated_at', 'created_at'])
            ->when($only !== null, fn ($changes) => $changes->only($only));

        if ($changed->isEmpty()) {
            return [];
        }

        return [
            'old' => $changed->keys()->mapWithKeys(fn (string $key): array => [$key => $model->getOriginal($key)])->all(),
            'new' => $changed->keys()->mapWithKeys(fn (string $key): array => [$key => $model->getAttribute($key)])->all(),
        ];
    }

    /**
     * Adds extra fields (e.g. ['massal' => true]) to every entry recorded inside the callback.
     *
     * @template TReturn
     *
     * @param  array<string, mixed>  $context
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function withContext(array $context, Closure $callback): mixed
    {
        $previous = $this->context;
        $this->context = [...$previous, ...$context];

        try {
            return $callback();
        } finally {
            $this->context = $previous;
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private function sanitize(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(Str::lower($key), self::SENSITIVE_KEYS, true)) {
                continue;
            }

            $clean[$key] = match (true) {
                is_array($value) => $this->sanitize($value),
                $value instanceof \DateTimeInterface => $value->format(DATE_ATOM),
                is_string($value) && mb_strlen($value) > self::MAX_STRING_LENGTH => mb_substr($value, 0, self::MAX_STRING_LENGTH).'…',
                is_scalar($value) || $value === null => $value,
                default => (string) json_encode($value),
            };
        }

        return $clean;
    }
}
