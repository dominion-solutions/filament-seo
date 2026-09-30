<?php

namespace DominionSolutions\FilamentSeo\Tests;

use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Models\Seo;
use DominionSolutions\FilamentSeo\Support\SeoData;
use DominionSolutions\FilamentSeo\Support\Text;
use DominionSolutions\FilamentSeo\Tests\Fixtures\DefaultFallbackSeoRecord;
use DominionSolutions\FilamentSeo\Tests\Fixtures\SeoRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Covers how a record's effective metadata is assembled.
 *
 * The promise being tested is the one the package sells: a record is indexable
 * the moment it exists, because the metadata is derived from the record itself;
 * authored values then take over field by field as an editor fills them in.
 *
 * @see InteractsWithSeo
 */
class ModelMetadataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A saved record of either fixture kind.
     *
     * @param  class-string<SeoRecord|DefaultFallbackSeoRecord>  $class
     */
    private function makeRecord(string $class = SeoRecord::class): SeoRecord|DefaultFallbackSeoRecord
    {
        $record = new $class;
        $record->name = 'Dominion Monitor';
        $record->tagline = 'Uptime monitoring for small teams.';
        $record->screenshot_url = 'https://example.test/monitor.png';
        $record->save();

        return $record;
    }

    /**
     * The record as the database holds it now.
     *
     * Tests about what was persisted care about the row, not about the record
     * having survived, so a missing one is a failure rather than a `null` to
     * propagate.
     */
    private function reload(SeoRecord|DefaultFallbackSeoRecord $record): SeoRecord|DefaultFallbackSeoRecord
    {
        $persisted = $record->fresh();

        $this->assertNotNull($persisted);

        return $persisted;
    }

    public function test_a_record_with_no_stored_metadata_uses_its_generated_fallbacks(): void
    {
        $record = $this->makeRecord();

        $this->assertNull($record->getSeoRecord());
        $this->assertSame('Dominion Monitor', $record->getSeoData()->title);
        $this->assertSame('Uptime monitoring for small teams.', $record->getSeoData()->description);
        $this->assertSame('https://example.test/monitor.png', $record->getSeoData()->imageUrl);
    }

    public function test_no_row_is_created_until_metadata_is_authored(): void
    {
        $record = $this->makeRecord();

        $this->assertDatabaseMissing('seos', ['seoable_id' => $record->getKey()]);
    }

    public function test_the_trait_sniffs_attributes_when_no_fallbacks_are_overridden(): void
    {
        $record = $this->makeRecord(DefaultFallbackSeoRecord::class);

        $data = $record->getSeoData();

        $this->assertSame('Dominion Monitor', $data->title);
        $this->assertSame('Uptime monitoring for small teams.', $data->description);
    }

    public function test_authored_metadata_wins_over_the_generated_fallbacks(): void
    {
        $record = $this->makeRecord();

        $record->saveSeo([
            'title' => 'A better title',
            'description' => 'A description an editor actually wrote.',
        ]);

        $data = $this->reload($record)->getSeoData();

        $this->assertSame('A better title', $data->title);
        $this->assertSame('A description an editor actually wrote.', $data->description);
    }

    public function test_a_field_left_blank_keeps_its_generated_fallback(): void
    {
        $record = $this->makeRecord();

        $record->saveSeo(['title' => 'A better title']);

        $data = $this->reload($record)->getSeoData();

        $this->assertSame('A better title', $data->title);
        $this->assertSame('Uptime monitoring for small teams.', $data->description);
    }

    public function test_saving_twice_updates_the_same_row(): void
    {
        $record = $this->makeRecord();

        $record->saveSeo(['title' => 'First']);
        $record->saveSeo(['title' => 'Second']);

        $this->assertSame(1, $record->seo()->count());
        $this->assertSame('Second', $this->reload($record)->getSeoData()->title);
    }

    public function test_saving_one_field_keeps_the_other_authored_fields(): void
    {
        $record = $this->makeRecord();

        $record->saveSeo([
            'title' => 'First title',
            'description' => 'Keep this description.',
        ]);
        $record->saveSeo(['title' => 'Updated title']);

        $data = $this->reload($record)->getSeoData();

        $this->assertSame('Updated title', $data->title);
        $this->assertSame('Keep this description.', $data->description);
    }

    public function test_robots_and_type_get_indexable_defaults_when_not_supplied(): void
    {
        $record = $this->makeRecord();

        $record->saveSeo(['title' => 'A better title']);

        $data = $this->reload($record)->getSeoData();

        $this->assertSame(SeoData::ROBOTS_INDEX_FOLLOW, $data->robots);
        $this->assertSame(SeoData::TYPE_WEBSITE, $data->type);
        $this->assertTrue($data->isIndexable());
    }

    public function test_an_editor_can_take_a_record_out_of_the_index(): void
    {
        $record = $this->makeRecord();

        $record->saveSeo([
            'title' => 'A draft',
            'robots' => SeoData::ROBOTS_NOINDEX_FOLLOW,
        ]);

        $this->assertFalse($this->reload($record)->getSeoData()->isIndexable());
    }

    public function test_a_description_is_reduced_to_plain_text_within_budget(): void
    {
        $record = $this->makeRecord();
        $record->tagline = '<p>'.str_repeat('word ', 80).'</p>';
        $record->save();

        $description = $record->getSeoData()->description;

        $this->assertNotNull($description);
        $this->assertSame(Text::excerpt($record->tagline), $description);
        $this->assertStringNotContainsString('<p>', $description);
        $this->assertLessThanOrEqual(Text::DESCRIPTION_LENGTH, mb_strlen($description));
    }

    public function test_the_relation_is_resolved_from_config(): void
    {
        $record = $this->makeRecord();

        $this->assertInstanceOf(Seo::class, $record->saveSeo(['title' => 'A title']));
        $this->assertSame('seos', $record->seo()->getRelated()->getTable());
    }
}
