<?php

namespace App\Http\Controllers;

use App\PoemBook;

class MainController extends Controller
{
    /**
     * @var PoemBook
     */
    protected $poemBook;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->poemBook = new PoemBook();
    }

    public function main()
    {
        return $this->page('main', '/', [
            'title' => 'Вислава Шимборская. Стихотворения',
            'main_page' => true,
        ]);
    }

    public function poem($parent, $code)
    {
        $poem = $this->poemBook->getPoem($code);

        if (!$poem) {
            abort(404);
        }

        return $this->page('page', $code, [
            'title' => "Вислава Шимборская. {$poem['title']}",
            'content' => "<h2>{$poem['title']}</h2>\n {$poem['content']}",
            'images' => isset($poem['images']) ? $poem['images'] : false,
            'notes' => isset($poem['notes']) ? $poem['notes'] : false,
        ]);
    }

    public function project()
    {
        return $this->page('project', 'project', [
            'title' => 'Вислава Шимборская. О проекте',
            'aboutproject' => true,
        ]);
    }

    public function author()
    {
        return $this->page('author', 'author', [
            'title' => 'Вислава Шимборская. Об авторе',
            'aboutauthor' => true,
        ]);
    }

    protected function page($view, $code, $params)
    {
        return view($view, array_merge(
            [
                'nav' => $this->poemBook->getNavigation($code),
                'toc' => $this->poemBook->getContent(),
                'tocCurrent' => $code,
                'notes' => false,
                'images' => false,
                'content' => '',
                'main_page' => false,
            ],
            $params
        ));
    }
}
