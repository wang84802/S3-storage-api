<?php
namespace App\Helpers;

use DB;
use Redis;
use Illuminate\Http\Request;

class RedisHelper
{
    public static function lock($key, $value = 1)
    {
        return Redis::connection('seq_db')->set(
            $key,
            $value,
            'PX',
            config('common.redis_timeout'),
            'NX'
        );
    }

    public static function unlock($key, $value = 1)
    {
        $script = '
        if redis.call("GET", KEYS[1]) == ARGV[1] then
        return redis.call("DEL", KEYS[1])
        else
        return 0
        end
        ';

        return Redis::connection('seq_db')
            ->eval($script, 1, $key, $value);
    }
}