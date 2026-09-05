<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category', 'All');

        $categories = collect(['All'])
            ->merge(Post::published()->distinct()->pluck('category')->filter()->sort()->values())
            ->unique();

        $posts = Post::published()
            ->category($category)
            ->latest('published_at')
            ->get();

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $category,
        ]);
    }

    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        return view('blog.show', [
            'post' => $post,
        ]);
    }
}
