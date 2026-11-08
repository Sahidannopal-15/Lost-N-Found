<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['hilang', 'temuan'])],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'location_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['aktif', 'dalam_klaim', 'selesai'])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Item::with('location', 'category');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->whereIn('status', ['aktif', 'dalam_klaim']);
        }

        if (! empty($filters['q'])) {
            $keyword = $filters['q'];
            $query->where(function ($items) use ($keyword): void {
                $items->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('description', 'like', '%'.$keyword.'%');
            });
        }

        foreach (['type', 'category_id', 'location_id'] as $filter) {
            if (isset($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('event_date', '>=', $filters['date_from']);
        }

        $items = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Item $item): array => $this->itemData($item));

        return response()->json($items);
    }

    public function show(int $id): JsonResponse
    {
        $item = Item::with('location', 'category')->find($id);

        if (! $item) {
            return response()->json(['message' => 'Barang tidak ditemukan.'], 404);
        }

        return response()->json($this->itemData($item));
    }

    private function itemData(Item $item): array
    {
        return [
            'id' => $item->id,
            'code' => $item->code,
            'type' => $item->type,
            'title' => $item->title,
            'description' => $item->description,
            'category' => $item->category->name,
            'location' => $item->location->name,
            'event_date' => $item->event_date->toDateString(),
            'photo_url' => $item->photo_path ? asset('storage/'.$item->photo_path) : null,
            'status' => $item->status,
            'created_at' => $item->created_at?->toISOString(),
        ];
    }
}
