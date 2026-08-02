# Deployment requirements: `shimborska`

Этот документ описывает требования приложения и проверяемый результат в
production. Способ размещения, права ОС и административные процедуры в
контракт не входят.

Не добавлять сюда значения секретов, содержимое production-конфигурации,
приватные ключи, сертификаты, базы данных или резервные копии.

## Приложение

| Параметр | Значение |
| --- | --- |
| Название | `shimborska` |
| Репозиторий | `https://github.com/boxfrommars/shimborska.dgroza.ru.git` |
| Production-ветка | `master` |
| Канонический URL | `https://shimborska.dgroza.ru` |
| Тип процесса | stateless HTTP-приложение |
| Runtime | PHP `^8.3`, Laravel `^13` |
| База данных | отсутствует |

## Runtime-интерфейс

- Package manager: Composer 2
- Установка production-зависимостей:

  ```shell
  composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
  ```

- Относительный публичный каталог: `public/`
- Entry point: `public/index.php`
- Frontend-сборка: отсутствует; CSS, изображения и vanilla JS хранятся готовыми
- PHP-расширения для production-установки Composer: `ext-ctype`, `ext-dom`,
  `ext-fileinfo`, `ext-filter`, `ext-hash`, `ext-iconv`, `ext-json`,
  `ext-libxml`, `ext-mbstring`, `ext-openssl`, `ext-pcre`, `ext-session` и
  `ext-tokenizer`
- Для установки dev-зависимостей и `composer check` дополнительно требуются
  `ext-phar`, `ext-xml` и `ext-xmlwriter`
- Ограничения процесса: состояние пользователя между HTTP-запросами не
  сохраняется

Web middleware для cookies, session, CSRF и shared validation errors
намеренно отключены в `bootstrap/app.php`.

## Конфигурация

| Переменная | Обязательна | Секрет | Назначение и production-инвариант |
| --- | ---: | ---: | --- |
| `APP_NAME` | нет | нет | Имя сайта; default `Wislawa Szymborska` |
| `APP_ENV` | да | нет | `production` |
| `APP_KEY` | да | да | Валидный ключ приложения |
| `APP_DEBUG` | да | нет | `false` |
| `APP_URL` | да | нет | `https://shimborska.dgroza.ru` |
| `APP_TIMEZONE` | нет | нет | `UTC` |
| `LOG_CHANNEL` | нет | нет | Поддерживается значение `stack` |
| `LOG_LEVEL` | нет | нет | Уровень, подходящий для production |
| `CACHE_STORE` | нет | нет | `file` |

`APP_URL` является единственным источником origin для sitemap. Переменная
`APP_HTTPS` не поддерживается.

## Состояние и запись

- Writable-пути runtime: `storage/`, `bootstrap/cache/`
- Генерируемый артефакт: `public/sitemap.xml`
- Команда генерации: `php artisan sitemap:generate`
- Постоянные пользовательские данные: отсутствуют
- Пользовательские загрузки: отсутствуют
- База данных: отсутствует
- Требование к отдельной application backup-копии: отсутствует

Содержимое `storage/framework/`, `storage/logs/`, `bootstrap/cache/` и
`public/sitemap.xml` не является пользовательскими данными.
`public/sitemap.xml` воспроизводится из `resources/data/poems.php`, маршрутов и
`APP_URL` и не входит в Git.

## Lifecycle hooks

Hooks выполняются после установки production-зависимостей в указанном порядке.

### Очистка кеша

- Момент выполнения: после установки зависимостей, до остальных hooks
- Команда:

  ```shell
  php artisan optimize:clear
  ```

- Эффект: удаляет кеши предыдущей версии приложения
- Изменяемые пути: `bootstrap/cache/`, `storage/framework/`
- Изменение постоянных данных: отсутствует
- Повторный запуск безопасен: да
- Ошибка блокирует релиз: да

### Генерация sitemap

- Момент выполнения: после очистки кеша, до завершения релиза
- Команда:

  ```shell
  php artisan sitemap:generate
  ```

- Эффект: полностью перезаписывает `public/sitemap.xml`, включая три
  статические страницы и все произведения из каталога с origin из `APP_URL`
