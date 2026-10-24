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
- [docs/SEO-MONITORING-2026-09-18.md](docs/SEO-MONITORING-2026-09-18.md)
  — протокол и журнал еженедельного SEO-мониторинга.
- [docs/SEO-DESCRIPTIONS-PILOT-2026-08-03.md](docs/SEO-DESCRIPTIONS-PILOT-2026-08-03.md)
  — архив завершённого пилота descriptions страниц стихотворений.
- [sources/README.md](sources/README.md) — устройство локального архива и
  правила ведения источников.
- [sources/POEMS.md](sources/POEMS.md) — произведения и версии по локальным
  источникам.
- [sources/POEMS-BY-SITE.md](sources/POEMS-BY-SITE.md) — источники по каталогу
  сайта.
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

### VS Code и Dev Containers

VS Code можно подключить к контейнеру приложения, чтобы терминал и проверка
синтаксиса PHP работали в том же окружении, что и сайт. Устанавливать PHP или
Composer в операционную систему для этого не требуется.

1. Запустить Docker Desktop и выполнить начальную установку и запуск из
   раздела выше.
2. Установить расширение Microsoft
   [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).
3. Открыть корень проекта в VS Code, вызвать палитру команд
   (`Ctrl+Shift+P`) и выбрать **Dev Containers: Reopen in Container**.

Конфигурация [.devcontainer/devcontainer.json](.devcontainer/devcontainer.json)
использует существующий `compose.yaml`: VS Code подключается к сервису `app`
и открывает каталог `/app`. Для встроенной проверки синтаксиса задан PHP
внутри контейнера: `/usr/local/bin/php`.

VS Code автоматически устанавливает в контейнер Intelephense для навигации
по PHP-коду и официальное расширение Laravel для поддержки фреймворка и Blade.

Во встроенном терминале подключённого окна команды выполняются напрямую:

```shell
php -v
composer check
```

Остальные Composer-команды из раздела «Проверки» также выполняются без
префикса `docker compose run --rm app`. Сайт остаётся доступен на
[localhost:8000](http://localhost:8000).

Закрытие VS Code оставляет контейнер работающим (`shutdownAction: none`).
Для остановки выполнить `docker compose down` из обычного терминала
операционной системы. После изменения `Dockerfile` или конфигурации Dev
Containers выбрать **Dev Containers: Rebuild Container** в палитре команд.

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

Структуру локального архива и правила ведения карты и индекса источников задаёт
[sources/README.md](sources/README.md), а текущие соответствия находятся в
[sources/POEMS.md](sources/POEMS.md) и
[sources/POEMS-BY-SITE.md](sources/POEMS-BY-SITE.md). Команды запуска и
автоматических проверок остаются в разделах выше.

## Развёртывание

Runtime-интерфейс, конфигурационные требования, lifecycle hooks, внешнее
поведение и совместимость rollback описаны в
[DEPLOYMENT.md](DEPLOYMENT.md). Серверные инструкции и значения production
`.env` в репозитории не хранятся.
