umechan
=======

Установка
---------

0. Установите `docker` и `docker-composer`
1. Склонируйте репозиторий с проектом: `git clone https://github.com/U-Me-Chan/umechan.git`; перейдите туда: `cd umechan`
2. Скопируйте пример конфигурации проекта: `cp .env.dist .env`, отредактируйте переменные ключа администрирования и публичного адреса до проекта
3. Запустите сборку и разворачивание проекта: `make up`
4. После успешного завершения готово!


Возможные проблемы и фокусы
-------------------

1. Может быть проблема доступа к директории `./data` и её содержимому, выход - выдать такие права: `chmod 777 -R data`.
2. Как накатить дамп? Положи его куда-нибудь, например, в `./data/dumps`; пробрось volume в сервисе `db`, отредактировав файл `docker-compose.prod.yml`; зайди в контейнер СУБД: `docker exec -it umechan-db bash`; накати дамп: `mysql -uroot -proot pissykaka < /path/to/dump/dump.sql`.
3. Дамп filestore нужно просто положить в `./data/files`.
4. Снять дамп БД проекта: `docker exec umechan-db mysqldump -uroot -proot pissykaka > dump.sql`

Для разработчиков
------------------

Собираем бекенд:
```bash
make up-dev
```

Запускаем дев-сервер клиентской части приложения:
```bash
cd frontend && npm i && npm run serve
```
