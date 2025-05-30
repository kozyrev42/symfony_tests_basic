<p align="center"><a href="https://symfony.com" target="_blank">
    <img src="https://symfony.com/logos/symfony_dynamic_01.svg" alt="Symfony Logo">
</a></p>

| Тип теста            | Что проверяет                    | Использует Symfony контейнер?  | Работает с БД?    |
| -------------------- | -------------------------------- | ------------------------------ | ----------------- |
| **Юнит-тест**        | Один класс/метод в изоляции      | ❌                             | ❌ (всё мокается) |
| **Интеграционный**   | Несколько компонентов вместе     | ✅                             | ✅                |
| **Функциональный**   | Всё приложение как "черный ящик" | ✅                             | Может быть ✅     |
| **E2E (end-to-end)** | Поведение системы через UI       | ✅ (обычно через браузер)      | ✅                |

1. Создание нового проекта:
`composer create-project symfony/website-skeleton symfony_tests_basic`

- Запуск встроенного сервера:`php -S localhost:8000 -t public`,
`APP_ENV=dev php -S localhost:8030 -t public public/index.php`

- узнать какой процесс запушен на порту:`sudo lsof -i :8030`

- используя PID остановить процесс:`sudo kill -9 1234`

- добавлен конфиг для дебага `.vscode/launch.json`

2. Написана консольная команда, проверка подключения к базе.

3. Созданы сущности, миграция для трех таблиц: user, post, comment.

4. Сделан функционал:
- создание пользователя
- создание поста
- получение постов, по user_id, с комментариями
- получение поста, по id поста, с комментариями
- создание комментария
- в PostRepository создан метод findByAuthorId

5. Написан тест PostServiceTest, с использованием Моков, для `PostService->createPost`.

Моки — это виртуальные версии зависимостей, с помощью моков мы изолируем тестируемый код от реального окружения (БД, сервисов). Это позволяет точно проверять логику и поведение сервиса без побочных эффектов.

Моки позволяют подменять зависимости в тестах, чтобы изолировать поведение сервиса и не использовать реальную БД.

6. Написан тест InMemoryPostRepository. Интерфейсы - прочувствовал пользу!

In-memory репозитории — это простые реализации, которые хранят данные в массиве. Это альтернатива мокам, позволяющая тестировать поведение, близкое к реальному, но без обращения к БД.

Плюсы: Быстро. Без SQL/Doctrine. Удобно для симуляции сложного поведения без моков.

Если мок — это актёр, играющий роль класса по заранее заданному сценарию,
то in-memory реализация — это реальный класс, который сам действует по логике, но живёт "в песочнице", не трогая настоящую БД.

===================
Выводы, ощутил на себе:

Моки/in-memory → требуют абстракций. Чем больше тестируешь, тем острее ощущается выгода от интерфейсов.

С интерфейсом:
- Контракт формализован. В тесте можно передать любой объект, реализующий этот интерфейс – мок, in-memory, фейк.
- Код зависит от абстракции. Реализацию можно сменить без правок сервиса.
- Тесты устойчивы: сигнатура интерфейса стабильна.

Интерфейсы действительно резко упрощают жизнь, особенно когда начинаешь:

- писать юнит-тесты → нужно подставлять заглушки;
- менять реализацию (БД ↔ in-memory, REST клиент ↔ gRPC и т.д.);
- соблюдать принципы SOLID, прежде всего D = Dependency Inversion.


7. Интеграционные тесты проверяют взаимодействие нескольких компонентов системы вместе.

Когда писать интеграционные тесты?
- Когда нужно проверить работу реальных компонентов в связке (например, Entity + Repository + DB).
- Когда мокать всё слишком сложно и нужен реализм, но не весь стек, как в E2E.

В нашем случае:
- Тест работает с реальной БД (через Doctrine).
- Проверяется, как Entity, Repository и связи между сущностями работают совместно.
- Symfony загружает контейнер, Doctrine, реальные сервисы — это НЕ изолированный юнит-тест.

+ создать бд: s_tests_basic_test (для тестов)
+ .env.test
+ накатим миграции на тестовую базу (тестовая база готова)
bin/console doctrine:schema:create --env=test

// очистка бд от таблиц
bin/console doctrine:schema:drop --full-database --force --env=test
// накат миграции
bin/console doctrine:migrations:migrate --no-interaction --env=test

- Ставим DAMADoctrineTestBundle (если ещё нет)	bash<br>composer require --dev dama/doctrine-test-bundle<br>
В config/bundles.php
DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],	чтобы каждый тест крутился в транзакции и откатывался

- DAMADoctrineTestBundle — 
Этот бандл автоматически оборачивает каждый тест в транзакцию,
и откатывает её после завершения, — база остаётся чистой

- Почему getId() возвращает значение, если в БД "нет" записи?
Doctrine использует внутреннюю транзакцию. При flush():
- EntityManager посылает INSERT в базу.
- База возвращает id.
- Doctrine сохраняет это значение в объекте.
- Но всё это остаётся внутри транзакции, которая потом будет откатана (если ты её явно не коммитишь).


8. Написать Функциональные тесты:
- PostControllerTest
- CommentControllerTest

> Функциональные тесты проверяют работу всего слоя HTTP — от запроса до ответа. Они вызывают реальные контроллеры, проходят через весь Symfony kernel и используют тестовую БД.

> Зачем:
- Проверяют всю цепочку обработки запроса, включая маршрутизацию, контроллеры, сериализацию, DI, валидацию и работу с базой.
- Позволяют убедиться, что эндпоинты работают как ожидается (в отличие от unit/integration, которые не проверяют HTTP).

> Чем отличаются от других:

| Тип теста           | Что проверяет                  | Использует Symfony Kernel |
| ------------------- | ------------------------------ | ------------------------- |
| Unit test           | Только одну функцию/класс      | ❌ Нет                     |
| Integration test    | Связку классов/слоёв           | ✅ Частично                |
| **Functional test** | **Весь HTTP-поток end-to-end** | ✅ Полностью               |


9. Разница между `PostServiceTest` и `PostServiceInMemoryTest`.

> PostServiceTest — с моком репозитория.

✔️ Цель:
Проверить взаимодействие сервиса с репозиторием. Мы следим, был ли вызван метод save(), и с какими аргументами.

📌 Особенности:
- Используем createMock() — это "поддельный" объект, который ничего реально не сохраняет.
- Мы не проверяем данные в репозитории — только то, что метод save() вообще вызвался и с нужным объектом.
- Это скорее unit-тест, потому что тестируется сервис в "изолированной среде", без настоящего поведения репозитория.


> PostServiceInMemoryTest — с настоящим in-memory репозиторием.

✔️ Цель:
Проверить, что:
- Объект реально "сохраняется" в репозитории (пусть и in-memory),
- Его можно потом найти и использовать.

📌 Особенности:
- InMemoryPostRepository — это реальная имплементация интерфейса, без моков.
- Мы не проверяем вызовы методов, но проверяем результат: данные действительно попали в "базу".
- Это уже ближе к интеграционному тесту: сервис работает с настоящей реализацией репозитория.


💬 Вывод
- Тест с моками — для проверки логики взаимодействия: кто кого вызвал и с чем.
- Тест с in-memory репо — для проверки работоспособности системы в целом (но всё ещё без настоящей БД).
- Их полезно иметь оба: первый даёт уверенность, что вызовы идут правильно, второй — что данные на самом деле сохраняются.