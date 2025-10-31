<?php

namespace controllers\main;

use controllers\Controller;
use vendor\rendering\View;

class MainController extends Controller
{
    public function index() {
        View::render("index");
    }
}