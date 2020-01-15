<?php

namespace App;

use League\Csv\Reader;

class PoemBook
{

    public $content;

    public function __construct()
    {
        $reader = Reader::createFromPath(resource_path('data' . DIRECTORY_SEPARATOR . 'index.csv'));
        $records = $reader->setHeaderOffset(0)->getRecords();
        $this->content = iterator_to_array($records, false);
    }

    public function getContent()
    {
        $content = [];

        foreach ($this->content as $key => $value) {
            if ($value['parent']) {
                $content[$value['parent']][] = $value;
            }
        }

        return $content;
    }

    public function getNavigation($title)
    {
        $paginationLeftPadding = 2;
        $paginationRightPadding = 3;
        $paginationSize = $paginationLeftPadding + $paginationRightPadding + 1;

        $lastPage = count($this->content) - 1;

        $key = array_search($title, array_column($this->content, 'href'));

        if ($key === false) {
            $key = -1;
            $offset = 0;
        } else {
            $offset = $key - $paginationLeftPadding;
            $offsetRight = $lastPage - ($key + $paginationRightPadding);
            if ($offsetRight < 0) {
                $offset -= abs($offsetRight);
            }
            $offset = max(0, $offset);
        }

        $content = array_slice($this->content, $offset, $paginationSize, true);

        return ['content' => $content, 'current_key' => $key];
    }

    public function getPoem($title)
    {
        $key = array_search($title, array_column($this->content, 'href'));

        return $key !== false ? $this->content[$key] : false;
    }
}
