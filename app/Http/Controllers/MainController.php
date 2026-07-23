<?php

namespace App\Http\Controllers;

use App\PoemCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MainController extends Controller
{
    public function __construct(private readonly PoemCatalog $poems) {}

    public function main(): View
    {
        return $this->page('main', 'main', [
            'title' => 'Вислава Шимборская. Стихотворения',
        ]);
    }

    public function poem(string $section, string $slug): View
    {
        $poem = $this->poems->find($section, $slug);
        $view = "poems.{$section}.{$slug}";

        if ($poem === null || !view()->exists($view)) {
            abort(404);
        }

        return $this->page($view, 'poem', [
            'title' => "Вислава Шимборская. {$poem['title']}",
        ], $poem);
    }

    public function project(): View
    {
        return $this->page('project', 'project', [
            'title' => 'Вислава Шимборская. О проекте',
        ]);
    }

    public function author(): View
    {
        return $this->page('author', 'author', [
            'title' => 'Вислава Шимборская. Об авторе',
        ]);
    }

    public function section(string $section): RedirectResponse
    {
        $poem = $this->poems->firstInSection($section);

        if ($poem === null) {
            abort(404);
        }

        return redirect()->route('poem', [
            'section' => $poem['section'],
            'slug' => $poem['slug'],
        ]);
    }

    /**
     * @param array<string, mixed> $params
     * @param array{section: string, slug: string, title: string}|null $poem
     */
    private function page(string $view, string $page, array $params, ?array $poem = null): View
    {
        return view($view, array_merge(
            [
                'navigation' => $this->poems->navigation(
                    $poem['section'] ?? null,
                    $poem['slug'] ?? null,
                ),
                'sections' => $this->poems->sections(),
                'page' => $page,
                'currentPoem' => $poem,
                'title' => '',
            ],
            $params
        ));
    }
}