- Изменяемые пути: `public/sitemap.xml`
- Изменение постоянных данных: отсутствует
- Повторный запуск безопасен: да
- Ошибка блокирует релиз: да

### Сборка кеша

- Момент выполнения: после генерации sitemap, до завершения релиза
- Команда:

  ```shell
  php artisan optimize
  ```

- Эффект: собирает поддерживаемые Laravel production-кеши
- Изменяемые пути: `bootstrap/cache/`, `storage/framework/`
- Изменение постоянных данных: отсутствует
- Повторный запуск безопасен: да
- Ошибка блокирует релиз: да

## Изменения данных

- Миграции: отсутствуют
- Maintenance mode: не требуется
- Ожидаемая недоступность: отсутствует
- Совместимость старой и новой версии с данными: не применимо

## Внешнее поведение

- Канонический URL: `https://shimborska.dgroza.ru`
- Дополнительные имена: отсутствуют
- HTTP-запрос должен постоянно перенаправляться на тот же path и query string
  канонического HTTPS URL
- `GET /up` возвращает `200`
- `GET /{section}` возвращает временный redirect на первое стихотворение
  существующего раздела
- `GET /different/little-girl-pull-tablecloth` возвращает постоянный redirect
  на `/moment/little-girl-pull-tablecloth`
- `GET /different/about-soul` возвращает постоянный redirect
  на `/moment/about-soul`
- `GET /different/in-park` возвращает постоянный redirect
  на `/moment/in-park`
- Неизвестный HTML URL и неправильная пара `section/slug` возвращают фирменную
  страницу с HTTP-статусом `404`
- Неизвестный JSON-запрос возвращает стандартный JSON 404 Laravel

Ключевые smoke-сценарии:

| Метод и путь | Ожидаемый результат |
| --- | --- |
| `GET /` | `200`, главная страница |
| `GET /up` | `200` |
| `GET /project` | `200` |
| `GET /author` | `200` |
| `GET /different/two-monkeys` | `200`, страница стихотворения |
| `GET /different/little-girl-pull-tablecloth` | `301` на `/moment/little-girl-pull-tablecloth` |
| `GET /moment/little-girl-pull-tablecloth` | `200`, страница стихотворения |
| `GET /different/about-soul` | `301` на `/moment/about-soul` |
| `GET /moment/about-soul` | `200`, страница стихотворения |
| `GET /different/in-park` | `301` на `/moment/in-park` |
| `GET /moment/in-park` | `200`, страница стихотворения |
| `GET /semicolon/two-monkeys` | фирменный HTML `404` |
| `GET /unknown`, `Accept: application/json` | JSON `404` |
| `GET /sitemap.xml` | `200`, актуальные HTTPS URL |
| HTTP `GET /project?source=smoke` | `301` на тот же path и query по каноническому HTTPS URL |

## Проверка перед релизом

Проверка исходного кода выполняется в отдельном окружении с установленными
dev-зависимостями:

```shell
composer check
composer sitemap
```

`composer check` выполняет строгую валидацию Composer, security audit, проверку
стиля PHP без изменения файлов и PHPUnit. После изменений layout, CSS или JS
дополнительно проверить desktop и mobile, диалог содержания, короткую и длинную
страницу и отсутствие ошибок в консоли.

`composer check` не является runtime hook и не запускается после production-
установки с `--no-dev`. После deployment выполняются lifecycle hooks и smoke-
сценарии из раздела «Внешнее поведение».

## Совместимость rollback

- Предыдущую версию кода можно вернуть без восстановления данных
- После возврата кода выполнить:

  ```shell
  composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
  php artisan optimize:clear
  php artisan sitemap:generate
  php artisan optimize
  ```

- Восстановление данных: не требуется
- Несовместимые изменения: смена major-версии PHP или Laravel требует
  отдельной проверки окружения
- После возврата выполнить те же smoke-сценарии

## Когда обновлять этот документ

Обновить `DEPLOYMENT.md`, если меняются runtime, production-зависимости,
переменные окружения, публичный интерфейс, writable-потребности, lifecycle
hooks, внешнее поведение или совместимость rollback.

Обычное изменение контента или исправление кода документа не затрагивает,
если требования приложения остаются прежними.
