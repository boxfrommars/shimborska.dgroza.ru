<?php

namespace App\Console\Commands;

use App\Sitemap\Tag;
use Illuminate\Console\Command;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Carbon\Carbon;
use Throwable;

class GenerateSitemap extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.';

    /**
     * Execute the console command.
     * @throws Throwable
     */
    public function handle()
    {
        app('url')->forceRootUrl(env('APP_URL'));
        $isSecure = (boolean) env('APP_HTTPS');

        $adapter = new Local(resource_path('views' . DIRECTORY_SEPARATOR . 'poems'));
        $filesystem = new Filesystem($adapter);
        $poems = $filesystem->listContents('', true);

        $tags = [];

        $tags[] = new Tag(route('main', [], $isSecure), Carbon::create(2020, 1, 16), 1);
        $tags[] = new Tag(route('author', [], $isSecure), Carbon::create(2020, 1, 16));
        $tags[] = new Tag(route('project', [], $isSecure), Carbon::create(2020, 1, 16));

        $pages_count = 3;
        $poems_count = 0;

        foreach ($poems as $poem) {
            if ($poem['type'] !== 'dir') {
                $url = route('poem', [
                    'parent' => $poem['dirname'],
                    'title' => explode('.', $poem['filename'])[0]
                ], $isSecure);
                $tags[] = new Tag($url, Carbon::createFromTimestampUTC($poem['timestamp']));
                $poems_count++;
            }
        }

        $xml = view('sitemap', [
            'tags' => $tags
        ])->render();

        $this->info("Success! Pages: {$pages_count}, Poems: {$poems_count}");

        file_put_contents(base_path('public' . DIRECTORY_SEPARATOR . 'sitemap.xml'), $xml);
    }
}
