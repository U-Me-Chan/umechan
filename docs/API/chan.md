Chan API
======

## boards
### GET /api/v2/board

Возвращает список досок

Параметр     | Тип           | Обязателен? | По умолчанию    | Описание
-------------|---------------|-------------|-----------------|-------------------------
exclude_tags | array[string] | -           | ['fap', 'test'] | Список исключаемых досок

Пример ответа:
```json

  "payload": {
    "boards": [
      {
        "id": 1,
        "tag": "b",
        "name": "Бред",
        "threads_count": 162,
        "new_posts_count": 36
      },
      {
        "id": 5,
        "tag": "cu",
        "name": "Кулинария",
        "threads_count": 0,
        "new_posts_count": 0
      },
      {
        "id": 7,
        "tag": "l",
        "name": "Литература",
        "threads_count": 0,
        "new_posts_count": 0
      },
      {
        "id": 8,
        "tag": "m",
        "name": "Музыка",
        "threads_count": 0,
        "new_posts_count": 0
      },
      {
        "id": 10,
        "tag": "mod",
        "name": " Модераторская",
        "threads_count": 0,
        "new_posts_count": 0
      },
      {
        "id": 3,
        "tag": "t",
        "name": "Техника молодёжи",
        "threads_count": 0,
        "new_posts_count": 0
      },
      {
        "id": 2,
        "tag": "v",
        "name": "Видеоконтент",
        "threads_count": 0,
        "new_posts_count": 0
      },
      {
        "id": 6,
        "tag": "vg",
        "name": "Видеоигры",
        "threads_count": 0,
        "new_posts_count": 0
      }
    ],
    "count": 8
  },
  "error": null
}
```
### GET /api/v2/board/{tags:[a-z\+]+}
	
Возвращает список нитей с ответами для досок, указанных в URI запроса

Параметр     | Тип           | Обязателен? | По умолчанию    | Описание
-------------|---------------|-------------|-----------------|-------------------------
limit        |    integer    |       -     |       20        | Количество тредов в ответе
offset       |    integer    |       -     |        0        | Смещение относительно первого элемента

