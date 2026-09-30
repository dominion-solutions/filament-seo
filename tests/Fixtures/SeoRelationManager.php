<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\Filament\Concerns\InteractsWithSeoActions;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

/**
 * A relation manager whose modal create/edit actions persist SEO metadata.
 *
 * Exists so Larastan analyses {@see InteractsWithSeoActions} in the context it
 * is actually used — capturing form state for `mutateDataUsing()` and applying
 * it once the record exists.
 */
class SeoRelationManager extends RelationManager
{
    use InteractsWithSeoActions;

    protected static string $relationship = 'seoable';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make('createSeoRecord')
                    ->mutateDataUsing($this->captureSeoFormData(...))
                    ->after(fn ($record) => $this->applyPendingSeoFormData($record)),
            ]);
    }
}
