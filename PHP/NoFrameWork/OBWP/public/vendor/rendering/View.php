<?php

namespace vendor\rendering;

abstract class View
{
    protected static string $temp = BASE_TEMPLATE;
    protected static string $title_section = "title";
    protected static string $title = "";
    protected static string $content_section = "content";
    protected static array $context = [];

    public static function content(string $section_name = null) : void {
        if ($section_name) {
            self::$content_section = $section_name;
        }
        echo "{{!".self::$content_section."!}}";
    }

    public static function template(string $template = null) : void {
        if ($template) {
            self::$temp = $template;
        }
    }

    public static function title_section(string $section_name = null) : void {
        if ($section_name) {
            self::$title_section = $section_name;
        }
        echo "{{!".self::$title_section."!}}";
    }

    public static function title(string $page_name = null) : void {
        if ($page_name) {
            self::$title = $page_name;
        }
    }

    public static function IncludeComponent(string $path, array $data = []) : void
    {
        $dir = $_SERVER["BASE_DIR"].COMPONENTS_DIR."/".$path.".php";
        if (file_exists($dir)) {
            extract(self::$context);
            extract($data);
            include $dir;
        } else {
            echo "No such file: ".$dir;
        }
    }

    public static function render(string $view, array $data = []) : void
    {
        if (!empty($data)) {
            self::$context = $data;
            extract($data);
        }

        // Подключение темплейта
        $dir = $_SERVER["BASE_DIR"].TEMPLATES_DIR."/".self::$temp.".php";
        if (file_exists($dir)) {
            ob_start();
            include $dir;
            $output = ob_get_clean();

        } else {
            echo "No such template: ".$dir;
        }

        // Загрузка страницы
        $dir = $_SERVER["BASE_DIR"].VIEW_DIR."/".$view.".php";
        if (file_exists($dir)) {
            ob_start();
            include $dir;
            $v = ob_get_clean();
            $output = str_replace('{{!' . self::$content_section . '!}}', $v, $output);
        } else {
            echo "No such view: ".$dir;
        }

        // название страницы
        $output = str_replace('{{!' . self::$title_section . '!}}', self::$title, $output);

        echo $output;
    }
}