Пример ответа:
```json
{
  "payload": {
    "count": 1,
    "posts": [
      {
        "id": 29,
        "poster": "Местный шизик",
        "subject": "Тред разработки браузерного фронтенда pissychan-front",
        "message": "Основной репозиторий: https://github.com/U-Me-Chan/pissychan-front\nЛицензия: WTFPL (Do Whatever The Fuck You Want Public License)\nИнстанс пока один, на данный момент активно поддерживается: http://pissychan.oxore.net/\n\nНадеюсь базу не дропнут и этот будет в некотором смысле багтрекером и просто для обсуждения впечатлений и идей по фронту.\n\nНа данный момент приоритет разработки отдаётся базовым возможностям, которые реализуются серверсайд рендерингом, типа элементарного рендеринга всех полей постов и поддержки API бекенда, но они почти все допилены. Так же сюда входит поддержка консольных браузеров. Второй приоритет отдаётся UX, реализуемому скриптами на стороне браузера (карта ответов, автовставка ссылок на посты в форму ввода и так далее). На третьем месте по приоритету стоит дизайн. А текущего дизайна, я думаю, хватит ещё очень надолго, если не навсегда.\n\nНедавно реализовал ссылки и форматирование таймстампов. В ближайших планах запилить удаление постов, чтобы у Рицки был стимул допилить пароль на удаление постов или вообще выпилить эту фичу кху-ям (я бы предпочёл именно этот вариант), а так же сделать вёрстку под консольный браузер Links.\n",
        "timestamp": 1602671692,
        "board": {
          "id": 3,
          "tag": "t",
          "name": "Техника молодёжи",
          "threads_count": 0,
          "new_posts_count": 0
        },
        "parent_id": null,
        "updated_at": 1720632350,
        "estimate": 1,
        "replies": [
          {
            "id": 27423,
            "poster": "Местный шизик",
            "subject": "",
            "message": "Понял, значит сокращение делай сам.",
            "timestamp": 1660335052,
            "board": {
              "id": 3,
              "tag": "t",
              "name": "Техника молодёжи",
              "threads_count": 0,
              "new_posts_count": 0
            },
            "parent_id": 29,
            "updated_at": 1660335052,
            "estimate": 4,
            "replies": [],
            "replies_count": 0,
            "board_id": 3,
            "truncated_message": "Понял, значит сокращение делай сам.",
            "media": {
              "images": [],
              "youtubes": []
            },
            "datetime": "2022-08-12 20:10:52",
            "is_verify": "yes"
          },
          {
            "id": 27924,
            "poster": "Местный шизик",
            "subject": "",
            "message": "''",
            "timestamp": 1720632296,
            "board": {
              "id": 3,
              "tag": "t",
              "name": "Техника молодёжи",
              "threads_count": 0,
              "new_posts_count": 0
            },
            "parent_id": 29,
            "updated_at": 1720632296,
            "estimate": 0,
            "replies": [],
            "replies_count": 0,
            "board_id": 3,
            "truncated_message": "''",
            "media": {
              "images": [],
              "youtubes": []
            },
            "datetime": "2024-07-10 17:24:56",
            "is_verify": "yes"
          },
          {
            "id": 27925,
            "poster": "Местный шизик",
            "subject": "",
            "message": "''",
            "timestamp": 1720632350,
            "board": {
              "id": 3,
              "tag": "t",
              "name": "Техника молодёжи",
              "threads_count": 0,
              "new_posts_count": 0
            },
            "parent_id": 29,
            "updated_at": 1720632350,
            "estimate": 0,
            "replies": [],
            "replies_count": 0,
            "board_id": 3,
            "truncated_message": "''",
            "media": {
              "images": [],
              "youtubes": []
            },
            "datetime": "2024-07-10 17:25:50",
            "is_verify": "yes"
          }
        ],
        "replies_count": 162,
        "board_id": 3,
        "truncated_message": "Основной репозиторий: https://github.com/U-Me-Chan/pissychan-front\nЛицензия: WTFPL (Do Whatever The Fuck You Want Public License)\nИнстанс пока один, на данный момент активно поддерживается: http://pissychan.oxore.net/\n\nНадеюсь базу не дропнут и этот будет в некотором смысле багтрекером и просто для обсуждения впечатлений и идей по фронту.\n\nНа данный момент приоритет разработки отдаётся базовым возможностям, которые реализуются серверсайд рендерингом, типа элементарного рендеринга всех полей постов и поддержки API бекенда, но они почти все допилены. Так же сюда входит поддержка консольных браузеров. Второй приоритет отдаётся UX, реализуемому скриптами на стороне браузера (карта ответов, автовставка ссылок на посты в форму ввода и так далее). На третьем месте по приоритету стоит дизайн. А текущего дизайна, я думаю, хватит ещё очень надолго, если не навсегда.\n\nНедавно реализовал ссылки и форматирование таймстампов. В ближайших планах запилить удаление постов, чтобы у Рицки был стимул допилить пароль на удаление постов или вообще выпилить эту фичу кху-ям (я бы предпочёл именно этот вариант), а так же сделать вёрстку под консольный браузер Links.\n",
        "media": {
          "images": [],
          "youtubes": []
        },
        "datetime": "2020-10-14 10:34:52",
        "is_verify": "yes"
      }
    ]
  },
  "error": null
}
```
	
## posts
### GET /api/v2/post/{id:[0-9]+}

Возвращает нить и её ответы

```json
{
  "payload": {
    "thread_data": {
      "id": 27927,
      "poster": "Местный шизик",
      "subject": "",
      "message": "sd",
      "timestamp": 1720632459,
      "board": {
        "id": 1,
        "tag": "b",
        "name": "Бред",
        "threads_count": 162,
        "new_posts_count": 36
      },
      "parent_id": null,
      "updated_at": 1720632459,
      "estimate": 0,
      "replies": [],
      "replies_count": 0,
      "board_id": 1,
      "truncated_message": "sd",
      "media": {
        "images": [],
        "youtubes": []
      },
      "datetime": "2024-07-10 17:27:39",
      "is_verify": "yes"
    }
  },
  "error": null
}
```
### POST /api/v2/post
	
Создаёт новую нить, возвращает её идентификатор и пароль для удаления

Параметр     | Тип           | Обязателен? | По умолчанию    | Описание
-------------|---------------|-------------|-----------------|-------------------------
tag          |  string       |      +      |       -         | Тег доски
message      |  string       |      +      |       -         | Сообщение
poster       |  string       |      -      |  'Anonymous'    | Имя постера либо пароль паспорта
subject      |  string       |      -      |       ''        | Заголовок нити

