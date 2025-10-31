<?php

namespace vendor\data_base;

use PDO;
use PDOException;

class DB
{
    protected static ?PDO $pdo = null;

    public static function connect(): void
    {
        if (self::$pdo === null) {
            $dsn = DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";

            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }

    public static function getPdo(): PDO
    {
        self::connect();
        return self::$pdo;
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder();
    }

    public static function manualQuery(string $query): array
    {
        try {
            $stmt = self::getPdo()->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
}