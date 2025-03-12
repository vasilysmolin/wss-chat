<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis\Async\Client;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\Chat;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

class WebSocketStart extends Command
{
    protected $signature = 'websocket-start {--port=}';
    protected $description = 'Start Ratchet WebSocket server';

    public function handle()
    {
        $port = (int)$this->option('port');

        $loop = LoopFactory::create();
        $options = [
            'eventloop' => $loop,
        ];
        $redisClient = new Client([
            'scheme'   => 'tcp',
            'host'     => '127.0.0.1',
            'port'     => 6579,
            'password' => 'siteWorld',
        ], $options);
        $component = new HttpServer(new WsServer(new Chat($loop)));

        $socket = new Reactor('0.0.0.0:' . $port, $loop);

        new IoServer($component, $socket, $loop);
        $redisClient->getEventLoop()->run();

    }
}
