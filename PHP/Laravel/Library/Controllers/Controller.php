<?php

namespace App\Http\Controllers;

use App\Services\LogsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\StoreFileService;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public $service, $logs;

    public function __construct(StoreFileService $service, LogsService $logs) {
        $this->service = $service;
        $this->logs = $logs;
    }
}
