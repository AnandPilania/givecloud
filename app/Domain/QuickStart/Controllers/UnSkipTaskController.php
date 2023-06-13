<?php

namespace Ds\Domain\QuickStart\Controllers;

use Ds\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UnSkipTaskController extends Controller
{
    public function __invoke(string $task): JsonResponse
    {
        $class = 'Ds\Domain\QuickStart\Tasks\\' . Str::studly($task);
        if (! class_exists($class)) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        if (! method_exists($class, 'skip')) {
            return response()->json(['error' => 'Task is not skippable'], 500);
        }

        app($class)->unskip();

        return response()->json(['success' => 'true']);
    }
}
