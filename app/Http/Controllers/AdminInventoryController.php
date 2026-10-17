<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;

class AdminInventoryController extends Controller
{
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $item = InventoryItem::query()->create($request->validated());

        return response()->json([
            'message' => 'Inventory item added.',
            'item' => $item,
        ], 201);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->update($request->validated());

        return response()->json([
            'message' => 'Inventory item updated.',
            'item' => $inventoryItem->fresh(),
        ]);
    }
}
