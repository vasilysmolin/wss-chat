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

start-socket - запуск вебсокета
make start-back - запуск сервера

* `make lint`
* `make test`


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


