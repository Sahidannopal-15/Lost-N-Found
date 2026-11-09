<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function show(Item $item)
    {
        $item->loadMissing('location', 'category', 'user');

        return view('items.show', compact('item'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('items.create', compact('categories', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['hilang', 'temuan'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'event_date' => ['required', 'date', 'before_or_equal:today'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'title.required' => 'Judul barang belum diisi.',
            'event_date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
            'photo.mimes' => 'Foto harus berformat JPG atau PNG, maksimal 2 MB.',
            'photo.max' => 'Foto harus berformat JPG atau PNG, maksimal 2 MB.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('items', 'public');
        }

        Item::create([
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'location_id' => $data['location_id'],
            'event_date' => $data['event_date'],
            'photo_path' => $photoPath,
            'status' => 'aktif',
        ]);

        return redirect()->route('items.index')->with('sukses', 'Laporan disimpan.');
    }

    public function edit(Item $item)
    {
        $this->authorizeOwner($item);

        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('items.edit', compact('item', 'categories', 'locations'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorizeOwner($item);

        $data = $request->validate([
            'type' => ['required', Rule::in(['hilang', 'temuan'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'event_date' => ['required', 'date', 'before_or_equal:today'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'title.required' => 'Judul barang belum diisi.',
            'event_date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
            'photo.mimes' => 'Foto harus berformat JPG atau PNG, maksimal 2 MB.',
            'photo.max' => 'Foto harus berformat JPG atau PNG, maksimal 2 MB.',
        ]);

        if ($request->hasFile('photo')) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
            $item->photo_path = $request->file('photo')->store('items', 'public');
        }

        $item->update([
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'location_id' => $data['location_id'],
            'event_date' => $data['event_date'],
            'photo_path' => $item->photo_path,
        ]);

        return redirect()->route('items.show', $item)->with('sukses', 'Laporan disimpan.');
    }

    public function destroy(Item $item)
    {
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';

        if (! $isAdmin) {
            $this->authorizeOwner($item);
        }

        if ($item->photo_path) {
            Storage::disk('public')->delete($item->photo_path);
        }

        $item->delete();

        return redirect()->route('items.index')->with('sukses', 'Laporan dihapus.');
    }

    public function selesai(Item $item)
    {
        $this->authorizeOwner($item);

        if ($item->type !== 'hilang') {
            abort(403);
        }

        $item->update(['status' => 'selesai']);

        return back()->with('sukses', 'Laporan ditandai selesai.');
    }

    private function authorizeOwner(Item $item): void
    {
        if (auth()->id() !== $item->user_id) {
            abort(403);
        }

        if ($item->status === 'dalam_klaim') {
            abort(403);
        }
    }
}
