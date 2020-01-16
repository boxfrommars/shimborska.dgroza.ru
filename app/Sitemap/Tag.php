<?php

namespace App\Sitemap;

use Carbon\Carbon;

class Tag
{
    /** @var string */
    public $url = '';

    /** @var Carbon */
    public $lastModificationDate;

    /** @var float */
    public $priority = 0.8;

    public function __construct(string $url, Carbon $lastModificationDate, float $priority = 0.8)
    {
        $this->url = $url;
        $this->lastModificationDate = $lastModificationDate;
        $this->priority = $priority;
    }
}
