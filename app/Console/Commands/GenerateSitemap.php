<?php

namespace App\Console\Commands;

use App\PoemCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap.';

    public function handle(PoemCatalog $poems): int
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);

        if (
            ! filter_var($appUrl, FILTER_VALIDATE_URL)
            || ! in_array($scheme, ['http', 'https'], true)
        ) {
            $this->error('APP_URL must be a valid HTTP or HTTPS URL.');

            return self::FAILURE;
        }

        $absoluteUrl = static fn (string $path): string => $appUrl.'/'.ltrim($path, '/');

        $urls = [
            $absoluteUrl(route('main', [], false)),
            $absoluteUrl(route('author', [], false)),
            $absoluteUrl(route('project', [], false)),
        ];

        foreach ($poems->poems() as $poem) {
            $viewPath = resource_path(
                "views/poems/{$poem['section']}/{$poem['slug']}.blade.php",
            );

            if (! File::exists($viewPath)) {
                $this->error("Poem view does not exist: {$viewPath}");

                return self::FAILURE;
            }

            $urls[] = $absoluteUrl(
                route('poem', [
                    'section' => $poem['section'],
                    'slug' => $poem['slug'],
                ], false)
            );
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        File::put(public_path('sitemap.xml'), $xml);

        $poemsCount = count($poems->poems());
        $this->info("Success! Pages: 3, Poems: {$poemsCount}");

        return self::SUCCESS;
    }
}
