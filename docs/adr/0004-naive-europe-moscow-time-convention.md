# Naive Europe/Moscow time convention (без UTC в пайплайне)

Все времена в системе — naive Europe/Moscow. API не передаёт timezone offset.
Бэкенд и фронтенд независимо предполагают MSK через `APP_TIMEZONE`.

## Context

Приложение работает для одного часового пояса (Europe/Moscow). Время передаётся
по API как naive строки (`"HH:mm"`, `"YYYY-MM-DD"`) без указания смещения.
На каждом уровне пайплайна часовой пояс задаётся отдельно:

| Уровень | Механизм |
|---------|----------|
| Backend PHP | `APP_TIMEZONE=Europe/Moscow` → `config/app.php` → все `Carbon::parse()` в MSK |
| API contract | `TimeOfDay` / `CalendarDate` scalar'ы — "naive, implied Europe/Moscow" |
| Frontend | `APP_TIMEZONE = 'Europe/Moscow'` → `nowInAppTz()` через `date-fns-tz` |
| БД PostgreSQL | `timestamp` колонки (`starts_at`/`ends_at`) — naive; `time` колонки — `HH:MM:SS` |
| Display | `Intl.DateTimeFormat` с явным `timeZone: APP_TIMEZONE` |

UTC-конвертации в пайплайне нет. Функция `toUtc()` существовала как заготовка,
но не использовалась — удалена.

## Considered Options

- **Naive Moscow (выбрано)** — простая модель, нет конвертаций, все компоненты
  работают на одной странице. Единый источник истины — `APP_TIMEZONE`.
- **UTC over the wire** — хранить/передавать UTC, конвертировать на UI.
  Отвергнуто: добавляет сложность, нет реальной multi-tz аудитории.
- **IANA timezone в каждом запросе** — избыточно для single-tz приложения.

## Consequences

- Менять `APP_TIMEZONE` = сломать всё: backend и frontend должны совпадать.
- `Europe/Moscow` не имеет DST (UTC+3 круглый год с 2014) → `toZonedTime`
  работает корректно без edge-case'ов перехода зима/лето.
- Если появится реальная multi-timezone потребность — нужна миграция на UTC
  в БД, API и фронтенде.
- PostgreSQL `timestamp` хранит naive значения; Carbon интерпретирует их
  как `APP_TIMEZONE` при чтении.
