<?php
namespace Dropblog\Utils;
                 
use Dropblog\Config;
use Hashids\Hashids;

class HashIdHelper {
    private static ?Hashids $hashids = null;

    private static function getHashids(): Hashids {
        if (self::$hashids === null) {
            self::$hashids = new Hashids(Config::hashidsSalt(), 8, '0123456789abcdefghijklmnopqrstuvwxyz'); 
        }
        return self::$hashids;
    }

    public static function encode(int $id): string {
        return self::getHashids()->encode($id);
    }

    public static function decode(string $hash): ?int {
        $decoded = self::getHashids()->decode($hash);
        return $decoded[0] ?? null;
    }
}
