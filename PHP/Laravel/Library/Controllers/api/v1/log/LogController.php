<?php

namespace App\Http\Controllers\api\v1\log;

use App\Http\Controllers\Controller;
use App\Models\Log;

class LogController extends Controller
{
    public function index($id) {
        $log = Log::findOrFail($id);

        if (Log::where([['id', '<', $id],['object_id', $log->object_id]])->first()) {
            $count = Log::where([['id', '<', $id],['object_id', $log->object_id]])->count();
            $prev_log = Log::where([['id', '<', $id],['object_id', $log->object_id]])->skip($count - 1)->first();

        } else {
            $prev_log = null;
        }

        if (Log::where([['id', '>', $id],['object_id', $log->object_id]])->first()) {
            $count = Log::where([['id', '>', $id],['object_id', $log->object_id]])->count();
            $next_log = Log::orderBy('id', 'desc')->where([['id', '>', $id],['object_id', $log->object_id]])->skip($count - 1)->first();
        } else {
            $next_log = null;
        }

        return view('views.web.logs.log', [
            'log' => $log,
            'prev_log' => $prev_log,
            'next_log' => $next_log,
            ]);
    }
}
