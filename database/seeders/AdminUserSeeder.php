<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Old seeder users keep Super Admin role
        $superAdmins = [
            'rochdi.karouali@glszentrum.com' => 'Rochdi Karouali',
            'amine.rafik@glszentrum.com' => 'Amine Rafik',
            'rafik@glszentrum.com' => 'Rafik',
            'abderrahimelmoulabbi@glszentrum.com' => 'Abderrahim Elmoulabbi',
            'achraf.elyounani@glszentrum.com' => 'Achraf Elyounani',
            'ichrak.fakroune@glszentrum.com' => 'Ichrak Fakroune',
            'rochdi.karouali1234@gmail.com' => 'Rochdi Karouali',
        ];

        $admins = [
            'yassine.ouledlaghzal@glszentrum.com' => 'Yassine Ouled Laghzal',
            'maria.jelloul@glszentrum.com' => 'Maria Jelloul',
            'elmehdi.bakhach@glszentrum.com' => 'El Mehdi Bakhach',
            'yassine.elbadaoui@glszentrum.com' => 'Yassine Elbadaoui',
        ];

        $receptions = [
            'ahmed.khadimerrahman@glszentrum.com' => 'Ahmed Khadimerrahman',
            'amal.laamiri@glszentrum.com' => 'Amal Laamiri',
            'hafsa.elkhatabi@glszentrum.com' => 'Hafsa Elkhatabi',
            'hamza.dahbany@glszentrum.com' => 'Hamza Dahbany',
            'ikram.boussila@glszentrum.com' => 'Ikram Boussila',
            'khaoula.elghanoui@glszentrum.com' => 'Khaoula El Ghanoui',
            'latifa.abouelfath@glszentrum.com' => 'Latifa Abouelfath',
            'loubna.elkhalfi@glszentrum.com' => 'Loubna El Khalfi',
            'mehdi.joundi@glszentrum.com' => 'Mehdi Joundi',
            'mouna.zakri@glszentrum.com' => 'Mouna Zakri',
            'mustapha.benlmekki@glszentrum.com' => 'Mustapha Benlmekki',
            'oumnya.salim@glszentrum.com' => 'Oumnya Salim',
            'rihab.riad@glszentrum.com' => 'Rihab Riad',
            'saad.soutafi@glszentrum.com' => 'Saad Soutafi',
            'sara.grija@glszentrum.com' => 'Sara Grija',
        ];

        $this->createUsersWithRole($superAdmins, 'Super Admin');
        $this->createUsersWithRole($admins, 'Admin');
        $this->createUsersWithRole($receptions, 'Reception');
    }

    private function createUsersWithRole(array $users, string $role): void
    {
        foreach ($users as $email => $name) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('Admin@12345'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}