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
- [sources/README.md](sources/README.md) — устройство локального архива и
  правила ведения источников.
- [sources/POEMS.md](sources/POEMS.md) — текущая карта произведений и локальных
  источников.
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

Для точечной сверки текста уже существующего произведения полный набор можно
отложить до контрольной точки. Критерии ускоренного режима, обязательные
целевые проверки и условия запуска полного набора описаны в
[docs/OCR.md](docs/OCR.md#ускоренная-проверка-точечной-правки).

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

## Работа с произведениями

Порядок добавления и переноса произведений, контентные ограничения и требуемые
проверки описаны в [docs/CONTENT.md](docs/CONTENT.md). Для работы по фотографиям
или сканам использовать восстанавливаемый процесс из
[docs/OCR.md](docs/OCR.md).

Структуру локального архива и правила ведения карты источников задаёт
[sources/README.md](sources/README.md), а текущие соответствия находятся в
[sources/POEMS.md](sources/POEMS.md). Команды запуска и автоматических проверок
остаются в разделах выше.

## Развёртывание

Runtime-интерфейс, конфигурационные требования, lifecycle hooks, внешнее
поведение и совместимость rollback описаны в
[DEPLOYMENT.md](DEPLOYMENT.md). Серверные инструкции и значения production
`.env` в репозитории не хранятся.
