<?php

namespace App\WebSocket;

use App\Http\Resources\MessagesResource;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use App\Objects\MessageTypes;
use Predis\Async\Client;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Chat implements MessageComponentInterface
{
    protected $clients;      // Храним все локальные WebSocket-подключения
    protected $loop;         // ReactPHP Loop
    protected $redisClient;  // Клиент для Redis

    public function __construct(LoopInterface $loop)
    {
        $this->clients = new \SplObjectStorage();
        $this->loop = $loop;
        $options = [
            'eventloop' => $loop,
        ];
        $redisClient = new Client([
            'scheme'   => 'tcp',
            'host'     => '127.0.0.1',
            'port'     => 6679,
        ], $options);
        $this->redisClient = $redisClient;
        $this->redisClient->connect(function (Client $client) {
            echo "Connected to Redis, now listening for Pub/Sub...\n";

            // Подписываемся на все каналы формата room-*
            $client->pubSubLoop([
                'psubscribe' => 'room-*',
            ], function ($event) {
                // $event->channel — название канала, $event->payload — контент
                echo "Received message from Redis on {$event->channel}\n";

                // Декодируем полезную нагрузку (JSON), которую мы сами будем публиковать
                $data = json_decode($event->payload, true);

                // Рассылаем всем локальным клиентам (всем подключениям на этом экземпляре)
                $this->broadcast($data);
            });
        });

    }

    /**
     * Рассылка локальным WebSocket-клиентам
     */
    public function broadcast(array $message)
    {
        if (empty($message['room_id'])) {
            return; // safety check
        }

        $room = Room::find($message['room_id']);
        if (!$room) {
            return;
        }

        // Собираем все соединения
        $collect = collect($this->clients);

        // Проходимся по всем пользователям комнаты
        foreach ($room->users as $user) {
            // Ищем, есть ли в SplObjectStorage подключение с таким user_id
            $client = $collect->where('user_id', $user->id)->first();
            if ($message['from_user_id'] != $user->getKey()) {
                if ($client) {
                    $client->send(json_encode($message));
                }
            } elseif($message['from_user_id'] === $user->getKey()) {
                if (MessageTypes::showRooms() === $message['type']) {
                    $message = Room::get();
                    if ($client) {
                        $client->send(json_encode($message));
                    }
                }
            }
        }
    }


    public function onOpen(ConnectionInterface $conn)
    {
        $currentConn = $this->checkAuth($conn);
        $this->clients->attach($currentConn);
        $messages = Message::lastDay($currentConn->user)->get();
        $currentConn->send((new MessagesResource($messages))->toJson());

        echo "New connection! ({$currentConn->resourceId})\n";
    }

    /**
     * Когда клиент WebSocket присылает сообщение
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $messageData = json_decode($msg, true);
        if (empty($messageData['room'])) {
            return;
        }

        $room = Room::find($messageData['room']);
        if (!$room) {
            return;
        }

        // Сохраняем сообщение в БД
        if ($messageData['type'] !== MessageTypes::showRooms()) {
            $message = $this->saveMessage($room, $from->user, $messageData);
        }


        // Формируем payload для отправки другим клиентам
        $outgoing = $messageData;
        // В $outgoing должна быть как минимум ['room_id' => .., 'msg' => ..]
        // Допустим, room_id и msg внутри него есть
        $outgoing['room_id'] = $messageData['room'];
        $outgoing['from_user_id'] = $from->user_id;
        $outgoing['type'] = $messageData['type'];

        // Публикуем в Redis (канал - например, room-5)
        $channel = 'room-'. $messageData['room'];;
        // Важно: publish сработает на все экземпляры, подписанные на room-*
        $options = [
            'eventloop' => $this->loop,
        ];
        $redisClient = new Client([
            'scheme'   => 'tcp',
            'host'     => '127.0.0.1',
            'port'     => 6679,
        ], $options);
        $redisClient->publish($channel, json_encode($outgoing));

        echo "New message from user-{$from->user->id} in {$channel}: {$msg}\n";
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
