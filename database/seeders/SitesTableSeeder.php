<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;

class SitesTableSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'name'    => 'GLS Casablanca',
                'slug'    => 'casablanca',
                'city'    => 'Casablanca',
                'address' => '14 Bd de Paris, 1er étage N°8, Casablanca 20000',
                'phone'   => '+212 80-8549717',
                'email'   => 'info@glssprachenzentrum.ma',
            ],
            [
                'name'    => 'GLS Marrakech',
                'slug'    => 'marrakech',
                'city'    => 'Marrakech',
                'address' => 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage, Bureau 28, Marrakech',
                'phone'   => '+212 80-86 639 83',
                'email'   => 'info@glssprachenzentrum.ma',
            ],
            [
                'name'    => 'GLS Rabat',
                'slug'    => 'rabat',
                'city'    => 'Rabat',
                'address' => 'Avenue Fal Ould Oumeir, Immeuble 77, 1er étage N°1, Agdal, Rabat',
                'phone'   => '+212 80-85 735 09',
                'email'   => 'info@glssprachenzentrum.ma',
            ],
            [
                'name'    => 'GLS Kénitra',
                'slug'    => 'kenitra',
                'city'    => 'Kenitra',
                'address' => 'Avenue Mohammed V, Bureaux Rania, 7ème étage, Kénitra',
                'phone'   => '+212 80-86 514 50',
                'email'   => 'info@glssprachenzentrum.ma',
            ],
            [
                'name'    => 'GLS Salé',
                'slug'    => 'sale',
                'city'    => 'Salé',
                'address' => 'Avenue Mohamed V, Rue Halima N°12 Diyar, Salé',
                'phone'   => '+212 80-85 40 625',
                'email'   => 'info@glssprachenzentrum.ma',
            ],
            [
                'name'    => 'GLS Agadir',
                'slug'    => 'agadir',
                'city'    => 'Agadir',
                'address' => 'Av. Massoude Al Wafkaoui, Place des Taxis, Hay Essalam, Agadir',
                'phone'   => '+212 606-48 40 51',
                'email'   => 'info@glssprachenzentrum.ma',
            ],
        ];

        foreach ($sites as $site) {
            Site::create([
                'name'      => $site['name'],
                'slug'      => $site['slug'], // clean slug
                'city'      => $site['city'],
                'address'   => $site['address'],
                'phone'     => $site['phone'],
                'email'     => $site['email'],
                'is_active' => true,
            ]);
        }
    }
}