Ответ на запрос без тега доски:
```json
{
  "payload": [],
  "error": {
    "type": "InvalidArgumentException",
    "message": "Не задан тег доски",
    "file": "/var/www/html/src/Posts/Controllers/CreateThread.php",
    "line": 32,
    "trace": [
      "#0 [internal function]: PK\\Posts\\Controllers\\CreateThread->__invoke(Object(PK\\Http\\Request), Array)",
      "#1 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Posts\\Controllers\\CreateThread), Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#3 /var/www/html/index.php(100): PK\\Application->run()",
      "#4 {main}"
    ]
  }
}
```

Ответа на запрос без сообщения:
```json
{
  "payload": [],
  "error": {
    "type": "InvalidArgumentException",
    "message": "Не задано сообщение",
    "file": "/var/www/html/src/Posts/Controllers/CreateThread.php",
    "line": 36,
    "trace": [
      "#0 [internal function]: PK\\Posts\\Controllers\\CreateThread->__invoke(Object(PK\\Http\\Request), Array)",
      "#1 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Posts\\Controllers\\CreateThread), Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#3 /var/www/html/index.php(100): PK\\Application->run()",
      "#4 {main}"
    ]
  }
}
```

Для всех ответов для неудачных запросов HTTP-код состояния 400

Пример удачного ответа:
```json
{
  "payload": {
    "post_id": 27960,
    "password": "4f1058b7751f8b13111e259027bc95901b0d249b26a810da5fbeb38d98583993"
  },
  "error": null
}
```

Код состояния ответа хорошего запроса 201
### PUT /api/v2/post/{id:[0-9]+}

Создаёт ответ на нить, возвращает идентификатор ответа и пароль для удаления

Параметр     | Тип           | Обязателен? | По умолчанию    | Описание
-------------|---------------|-------------|-----------------|-------------------------
message      |  string       |      +      |       -         | Сообщение
poster       |  string       |      -      |  'Anonymous'    | Имя постера либо пароль паспорта
subject      |  string       |      -      |       ''        | Заголовок ответа

Пример ответа при попытке создать без сообщения:
```json 
{
  "payload": [],
  "error": {
    "type": "InvalidArgumentException",
    "message": "Не передано сообщение",
    "file": "/var/www/html/src/Posts/Controllers/CreateReply.php",
    "line": 28,
    "trace": [
      "#0 [internal function]: PK\\Posts\\Controllers\\CreateReply->__invoke(Object(PK\\Http\\Request), Array)",
      "#1 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Posts\\Controllers\\CreateReply), Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#3 /var/www/html/index.php(100): PK\\Application->run()",
      "#4 {main}"
    ]
  }
}
```
HTTP-код состояния 400.

Пример ответа при успешном создании ответа на нить:
```json
{
  "payload": {
    "post_id": 27961,
    "password": "d9da3c235054bddf478153b5e9a2cfd0eb50cb066cc58cb3e1930f06125b4069"
  },
  "error": null
}
```
### DELETE /api/v2/post/{id:[0-9]+}

Удаляет пост(затирает сообщение, постера и заголовок)

Параметр     | Тип           | Обязателен? | По умолчанию    | Описание
-------------|---------------|-------------|-----------------|-------------------------
password     |    string     |       +     |       -         | Пароль поста

Ответ при попытке удалить пост без пароля:
```json
{
  "payload": [],
  "error": {
    "type": "InvalidArgumentException",
    "message": "Укажите пароль для удаления поста",
    "file": "/var/www/html/src/Posts/Controllers/DeletePost.php",
    "line": 23,
    "trace": [
      "#0 [internal function]: PK\\Posts\\Controllers\\DeletePost->__invoke(Object(PK\\Http\\Request), Array)",
      "#1 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Posts\\Controllers\\DeletePost), Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#3 /var/www/html/index.php(100): PK\\Application->run()",
      "#4 {main}"
    ]
  }
}
```
HTTP-код состояния 400.

Ответ при попытке удалить несуществующий пост:
```json
{
  "payload": [],
  "error": null
}
```
HTTP-код состояния 404

Ответ при попытке удалить пост с неверным паролем:
```json
{
  "payload": [],
  "error": null
}
```
HTTP-код состояния 403

Ответ при успешном удалении поста: пустой, код состояния 204

## passports
### GET /api/v2/passport

Возвращает список зарегистрированных имён

