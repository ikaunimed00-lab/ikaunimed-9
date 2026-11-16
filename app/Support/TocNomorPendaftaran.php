<?php

namespace App\Support;

class TocNomorPendaftaran
{
    public const MIN_SEQUENCE = 1;

    public const MAX_SEQUENCE = 4118;

    /**
     * Normalize loose registration inputs into "TOC-2026-XXXX".
     * Accepts: TOC-2026-0071, toc20260071, TOC 2026 0071, 0071, 71.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $compact = strtoupper(preg_replace('/\s+/', '', $trimmed) ?? '');

        if (preg_match('/^TOC-?2026-?(\d{1,4})$/', $compact, $matches) === 1) {
            return self::fromSequence((int) $matches[1]);
        }

        $digitsOnly = preg_replace('/\D+/', '', $compact) ?? '';

        if (preg_match('/^2026(\d{1,4})$/', $digitsOnly, $matches) === 1) {
            return self::fromSequence((int) $matches[1]);
        }

        if (preg_match('/^(\d{1,4})$/', $digitsOnly, $matches) === 1) {
            return self::fromSequence((int) $matches[1]);
        }

        return null;
    }

    private static function fromSequence(int $sequence): ?string
    {
        if ($sequence < self::MIN_SEQUENCE || $sequence > self::MAX_SEQUENCE) {
            return null;
        }

        return sprintf('TOC-2026-%04d', $sequence);
    }
}
