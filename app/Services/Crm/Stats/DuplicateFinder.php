<?php

namespace App\Services\Crm\Stats;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Scans student records returned by the Wimschool CRM and identifies likely
 * duplicates by phone, WhatsApp, email, CIN, and name+center.
 *
 * The CRM API doesn't expose a server-side dedup endpoint, so we pull pages of
 * students into memory and bucket them client-side. Cached 5 min.
 */
class DuplicateFinder
{
    public const CACHE_TTL = 300;
    public const PAGE_SIZE = 100;
    public const MAX_PAGES = 30; // 3000 students per scan — protects against runaway scans

    public function __construct(protected Crm $crm)
    {
    }

    /**
     * @return array{
     *   scanned: int,
     *   centers: int|null,
     *   groups: array<string, array<int, array<int, array<string,mixed>>>>,
     *   summary: array<string, int>,
     *   error: ?string,
     *   scanned_at: string,
     * }
     */
    public function find(?int $strStoreId = null, bool $bustCache = false): array
    {
        $cacheKey = 'crm.duplicates:' . ($strStoreId ?: 'all');
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($strStoreId) {
            return $this->scan($strStoreId);
        });
    }

    /**
     * @return array<string,mixed>
     */
    protected function scan(?int $strStoreId): array
    {
        $allStudents = [];
        $error = null;
        $centers = [];

        try {
            for ($page = 0; $page < self::MAX_PAGES; $page++) {
                $resp = $this->crm->students()->list(
                    page: $page,
                    size: self::PAGE_SIZE,
                    strStoreId: $strStoreId,
                );
                $batch = $resp['data'] ?? [];
                if (empty($batch)) break;

                foreach ($batch as $s) {
                    $allStudents[] = $s;
                    if (!empty($s['STR_STORE_ID'])) {
                        $centers[(int) $s['STR_STORE_ID']] = true;
                    }
                }

                $hasNext = $resp['pagination']['hasNext'] ?? $resp['pagination']['hasMore'] ?? false;
                if (!$hasNext) break;
            }
        } catch (CrmException $e) {
            $error = $e->getMessage();
        }

        return [
            'scanned'    => count($allStudents),
            'centers'    => count($centers) ?: null,
            'groups'     => $this->bucket($allStudents),
            'summary'    => $this->summarize($allStudents),
            'error'      => $error,
            'scanned_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Group students by every detection key, keep only buckets with > 1 entry.
     *
     * @param  array<int, array<string,mixed>>  $students
     * @return array<string, array<int, array<int, array<string,mixed>>>>
     */
    protected function bucket(array $students): array
    {
        $byPhone     = [];
        $byWhatsapp  = [];
        $byEmail     = [];
        $byCin       = [];
        $byNameStore = [];

        foreach ($students as $s) {
            $phone = $this->normalizePhone($s['PHONE_NUMBER'] ?? null);
            if ($phone) $byPhone[$phone][] = $s;

            $wa = $this->normalizePhone($s['WHATSAPP_NUMBER'] ?? null);
            if ($wa && $wa !== $phone) $byWhatsapp[$wa][] = $s;

            $email = $this->normalizeEmail($s['EMAIL'] ?? null);
            if ($email) $byEmail[$email][] = $s;

            $cin = $this->normalizeCin($s['CNE'] ?? $s['IDENTITY_ID'] ?? null);
            if ($cin) $byCin[$cin][] = $s;

            $nameKey = $this->nameKey($s);
            if ($nameKey) $byNameStore[$nameKey][] = $s;
        }

        return [
            'phone'     => $this->onlyDuplicates($byPhone),
            'whatsapp'  => $this->onlyDuplicates($byWhatsapp),
            'email'     => $this->onlyDuplicates($byEmail),
            'cin'       => $this->onlyDuplicates($byCin),
            'name'      => $this->onlyDuplicates($byNameStore),
        ];
    }

    /**
     * @param  array<string, array<int, array<string,mixed>>>  $bucket
     * @return array<int, array<int, array<string,mixed>>>
     */
    protected function onlyDuplicates(array $bucket): array
    {
        $out = [];
        foreach ($bucket as $key => $group) {
            if (count($group) > 1) {
                $out[] = ['key' => $key, 'count' => count($group), 'students' => $group];
            }
        }
        // Show the biggest groups first.
        usort($out, fn ($a, $b) => $b['count'] <=> $a['count']);
        return $out;
    }

    /**
     * @param  array<int, array<string,mixed>>  $students
     */
    protected function summarize(array $students): array
    {
        $withPhone    = 0;
        $withEmail    = 0;
        $withWhatsapp = 0;
        $withCin      = 0;
        foreach ($students as $s) {
            if ($this->normalizePhone($s['PHONE_NUMBER'] ?? null)) $withPhone++;
            if ($this->normalizePhone($s['WHATSAPP_NUMBER'] ?? null)) $withWhatsapp++;
            if ($this->normalizeEmail($s['EMAIL'] ?? null)) $withEmail++;
            if ($this->normalizeCin($s['CNE'] ?? $s['IDENTITY_ID'] ?? null)) $withCin++;
        }
        return [
            'with_phone'    => $withPhone,
            'with_whatsapp' => $withWhatsapp,
            'with_email'    => $withEmail,
            'with_cin'      => $withCin,
        ];
    }

    /** Normalize phone: keep digits only, drop empty & obviously bogus values. */
    protected function normalizePhone(?string $v): ?string
    {
        if (empty($v)) return null;
        $digits = preg_replace('/\D+/', '', $v);
        // Reject single-digit, all-zero, repeated-digit placeholders ("00000", "11111").
        if (!$digits || strlen($digits) < 6) return null;
        if (preg_match('/^(\d)\1+$/', $digits)) return null;
        return $digits;
    }

    protected function normalizeEmail(?string $v): ?string
    {
        if (empty($v)) return null;
        $v = trim(strtolower($v));
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) return null;
        return $v;
    }

    protected function normalizeCin(?string $v): ?string
    {
        if (empty($v)) return null;
        $v = strtoupper(preg_replace('/[\s\-]+/', '', $v));
        // Reject placeholder CINs like "00000"
        if (!$v || preg_match('/^0+$/', $v)) return null;
        return $v;
    }

    protected function nameKey(array $s): ?string
    {
        $first = trim((string) ($s['FIRST_NAME'] ?? ''));
        $last  = trim((string) ($s['LAST_NAME']  ?? ''));
        if ($first === '' || $last === '') return null;
        $store = (int) ($s['STR_STORE_ID'] ?? 0);
        return $store . '|' . Str::lower(Str::ascii($first . ' ' . $last));
    }
}
