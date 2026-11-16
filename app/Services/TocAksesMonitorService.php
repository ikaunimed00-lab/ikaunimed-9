<?php

namespace App\Services;

use App\Models\TocAksesLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TocAksesMonitorService
{
    public function isIpBlocked(?string $ip): bool
    {
        if ($ip === null || $ip === '') {
            return false;
        }

        return Cache::has($this->blockKey($ip));
    }

    public function blockIp(?string $ip, string $reason = 'scan'): void
    {
        if ($ip === null || $ip === '') {
            return;
        }

        $minutes = (int) config('toc.login.scan_block_minutes', 60);
        Cache::put($this->blockKey($ip), [
            'reason' => $reason,
            'blocked_at' => now()->toIso8601String(),
        ], now()->addMinutes($minutes));
    }

    public function clearIpBlock(?string $ip): void
    {
        if ($ip === null || $ip === '') {
            return;
        }

        Cache::forget($this->blockKey($ip));
        Cache::forget($this->scanKey($ip));
    }

    public function record(Request $request, ?string $nomor, string $hasil, string $sumber): void
    {
        TocAksesLog::query()->create([
            'nomor_pendaftaran' => $nomor,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent() ? Str::limit($request->userAgent(), 500, '') : null,
            'hasil' => $hasil,
            'sumber' => $sumber,
            'created_at' => now(),
        ]);
    }

    public function trackDistinctNomor(?string $ip, string $nomor): void
    {
        if ($ip === null || $ip === '') {
            return;
        }

        $key = $this->scanKey($ip);
        $window = (int) config('toc.login.scan_window_minutes', 10);
        $payload = Cache::get($key, ['nomors' => [], 'started_at' => now()->timestamp]);

        if (! is_array($payload)) {
            $payload = ['nomors' => [], 'started_at' => now()->timestamp];
        }

        $nomors = collect($payload['nomors'] ?? [])
            ->push($nomor)
            ->unique()
            ->values()
            ->all();

        Cache::put($key, [
            'nomors' => $nomors,
            'started_at' => $payload['started_at'] ?? now()->timestamp,
        ], now()->addMinutes($window));
    }

    public function shouldBlockForScanning(?string $ip): bool
    {
        if ($ip === null || $ip === '') {
            return false;
        }

        $payload = Cache::get($this->scanKey($ip), []);
        $nomors = $payload['nomors'] ?? [];
        $threshold = (int) config('toc.login.scan_distinct_threshold', 30);

        return is_array($nomors) && count($nomors) > $threshold;
    }

    /**
     * @return Collection<int, array{ip: string, distinct_count: int, blocked_at: ?string, sample_nomors: list<string>}>
     */
    public function blockedIpAlerts(): Collection
    {
        $windowMinutes = (int) config('toc.login.scan_window_minutes', 10);
        $threshold = (int) config('toc.login.scan_distinct_threshold', 30);

        $recent = TocAksesLog::query()
            ->where('created_at', '>=', now()->subMinutes(max($windowMinutes, 60)))
            ->whereNotNull('ip')
            ->get(['ip', 'nomor_pendaftaran', 'created_at']);

        return $recent
            ->groupBy('ip')
            ->map(function (Collection $rows, string $ip) use ($threshold) {
                $distinct = $rows->pluck('nomor_pendaftaran')->filter()->unique()->values();
                $blocked = Cache::get($this->blockKey($ip));

                if ($distinct->count() <= $threshold && ! $blocked) {
                    return null;
                }

                return [
                    'ip' => $ip,
                    'distinct_count' => $distinct->count(),
                    'blocked_at' => is_array($blocked) ? ($blocked['blocked_at'] ?? null) : null,
                    'sample_nomors' => $distinct->take(5)->all(),
                ];
            })
            ->filter()
            ->sortByDesc('distinct_count')
            ->values();
    }

    private function blockKey(string $ip): string
    {
        return 'toc:scan:block:'.$ip;
    }

    private function scanKey(string $ip): string
    {
        return 'toc:scan:nomors:'.$ip;
    }
}
