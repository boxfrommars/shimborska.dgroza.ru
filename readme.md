[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/badges/build.png?b=master)](https://scrutinizer-ci.com/g/boxfrommars/shimborska.dgroza.ru/build-status/master)

## Development

The local environment requires Docker Desktop:

```shell
docker compose build
docker compose run --rm app composer install
docker compose up -d
```

The site is then available at http://localhost:8000.

Run the checks with:

```shell
docker compose run --rm app composer test
docker compose run --rm app composer validate --strict
docker compose run --rm app composer audit
```

Production deployments must provide their own `APP_KEY`; generate one with
`php artisan key:generate` after creating the `.env` file. `APP_URL` must contain
the complete public origin, including the `https://` scheme in production.

## Adding a poem

1. Add its `slug` and `title` to the appropriate section in
   `resources/data/poems.php`. The position in the list controls the table of
   contents and page navigation.
2. Add the matching Blade view at
   `resources/views/poems/{section}/{slug}.blade.php`.
3. Run `composer test` and `php artisan sitemap:generate`.

## https://shimborska.dgroza.ru

Сайт посвящённый польской поэтессе Виславе Шимборской
