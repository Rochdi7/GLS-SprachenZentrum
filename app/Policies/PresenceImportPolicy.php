<?php

namespace App\Policies;

use App\Models\PresenceImport;
use App\Models\User;

/**
 * Authorization for viewing professor payment imports.
 *
 * The security guarantee: a professor may ONLY view imports that belong to
 * their own groups (group.teacher_id === user.teacher_id). Admins/managers
 * and staff holding the backoffice payroll permission may view any import.
 */
class PresenceImportPolicy
{
    /**
     * Grant everything to Super Admin without further checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null; // fall through to the specific ability
    }

    /**
     * Can the user view this specific import?
     */
    public function view(User $user, PresenceImport $import): bool
    {
        // Backoffice staff with the payroll permission can view any import.
        if ($user->can('crm_prof_payment.view')) {
            return true;
        }

        // Professors: only their own teacher's imports.
        if ($user->teacher_id !== null) {
            return (int) $import->group?->teacher_id === (int) $user->teacher_id;
        }

        return false;
    }

    /**
     * Only backoffice staff manage (create/edit/override/status) imports.
     * Professors have NO write access whatsoever.
     */
    public function manage(User $user, PresenceImport $import): bool
    {
        return $user->can('crm_prof_payment.edit');
    }
}
