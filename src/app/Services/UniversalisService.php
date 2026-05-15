<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UniversalisService
{
    public function getPrice(string $world, int $itemId): array
    {
        $url = "https://universalis.app/api/v2/{$world}/{$itemId}";

        $response = Http::timeout(10)->get($url);

        if (! $response->successful()) {
            return [
                'error' => '外部APIの取得に失敗しました',
                'status' => $response->status(),
            ];
        }

        $data = $response->json();

        return [
            'world' => $world,
            'item_id' => $itemId,
            'itemID' => $data['itemID'] ?? $itemId,
            'minPriceNQ' => $data['minPriceNQ'] ?? 0,
            'minPriceHQ' => $data['minPriceHQ'] ?? 0,
            'averagePrice' => $data['averagePrice'] ?? 0,
            'listings' => collect($data['listings'] ?? [])->map(function ($l) {
                return [
                    'hq' => $l['hq'] ?? false,
                    'pricePerUnit' => $l['pricePerUnit'] ?? 0,
                    'quantity' => $l['quantity'] ?? 0,
                    'total' => $l['total'] ?? 0,
                    'retainerName' => $l['retainerName'] ?? 'Unknown',
                ];
            })->toArray(),
            'lastUploadTime' => $data['lastUploadTime'] ?? null,
        ];
    }
}
