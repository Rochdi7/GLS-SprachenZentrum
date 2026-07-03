<?php

namespace App\Services\Payroll;

use App\Models\PayrollStatusLog;
use App\Models\PresenceImport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Drives the payroll lifecycle: draft → validated → paid → locked (+ the
 * validated → draft rollback and the Super-Admin unlock escape hatch).
 *
 * Every transition is transactional and writes an immutable PayrollStatusLog
 * row. This service NEVER performs the payment calculation itself — it only
 * changes state and records who/when — so the calculation services stay
 * untouched.
 */
class PayrollLifecycleService
{
    /**
     * draft → validated. Freezes the calculation.
     */
    public function validate(PresenceImport $import, User $user, ?string $comment = null): PresenceImport
    {
        $this->assertTransition($import, PresenceImport::STATUS_VALIDATED, $user);

        return DB::transaction(function () use ($import, $user, $comment) {
            $from = $import->status;
            $import->update([
                'status'       => PresenceImport::STATUS_VALIDATED,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]);
            $this->log($import, $user, PayrollStatusLog::ACTION_VALIDATE, $from, PresenceImport::STATUS_VALIDATED, $comment);

            return $import;
        });
    }

    /**
     * validated → draft. Re-opens the import for editing. Clears the
     * validated_by/at stamp so it reflects the current (open) state.
     */
    public function returnToDraft(PresenceImport $import, User $user, ?string $comment = null): PresenceImport
    {
        $this->assertTransition($import, PresenceImport::STATUS_DRAFT, $user);

        return DB::transaction(function () use ($import, $user, $comment) {
            $from = $import->status;
            $import->update([
                'status'       => PresenceImport::STATUS_DRAFT,
                'validated_by' => null,
                'validated_at' => null,
            ]);
            $this->log($import, $user, PayrollStatusLog::ACTION_RETURN_TO_DRAFT, $from, PresenceImport::STATUS_DRAFT, $comment);

            return $import;
        });
    }

    /**
     * validated → paid. Requires payment date + method.
     *
     * @param array{payment_date:string,payment_method:string,payment_reference?:?string,payment_notes?:?string} $payment
     */
    public function markPaid(PresenceImport $import, User $user, array $payment, ?string $comment = null): PresenceImport
    {
        $this->assertTransition($import, PresenceImport::STATUS_PAID, $user);

        // Defensive server-side guard (the FormRequest validates too).
        if (empty($payment['payment_date']) || empty($payment['payment_method'])) {
            throw ValidationException::withMessages([
                'payment_date'   => empty($payment['payment_date']) ? 'La date de paiement est requise.' : [],
                'payment_method' => empty($payment['payment_method']) ? 'Le mode de paiement est requis.' : [],
            ]);
        }

        if (! in_array($payment['payment_method'], PresenceImport::PAYMENT_METHODS, true)) {
            throw ValidationException::withMessages(['payment_method' => 'Mode de paiement invalide.']);
        }

        return DB::transaction(function () use ($import, $user, $payment, $comment) {
            $from = $import->status;
            $import->update([
                'status'            => PresenceImport::STATUS_PAID,
                'payment_date'      => $payment['payment_date'],
                'payment_method'    => $payment['payment_method'],
                'payment_reference' => $payment['payment_reference'] ?? null,
                'payment_notes'     => $payment['payment_notes'] ?? null,
                'paid_by'           => $user->id,
                'paid_at'           => now(),
            ]);
            $this->log($import, $user, PayrollStatusLog::ACTION_MARK_PAID, $from, PresenceImport::STATUS_PAID, $comment, [
                'payment_date'      => $payment['payment_date'],
                'payment_method'    => $payment['payment_method'],
                'payment_reference' => $payment['payment_reference'] ?? null,
            ]);

            return $import;
        });
    }

    /**
     * paid → locked. Accounting close; fully immutable afterwards.
     */
    public function lock(PresenceImport $import, User $user, ?string $comment = null): PresenceImport
    {
        $this->assertTransition($import, PresenceImport::STATUS_LOCKED, $user);

        return DB::transaction(function () use ($import, $user, $comment) {
            $from = $import->status;
            $import->update([
                'status'    => PresenceImport::STATUS_LOCKED,
                'locked_by' => $user->id,
                'locked_at' => now(),
            ]);
            $this->log($import, $user, PayrollStatusLog::ACTION_LOCK, $from, PresenceImport::STATUS_LOCKED, $comment);

            return $import;
        });
    }

    /**
     * Super-Admin only: roll a LOCKED import back to an earlier state
     * (accounting correction). Clears the locked stamp.
     */
    public function unlock(PresenceImport $import, User $user, string $target, ?string $comment = null): PresenceImport
    {
        if (! $user->hasRole('Super Admin')) {
            throw ValidationException::withMessages(['status' => 'Seul un Super Admin peut déverrouiller un paiement.']);
        }
        $this->assertTransition($import, $target, $user);

        return DB::transaction(function () use ($import, $user, $target, $comment) {
            $from = $import->status;
            $updates = ['status' => $target, 'locked_by' => null, 'locked_at' => null];
            // If rolling back past "paid", clear payment stamp too.
            if ($target === PresenceImport::STATUS_VALIDATED || $target === PresenceImport::STATUS_DRAFT) {
                $updates += ['paid_by' => null, 'paid_at' => null];
            }
            if ($target === PresenceImport::STATUS_DRAFT) {
                $updates += ['validated_by' => null, 'validated_at' => null];
            }
            $import->update($updates);
            $this->log($import, $user, PayrollStatusLog::ACTION_UNLOCK, $from, $target, $comment);

            return $import;
        });
    }

    /**
     * Record a non-transition action (recalculation, override) in the audit
     * trail without changing status. Called from the payroll controller.
     */
    public function logAction(PresenceImport $import, User $user, string $action, ?string $comment = null, ?array $meta = null): void
    {
        $this->log($import, $user, $action, $import->status, $import->status, $comment, $meta);
    }

    /* ------------------------------------------------------------------ */

    protected function assertTransition(PresenceImport $import, string $target, User $user): void
    {
        if (! $import->canTransitionTo($target, $user)) {
            throw ValidationException::withMessages([
                'status' => "Transition non autorisée : {$import->statusLabel()} → " .
                    (PresenceImport::STATUS_LABELS[$target] ?? $target) . '.',
            ]);
        }
    }

    protected function log(PresenceImport $import, User $user, string $action, ?string $from, ?string $to, ?string $comment, ?array $meta = null): void
    {
        PayrollStatusLog::create([
            'presence_import_id' => $import->id,
            'user_id'            => $user->id,
            'action'             => $action,
            'from_status'        => $from,
            'to_status'          => $to,
            'comment'            => $comment,
            'meta'               => $meta,
        ]);
    }
}
