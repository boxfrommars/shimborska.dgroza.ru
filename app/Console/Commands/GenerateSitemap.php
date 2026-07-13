<?php

namespace App\Console\Commands;

use App\Sitemap\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap.';

    public function handle(): int
    {
        URL::forceRootUrl(config('app.url'));

        if (config('app.https')) {
            URL::forceScheme('https');
        }

        $tags = [
            new Tag(route('main'), Carbon::create(2020, 1, 16), 1),
            new Tag(route('author'), Carbon::create(2020, 1, 16)),
            new Tag(route('project'), Carbon::create(2020, 1, 16)),
        ];

        $poemsCount = 0;

        foreach (File::allFiles(resource_path('views/poems')) as $poem) {
            $parent = str_replace('\\', '/', $poem->getRelativePath());
            $tags[] = new Tag(
                route('poem', [
                    'parent' => $parent,
                    'title' => $poem->getFilenameWithoutExtension(),
                ]),
                Carbon::createFromTimestampUTC($poem->getMTime()),
            );
            $poemsCount++;
        }

        $xml = view('sitemap', ['tags' => $tags])->render();

        File::put(public_path('sitemap.xml'), $xml);

        $this->info("Success! Pages: 3, Poems: {$poemsCount}");

        return self::SUCCESS;
    }
}
