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
`php artisan key:generate` after creating the `.env` file.

## https://shimborska.dgroza.ru

Сайт посвящённый польской поэтессе Виславе Шимборской
