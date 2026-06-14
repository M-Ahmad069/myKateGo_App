<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\GroceryListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroceryController extends Controller
{
    public function generate(Request $request, GroceryListService $grocery): JsonResponse
    {
        return response()->json($grocery->forUser($request->user()));
    }
}
