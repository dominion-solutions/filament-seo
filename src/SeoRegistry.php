<?php

namespace DominionSolutions\FilamentSeo;

use DominionSolutions\FilamentSeo\Support\SeoData;

/**
 * Holds the metadata for the page currently being rendered.
 *
 * Filament renders its `<head>` render hooks outside the Livewire component's
 * own scope, so `Livewire::current()` is not usable there. Instead the page
 * publishes its metadata from a Livewire lifecycle hook — which does run before
 * rendering — and the plugin's render hook reads it back.
 *
 * Bound as a singleton, so it lasts exactly one request.
 */
class SeoRegistry
{
    protected ?SeoData $data = null;

    public function set(?SeoData $data): void
    {
        $this->data = $data;
    }

    public function get(): ?SeoData
    {
        return $this->data;
    }

    public function flush(): void
    {
        $this->data = null;
    }
}
