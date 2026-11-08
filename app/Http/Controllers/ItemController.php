<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request): View
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
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $hasFilters = collect(['q', 'type', 'category_id', 'location_id', 'status', 'date_from'])
            ->contains(fn (string $filter): bool => filled($filters[$filter] ?? null));

        return view('items.index', compact('items', 'categories', 'locations', 'hasFilters'));
    }

    public function show(Item $item): View
    {
        $item->loadMissing('location', 'category');

        return view('items.show', compact('item'));
    }
}
