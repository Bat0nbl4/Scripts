<?php

namespace controllers\main;

use controllers\controller;
use vendor\rendering\View;

class MainController extends controller
{
    public function index() {
        $data = [
            "list" => [
                1 => "one",
                2 => "two",
                3 => null,
                4 => "four",
                5 => "five"
            ],
            "text" => "GG WP NT EZ SOLO"
        ];
        View::render("index", $data);
    }
}