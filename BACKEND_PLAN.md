Финальный план: backend для Booking Calendar
1. Инфраструктура и размещение
Laravel 11 в backend/ (PHP 8.2).
web/ — существующий React SPA, Vite проксирует /api/v1 на localhost:8000.
Корневой docker-compose.yml: PostgreSQL 15, Adminer, Nginx, Redis.
Makefile с командами: up, down, install, backend-install, frontend-install, dev, backend-test, frontend-test, lint, build.
APP_TIMEZONE=Europe/Moscow, БД хранит naive timestamp.
Cache driver + rate limit store = Redis.
2. Установка зависимостей
Laravel + Filament 3.2 + spatie/laravel-permission + Pest + Pint + Larastan.

3. Модели и миграции
Модель	Ключевые поля	Constraints
User	стандартно	роль super_admin через spatie
Guest	email unique, name, phone	email lower/trim
EventType	name, description, duration_minutes, color, is_active	CHECK duration_minutes IN (15,30), HEX валидация
AvailabilityRule	weekday, start_time, end_time	unique weekday, CHECK start_time < end_time
AvailabilityException	date, is_closed, start_time, end_time	unique date, only is_closed=true
Booking	event_type_id, guest_id, booking_group_id (UUID v7), starts_at, ends_at, comment, status, deleted_at	unique (event_type_id, date, start_time); soft deletes
4. Бизнес-логика
4.1 SlotService
Принимает EventType + Date.
Находит AvailabilityRule для дня недели.
Применяет AvailabilityException (вычитает закрытый интервал).
Округляет end_time вверх до полного часа.
Генерирует слоты длительностью event_type.duration_minutes.
Определяет статус по пересечению с активными бронями (pending/confirmed).
Игнорирует soft-deleted и cancelled брони.
Кеширует результат в Redis на 10 минут с тегом slots:{event_type_id}:{date}.
Инвалидация тега при: создании/отмене брони, изменении правил/исключений/event_type.
4.2 BookingService
Внутри транзакции:
advisory lock по (event_type_id, date).
Повторная проверка занятости слотов.
Проверка последовательности слотов (backend сортирует).
Проверка лимита 2 часа в сутки на гостя.
Guest::firstOrCreate.
Генерация UUID v7 booking_group_id.
Создание N записей Booking со статусом pending.
При ошибках — ApiError с кодами: LIMIT_EXCEEDED, SLOT_TAKEN, SLOT_UNAVAILABLE, SLOT_NOT_SEQUENTIAL, DATE_OUT_OF_RANGE, VALIDATION_ERROR.
4.3 GuestService
firstOrCreate по email.
Не обновляет существующего гостя.
5. Public API (routes/api.php, api middleware, /api/v1)
Метод	Роут	Поведение
GET	/api/v1/event-types	активные, сортировка по id
GET	/api/v1/closed-dates	закрытые даты в горизонте 14 дней
GET	/api/v1/slots	слоты на дату + тип; 404 если дата вне горизонта; пустой список для неактивного типа
GET	/api/v1/guest-bookings	брони гостя по email на дату
POST	/api/v1/bookings	создание брони(ей), 201 + { booking_group_id, status }
CORS: разрешённые origins из .env.
Rate limiting: POST /bookings — 10/min, GET /slots — 10/min (на IP).
Ошибки в формате TypeSpec ApiError, VALIDATION_ERROR → 422, остальные бизнес-ошибки → 400.
6. Filament Admin
Resources: EventType, AvailabilityRule, AvailabilityException, Booking, Guest.
BookingResource:
Табличный вид.
Группировка по booking_group_id с деталями.
Action-кнопки «Подтвердить» / «Отменить».
Фильтры по дате и типу события.
Dashboard: виджеты (брони на сегодня, pending, event types).
Доступ: Gate::before для super_admin.
Не реализуем: JSON-роуты /admin/* из TypeSpec — они покрываются Filament UI.

7. Тестирование
Unit: SlotServiceTest — генерация, исключения, статусы, округление, горизонт.
Feature: все public endpoints, CORS preflight, rate limiting.
Гонки: последовательные запросы с проверкой advisory lock + unique constraint.
Filament: не тестируем.
База: RefreshDatabase + PostgreSQL.
Фабрики: User, Guest, EventType, AvailabilityRule, AvailabilityException, Booking.
Покрытие: среднее.
8. CI/CD
Новый .github/workflows/backend-check.yml.
Шаги: checkout → PHP 8.2 + extensions → PostgreSQL 15 service → composer install → .env.testing → migrate → Pint → Larastan → Pest.
9. Риски и предупреждения
Rate limit GET /slots = 10/min: при активном листании 14 дней пользователь может упереться в лимит. Рекомендую prefetch или поднять до 60/min.
Rounding end_time вверх: может создавать слоты, выходящие за изначальное правило. Это осознанное решение, но важно документировать.
Guest данные не обновляются: если человек сменил имя/телефон, старые данные останутся. Ок для v1.
GET /guest-bookings по email: любой, знающий email, видит чужие брони. Ок для v1, но стоит упомянуть в документации.