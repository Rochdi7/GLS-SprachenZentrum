<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |----------------------------------------------------------------------
        | Define all modules and their CRUD actions
        |----------------------------------------------------------------------
        */
        $modules = [
            'dashboard'        => ['view'],
            'sites'            => ['view', 'create', 'edit', 'delete'],
            'teachers'         => ['view', 'create', 'edit', 'delete'],
            'groups'           => ['view', 'create', 'edit', 'delete'],
            'certificates'     => ['view', 'create', 'edit', 'delete'],
            'attestations'     => ['view', 'create', 'edit', 'delete'],
            'studienkollegs'   => ['view', 'create', 'edit', 'delete'],
            'quizzes'          => ['view', 'create', 'edit', 'delete'],
            'blog_categories'  => ['view', 'create', 'edit', 'delete'],
            'blog_posts'       => ['view', 'create', 'edit', 'delete'],
            'leads'            => ['view', 'delete'],
            'lead_stats'       => ['view'],
            'applications'     => ['view', 'create', 'edit', 'delete'],
            'users'            => ['view', 'create', 'edit', 'delete'],
            'roles'            => ['view', 'create', 'edit', 'delete'],
            'payroll'          => ['view', 'create', 'edit', 'delete'],
            'presence'         => ['view', 'create', 'edit', 'delete'],
            'level_followups'  => ['view', 'create', 'edit', 'delete'],
            'weekly_reports'   => ['view', 'create', 'edit', 'delete'],
            'schedules'        => ['view', 'create', 'edit', 'delete'],
            'encaissements'    => ['view', 'create', 'edit', 'delete'],
            'whatsapp_campaigns' => ['view', 'create', 'edit', 'delete'],
        ];

        // Create all permissions
        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permName = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                $allPermissions[] = $permName;
            }
        }

        // Purge legacy permissions: the standalone "employees" CRUD has been merged
        // into "users" — any lingering employees.* grants are meaningless now.
        Permission::where('name', 'like', 'employees.%')->delete();

        /*
        |----------------------------------------------------------------------
        | Super Admin — gets ALL permissions
        |----------------------------------------------------------------------
        */
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        /*
        |----------------------------------------------------------------------
        | Admin — everything except roles & users management
        | Admins manage day-to-day operations but cannot touch access control
        |----------------------------------------------------------------------
        */
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = collect($allPermissions)->reject(function ($perm) {
            return str_starts_with($perm, 'roles.') || str_starts_with($perm, 'users.');
        })->values()->toArray();
        $admin->syncPermissions($adminPermissions);

        /*
        |----------------------------------------------------------------------
        | Réception — front-desk operations only
        | Can view most things, create/edit leads & school data, no delete,
        | no access to payroll, HR, admin, or content management
        |----------------------------------------------------------------------
        */
        $reception = Role::firstOrCreate(['name' => 'Réception', 'guard_name' => 'web']);
        $receptionPermissions = [
            // Dashboard
            'dashboard.view',

            // Pilotage — view + create/edit reports, no delete
            'level_followups.view', 'level_followups.create', 'level_followups.edit',
            'weekly_reports.view', 'weekly_reports.create', 'weekly_reports.edit',

            // Ecole — view + create/edit, no delete (reception shouldn't delete data)
            'sites.view',
            'teachers.view', 'teachers.create', 'teachers.edit',
            'groups.view', 'groups.create', 'groups.edit',
            'certificates.view', 'certificates.create', 'certificates.edit',
            'attestations.view', 'attestations.create', 'attestations.edit',
            'studienkollegs.view',

            // Admissions — reception handles incoming students
            'applications.view', 'applications.create', 'applications.edit',
            'leads.view',
            'lead_stats.view',

            // Quizzes — view only (teachers/admins manage content)
            'quizzes.view',
        ];
        $reception->syncPermissions($receptionPermissions);
    }
}
