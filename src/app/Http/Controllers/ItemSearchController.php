<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ItemSearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $name = $validated['name'];
        $page = $validated['page'] ?? 1;
        $limit = 20;
        $debug = [];

        // 1. XIVAPI (v2) を試行
        try {
            $response = Http::timeout(5)->acceptJson()->get('https://v2.xivapi.com/api/search', [
                'sheets' => 'Item',
                'query' => "+Name~\"{$name}\" +ItemSearchCategory>=1",
                'language' => 'ja',
                'limit' => $limit,
                'page' => $page,
                'fields' => 'Name,Icon',
            ]);

            $debug['xivapi_v2_status'] = $response->status();

            if ($response->successful()) {
                $data = $response->json();

                // XIVAPI v2 の全ヒット件数は 'total' または 'results_total' などに含まれる可能性がある
                $totalResults = $data['total'] ?? $data['results_total'] ?? $data['ResultsTotal'] ?? 0;

                $items = collect($data['results'] ?? [])->map(function ($result) {
                    $itemId = $result['row_id'] ?? null;
                    $iconPath = data_get($result, 'fields.Icon.path');

                    $iconUrl = $iconPath
                        ? 'https://v2.xivapi.com/api/asset?path=' . urlencode($iconPath) . '&format=png'
                        : null;

                    return [
                        'id' => $itemId,
                        'name' => data_get($result, 'fields.Name', ''),
                        'icon' => $iconUrl,
                    ];
                })->filter(fn($item) => !is_null($item['id']));

                if ($items->isNotEmpty()) {
                    // has_more の判定をより正確に
                    $hasMore = ($totalResults > ($page * $limit));

                    return response()->json([
                        'items' => $items->values(),
                        'total' => (int)$totalResults,
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'has_more' => $hasMore
                    ]);
                }
                $debug['xivapi_v2_info'] = 'Results empty';
            } else {
                $debug['xivapi_v2_body'] = substr($response->body(), 0, 200);
            }
        } catch (\Exception $e) {
            $debug['xivapi_v2_exception'] = $e->getMessage();
        }

        // 2. フォールバック: Universalis API で検索
        try {
            $univResponse = Http::timeout(5)->get('https://universalis.app/api/v2/search', [
                'query' => $name,
            ]);

            $debug['universalis_status'] = $univResponse->status();

            if ($univResponse->successful()) {
                $univData = $univResponse->json();

                if (!empty($univData)) {
                    $items = collect($univData)->map(function ($item) {
                        $itemId = $item['itemID'] ?? $item['item_id'] ?? null;
                        return [
                            'id' => $itemId,
                            'name' => $item['name'] ?? '不明なアイテム',
                            'icon' => $itemId ? "https://universalis.app/i/{$itemId}" : null,
                        ];
                    })->filter(fn($item) => !is_null($item['id']));

                    if ($items->isNotEmpty()) {
                        return response()->json([
                            'items' => $items->values(),
                            'source' => 'universalis'
                        ]);
                    }
                }
                $debug['universalis_info'] = 'Results empty';
            } else {
                $debug['universalis_body'] = substr($univResponse->body(), 0, 200);
            }
        } catch (\Exception $e) {
            $debug['universalis_exception'] = $e->getMessage();
        }

        // 検索結果が空、またはAPIエラーの場合
        return response()->json([
            'error' => 'アイテム名から候補が見つかりませんでした。',
            'suggestion' => '外部サービスが混み合っているか、アイテム名が正確でない可能性があります。Item ID を直接入力してお試しください。',
            'items' => [],
            'debug' => $debug
        ], 200);
    }
}
