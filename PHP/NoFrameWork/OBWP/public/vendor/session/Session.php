<?php

namespace vendor\session;

class Session
{
    /**
     * Стартует сессию, если она не активна.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Записывает данные в сессию.
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        self::setNestedValue($_SESSION, $key, $value);
    }

    /**
     * Получает данные из сессии.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return self::getNestedValue($_SESSION, $key, $default);
    }

    /**
     * Получает все данные из сессии.
     */
    public static function all(mixed $default = null): mixed
    {
        self::start();
        return $_SESSION ?? $default;
    }

    /**
     * Удаляет данные из сессии.
     */
    public static function remove(string $key): void
    {
        self::start();
        self::removeNestedKey($_SESSION, $key);
    }

    /**
     * Очищает все данные сессии.
     */
    public static function clear(): void
    {
        self::start();
        session_unset();
    }

    /**
     * Проверяет наличие данных в сессии с поддержкой точечной нотации.
     */
    public static function has(string $key): bool
    {
        self::start();
        return self::hasNestedKey($_SESSION, $key);
    }

    /**
     * Записывает flash-данные (доступны только один раз) с поддержкой точечной нотации.
     */
    public static function flash(string $key, mixed $value): void
    {
        self::start();

        // Инициализируем flash-массив, если его нет
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }

        // Устанавливаем значение с поддержкой точечной нотации
        self::setNestedValue($_SESSION['_flash'], $key, $value);
    }

    /**
     * Получает flash-данные и помечает их для удаления.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        $value = self::getNestedValue($_SESSION['_flash'] ?? [], $key, $default);

        // Помечаем конкретный ключ для удаления (включая точечную нотацию)
        self::markForDeletion($key);

        return $value;
    }

    /**
     * Проверяет наличие flash-данных с поддержкой точечной нотации.
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return self::hasNestedKey($_SESSION['_flash'] ?? [], $key);
    }

    public static function removeFlash(string $key): void
    {
        self::start();
        self::removeNestedKey($_SESSION["_flash"], $key);
    }

    private static function markForDeletion(string $key): void
    {
        if (!isset($_SESSION['_flash_marked'])) {
            $_SESSION['_flash_marked'] = [];
        }
        $_SESSION['_flash_marked'][] = $key;
    }

    /**
     * Удаляет помеченные flash-данные.
     * Вызывайте этот метод после отрисовки страницы (например, в конце Middleware).
     */
    public static function clearFlash(): void
    {
        self::start();

        // Если нет помеченных ключей, выходим
        if (!isset($_SESSION['_flash_marked']) || empty($_SESSION['_flash_marked'])) {
            unset($_SESSION['_flash_marked']);
            return;
        }

        // Если нет flash-данных, просто очищаем помеченные ключи и выходим
        if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
            unset($_SESSION['_flash_marked']);
            return;
        }

        // Удаляем помеченные ключи
        foreach ($_SESSION['_flash_marked'] as $key) {
            self::removeNestedKey($_SESSION['_flash'], $key);
        }

        unset($_SESSION['_flash_marked']);

        // Если flash-массив пуст, удаляем его
        if (empty($_SESSION['_flash'])) {
            unset($_SESSION['_flash']);
        }
    }

    /**
     * Рекурсивно проверяет наличие ключа с поддержкой точечной нотации.
     */
    private static function hasNestedKey(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return false;
            }
            $current = $current[$part];
        }

        return true;
    }

    /**
     * Рекурсивно получает значение с поддержкой точечной нотации.
     */
    private static function getNestedValue(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $default;
            }
            $current = $current[$part];
        }

        return $current;
    }

    /**
     * Рекурсивно устанавливает значение с поддержкой точечной нотации.
     */
    private static function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $part) {
            if ($i === count($keys) - 1) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
    }

    /**
     * Рекурсивно удаляет ключ с поддержкой точечной нотации.
     */
    private static function removeNestedKey(array &$array, string $key): void
    {
        // Если массив пуст или не является массивом, выходим
        if (!is_array($array) || empty($array)) {
            return;
        }

        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return;
            }

            if ($i === count($keys) - 1) {
                unset($current[$part]);
                return;
            }

            $current = &$current[$part];
        }
    }
}