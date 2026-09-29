<?php

use DominionSolutions\FilamentSeo\Models\Seo;

return [

    /*
    |--------------------------------------------------------------------------
    | Metadata Model
    |--------------------------------------------------------------------------
    |
    | The model used to store per-record SEO metadata. Swap it for your own
    | if you need extra columns or different casts.
    |
    */

    'model' => Seo::class,

    /*
    |--------------------------------------------------------------------------
    | Render Title Element
    |--------------------------------------------------------------------------
    |
    | Filament already renders a <title> from the page's title, which
    | HasSeoMetadata points at your SEO title. Leave this false so exactly one
    | <title> appears in the <head>. Set it to true only when using a layout
    | that emits no title of its own.
    |
    */

    'render_title' => false,

    /*
    |--------------------------------------------------------------------------
    | Canonical URL Fallback
    |--------------------------------------------------------------------------
    |
    | When a page does not set its own canonical URL, the package falls back to
    | the current request URL. Disable this if you would rather omit the tag.
    |
    */

    'canonical_fallback' => true,

    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | Used for the Open Graph "site_name" tag. Falls back to the app name when
    | this is null.
    |
    */

    'site_name' => null,

];
