<?php

namespace App\WebSocket;

use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use App\Objects\MessageTypes;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Chat implements MessageComponentInterface
{
    public $clients;
//    public static $instance;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
//        self::$instance = $this;
    }

//    public function getInstance() {
//        return $this;
//    }

    public function broadcast(array $message)
    {
        $room = Room::find($message['room_id']);
        $collect = collect($this->clients);

        foreach ($room->users as $user) {
            $client = $collect->where('user_id', $user->getKey())->first();
            if ($client) {
                $client->send($message);
            }
        }
    }


    public function onOpen(ConnectionInterface $conn)
    {
        $currentConn = $this->checkAuth($conn);
        $this->clients->attach($currentConn);
        $messages = Message::lastDay($currentConn->user)->get();
        $currentConn->send($messages);

        echo "New connection! ({$currentConn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $messageData = json_decode($msg, true);
        $room = Room::find($messageData['room']);
        $collect = collect($this->clients);
//        Redis::publish("room-{$room->getKey()}", $message);


        if (MessageTypes::showRooms() === $messageData['type']) {
            $message = Room::get();
            $from->send($message);
        } else {
            $message = $this->saveMessage($room, $from->user, $messageData);
            foreach ($room->users as $user) {
                if ($from->user_id !== $user->getKey()) {
                    $client = $collect->where('user_id', $user->getKey())->first();
                    if ($client) {
                        $client->send($message);
                    }
                }
            }
        }

//        Redis::psubscribe(['room-*'], function ($msg) {
//            $this->broadcast($msg);
//        });
        echo "New message! ({$msg})\n";
    }


    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        foreach ($this->clients as $client) {
            if ($conn !== $client) {
                $client->send("disconnect to chat {$conn->user->name}");
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function saveMessage(Room $room, User $user, array $messageDate): Message
    {

        switch ($messageDate['type']) {
            case MessageTypes::intoRoom():
                $user->rooms()->syncWithoutDetaching($room);
                $messageDate['msg'] = __('message.into_room', [
                    'userName' => $user->name,
                    'roomName' => $room->name
                ]);
                break;
            case MessageTypes::outRoom():
                $user->rooms()->detach($room);
                $messageDate['msg'] = __('message.out_room', [
                    'userName' => $user->name,
                    'roomName' => $room->name
                ]);
            default:
        }

        return Message::create([
            'text' => $messageDate['msg'],
            'type' => $messageDate['type'],
            'room_id' => $messageDate['room'],
            'user_id' => $user->getKey(),
        ]);
    }

    private function checkAuth(ConnectionInterface $conn): ConnectionInterface
    {
        $queryString = $conn->httpRequest->getUri()->getQuery();
        $queryCollect = collect(explode('&', $queryString));
        $query = $queryCollect->mapWithKeys(function ($item) {
            $param = collect(explode('=', $item));
            return [$param[0] => $param[1]];
        })->all();

        try {
            $user = JWTAuth::setToken($query['token'])->toUser();
        } catch (TokenExpiredException $e) {
            // обработка исключения
        } catch (TokenInvalidException $e) {
            // обработка исключения
        } catch (JWTException $e) {
            // обработка исключения
        }
        $conn->user_id = $user->getKey();
        $conn->user = $user;

        return $conn;
    }
}