```json
{
  "payload": {
    "passports": [
      {
        "name": "69lTblPg"
      },
      {
        "name": "ayavr"
      },
      {
        "name": "nogaems"
      },
      {
        "name": "Oxore"
      },
      {
        "name": "pidor"
      },
      {
        "name": "pissykaker"
      },
      {
        "name": "prince"
      },
      {
        "name": "tes"
      },
      {
        "name": "Местный шизик"
      },
      {
        "name": "П.И. Бурэ"
      }
    ],
    "count": 11
  },
  "error": null
}
```
### POST /api/v2/passport

Регистирует новое имя

Параметр     | Тип           | Обязателен? | По умолчанию    | Описание
-------------|---------------|-------------|-----------------|-------------------------
name         |  string       |     +       |        -        | Отображаемое имя
key          |  string       |     +       |        -        | Пароль

Ответ при попытке запроса без имени:
```json
{
  "payload": [],
  "error": {
    "type": "InvalidArgumentException",
    "message": "Параметр name не передан",
    "file": "/var/www/html/src/Passports/Controllers/CreatePassport.php",
    "line": 22,
    "trace": [
      "#0 [internal function]: PK\\Passports\\Controllers\\CreatePassport->__invoke(Object(PK\\Http\\Request), Array)",
      "#1 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Passports\\Controllers\\CreatePassport), Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#3 /var/www/html/index.php(100): PK\\Application->run()",
      "#4 {main}"
    ]
  }
}
```

Ответ при попытке запроса без пароля:
```json
{
  "payload": [],
  "error": {
    "type": "InvalidArgumentException",
    "message": "Параметр key не передан",
    "file": "/var/www/html/src/Passports/Controllers/CreatePassport.php",
    "line": 32,
    "trace": [
      "#0 [internal function]: PK\\Passports\\Controllers\\CreatePassport->__invoke(Object(PK\\Http\\Request), Array)",
      "#1 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Passports\\Controllers\\CreatePassport), Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#3 /var/www/html/index.php(100): PK\\Application->run()",
      "#4 {main}"
    ]
  }
}
```

Ответ при попытке зарегистрировать существующие имя либо пароль: 
```json
{
  "payload": [],
  "error": {
    "type": "RuntimeException",
    "message": "Нельзя использовать такое имя или пароль",
    "file": "/var/www/html/src/Passports/Repositories/MedooPassportRepository.php",
    "line": 84,
    "trace": [
      "#0 /var/www/html/src/Passports/Controllers/CreatePassport.php(50): PK\\Passports\\Repositories\\MedooPassportRepository->save(Object(PK\\Passports\\Passport\\Passport))",
      "#1 [internal function]: PK\\Passports\\Controllers\\CreatePassport->__invoke(Object(PK\\Http\\Request), Array)",
      "#2 /var/www/html/src/Router.php(44): call_user_func(Object(PK\\Passports\\Controllers\\CreatePassport), Object(PK\\Http\\Request), Array)",
      "#3 /var/www/html/src/Application.php(20): PK\\Router->handle(Object(PK\\Http\\Request))",
      "#4 /var/www/html/index.php(100): PK\\Application->run()",
      "#5 {main}"
    ]
  }
}
```

Для всех неуспешных запросов HTTP-код состояния 400

Ответ при успешном создании:
```json
{
  "payload": [],
  "error": null
}
```
HTTP-код состояния 201
## events
### GET /api/v2/events

Возвращает список событий

Параметр       | Тип           | Обязателен? | По умолчанию    | Описание
-------------  |---------------|-------------|-----------------|-------------------------
from_timestamp |      integer  |     -       |        0        | unixtime-метка, с которой возвращать события
limit          |      integer  |     -       |       20        | Количество событий в ответе
offset         |      integer  |     -       |        0        | Смещение относительно первого элемента

Ответ:
```json
{
  "payload": {
    "count": 0,
    "events": [
      {
        "id": 93,
        "type": "PostDeleted",
        "timestamp": "18-07-2024 11:16:11",
        "post_id": 27961,
        "board_id": null
      },
      {
        "id": 89,
        "type": "BoardUpdateTriggered",
        "timestamp": "18-07-2024 11:06:27",
        "post_id": null,
        "board_id": 1
      },
      {
        "id": 74,
        "type": "PostCreated",
        "timestamp": "11-07-2024 07:23:07",
        "post_id": 27953,
        "board_id": null
      },
      {
        "id": 75,
        "type": "ThreadUpdateTriggered",
        "timestamp": "11-07-2024 07:23:07",
        "post_id": 27952,
        "board_id": null
      }
    ]
  },
  "error": null
}
```
