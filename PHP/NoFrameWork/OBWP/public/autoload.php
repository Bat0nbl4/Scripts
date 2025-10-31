<?php

function autoloadClasses($directory)
{
    // Получаем список элементов в директории
    $items = scandir($directory);

    // Перебираем элементы
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue; // Пропускаем точки (.) и (..)
        }

        $path = $directory . DIRECTORY_SEPARATOR . $item;

        // Если это файл и он имеет расширение .php, загружаем его
        if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            require_once $path;
        }
    }

    // Теперь перебираем элементы снова, но на этот раз для обработки директорий
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue; // Пропускаем точки (.) и (..)
        }

        $path = $directory . DIRECTORY_SEPARATOR . $item;

        // Если это директория, рекурсивно обрабатываем её
        if (is_dir($path)) {
            autoloadClasses($path);
        }
    }
}

foreach (LOAD_CLASSES_DIRS as $dir) {
    autoloadClasses(__DIR__ . $dir);
}