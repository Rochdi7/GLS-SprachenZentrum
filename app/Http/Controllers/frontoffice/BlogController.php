<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(9);
        $featured = Post::latest()->first();
        $categories = Category::orderBy('name')->get();
        $popular = Post::orderBy('views', 'desc')->take(5)->get();

        return view('frontoffice.blog.blog', [
            'posts'      => $posts,
            'featured'   => $featured,
            'categories' => $categories,
            'popular'    => $popular,
        ]);
    }

    public function details($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->increment('views');

        $recentPosts = Post::latest()->take(5)->get();
        $categories = Category::orderBy('name')->get();

        return view('frontoffice.blog.blog-details', [
            'post'        => $post,
            'recentPosts' => $recentPosts,
            'categories'  => $categories,
        ]);
    }
}
