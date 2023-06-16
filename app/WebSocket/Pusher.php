<?php

namespace App\WebSocket;

use Illuminate\Support\Facades\Redis;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Pusher implements MessageComponentInterface {
    public function __construct() {
//        $chat = Chat::getInstance();
//        dd($chat->clients);
//        Redis::psubscribe(['room-*'], function ($msg) {
//            Chat::getInstance()->broadcast($msg);
//        });
    }


    public function onOpen(ConnectionInterface $conn)
    {

    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

    }


    public function onClose(ConnectionInterface $conn)
    {

    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {

    }



}
