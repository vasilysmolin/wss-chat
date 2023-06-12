<?php

namespace App\WebSocket;

use App\Models\Message;
use App\Models\Room;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Chat implements MessageComponentInterface
{
    protected $clients;
    protected $rooms;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $queryString = $conn->httpRequest->getUri()->getQuery();
        $queryCollect = collect(explode('&',$queryString));
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

        $this->clients->attach($conn);

        $messages = Message::where('user_id', $user->getKey())
            ->where('created_at', '>=', now()->subDay())->get();
        $conn->send($messages);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $message = json_decode($msg, true);

        $room = Room::find($message['room']);
        if ($message['type'] === 'into_room') {
            $from->user->rooms()->syncWithoutDetaching($room);
            $message['msg'] = "{$from->user->name} joined the group ";
        }

        if ($message['type'] === 'out_room') {
            $from->user->rooms()->detach($room);
            $message['msg'] = "{$from->user->name} out the group ";
        }

        $message = Message::create([
            'text' => $message['msg'],
            'room_id' => $message['room'],
            'user_id' => $from->user->getKey(),
        ]);

        $collect = collect($this->clients);

        foreach ($room->users as $user) {
            if ($from->user_id !== $user->getKey()) {
                $client = $collect->where('user_id', $user->getKey())->first();
                if ($client) {
                    $client->send($message);
                }
            }
        }
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
}
