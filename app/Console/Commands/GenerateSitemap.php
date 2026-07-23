<?php

namespace App\Console\Commands;

use App\PoemCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap.';

    public function handle(PoemCatalog $poems): int
    {
        $appUrl = (string) config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);

        URL::forceRootUrl($appUrl);

        if (is_string($scheme)) {
            URL::forceScheme($scheme);
        }

        $tags = [
            [
                'url' => route('main'),
                'lastModificationDate' => Carbon::create(2020, 1, 16),
                'priority' => 1.0,
            ],
            [
                'url' => route('author'),
                'lastModificationDate' => Carbon::create(2020, 1, 16),
                'priority' => 0.8,
            ],
            [
                'url' => route('project'),
                'lastModificationDate' => Carbon::create(2020, 1, 16),
                'priority' => 0.8,
            ],
        ];

        foreach ($poems->poems() as $poem) {
            $viewPath = resource_path(
                "views/poems/{$poem['section']}/{$poem['slug']}.blade.php",
            );

            if (!File::exists($viewPath)) {
                $this->error("Poem view does not exist: {$viewPath}");

                return self::FAILURE;
            }

            $tags[] = [
                'url' => route('poem', [
                    'section' => $poem['section'],
                    'slug' => $poem['slug'],
                ]),
                'lastModificationDate' => Carbon::createFromTimestampUTC(
                    File::lastModified($viewPath),
                ),
                'priority' => 0.8,
            ];
        }

        $xml = view('sitemap', ['tags' => $tags])->render();

        File::put(public_path('sitemap.xml'), $xml);

        $poemsCount = count($poems->poems());
        $this->info("Success! Pages: 3, Poems: {$poemsCount}");

        return self::SUCCESS;
    }
}
