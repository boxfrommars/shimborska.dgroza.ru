<?php

function parent_alias($parent)
{
    $aliases = [
        'different' => 'Разные стихотворения',
        'semicolon' => 'Двоеточие',
        'moment' => 'Мгновение',
        'text' => 'Проза',
    ];

    return array_key_exists($parent, $aliases) ? $aliases[$parent] : 'Другое';
}
