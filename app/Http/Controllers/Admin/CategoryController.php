<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('facilities')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon ?? 'fa-box',
            'color' => $request->color ?? '#3498db',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        ActivityLog::record('create', 'category', "Membuat kategori: {$category->name}");

        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan.', 'data' => $category]);
    }

    public function edit(Category $kategori)
    {
        return response()->json($kategori);
    }

    public function update(Request $request, Category $kategori)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:categories,code,' . $kategori->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $kategori->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::record('update', 'category', "Memperbarui kategori: {$kategori->name}");

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui.', 'data' => $kategori]);
    }

    public function destroy(Category $kategori)
    {
        if ($kategori->facilities()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak dapat dihapus karena masih memiliki fasilitas.'], 422);
        }

        ActivityLog::record('delete', 'category', "Menghapus kategori: {$kategori->name}");
        $kategori->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }
}
