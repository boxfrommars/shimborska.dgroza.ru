<?php

namespace App;

final class PoemCatalog
{
    /** Two entries before the current poem in the regular six-number pager. */
    private const NAVIGATION_LEFT_PADDING = 2;

    /** Three entries after the current poem preserve the established 2 + 1 + 3 window. */
    private const NAVIGATION_RIGHT_PADDING = 3;

    /** The narrow pager keeps the same two preceding entries. */
    private const COMPACT_NAVIGATION_LEFT_PADDING = 2;

    /** Two following entries make the compact window symmetric: 2 + 1 + 2. */
    private const COMPACT_NAVIGATION_RIGHT_PADDING = 2;

    /**
     * @var array<string, array{title: string, poems: list<array{slug: string, title: string, description?: string}>}>
     */
    private array $sections;

    /**
     * Flat catalog in public page-number order.
     *
     * @var list<array{section: string, slug: string, title: string, description: string|null}>
     */
    private array $poems = [];

    /**
     * Exact section/slug lookup without changing the ordered catalog above.
     *
     * @var array<string, array{section: string, slug: string, title: string, description: string|null}>
     */
    private array $poemsByPath = [];

    public function __construct()
    {
        $this->sections = require resource_path('data/poems.php');

        // Source order defines both public numbering and every navigation window.
        foreach ($this->sections as $sectionSlug => $section) {
            foreach ($section['poems'] as $poem) {
                $entry = [
                    'section' => $sectionSlug,
                    'slug' => $poem['slug'],
                    'title' => $poem['title'],
                    'description' => $poem['description'] ?? null,
                ];

                $this->poems[] = $entry;
                $this->poemsByPath[$this->path($sectionSlug, $poem['slug'])] = $entry;
            }
        }
    }

    /**
     * @return array<string, array{title: string, poems: list<array{slug: string, title: string, description?: string}>}>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * @return list<array{section: string, slug: string, title: string, description: string|null}>
     */
    public function poems(): array
    {
        return $this->poems;
    }

    /**
     * @return array{section: string, slug: string, title: string, description: string|null}|null
     */
    public function find(string $section, string $slug): ?array
    {
        return $this->poemsByPath[$this->path($section, $slug)] ?? null;
    }

    /**
     * @return array{section: string, slug: string, title: string, description: string|null}|null
     */
    public function firstInSection(string $section): ?array
    {
        $first = $this->sections[$section]['poems'][0] ?? null;

        return $first === null ? null : $this->find($section, $first['slug']);
    }

    /**
     * @return array{
     *     items: array<int, array{section: string, slug: string, title: string, description: string|null}>,
     *     compactItems: array<int, array{section: string, slug: string, title: string, description: string|null}>,
     *     currentIndex: int|null
     * }
     */
    public function navigation(?string $section = null, ?string $slug = null): array
    {
        // Cover and service pages have no current poem, so their windows start at item zero.
        $currentIndex = null;

        if ($section !== null && $slug !== null) {
            foreach ($this->poems as $index => $poem) {
                if ($poem['section'] === $section && $poem['slug'] === $slug) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        // Blade renders the regular window once and marks entries absent from
        // compactItems, avoiding a duplicate mobile navigation tree.
        return [
            'items' => $this->navigationWindow(
                $currentIndex,
                self::NAVIGATION_LEFT_PADDING,
                self::NAVIGATION_RIGHT_PADDING,
            ),
            'compactItems' => $this->navigationWindow(
                $currentIndex,
                self::COMPACT_NAVIGATION_LEFT_PADDING,
                self::COMPACT_NAVIGATION_RIGHT_PADDING,
            ),
            'currentIndex' => $currentIndex,
        ];
    }

    /**
     * @return array<int, array{section: string, slug: string, title: string, description: string|null}>
     */
    private function navigationWindow(?int $currentIndex, int $leftPadding, int $rightPadding): array
    {
        $offset = 0;

        if ($currentIndex !== null) {
            // Initially place the current poem after the requested number of predecessors.
            $offset = $currentIndex - $leftPadding;
            $lastIndex = count($this->poems) - 1;
            $rightOverflow = $lastIndex - ($currentIndex + $rightPadding);

            // Near the catalog end, shift the whole window left instead of returning
            // fewer entries. Near the beginning, clamp the resulting offset to zero.
            if ($rightOverflow < 0) {
                $offset -= abs($rightOverflow);
            }

            $offset = max(0, $offset);
        }

        return array_slice(
            $this->poems,
            $offset,
            $leftPadding + $rightPadding + 1,
            // Original zero-based keys are public page indexes and let Blade compare
            // regular and compact windows without rebuilding an index map.
            true,
        );
    }

    private function path(string $section, string $slug): string
    {
        return "{$section}/{$slug}";
    }
}
