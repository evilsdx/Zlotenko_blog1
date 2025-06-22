<?php
namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Метод для отримання списку блог-постів для API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = BlogPost::with(['user', 'category'])->get();
        return $posts;
    }
    public function paginated(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $posts = BlogPost::with(['user:id,name', 'category:id,title'])
            ->orderBy('id', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'is_published' => $post->is_published,
                'published_at' => $post->published_at ? \Carbon\Carbon::parse($post->published_at)->format('d.M H:i') : '',
                'user' => ['name' => $post->user ? $post->user->name : 'Невідомий автор'],
                'category' => ['title' => $post->category ? $post->category->title : 'Без категорії'],
            ];
        })->values()->toArray();

        return response()->json([
            'data' => $formattedPosts,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'from' => $posts->firstItem(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'to' => $posts->lastItem(),
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
                'id' => $post->user->id,
                'name' => $post->user->name
            ],
            'category' => [
                'id' => $post->category->id,
                'title' => $post->category->title
            ]
        ]);
    }
}
