# shimborska.dgroza.ru

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/badges/build.png?b=master)](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/build-status/master)

Сайт, посвящённый польской поэтессе Виславе Шимборской:
[shimborska.dgroza.ru](https://shimborska.dgroza.ru).

Проект работает на PHP 8.3 и Laravel 13, не использует базу данных и не
требует frontend-сборки.

## Локальная разработка

Для запуска требуется Docker Desktop:

```shell
docker compose build
docker compose run --rm app composer install
docker compose up -d
```

После запуска сайт доступен на [localhost:8000](http://localhost:8000).

Остановить контейнер:

```shell
docker compose down
```

## Проверки

Полная автоматическая проверка выполняет строгую валидацию Composer, security
audit и PHPUnit:

```shell
docker compose run --rm app composer check
```

Сгенерировать локальный `public/sitemap.xml`:

```shell
docker compose run --rm app composer sitemap
```

Sitemap является генерируемым файлом и не входит в Git.

## Добавление стихотворения

1. Добавить `slug` и `title` в нужный раздел
   `resources/data/poems.php`. Положение записи определяет содержание и
   постраничную навигацию.
2. Добавить соответствующий Blade-шаблон
   `resources/views/poems/{section}/{slug}.blade.php`.
3. Обновить ожидаемые количества каталога и URL в `tests/SiteTest.php`.
4. Выполнить обе команды из раздела «Проверки».

Не изменять механически орфографию, пунктуацию, переводы и польские оригиналы.

## Развёртывание

Runtime-интерфейс, конфигурационные требования, lifecycle hooks, внешнее
поведение и совместимость rollback описаны в
[DEPLOYMENT.md](DEPLOYMENT.md). Серверные инструкции и значения production
`.env` в репозитории не хранятся.
