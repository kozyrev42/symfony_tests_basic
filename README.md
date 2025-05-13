<p align="center"><a href="https://symfony.com" target="_blank">
    <img src="https://symfony.com/logos/symfony_dynamic_01.svg" alt="Symfony Logo">
</a></p>

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