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
        ]);
    }

    public function poem($parent, $code)
    {
        $poem = $this->poemBook->getPoem($code);

        if (!$poem) {
            abort(404);
        }

        return $this->page("poems.{$parent}.{$code}", $code, [
            'title' => "Вислава Шимборская. {$poem['title']}",
        ]);
    }

    public function project()
    {
        return $this->page('project', 'project', [
            'title' => 'Вислава Шимборская. О проекте',
        ]);
    }

    public function author()
    {
        return $this->page('author', 'author', [
            'title' => 'Вислава Шимборская. Об авторе'
        ]);
    }

    protected function page($view, $code, $params)
    {
        return view($view, array_merge(
            [
                'nav' => $this->poemBook->getNavigation($code),
                'toc' => $this->poemBook->getContent(),
                'code' => $code,
                'title' => '',
            ],
            $params
        ));
    }
}
