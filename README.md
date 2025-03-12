# About Project

Chat from socket

## Требования

* PHP >= 8.1
* Composer >= 2
* make >= 4


## Install and start project
Установка докера и docker-compose

curl -sSL get.docker.com | sh
sudo apt-get install docker-compose

php artisan websocket-start --port=8087 - запуск вебсокета
php artisan websocket-start --port=8086 - запуск вебсокета

Создаем пользователей 1234567 пароль
```
[
  {
    "id": 1,
    "name": "1",
    "email": "vas@mail.ru",
    "email_verified_at": null,
    "password": "$2y$10$..HgWeQuVJtn2btBFosTrePGT/PqnIXzgCEXBPjsOBcg3dIr1tOpu",
    "remember_token": null,
    "created_at": null,
    "updated_at": null
  },
  {
    "id": 2,
    "name": "2",
    "email": "2@mail.ru",
    "email_verified_at": null,
    "password": "$2y$10$..HgWeQuVJtn2btBFosTrePGT/PqnIXzgCEXBPjsOBcg3dIr1tOpu",
    "remember_token": null,
    "created_at": null,
    "updated_at": null
  }
]
```

Получаем токены для пользователей и генерим урлы
ws://localhost:8086?token=123
ws://localhost:8087?token=321

Создаем модели комнат и привязки






## Отправить сообщение
`{
"msg": "Hello",
"type": "message",
"room": 1
}`

## Вступить в группу
`{
"type": "into_room",
"room": 1
}`

## Выйти из группы
`{
"type": "out_room",
"room": 1
}`



* `make lint`
* `make test`


