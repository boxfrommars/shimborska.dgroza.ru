<?php

namespace App;

final class PoemCatalog
{
    private const NAVIGATION_LEFT_PADDING = 2;

    private const NAVIGATION_RIGHT_PADDING = 3;

    /**
     * @var array<string, array{title: string, poems: list<array{slug: string, title: string}>}>
     */
    private array $sections;

    /**
     * @var list<array{section: string, slug: string, title: string}>
     */
    private array $poems = [];

    /**
     * @var array<string, array{section: string, slug: string, title: string}>
     */
    private array $poemsByPath = [];

    public function __construct()
    {
        $this->sections = require resource_path('data/poems.php');

        foreach ($this->sections as $sectionSlug => $section) {
            foreach ($section['poems'] as $poem) {
                $entry = [
                    'section' => $sectionSlug,
                    'slug' => $poem['slug'],
                    'title' => $poem['title'],
                ];

                $this->poems[] = $entry;
                $this->poemsByPath[$this->path($sectionSlug, $poem['slug'])] = $entry;
            }
        }
    }

    /**
     * @return array<string, array{title: string, poems: list<array{slug: string, title: string}>}>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * @return list<array{section: string, slug: string, title: string}>
     */
    public function poems(): array
    {
        return $this->poems;
    }

    /**
     * @return array{section: string, slug: string, title: string}|null
     */
    public function find(string $section, string $slug): ?array
    {
        return $this->poemsByPath[$this->path($section, $slug)] ?? null;
    }

    /**
     * @return array{section: string, slug: string, title: string}|null
     */
    public function firstInSection(string $section): ?array
    {
        $first = $this->sections[$section]['poems'][0] ?? null;

        return $first === null ? null : $this->find($section, $first['slug']);
    }

    /**
     * @return array{
     *     items: array<int, array{section: string, slug: string, title: string}>,
     *     currentIndex: int|null
     * }
     */
    public function navigation(?string $section = null, ?string $slug = null): array
    {
        $currentIndex = null;

        if ($section !== null && $slug !== null) {
            foreach ($this->poems as $index => $poem) {
                if ($poem['section'] === $section && $poem['slug'] === $slug) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        $offset = 0;

        if ($currentIndex !== null) {
            $offset = $currentIndex - self::NAVIGATION_LEFT_PADDING;
            $lastIndex = count($this->poems) - 1;
            $rightOverflow = $lastIndex - ($currentIndex + self::NAVIGATION_RIGHT_PADDING);

            if ($rightOverflow < 0) {
                $offset -= abs($rightOverflow);
            }

            $offset = max(0, $offset);
        }

        return [
            'items' => array_slice(
                $this->poems,
                $offset,
                self::NAVIGATION_LEFT_PADDING + self::NAVIGATION_RIGHT_PADDING + 1,
                true,
            ),
            'currentIndex' => $currentIndex,
        ];
    }

    private function path(string $section, string $slug): string
    {
        return "{$section}/{$slug}";
    }
}
