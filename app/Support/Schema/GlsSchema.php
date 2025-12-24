<?php

namespace App\Support\Schema;

use Spatie\SchemaOrg\Schema;

class GlsSchema
{
    /**
     * Global Organization / School schema
     */
    public static function organization(): string
    {
        return Schema::educationalOrganization()
            ->name('GLS Sprachen Zentrum')
            ->url(config('app.url'))
            ->logo(asset('assets/images/logo/gls-logo.webp'))
            ->sameAs([
                'https://www.facebook.com/glssprachenzentrum',
                'https://www.instagram.com/glssprachenzentrum',
            ])
            ->address(
                Schema::postalAddress()
                    ->addressCountry('MA')
                    ->addressLocality('Morocco')
            )
            ->toScript();
    }

    /**
     * Blog article schema
     */
    public static function blog($post): string
    {
        return Schema::blogPosting()
            ->headline($post->title)
            ->description($post->excerpt)
            ->image(asset($post->featured_image))
            ->author(
                Schema::person()->name('GLS Sprachen Zentrum')
            )
            ->publisher(
                Schema::organization()
                    ->name('GLS Sprachen Zentrum')
                    ->logo(
                        Schema::imageObject()
                            ->url(asset('assets/images/logo/gls-logo.webp'))
                    )
            )
            ->datePublished($post->created_at->toIso8601String())
            ->dateModified($post->updated_at->toIso8601String())
            ->mainEntityOfPage(url()->current())
            ->toScript();
    }

    /**
     * Studienkolleg schema
     */
    public static function studienkolleg($item): string
    {
        return Schema::course()
            ->name($item->name)
            ->description($item->meta_description)
            ->provider(
                Schema::organization()
                    ->name($item->university)
            )
            ->educationalCredentialAwarded('Studienkolleg Certificate')
            ->toScript();
    }
}
