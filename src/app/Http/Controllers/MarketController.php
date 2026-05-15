<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UniversalisService;

class MarketController extends Controller
{
    public function price(Request $request, UniversalisService $service)
    {
        try {
            $validated = $request->validate([
                'world' => ['required', 'string'],
                'item_id' => ['required', 'integer'],
            ]);

            $result = $service->getPrice(
                $validated['world'],
                $validated['item_id']
            );

            if (isset($result['error'])) {
                return response()->json($result, 400);
            }

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => '入力データが正しくありません',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'サーバー内部でエラーが発生しました',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}