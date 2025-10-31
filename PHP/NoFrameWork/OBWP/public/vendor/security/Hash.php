<?php

abstract class Hash {
    // Алгоритм хеширования (по умолчанию bcrypt)
    private const HASH_ALGO = PASSWORD_DEFAULT;

    // Настройки для хеширования (можно менять)
    private const HASH_OPTIONS = [
        'cost' => 12 // Чем выше, тем безопаснее (но медленнее)
    ];

    /**
     * Хеширует пароль (одностороннее преобразование, нельзя расшифровать).
     * @param string $password Пароль в открытом виде
     * @return string Хеш пароля
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, self::HASH_ALGO, self::HASH_OPTIONS);
    }

    /**
     * Проверяет, соответствует ли пароль хешу.
     * @param string $password Пароль в открытом виде
     * @param string $hash Хеш из базы данных
     * @return bool true, если пароль верный
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Проверяет, нужно ли пересоздать хеш (например, если поменялся алгоритм).
     * @param string $hash Существующий хеш
     * @return bool true, если хеш устарел
     */
    public static function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, self::HASH_ALGO, self::HASH_OPTIONS);
    }

    /**
     * Шифрует данные (AES-256-CBC).
     * @param string $data Данные для шифрования
     * @param string $key Ключ шифрования (должен храниться в секрете)
     * @return string Зашифрованные данные в формате base64
     */
    public static function encryptData(string $data, string $key): string {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Расшифровывает данные (AES-256-CBC).
     * @param string $data Зашифрованные данные в base64
     * @param string $key Ключ шифрования
     * @return string|false Расшифрованные данные или false при ошибке
     */
    public static function decryptData(string $data, string $key) {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}