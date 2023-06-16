<?php

namespace App\Console\Commands;

use App\WebSocket\Pusher;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\Chat;

class SocketServerConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket-start {--port=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $port = $this->option('port') ?? config('app.websocket_port');
        $chat = new Chat();
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $chat
                )
            ),
            (int) $port
        );

        $server->run();
    }
}
