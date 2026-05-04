<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

use App\Models\BlogPost;
use App\Models\Studienkolleg;

class GenerateSitemap extends Command
{
    protected $signature = 'gls:generate-sitemap {--with-quiz : Inclure /discover-your-level/quiz (facultatif)}';

    protected $description = 'Génère le sitemap.xml du site GLS (URLs localisées + hreflang)';

    /**
     * Locales we expose to search engines. fr is default and used for x-default.
     */
    private array $locales = ['fr', 'en', 'de', 'ar'];

    private string $defaultLocale = 'fr';

    public function handle(): int
    {
        $baseUrl = rtrim(config('app.url'), '/');

        if (empty($baseUrl) || str_contains($baseUrl, '127.0.0.1') || str_contains($baseUrl, 'localhost')) {
            $baseUrl = 'https://gls-sprachzentrum.ma';
            $this->warn("APP_URL is local/empty — falling back to {$baseUrl}");
        }

        $this->info("Generating sitemap for: {$baseUrl}");

        $sitemap = Sitemap::create();

        $staticPaths = [
            '/' => 1.0,
            '/about' => 0.7,
            '/faq' => 0.7,
            '/contact' => 0.7,
            '/intensive-courses' => 0.7,
            '/online-courses' => 0.7,
            '/pricing' => 0.7,
            '/exams/gls' => 0.7,
            '/exams/osd' => 0.7,
            '/exams/goethe' => 0.7,
            '/blog' => 0.7,
            '/student-stories' => 0.7,
            '/certificate-check' => 0.7,
            '/niveaux/a1' => 0.7,
            '/niveaux/a2' => 0.7,
            '/niveaux/b1' => 0.7,
            '/niveaux/b2' => 0.7,
            '/studienkollegs' => 0.7,
            '/discover-your-level' => 0.7,
            '/terms' => 0.7,
            '/privacy' => 0.7,
            '/partners/fc-marokko' => 0.7,
        ];

        foreach ($staticPaths as $path => $priority) {
            $this->addLocalizedUrl($sitemap, $baseUrl, $path, Url::CHANGE_FREQUENCY_WEEKLY, $priority);
        }

        if ($this->option('with-quiz')) {
            $this->addLocalizedUrl($sitemap, $baseUrl, '/discover-your-level/quiz', Url::CHANGE_FREQUENCY_WEEKLY, 0.6);
        }

        if (class_exists(BlogPost::class)) {
            BlogPost::query()
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->latest()
                ->get(['slug', 'updated_at'])
                ->each(function ($post) use ($sitemap, $baseUrl) {
                    $this->addLocalizedUrl(
                        $sitemap,
                        $baseUrl,
                        '/blog/' . ltrim($post->slug, '/'),
                        Url::CHANGE_FREQUENCY_MONTHLY,
                        0.6,
                        $post->updated_at ? Carbon::parse($post->updated_at) : null
                    );
                });
        }

        if (class_exists(Studienkolleg::class)) {
            Studienkolleg::query()
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->latest()
                ->get(['slug', 'updated_at'])
                ->each(function ($item) use ($sitemap, $baseUrl) {
                    $this->addLocalizedUrl(
                        $sitemap,
                        $baseUrl,
                        '/studienkollegs/' . ltrim($item->slug, '/'),
                        Url::CHANGE_FREQUENCY_MONTHLY,
                        0.6,
                        $item->updated_at ? Carbon::parse($item->updated_at) : null
                    );
                });
        }

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info("Sitemap generated: {$path}");
        $this->info("Public URL: {$baseUrl}/sitemap.xml");

        return self::SUCCESS;
    }

    /**
     * Adds one <url> entry per locale, each annotated with xhtml:link hreflang
     * pointing to all locale alternates plus an x-default. Listed URLs are the
     * final 200-OK localized URLs (e.g. /fr/about), not the bare /about that
     * 302-redirects.
     */
    private function addLocalizedUrl(
        Sitemap $sitemap,
        string $baseUrl,
        string $path,
        string $changeFreq,
        float $priority,
        ?Carbon $lastMod = null
    ): void {
        foreach ($this->locales as $locale) {
            $loc = $baseUrl . '/' . $locale . $this->normalizePath($path);

            $tag = Url::create($loc)
                ->setChangeFrequency($changeFreq)
                ->setPriority($priority);

            foreach ($this->locales as $alt) {
                $tag->addAlternate($baseUrl . '/' . $alt . $this->normalizePath($path), $alt);
            }
            $tag->addAlternate($baseUrl . '/' . $this->defaultLocale . $this->normalizePath($path), 'x-default');

            if ($lastMod) {
                $tag->setLastModificationDate($lastMod);
            }

            $sitemap->add($tag);
        }
    }

    private function normalizePath(string $path): string
    {
        if ($path === '/' || $path === '') {
            return '';
        }
        return '/' . ltrim($path, '/');
    }
}
