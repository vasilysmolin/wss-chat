<?php

namespace App\Objects;

class MessageTypes
{
    private const MESSAGE = 'message';
    private const OUT_ROOM = 'out_room';
    private const INTO_ROOM = 'into_room';



    private const UNIT_KEYS = [
        self::MESSAGE,
        self::OUT_ROOM,
        self::INTO_ROOM,
    ];

    public static function all(): array
    {
        return [
            self::MESSAGE => __("message.message"),
            self::OUT_ROOM => __("message.out_room"),
            self::INTO_ROOM => __("message.into_room"),
        ];
    }


    public static function keys(): array
    {
        return self::UNIT_KEYS;
    }

    public static function chat(): string
    {
        return self::MESSAGE;
    }

    public static function intoRoom(): string
    {
        return self::INTO_ROOM;
    }

    public static function outRoom(): string
    {
        return self::OUT_ROOM;
    }
}
