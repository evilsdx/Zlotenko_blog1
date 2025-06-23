<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::orderBy('title')->get();
        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|min:3|max:200',
            'slug' => 'nullable|max:200',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'required|integer|exists:blog_categories,id',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $item = BlogCategory::create($data);

        return response()->json(['data' => $item], 201);
    }

    public function update(Request $request, $id)
    {
        $item = BlogCategory::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|min:3|max:200',
            'slug' => 'nullable|max:200',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'required|integer|exists:blog_categories,id',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $item->update($data);

        return response()->json(['data' => $item]);
    }

    public function destroy($id)
    {
        $item = BlogCategory::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Категорію видалено']);
    }

    public function show($id)
    {
        $item = BlogCategory::findOrFail($id);
        return response()->json(['data' => $item]);
    }
}
