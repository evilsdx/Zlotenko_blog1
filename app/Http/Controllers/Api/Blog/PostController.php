<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with(['user', 'category'])->get();
        return response()->json($posts);
    }

    public function paginated(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $posts = BlogPost::with(['user:id,name', 'category:id,title'])
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'is_published' => $post->is_published,
                'published_at' => optional($post->published_at)->format('d.M H:i'),
                'user' => ['name' => optional($post->user)->name ?? 'Невідомий автор'],
                'category' => ['title' => optional($post->category)->title ?? 'Без категорії'],
            ];
        });

        return response()->json([
            'data' => $formattedPosts,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $post = BlogPost::with(['user', 'category'])->findOrFail($id);

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content_raw' => $post->content_raw,
            'content' => $post->content,
            'is_published' => $post->is_published,
            'published_at' => $post->published_at,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'user' => [
                'id' => optional($post->user)->id,
                'name' => optional($post->user)->name
            ],
            'category' => [
                'id' => optional($post->category)->id,
                'title' => optional($post->category)->title
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string',
            'content_raw' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'user_id' => 'required|exists:users,id',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $post = BlogPost::create($validated);

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => "nullable|string|max:255|unique:blog_posts,slug,{$post->id}",
            'excerpt' => 'nullable|string',
            'content_raw' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'user_id' => 'required|exists:users,id',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);

        $post->delete();

        return response()->json(['message' => 'Пост успішно видалено']);
    }
}
