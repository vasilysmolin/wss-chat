<?php

namespace App\Console\Commands;

use App\WebSocket\Chat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisSubscribeConsole extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';

    /**
     * Выполнить консольную команду.
     *
     * @return mixed
     */
    public function handle()
    {
//        Redis::psubscribe(['room-*'], function ($msg) {
//        });
    }
}
