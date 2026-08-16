# shimborska.dgroza.ru

Сайт, посвящённый польской поэтессе Виславе Шимборской:
[shimborska.dgroza.ru](https://shimborska.dgroza.ru).

Проект работает на PHP 8.3 и Laravel 13, не использует базу данных и не
требует frontend-сборки.

## Документация

- [AGENTS.md](AGENTS.md) — постоянные правила работы с репозиторием.
- [docs/CONTENT.md](docs/CONTENT.md) — устройство каталога и изменение произведений.
- [docs/OCR.md](docs/OCR.md) — распознавание произведений по снимкам и
  восстановление прерванной задачи.
- [docs/FRONTEND.md](docs/FRONTEND.md) — frontend-контракты и ручные проверки.
- [DEPLOYMENT.md](DEPLOYMENT.md) — переносимые production-требования приложения.

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
audit, проверку стиля PHP без изменения файлов и PHPUnit:

```shell
docker compose run --rm app composer check
```

Проверить только стиль PHP без изменения файлов:

```shell
docker compose run --rm app composer lint
```

Автоматическое исправление стиля не запускается проверками и доступно только
по отдельной ручной команде:

```shell
docker compose run --rm app composer format
```

Сгенерировать локальный `public/sitemap.xml`:

```shell
docker compose run --rm app composer sitemap
```

Sitemap является генерируемым файлом и не входит в Git.

## Добавление стихотворения

1. Прочитать полный порядок действий и контентные ограничения в
   [docs/CONTENT.md](docs/CONTENT.md).
2. Если источником служат снимки, следовать [docs/OCR.md](docs/OCR.md) и до
   распознавания создать локальный журнал `sources/PROGRESS.md`.
3. Добавить запись в `resources/data/poems.php` и соответствующий Blade-шаблон
   `resources/views/poems/{section}/{slug}.blade.php`.
4. Обновить затронутые ожидания в `tests/SiteTest.php`.
5. Выполнить `docker compose run --rm app composer check` и
   `docker compose run --rm app composer sitemap`.

Не изменять механически орфографию, пунктуацию, переводы и польские оригиналы.

## Развёртывание

Runtime-интерфейс, конфигурационные требования, lifecycle hooks, внешнее
поведение и совместимость rollback описаны в
[DEPLOYMENT.md](DEPLOYMENT.md). Серверные инструкции и значения production
`.env` в репозитории не хранятся.
