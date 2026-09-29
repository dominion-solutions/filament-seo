<?php

namespace DominionSolutions\FilamentSeo\Tests;

use DominionSolutions\FilamentSeo\Support\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function test_plain_strips_markup_and_collapses_whitespace(): void
    {
        $html = "<h2>Great   work</h2>\n<p>Ship &amp; deliver</p>";

        $this->assertSame('Great work Ship & deliver', Text::plain($html));
    }

    public function test_plain_returns_empty_string_for_blank_input(): void
    {
        $this->assertSame('', Text::plain(null));
        $this->assertSame('', Text::plain('   '));
    }

    public function test_truncate_leaves_short_text_untouched(): void
    {
        $this->assertSame('Short enough', Text::truncate('Short enough', 160));
    }

    public function test_truncate_cuts_on_a_word_boundary_within_budget(): void
    {
        $value = 'Laravel makes it straightforward to build Filament plugins with clean SEO metadata support';

        $truncated = Text::truncate($value, 60);

        $this->assertNotNull($truncated);
        $this->assertLessThanOrEqual(60, mb_strlen($truncated));
        $this->assertStringEndsWith('…', $truncated);
        // Cutting on a space means the surviving text is whole words only.
        $this->assertStringNotContainsString('wit…', $truncated);
        $this->assertStringStartsWith('Laravel makes it', $truncated);
    }

    public function test_truncate_never_exceeds_the_budget_including_the_ellipsis(): void
    {
        foreach ([1, 5, 20, 159, 160] as $limit) {
            $truncated = Text::truncate(str_repeat('word ', 100), $limit);

            $this->assertNotNull($truncated);
            $this->assertLessThanOrEqual(
                $limit,
                mb_strlen($truncated),
                "Expected the truncated value to fit within {$limit} characters.",
            );
        }
    }

    public function test_truncate_still_cuts_a_very_long_unbroken_word(): void
    {
        $truncated = Text::truncate(str_repeat('a', 50), 20);

        $this->assertSame(str_repeat('a', 19).'…', $truncated);
    }

    public function test_truncate_returns_null_when_nothing_is_given(): void
    {
        $this->assertNull(Text::truncate(null, 160));
        $this->assertNull(Text::truncate('   ', 160));
    }

    public function test_excerpt_plain_texts_then_truncates(): void
    {
        $excerpt = Text::excerpt('<p>'.str_repeat('word ', 60).'</p>', 40);

        $this->assertNotNull($excerpt);
        $this->assertLessThanOrEqual(40, mb_strlen($excerpt));
        $this->assertStringNotContainsString('<p>', $excerpt);
    }

    public function test_from_markdown_strips_markdown_syntax(): void
    {
        $excerpt = Text::fromMarkdown("# It was a mess\n\nWe **replaced** the whole thing.");

        $this->assertSame('It was a mess We replaced the whole thing.', $excerpt);
    }

    public function test_from_markdown_respects_the_budget(): void
    {
        $excerpt = Text::fromMarkdown('## '.str_repeat('word ', 60), 40);

        $this->assertNotNull($excerpt);
        $this->assertLessThanOrEqual(40, mb_strlen($excerpt));
        $this->assertStringNotContainsString('#', $excerpt);
    }

    public function test_from_markdown_returns_null_for_blank_input(): void
    {
        $this->assertNull(Text::fromMarkdown(null));
        $this->assertNull(Text::fromMarkdown('   '));
    }
}
