<?php
// Force re-fetch Dec 2025 payments for Marrakech and update snapshot
use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\Crm;
use Carbon\Carbon;

$crm  = app(Crm::class);
$sid  = 50970;
$date = '2025-12-31';

$page = 0; $all = [];
do {
    $r    = $crm->client()->get('/api/external/v1/bulk/payments', [
        'strStoreId' => $sid,
        'startDate'  => '2025-12-01',
        'endDate'    => '2025-12-31',
        'page'       => $page,
        'size'       => 500,
    ]);
    $all  = array_merge($all, $r['data'] ?? []);
    $more = $r['pagination']['hasMore'] ?? false;
    $page++;
    if ($more) sleep(2);
} while ($more && $page < 20);

echo 'Fetched: ' . count($all) . ' payments' . PHP_EOL;

$written = 0; $skipped = 0;
foreach ($all as $row) {
    $pid  = (int) ($row['ID'] ?? 0);
    if (!$pid) continue;

    $effRaw = $row['EFFECTIVE_DATE'] ?? null;
    $eff    = $effRaw ? Carbon::parse($effRaw)->setTimezone('Africa/Casablanca')->toDateString() : null;

    $updated = CrmPaymentSnapshot::where('crm_payment_id', $pid)
        ->where('snapshot_date', $date)
        ->update([
            'amount'           => $row['AMOUNT'] ?? null,
            'effective_date'   => $eff,
            'payment_type_id'  => $row['PAYMENT_TYPE_ID'] ?? null,
            'payment_type_name'=> $row['PAYMENT_TYPE_NAME'] ?? null,
            'payload'          => $row,
        ]);

    if ($updated) {
        $skipped++;
    } else {
        // New payment not in snapshot — insert it
        CrmPaymentSnapshot::create([
            'crm_payment_id'          => $pid,
            'snapshot_date'           => $date,
            'crm_store_id'            => $row['STR_STORE_ID']            ?? null,
            'student_id'              => $row['STUDENT_ID']              ?? null,
            'reference'               => $row['REFERENCE']               ?? null,
            'amount'                  => $row['AMOUNT']                  ?? null,
            'effective_date'          => $eff,
            'payment_method_id'       => $row['PAYMENT_METHOD_ID']       ?? null,
            'payment_method_name'     => $row['PAYMENT_METHOD_NAME']     ?? null,
            'payment_type_id'         => $row['PAYMENT_TYPE_ID']         ?? null,
            'payment_type_name'       => $row['PAYMENT_TYPE_NAME']       ?? null,
            'user_creation_id'        => $row['USER_CREATION']           ?? null,
            'user_creation_full_name' => $row['USER_CREATION_FULL_NAME'] ?? null,
            'date_creation'           => $row['DATE_CREATION']           ?? null,
            'payload'                 => $row,
            'payload_hash'            => hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE)),
        ]);
        $written++;
    }
}

echo 'Updated existing: ' . $skipped . PHP_EOL;
echo 'New inserted: '     . $written . PHP_EOL;

// Verify
$sum = CrmPaymentSnapshot::where('snapshot_date', $date)
    ->where('crm_store_id', $sid)
    ->where('payment_type_id', 1)
    ->whereRaw("DATE_FORMAT(effective_date,'%Y-%m')='2025-12'")
    ->sum('amount');
echo 'Dec 2025 Reglement after fix: ' . number_format($sum) . ' DH (target: 456,000)' . PHP_EOL;
