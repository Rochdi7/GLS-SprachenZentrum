<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Studienkolleg;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate public/sitemap.xml with hreflang alternates for all supported locales';

    /**
     * Locale prefixes emitted for every page. x-default points to the first one.
     */
    private array $locales = ['fr', 'en', 'de', 'ar'];

    /**
     * Static frontoffice pages: path => priority (changefreq weekly).
     */
    private array $staticPaths = [
        ''                      => 1.0,
        '/about'                => 0.7,
        '/faq'                  => 0.7,
        '/contact'              => 0.7,
        '/intensive-courses'    => 0.7,
        '/online-courses'       => 0.7,
        '/pricing'              => 0.7,
        '/exams/gls'            => 0.7,
        '/exams/osd'            => 0.7,
        '/exams/goethe'         => 0.7,
        '/blog'                 => 0.7,
        '/student-stories'      => 0.7,
        '/certificate-check'    => 0.7,
        '/niveaux/a1'           => 0.7,
        '/niveaux/a2'           => 0.7,
        '/niveaux/b1'           => 0.7,
        '/niveaux/b2'           => 0.7,
        '/studienkollegs'       => 0.7,
        '/discover-your-level'  => 0.7,
        '/terms'                => 0.7,
        '/privacy'              => 0.7,
        '/partners/fc-marokko'  => 0.7,
    ];

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        $sitemap = Sitemap::create();

        foreach ($this->staticPaths as $path => $priority) {
            $this->addLocalizedUrls($sitemap, $base, $path, $priority, Url::CHANGE_FREQUENCY_WEEKLY);
        }

        Studienkolleg::where('public', true)
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->each(function ($sk) use ($sitemap, $base) {
                $this->addLocalizedUrls(
                    $sitemap, $base, "/studienkollegs/{$sk->slug}",
                    0.6, Url::CHANGE_FREQUENCY_MONTHLY, $sk->updated_at
                );
            });

        BlogPost::where('status', 'published')
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->each(function ($post) use ($sitemap, $base) {
                $this->addLocalizedUrls(
                    $sitemap, $base, "/blog/{$post->slug}",
                    0.6, Url::CHANGE_FREQUENCY_MONTHLY, $post->updated_at
                );
            });

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        // Re-insert the stylesheet PI so browsers render a readable table
        // (documents containing xhtml:link nodes bypass the built-in XML viewer).
        $xml = file_get_contents($path);
        $xml = preg_replace(
            '/^(<\?xml[^>]*\?>)/',
            "$1\n<?xml-stylesheet type=\"text/xsl\" href=\"/sitemap-style.xml\"?>",
            $xml,
            1
        );
        file_put_contents($path, $xml);

        $count = substr_count($xml, '<url>');
        $this->info("Sitemap written to {$path} ({$count} URLs).");

        return self::SUCCESS;
    }

    private function addLocalizedUrls(
        Sitemap $sitemap,
        string $base,
        string $path,
        float $priority,
        string $changeFrequency,
        $lastModified = null
    ): void {
        foreach ($this->locales as $locale) {
            $url = Url::create("{$base}/{$locale}{$path}")
                ->setChangeFrequency($changeFrequency)
                ->setPriority($priority);

            if ($lastModified) {
                $url->setLastModificationDate($lastModified);
            }

            foreach ($this->locales as $alternate) {
                $url->addAlternate("{$base}/{$alternate}{$path}", $alternate);
            }
            $url->addAlternate("{$base}/{$this->locales[0]}{$path}", 'x-default');

            $sitemap->add($url);
        }
    }
}
