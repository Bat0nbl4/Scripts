<?php

function autoloadClasses($directory)
{
    // Перебираем все поддиректории в указанной директории
    foreach (new DirectoryIterator($directory) as $item) {
        if (!$item->isDot()) { // Пропускаем точки (.) и (..)
            if ($item->isDir()) {
                // Рекурсивно обрабатываем вложенные директории
                autoloadClasses($item->getRealPath());
            } else {
                // Загрузка файла, если он является PHP-файлом
                if (pathinfo($item->getFilename(), PATHINFO_EXTENSION) == 'php') {
                    require_once $item->getRealPath();
                }
            }
        }
    }
}

foreach (LOAD_CLASSES_DIRS as $dir) {
    autoloadClasses(__DIR__ . $dir);
}