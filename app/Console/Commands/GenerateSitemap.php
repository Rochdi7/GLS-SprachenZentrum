<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\BlogPost;
use App\Models\Studienkolleg;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gls:generate-sitemap';

    /**
     * The console command description.
     */
    protected $description = 'Generate sitemap.xml (starter local version)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap (local starter)...');

        $sitemap = Sitemap::create();

        /*
        |--------------------------------------------------------------------------
        | Static pages (SAFE)
        |--------------------------------------------------------------------------
        */
        $pages = [
            '/',
            '/about',
            '/contact',
            '/faq',
            '/pricing',
            '/blog',
            '/studienkollegs',
        ];

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create($page)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Blog posts (NO condition – local safe)
        |--------------------------------------------------------------------------
        */
        if (class_exists(BlogPost::class)) {
            BlogPost::all()->each(function ($post) use ($sitemap) {
                if (!empty($post->slug)) {
                    $sitemap->add(
                        Url::create('/blog/' . $post->slug)
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Studienkollegs (NO condition – local safe)
        |--------------------------------------------------------------------------
        */
        if (class_exists(Studienkolleg::class)) {
            Studienkolleg::all()->each(function ($item) use ($sitemap) {
                if (!empty($item->slug)) {
                    $sitemap->add(
                        Url::create('/studienkollegs/' . $item->slug)
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Write sitemap
        |--------------------------------------------------------------------------
        */
        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info("Sitemap generated successfully (local): {$path}");

        return self::SUCCESS;
    }
}
