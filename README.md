# About Project

Chat from socket redis pub/sub

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

php artisan db:seed - заполнение тестовыми данными user и room

Получаем токены для пользователей и генерим урлы
ws://localhost:8086?token=123
ws://localhost:8087?token=321

Установить клиента в браузере


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


## дополнительно
* `make lint`
* `make test`


