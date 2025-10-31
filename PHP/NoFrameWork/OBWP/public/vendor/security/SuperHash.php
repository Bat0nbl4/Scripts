<?php

namespace vendor\security;

abstract class SuperHash
{
    public static function hashPassword(string $password): string {
        return md5(sha1($password));
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return self::hashPassword($password) === $hash;
    }
}