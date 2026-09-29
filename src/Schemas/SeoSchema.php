<?php

namespace DominionSolutions\FilamentSeo\Schemas;

use DominionSolutions\FilamentSeo\Filament\Concerns\InteractsWithSeoForm;
use DominionSolutions\FilamentSeo\Support\SeoData;
use DominionSolutions\FilamentSeo\Support\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * A ready-made "SEO" section for a Filament form.
 *
 * Returns a plain `Section` rather than extending it: `Section`'s constructor
 * is `final`, and building on the class would tie this package to Filament
 * internals that differ between v4 and v5. The individual field factories are
 * public too, so a host that needs a different layout can compose its own
 * section from the same fields.
 *
 * All fields live under the `seo.*` state path, so the form data arrives as a
 * single `['seo' => [...]]` array. Pair this with
 * {@see InteractsWithSeoForm}
 * on the resource's Create/Edit page to load and save the related row.
 */
final class SeoSchema
{
    public const ROBOTS_OPTIONS = [
        SeoData::ROBOTS_INDEX_FOLLOW => 'Index, follow (recommended)',
        SeoData::ROBOTS_NOINDEX_FOLLOW => 'No index, follow (hide from search)',
        SeoData::ROBOTS_NOINDEX_NOFOLLOW => 'No index, no follow (hide and do not follow)',
    ];

    public const TYPE_OPTIONS = [
        SeoData::TYPE_WEBSITE => 'Website',
        SeoData::TYPE_ARTICLE => 'Article',
        SeoData::TYPE_PROFILE => 'Profile',
    ];

    public static function make(?string $heading = 'SEO', ?string $description = null): Section
    {
        return Section::make($heading)
            ->description($description ?? 'Leave these blank to fall back to values generated from the record.')
            ->schema([
                self::titleField(),
                self::descriptionField(),
                self::imageUrlField(),
                self::imageAltField(),
                self::canonicalUrlField(),
                self::robotsField(),
                self::typeField(),
            ])
            ->collapsible()
            ->columnSpanFull();
    }

    public static function titleField(): TextInput
    {
        return TextInput::make('title')
            ->statePath('seo.title')
            ->label('Title')
            ->maxLength(255)
            ->helperText('Shown as the page title in search results. Keep it specific and lead with the page subject.');
    }

    public static function descriptionField(): Textarea
    {
        return Textarea::make('description')
            ->statePath('seo.description')
            ->label('Meta description')
            ->rows(3)
            ->maxLength(Text::DESCRIPTION_LENGTH)
            ->helperText(sprintf('Summarises the page in search results. Around %d characters; anything longer is truncated.', Text::DESCRIPTION_LENGTH));
    }

    public static function imageUrlField(): TextInput
    {
        return TextInput::make('image_url')
            ->statePath('seo.image_url')
            ->label('Share image URL')
            ->url()
            ->maxLength(2048)
            ->helperText('Absolute URL of the image used for link previews on social platforms.');
    }

    public static function imageAltField(): TextInput
    {
        return TextInput::make('image_alt')
            ->statePath('seo.image_alt')
            ->label('Share image alt text')
            ->maxLength(255)
            ->helperText('Describes the share image for people who cannot see it.');
    }

    public static function canonicalUrlField(): TextInput
    {
        return TextInput::make('canonical_url')
            ->statePath('seo.canonical_url')
            ->label('Canonical URL')
            ->url()
            ->maxLength(2048)
            ->helperText('Defaults to this page\'s own URL. Set one only to point search engines at a different address for the same content.');
    }

    public static function robotsField(): Select
    {
        return Select::make('robots')
            ->statePath('seo.robots')
            ->label('Robots')
            ->options(self::ROBOTS_OPTIONS)
            ->default(SeoData::ROBOTS_INDEX_FOLLOW)
            ->selectablePlaceholder(false);
    }

    public static function typeField(): Select
    {
        return Select::make('type')
            ->statePath('seo.type')
            ->label('Content type')
            ->options(self::TYPE_OPTIONS)
            ->default(SeoData::TYPE_WEBSITE)
            ->selectablePlaceholder(false);
    }
}
